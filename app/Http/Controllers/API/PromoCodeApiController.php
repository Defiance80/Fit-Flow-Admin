<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Cart;
use App\Models\PromoCodeCourse;
use App\Models\Course\Course;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PromoCodeApiController extends Controller
{
  public function getPromoCodesByCourse(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }

        $courseIds = $request->course_ids;

        // 1. Get instructor promo codes linked to the course
        $instructorPromoCodes = PromoCode::whereHas('courses', function ($query) use ($courseIds) {
                $query->whereIn('course_id', $courseIds);
            })
            ->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->whereHas('user.roles', function ($q) {
                $q->where('name', 'instructor');
            });

        // 2. Get admin promo codes (not bound to courses)
        $adminPromoCodes = PromoCode::where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->whereHas('user.roles', function ($q) {
                $q->where('name', 'admin');
            });

        // 3. Combine both using union
        $promoCodes = $instructorPromoCodes->union($adminPromoCodes)->get();

        return ApiResponseService::successResponse('Promo codes fetched successfully', $promoCodes);

    } catch (\Exception $e) {
        ApiResponseService::logErrorResponse($e, 'Failed to fetch promo codes');
        return ApiResponseService::errorResponse('Something went wrong while fetching promo codes.');
    }
}

    /**
     * Get instructor promo codes for a single course (Instructor Only)
     */
    public function getPromoCodesForCourse(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            $courseId = $request->course_id;

            // Get course details
            $course = Course::with('user')->find($courseId);

            // Get ONLY instructor promo codes linked to this specific course
            $instructorPromoCodes = PromoCode::whereHas('courses', function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                })
                ->where('status', 1)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('user_id', '!=', 1) // Exclude admin promo codes (user_id != 1)
                ->with(['user:id,name,email', 'courses' => function($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                }])
                ->get()
                ->map(function ($promo) {
                    return [
                        'id' => $promo->id,
                        'promo_code' => $promo->promo_code,
                        'message' => $promo->message,
                        'discount' => $promo->discount,
                        'discount_type' => $promo->discount_type,
                        'max_discount_amount' => $promo->max_discount_amount,
                        'minimum_order_amount' => $promo->minimum_order_amount,
                        'start_date' => $promo->start_date,
                        'end_date' => $promo->end_date,
                        'instructor_name' => $promo->user->name,
                        'instructor_email' => $promo->user->email,
                        'no_of_users' => $promo->no_of_users,
                    ];
                });

            $responseData = [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'price' => $course->price,
                    'discount_price' => $course->discount_price,
                    'instructor_name' => $course->user->name ?? null,
                ],
                'promo_codes' => $instructorPromoCodes,
                'total_codes' => $instructorPromoCodes->count(),
            ];

            return ApiResponseService::successResponse(
                'Instructor promo codes for course fetched successfully',
                $responseData
            );

        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to fetch promo codes for course');
            return ApiResponseService::errorResponse('Something went wrong while fetching promo codes.');
        }
    }

    /**
     * Calculate discount for a course with multiple promo codes
     */
    public function calculateCourseDiscount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'promo_code_ids' => 'required|array|min:1',
                'promo_code_ids.*' => 'exists:promo_codes,id',
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            $courseId = $request->course_id;
            $promoCodeIds = $request->promo_code_ids;

            // Get course details
            $course = Course::find($courseId);
            $originalPrice = $course->discount_price ?? $course->price ?? 0;

            if ($originalPrice <= 0) {
                return ApiResponseService::errorResponse('Course price not available');
            }

            // Get promo codes with their details
            $promoCodes = PromoCode::whereIn('id', $promoCodeIds)
                ->where('status', 1)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with(['user.roles', 'courses'])
                ->get();

            if ($promoCodes->isEmpty()) {
                return ApiResponseService::errorResponse('No valid promo codes found');
            }

            // Validate promo codes are applicable to this course
            $validPromoCodes = [];
            $adminCodes = [];
            $instructorCodes = [];

            foreach ($promoCodes as $promo) {
                $isAdmin = $promo->user->roles->contains('name', 'Admin');
                $isInstructor = $promo->user->roles->contains('name', 'Instructor');

                if ($isAdmin) {
                    // Admin codes apply to all courses
                    $adminCodes[] = $promo;
                    $validPromoCodes[] = $promo;
                } elseif ($isInstructor) {
                    // Check if instructor owns this course
                    $instructorCourses = $promo->courses->pluck('id')->toArray();
                    if (in_array($courseId, $instructorCourses)) {
                        $instructorCodes[] = $promo;
                        $validPromoCodes[] = $promo;
                    }
                }
            }

            // Business rule: Cannot use admin and instructor codes together
            if (!empty($adminCodes) && !empty($instructorCodes)) {
                return ApiResponseService::errorResponse(
                    'Admin and instructor promo codes cannot be used together. Please choose either admin codes or instructor codes.'
                );
            }

            if (empty($validPromoCodes)) {
                return ApiResponseService::errorResponse('None of the selected promo codes are applicable to this course');
            }

            // Calculate total discount
            $totalDiscountAmount = 0;
            $appliedPromoCodes = [];
            $currentPrice = $originalPrice;

            foreach ($validPromoCodes as $promo) {
                $discountAmount = 0;

                // Check minimum order amount
                if ($promo->minimum_order_amount > 0 && $currentPrice < $promo->minimum_order_amount) {
                    continue; // Skip this promo code
                }

                // Calculate discount based on type
                if ($promo->discount_type === 'amount') {
                    // Fixed amount discount
                    $discountAmount = $promo->discount;
                } elseif ($promo->discount_type === 'percentage') {
                    // Percentage discount
                    $discountAmount = ($currentPrice * $promo->discount) / 100;

                    // Apply max discount limit if set
                    if ($promo->max_discount_amount > 0) {
                        $discountAmount = min($discountAmount, $promo->max_discount_amount);
                    }
                }

                // Apply discount to current price
                $newPrice = max(0, $currentPrice - $discountAmount);
                $actualDiscount = $currentPrice - $newPrice;

                if ($actualDiscount > 0) {
                    $appliedPromoCodes[] = [
                        'id' => $promo->id,
                        'promo_code' => $promo->promo_code,
                        'message' => $promo->message,
                        'discount_type' => $promo->discount_type,
                        'discount_value' => $promo->discount,
                        'discount_amount' => $actualDiscount,
                        'minimum_order_amount' => $promo->minimum_order_amount,
                        'max_discount_amount' => $promo->max_discount_amount,
                        'created_by' => $promo->user->roles->contains('name', 'admin') ? 'admin' : 'instructor',
                        'creator_name' => $promo->user->name,
                    ];

                    $totalDiscountAmount += $actualDiscount;
                    $currentPrice = $newPrice;
                }
            }

            $finalPrice = max(0, $originalPrice - $totalDiscountAmount);
            $totalDiscountPercentage = $originalPrice > 0 ? (($totalDiscountAmount / $originalPrice) * 100) : 0;

            $responseData = [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'original_price' => $originalPrice,
                    'final_price' => $finalPrice,
                    'total_discount_amount' => $totalDiscountAmount,
                    'total_discount_percentage' => round($totalDiscountPercentage, 2),
                ],
                'applied_promo_codes' => $appliedPromoCodes,
                'calculation_summary' => [
                    'original_price' => $originalPrice,
                    'total_discount' => $totalDiscountAmount,
                    'final_price' => $finalPrice,
                    'discount_percentage' => round($totalDiscountPercentage, 2),
                    'promo_codes_applied' => count($appliedPromoCodes),
                    'savings' => $totalDiscountAmount,
                ],
                'business_rules_applied' => [
                    'admin_instructor_exclusion' => !empty($adminCodes) && !empty($instructorCodes) ? 'Applied' : 'Not needed',
                    'minimum_order_validation' => 'Applied',
                    'max_discount_limits' => 'Applied for percentage codes',
                    'price_never_negative' => 'Applied',
                ],
                'usage_recommendations' => [
                    'can_apply_more_codes' => count($appliedPromoCodes) < count($validPromoCodes),
                    'remaining_discount_potential' => max(0, $finalPrice),
                    'suggested_next_codes' => count($validPromoCodes) - count($appliedPromoCodes),
                ]
            ];

            return ApiResponseService::successResponse(
                'Discount calculation completed successfully',
                $responseData
            );

        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to calculate course discount');
            return ApiResponseService::errorResponse('Something went wrong while calculating discount.');
        }
    }

