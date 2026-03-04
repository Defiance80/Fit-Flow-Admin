<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\PromoCode;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartApiController extends Controller
{
    /**
     * Get the current user's cart.
     */
    public function getUserCart(Request $request)
    {
        
        try {
            $user = Auth::user();
            $cartData = $this->formatUserCart($user);

            return ApiResponseService::successResponse('Cart fetched successfully', $cartData);
        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to get cart');
            return ApiResponseService::errorResponse('Failed to get cart');
        }
    }

    /**
     * Add a course to the current user's cart.
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'promo_code_id' => 'nullable|exists:promo_codes,id',
            'promo_code' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            $user = Auth::user();
            $courseId = $request->course_id;
            $promoCodeId = null;
            $promoCode = null;

            // Check if course already in cart
            $existingCart = $user->carts()->where('course_id', $courseId)->first();

            // If promo code is provided (either by ID or code), validate it
            if ($request->filled('promo_code_id') || $request->filled('promo_code')) {
                // Get promo code - either by ID or by code
                if ($request->filled('promo_code_id')) {
                    $promoCode = PromoCode::with(['user.roles', 'courses'])->find($request->promo_code_id);
                } else {
                    $promoCode = PromoCode::with(['user.roles', 'courses'])
                        ->where('promo_code', $request->promo_code)
                        ->first();
                }
                
                if (!$promoCode) {
                    return ApiResponseService::validationError('Promo code not found');
                }
                
                $promoCodeId = $promoCode->id;
                
                // Check if promo code is active and valid
                if ($promoCode->status != 1) {
                    return ApiResponseService::validationError('Promo code is not active');
                }

                // Check if promo code is within valid date range
                if ($promoCode->start_date > now() || $promoCode->end_date < now()) {
                    return ApiResponseService::validationError('Promo code is expired or not yet active');
                }

                // Check if promo code is applicable to this course
                $isAdmin = $promoCode->user->roles->contains('name', 'Admin');
                $isInstructor = $promoCode->user->roles->contains('name', 'Instructor');
                $isApplicable = false;

                if ($isAdmin) {
                    // Admin promo codes apply to all courses
                    $isApplicable = true;
                } elseif ($isInstructor) {
                    // Instructor promo codes apply only to their courses
                    $instructorCourseIds = $promoCode->courses->pluck('id')->toArray();
                    $isApplicable = in_array($courseId, $instructorCourseIds);
                }

                if (!$isApplicable) {
                    return ApiResponseService::validationError('Promo code is not applicable to this course');
                }
            }

            if ($existingCart) {
                // Course already in cart - update promo code if different
                if ($existingCart->promo_code_id != $promoCodeId) {
                    $existingCart->update(['promo_code_id' => $promoCodeId]);
                    return ApiResponseService::successResponse('Cart updated with promo code', $this->formatUserCart($user));
                } else {
                    return ApiResponseService::successResponse('Course already in cart with the same promo code', $this->formatUserCart($user));
                }
            }

            // Create new cart row
            $user->carts()->create([
                'course_id' => $courseId,
                'promo_code_id' => $promoCodeId,
            ]);

            return ApiResponseService::successResponse('Course added to cart', $this->formatUserCart($user));
        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to add course to cart');
            return ApiResponseService::errorResponse('Failed to add course to cart'. $e->getMessage());
        }
    }


    /**
     * Apply promo code to cart (smart detection)
     * - Automatically detects if admin or instructor promo code
     * - Admin promo codes (user_id = 1) replace ALL existing promo codes and apply to ALL courses
     * - Instructor promo codes remove ALL admin promo codes first, then apply to their mapped courses only
     */
    public function applyPromoCodeToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promo_code_id' => 'nullable|exists:promo_codes,id',
            'promo_code' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }

        // At least one field must be provided
        if (!$request->filled('promo_code_id') && !$request->filled('promo_code')) {
            return ApiResponseService::validationError('Either promo_code_id or promo_code is required');
        }

        try {
            $user = Auth::user();

            // Get promo code with relationships - either by ID or by code
            if ($request->filled('promo_code_id')) {
                $promoCode = PromoCode::with(['user.roles', 'courses'])->find($request->promo_code_id);
            } else {
                $promoCode = PromoCode::with(['user.roles', 'courses'])
                    ->where('promo_code', $request->promo_code)
                    ->first();
            }

            if (!$promoCode) {
                return ApiResponseService::validationError('Promo code not found');
            }

            $promoCodeId = $promoCode->id;

            // Check if promo code is active and valid
            if ($promoCode->status != 1) {
                return ApiResponseService::validationError('Promo code is not active');
            }

            // Check if promo code is within valid date range
            if ($promoCode->start_date > now() || $promoCode->end_date < now()) {
                return ApiResponseService::validationError('Promo code is expired or not yet active');
            }

            // Get all cart items
            $cartItems = $user->carts()->with('course')->get();

            if ($cartItems->isEmpty()) {
                return ApiResponseService::validationError('Cart is empty');
            }

            // Determine if this is an admin promo code (user_id = 1)
            $isAdminPromo = ($promoCode->user_id == 1);
            
            if ($isAdminPromo) {
                // ========== ADMIN PROMO CODE ==========
                // Replace ALL existing promo codes and apply to ALL courses
                $allCourseIds = $cartItems->pluck('course_id')->toArray();
                
                // Update all cart items with admin promo code
                $user->carts()->update(['promo_code_id' => $promoCodeId]);
                
                $message = "Admin promo code applied to all " . count($allCourseIds) . " course(s) in cart";
            } else {
                // ========== INSTRUCTOR PROMO CODE ==========
                // First, remove ALL admin promo codes from entire cart
                $cartItemsWithAdminPromo = $user->carts()
                    ->whereHas('promoCode', function($query) {
                        $query->where('user_id', 1);
                    })
                    ->get();
                
                if ($cartItemsWithAdminPromo->isNotEmpty()) {
                    // Remove admin promo codes from all courses
                    $user->carts()->update(['promo_code_id' => null]);
                }
                
                // Get instructor's mapped courses
                $promoCourseIds = $promoCode->courses->pluck('id')->toArray();
                
                // Find which cart courses are applicable
                $applicableCourseIds = [];
                foreach ($cartItems as $cartItem) {
                    if (in_array($cartItem->course_id, $promoCourseIds)) {
                        $applicableCourseIds[] = $cartItem->course_id;
                    }
                }
                
                if (empty($applicableCourseIds)) {
                    return ApiResponseService::validationError('This promo code is not applicable to any course in your cart');
                }
                
                // Apply instructor promo code only to applicable courses
                $user->carts()->whereIn('course_id', $applicableCourseIds)
                    ->update(['promo_code_id' => $promoCodeId]);
                
                $message = "Promo code applied to " . count($applicableCourseIds) . " applicable course(s) in cart";
            }

            // Refresh user to clear relationship cache and reload cart with updated promo codes
            $user->refresh();
            
            return ApiResponseService::successResponse($message, $this->formatUserCart($user));
        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to apply promo code');
            return ApiResponseService::errorResponse('Failed to apply promo code: ' . $e->getMessage());
        }
    }

    /**
     * Remove promo code from entire cart
     */
    public function removePromoCode(Request $request)
    {
        try {
            $user = Auth::user();

            // Remove promo codes from all cart items
            $user->carts()->update(['promo_code_id' => null]);

            return ApiResponseService::successResponse('Promo codes removed from cart', $this->formatUserCart($user));
        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to remove promo codes');
            return ApiResponseService::errorResponse('Failed to remove promo codes: ' . $e->getMessage());
        }
    }

    /**
     * Remove a course from the current user's cart.
     */
    public function removeFromCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id'
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors()->first());
        }

        try {
            $user = Auth::user();
            $courseId = $request->course_id;

            $cartItem = $user->carts()->where('course_id', $courseId)->first();

            if (!$cartItem) {
                return ApiResponseService::errorResponse('Course not found in cart');
            }

            $cartItem->delete();

            return ApiResponseService::successResponse('Course removed from cart', $this->formatUserCart($user));
        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to remove course from cart');
            return ApiResponseService::errorResponse('Failed to remove course from cart');
        }
    }

    /**
     * Clear all items from the current user's cart.
     */
    public function clearCart(Request $request)
    {
        try {
            $user = Auth::user();

            // Delete all cart items for the user
            $deletedCount = $user->carts()->delete();

            return ApiResponseService::successResponse('Cart cleared successfully', $this->formatUserCart($user));
        } catch (\Exception $e) {
            ApiResponseService::logErrorResponse($e, 'Failed to clear cart');
            return ApiResponseService::errorResponse('Failed to clear cart: ' . $e->getMessage());
        }
    }

    /**
     * Format the user's cart with total price and course data.
     */
    public function formatUserCart($user)
    {
    // Eager load course relationships and promo codes (force reload)
    $cart = $user->carts()->with([
        'course.taxes', 
        'course.user', 
        'promoCode.user.roles'
    ])->with(['course' => function($query) {
        $query->withAvg('ratings', 'rating')
              ->withCount('ratings')
              ->where('is_active', 1) // Only active courses
              ->where('status', 'publish') // Only published courses
              ->where('approval_status', 'approved') // Only approved courses
              ->whereHas('user', function($userQuery) {
                  $userQuery->where('is_active', 1) // User should be active
                      ->whereHas('instructor_details', function($instructorQuery) {
                          $instructorQuery->where('status', 'approved'); // Instructor status should be approved
                      });
              });
    }])->get();
    if ($cart->isEmpty()) {
        return [
            'courses' => [],
            'total_display_price' => 0, // Sum of all original display_price
            'subtotal_price' => 0,
            'promo_discount' => 0, // Promo code discount amount
            'discount' => 0, // Total discount = total_display_price - total_discounted_price
            'total_price' => 0, // Final price after all discounts
            'promo_discounts' => []
        ];
    }

    $courses = $cart->pluck('course')->filter();
    
    // Get all course IDs in cart
    $courseIds = $courses->pluck('id')->toArray();
    
    // Check which courses are wishlisted by the user
    $wishlistedCourseIds = \App\Models\Wishlist::where('user_id', $user->id)
        ->whereIn('course_id', $courseIds)
        ->pluck('course_id')
        ->toArray();

   // Calculate promo discounts for each cart item
   $totalDiscountPrice = 0;
   $promoDiscounts = [];

   $formattedCourses = $cart->map(function ($cartItem) use ($wishlistedCourseIds, &$totalDiscountPrice, &$promoDiscounts) {
    $course = $cartItem->course;
    if (!$course) {
        return null;
    }

    // Get base price (without tax) for calculations
    $taxType = \App\Services\HelperService::systemSettings('tax_type');
    $basePrice = ($course->discount_price && $course->discount_price > 0) 
        ? $course->discount_price 
        : ($course->price ?? 0);
    
    // Get total tax percentage once for display calculations
    $totalTaxPercentage = \App\Models\Tax::where('is_active', 1)->sum('percentage');

    // Compute display prices (match promo preview API)
    $baseDisplayPrice = $course->price ?? 0;
    $baseDisplayDiscountPrice = $course->discount_price ?? 0;
    $computedDisplayPrice = $baseDisplayPrice;
    $computedDisplayDiscountPrice = $baseDisplayDiscountPrice;

    if ($totalTaxPercentage > 0 && $taxType === 'inclusive') {
        $computedDisplayPrice = $baseDisplayPrice + (($baseDisplayPrice * $totalTaxPercentage) / 100);
        if ($baseDisplayDiscountPrice > 0) {
            $computedDisplayDiscountPrice = $baseDisplayDiscountPrice + (($baseDisplayDiscountPrice * $totalTaxPercentage) / 100);
        }
    }

    $originalPrice = ($computedDisplayDiscountPrice !== null && $computedDisplayDiscountPrice > 0)
        ? round($computedDisplayDiscountPrice, 2)
        : round($computedDisplayPrice, 2);

    $discountAmount = 0;
    $displayPriceAfterDiscount = $originalPrice;
    $promoCodeDetails = null;

    // Apply promo code if exists (apply to base price)
    if ($cartItem->promo_code_id) {
        // Load promo code if not already loaded
        if (!$cartItem->relationLoaded('promoCode') || !$cartItem->promoCode) {
            $cartItem->load('promoCode.user.roles');
        }
        
        $promo = $cartItem->promoCode;
        
        if ($promo) {
            // Check if promo code is still valid
            if ($promo->status == 1 && $promo->start_date <= now() && $promo->end_date >= now()) {
                // Check minimum order amount (use base price for comparison)
                if ($promo->minimum_order_amount <= 0 || $basePrice >= $promo->minimum_order_amount) {
                    // Calculate discount on base price
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
                    $displayPriceAfterDiscount = max(0, $originalPrice - $discountAmount);
                    $totalDiscountPrice += $discountAmount;

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
                }
            }
        }
    }

    // Determine tax amount and final price based on tax type
    $taxAmount = 0;
    $finalPrice = $displayPriceAfterDiscount;
    $basePriceAfterDiscount = $basePrice;

    if ($taxType === 'inclusive') {
        if ($totalTaxPercentage > 0 && $displayPriceAfterDiscount > 0) {
            $basePriceAfterDiscount = $displayPriceAfterDiscount / (1 + ($totalTaxPercentage / 100));
            $taxAmount = $displayPriceAfterDiscount - $basePriceAfterDiscount;
        } else {
            $basePriceAfterDiscount = $displayPriceAfterDiscount;
        }
        $finalPrice = $displayPriceAfterDiscount;
    } else {
        // Exclusive tax - base price equals display price
        $basePriceAfterDiscount = $displayPriceAfterDiscount;
        if ($totalTaxPercentage > 0 && $basePriceAfterDiscount > 0) {
            $taxAmount = ($basePriceAfterDiscount * $totalTaxPercentage) / 100;
        }
        $finalPrice = $basePriceAfterDiscount;
    }
    
    return [
        'id' => $course->id,
        'title' => $course->title,
        'slug' => $course->slug,
        'thumbnail' => $course->thumbnail,
        'display_price' => round($computedDisplayPrice, 2),
        'display_discount_price' => round($computedDisplayDiscountPrice, 2),
        'original_price' => $originalPrice,
        'promo_discount' => $discountAmount,
        'final_price' => round($finalPrice, 2),
        'promo_code' => $promoCodeDetails,
        'tax_amount' => round($taxAmount, 2),
        'total_tax_percentage' => $totalTaxPercentage,
        'instructor' => $course->user['name'] ?? '',
        'is_wishlisted' => in_array($course->id, $wishlistedCourseIds),
        'ratings' => (int) ($course->ratings_count ?? 0),
        'average_rating' => round((float) ($course->ratings_avg_rating ?? 0), 2),
    ];
})->filter()->values(); 

    // Calculate total_display_price (sum of all original display_price)
    $totalDisplayPrice = $cart->sum(function ($cartItem) {
        $course = $cartItem->course;
        if (!$course) return 0;
        return $course->display_price ?? 0;
    });

    // Subtotal before discount (using display_discount_price if available, else display_price)
    $subtotalPrice = $cart->sum(function ($cartItem) {
        $course = $cartItem->course;
        if (!$course) return 0;
        
        return ($course->display_discount_price !== null && $course->display_discount_price > 0)
            ? $course->display_discount_price
            : ($course->display_price ?? 0);
    });

    // Total discounted price (after promo discounts)
    $totalDiscountedPrice = max(0, $subtotalPrice - $totalDiscountPrice);
    
    // Discount = total_display_price - courses' total discounted price (subtotal_price, not including promo discount)
    // Only subtract the course-level discounts, not promo discounts
    $discount = $totalDisplayPrice - $subtotalPrice;
    
    // Calculate total tax amount
    $totalTaxAmount = $formattedCourses->sum('tax_amount');
    
    // Get tax type setting
    $taxType = \App\Services\HelperService::systemSettings('tax_type');
    
    // Calculate final total based on tax type
    $finalTotal = $totalDiscountedPrice;
    if ($taxType === 'exclusive') {
        // If exclusive, add tax to final total
        $finalTotal = $totalDiscountedPrice + $totalTaxAmount;
    }
    // If inclusive, tax is already included in prices
    
    return [
        'courses' => $formattedCourses,
        'total_display_price' => $totalDisplayPrice, // Sum of all original display_price
        'subtotal_price' => $subtotalPrice, // Price before promo discount
        'promo_discount' => $totalDiscountPrice, // Promo code discount amount
        'discount' => $discount, // Total discount = total_display_price - total_discounted_price
        'total_price' => $totalDiscountedPrice, // Final price after all discounts (before tax if exclusive)
        'total_tax_amount' => round($totalTaxAmount, 2), // Total tax amount
        'tax_type' => $taxType ?? 'exclusive', // Tax type setting
        'final_total' => round($finalTotal, 2), // Final total (including tax if exclusive)
        'promo_discounts' => $promoDiscounts,
    ];
}

}