public function getValidPromoCodes(Request $request)
{
    try {
        $userId = Auth::id();

        // Get course IDs from cart
        $cartCourses = Cart::where('user_id', $userId)->pluck('course_id')->toArray();

        if (empty($cartCourses)) {
            return ApiResponseService::successResponse('No items in cart', []);
        }

        // No applied promo codes in cart (promo codes are now applied at order level)
        $applied = collect();

        $today = Carbon::today();

        // Get cart with course price/discount_price
        $cartItems = Cart::where('user_id', $userId)
            ->with('course:id,id,price,discount_price')
            ->get();

        // Build course price map: use course.discount_price or course.price
        $cartPriceMap = [];
        foreach ($cartItems as $item) {
            $course = $item->course;
            $cartPriceMap[$item->course_id] = $course->discount_price ?? $course->price ?? 0;
        }

        // Only return admin promo codes (user_id = 1)
        $adminPromoCodes = PromoCode::where('status', 1)
            ->where('user_id', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        // Use only admin promo codes (exclude instructor promo codes)
        $promoCodes = $adminPromoCodes;

        // Calculate discount for each promo
        $promoCodesWithDiscount = $promoCodes->map(function ($promo) use ($cartCourses, $cartPriceMap) {
            if ($promo->user_id == 1) {
                $applicableCourses = $cartCourses;
            } else {
                $applicableCourses = PromoCodeCourse::where('promo_code_id', $promo->id)
                    ->whereIn('course_id', $cartCourses)
                    ->pluck('course_id')
                    ->toArray();
            }

            // Total of applicable courses
            $total = collect($applicableCourses)->sum(function ($courseId) use ($cartPriceMap) {
                return $cartPriceMap[$courseId] ?? 0;
            });

            if ($total < $promo->minimum_order_amount) {
                $promo->discounted_amount = 0;
                return $promo;
            }

            // Apply discount logic
            if ($promo->discount_type === 'percentage') {
                $discount = ($total * $promo->discount) / 100;
                $discount = min($discount, $promo->max_discount_amount);
            } else {
                $discount = min($promo->discount, $promo->max_discount_amount);
            }

            $promo->discounted_amount = round($discount, 2);
            return $promo;
        });

        return ApiResponseService::successResponse('Valid promo codes fetched', $promoCodesWithDiscount->values());

    } catch (\Exception $e) {
        return ApiResponseService::logErrorResponse($e, 'Failed to get valid promo codes');
    }
}






    /**
     * Preview promo code discount without applying to cart
     * Just check how much discount will be applied
     */
    public function applyPromoCode(Request $request)
    {
        try {
            // 🧹 Clean extra quotes if present (handle null values safely)
            $requestData = $request->all();
            if (isset($requestData['promo_code_id'])) {
                $requestData['promo_code_id'] = trim($requestData['promo_code_id'], '"\'');
            }
            if (isset($requestData['promo_code'])) {
                $requestData['promo_code'] = trim($requestData['promo_code'], '"\'');
            }
            if (isset($requestData['course_id'])) {
                $requestData['course_id'] = trim($requestData['course_id'], '"\'');
            }
            $request->merge($requestData);

            $validator = Validator::make($request->all(), [
                'promo_code_id' => 'required_without:promo_code|nullable|exists:promo_codes,id',
                'promo_code'    => 'required_without:promo_code_id|nullable|string|max:255',
                'course_id'     => 'required|exists:courses,id',
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            $courseId = $request->course_id;

            // Get promo code - either by ID or by code
            if ($request->filled('promo_code_id')) {
                $promo = PromoCode::with(['user.roles', 'courses'])->find($request->promo_code_id);
            } else {
                $promo = PromoCode::with(['user.roles', 'courses'])
                    ->where('promo_code', $request->promo_code)
                    ->first();
            }

            if (!$promo) {
                return ApiResponseService::validationError('Promo code not found');
            }

            // Check if promo code is active and valid
            if ($promo->status != 1) {
                return ApiResponseService::validationError('Promo code is not active');
            }

            // Check if promo code is within valid date range
            if ($promo->start_date > now() || $promo->end_date < now()) {
                return ApiResponseService::validationError('Promo code is expired or not yet active');
            }

            // Reject admin promo codes (user_id = 1 or Admin role)
            $isAdmin = $promo->user_id == 1 || $promo->user->roles->contains('name', 'Admin');
            
            if ($isAdmin) {
                return ApiResponseService::validationError('Admin promo codes are not allowed. Only instructor promo codes can be previewed.');
            }

            // Verify course exists
            $course = Course::find($courseId);
            if (!$course) {
                return ApiResponseService::validationError('Course not found');
            }

            // Check if instructor promo code is linked to this course
            $isInstructor = $promo->user->roles->contains('name', 'Instructor');
            if ($isInstructor) {
                $isLinkedToCourse = PromoCodeCourse::where('promo_code_id', $promo->id)
                    ->where('course_id', $courseId)
                    ->exists();
                
                if (!$isLinkedToCourse) {
                    return ApiResponseService::validationError('This instructor promo code is not applicable to the selected course');
                }
            }

            // Load course with relationships like formatUserCart does
            $course->load('taxes', 'user');
            
            // Check if course is wishlisted (if user is authenticated)
            $isWishlisted = false;
            if (Auth::check()) {
                $isWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())
                    ->where('course_id', $courseId)
                    ->exists();
            }
            
            // Get course price (same logic as formatUserCart)
            $originalPrice = ($course->display_discount_price !== null && $course->display_discount_price > 0)
                ? $course->display_discount_price
                : ($course->display_price ?? 0);

            if ($originalPrice <= 0) {
                return ApiResponseService::validationError('Course price not available');
            }

            // Calculate discount amount (same logic as formatUserCart)
            $discountAmount = 0;
            $finalPrice = $originalPrice;
            $promoCodeDetails = null;
            $promoDiscounts = [];

            // Check minimum order amount
            if ($promo->minimum_order_amount > 0 && $originalPrice < $promo->minimum_order_amount) {
                // Return format same as apply endpoint but with 0 discount
                $formattedCourse = [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'thumbnail' => $course->thumbnail,
                    'display_price' => $course->display_price,
                    'display_discount_price' => $course->display_discount_price,
                    'original_price' => $originalPrice,
                    'promo_discount' => 0,
                    'final_price' => $originalPrice,
                    'promo_code' => null,
                    'tax_amount' => $course->tax_amount ?? 0,
                    'total_tax_percentage' => $course->total_tax_percentage ?? 0,
                    'instructor' => $course->user->name ?? '',
                    'is_wishlisted' => $isWishlisted,
                ];

                $totalDisplayPrice = $course->display_price ?? 0;
                $subtotalPrice = $originalPrice;
                $totalPrice = $originalPrice;
                $discount = $totalDisplayPrice - $subtotalPrice;

                return ApiResponseService::successResponse('Promo code preview', [
                    'courses' => [$formattedCourse],
                    'total_display_price' => $totalDisplayPrice,
                    'subtotal_price' => $subtotalPrice,
                    'promo_discount' => 0,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'promo_discounts' => [],
                ]);
            }

            // Calculate discount based on type (same as formatUserCart)
            if ($promo->discount_type === 'amount') {
                $discountAmount = min($promo->discount, $originalPrice);
            } elseif ($promo->discount_type === 'percentage') {
                $discountAmount = ($originalPrice * $promo->discount) / 100;
                
                // Apply max discount limit if set
                if ($promo->max_discount_amount > 0) {
                    $discountAmount = min($discountAmount, $promo->max_discount_amount);
                }
            }

            $discountAmount = min($discountAmount, $originalPrice);
            $finalPrice = max(0, $originalPrice - $discountAmount);

            // Format promo code details (same as formatUserCart)
            $promoCodeDetails = [
                'id' => $promo->id,
                'code' => $promo->promo_code,
                'message' => $promo->message,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount,
                'discount_amount' => $discountAmount,
            ];

            // Add to promo discounts array
            $promoDiscounts[] = [
                'course_id' => $course->id,
                'course_title' => $course->title,
                'promo_code' => $promo->promo_code,
                'discount_amount' => $discountAmount,
            ];

            // Format course data (same structure as formatUserCart)
            $formattedCourse = [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'thumbnail' => $course->thumbnail,
                'display_price' => $course->display_price,
                'display_discount_price' => $course->display_discount_price,
                'original_price' => $originalPrice,
                'promo_discount' => $discountAmount,
                'final_price' => $finalPrice,
                'promo_code' => $promoCodeDetails,
                'tax_amount' => $course->tax_amount ?? 0,
                'total_tax_percentage' => $course->total_tax_percentage ?? 0,
                'instructor' => $course->user->name ?? '',
                'is_wishlisted' => $isWishlisted,
            ];

            // Calculate totals (same as formatUserCart)
            $totalDisplayPrice = $course->display_price ?? 0;
            $subtotalPrice = $originalPrice;
            $totalPrice = $finalPrice;
            $discount = $totalDisplayPrice - $subtotalPrice; // Course-level discount

            return ApiResponseService::successResponse('Promo code preview', [
                'courses' => [$formattedCourse],
                'total_display_price' => $totalDisplayPrice,
                'subtotal_price' => $subtotalPrice,
                'promo_discount' => $discountAmount,
                'discount' => $discount,
                'total_price' => $totalPrice,
                'promo_discounts' => $promoDiscounts,
            ]);

        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to preview promo code discount');
            return ApiResponseService::errorResponse('Failed to preview promo code discount: ' . $e->getMessage());
        }
    }

    // 3. APPLIED PROMO CODES
    public function getAppliedPromoCodes(Request $request)
{
    try {
        // No applied promo codes in cart (promo codes are now applied at order level)
        return ApiResponseService::successResponse('Applied promo codes', [
            'total_discounted_amount' => 0,
            'applied_promo_codes'     => []
        ]);

    } catch (\Exception $e) {
        return ApiResponseService::logErrorResponse($e, 'Failed to get applied promo codes');
    }
}
}
