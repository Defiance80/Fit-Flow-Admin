<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ApiResponseService;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\Commission;
use App\Models\WithdrawalRequest;
use App\Models\Course\UserCourseTrack;
use Carbon\Carbon;

class InstructorEarningsApiController extends Controller
{
    /**
     * Get instructor earnings dashboard data
     */
    public function getInstructorEarnings(Request $request)
    {
        try {
            // In single instructor mode, return error
            if (\App\Services\InstructorModeService::isSingleInstructorMode()) {
                return ApiResponseService::validationError('Instructor earnings are disabled in Single Instructor mode.');
            }
            
            $user = Auth::user();
            if (!$user) {
                return ApiResponseService::unauthorizedResponse('User not authenticated');
            }

            // Check if user is an instructor
            if (!$user->hasRole(config('constants.SYSTEM_ROLES.INSTRUCTOR'))) {
                return ApiResponseService::validationError('User is not an instructor');
            }

            $instructor = $user->instructor_details;
            if (!$instructor) {
                return ApiResponseService::validationError('Instructor details not found');
            }

            // Get date range (default to current year)
            $year = $request->get('year', date('Y'));
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Calculate total revenue from orders
            $totalRevenue = Order::whereHas('orderCourses', function($query) use ($user) {
                $query->whereHas('course', function($courseQuery) use ($user) {
                    $courseQuery->where('user_id', $user->id);
                });
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('final_price');

            // Calculate total commission earned
            $totalCommission = Commission::where('instructor_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('instructor_commission_amount');

            // Calculate total earnings (revenue - platform commission)
            $totalEarning = $totalRevenue - $totalCommission;

            // Calculate available to withdraw (earnings - withdrawn amount)
            $totalWithdrawn = WithdrawalRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');

            $availableToWithdraw = max(0, $totalEarning - $totalWithdrawn);

            $currentMonth = now()->month;
            $currentWeek = now()->weekOfYear;

            // Get monthly revenue and commission data for charts (for yearly view)
            $monthlyData = $this->getMonthlyRevenueData($user->id, $year);
            $monthlyEarnings = $this->getMonthlyEarningsData($user->id, $year);

            // Get daily data for monthly and weekly views
            $dailyDataForMonth = $this->getDailyRevenueData($user->id, $year, $currentMonth);
            $dailyDataForWeek = $this->getWeeklyRevenueData($user->id, $year, $currentWeek);

            // Prepare chart data for summary cards (yearly view - 12 months)
            $revenueChartData = array_map(function($month) {
                return [
                    'name' => $month['month'],
                    'earning' => (float) $month['revenue']
                ];
            }, $monthlyData);

            $commissionChartData = array_map(function($month) {
                return [
                    'name' => $month['month'],
                    'earning' => (float) $month['commission']
                ];
            }, $monthlyData);

            $earningChartData = array_map(function($month) {
                return [
                    'name' => $month['month'],
                    'earning' => (float) $month['earnings']
                ];
            }, $monthlyEarnings);

            // Prepare revenue chart data for all periods
            $revenueChartYearly = array_map(function($month) {
                return [
                    'name' => $month['month'],
                    'revenue' => (float) $month['revenue'],
                    'commission' => (float) $month['commission']
                ];
            }, $monthlyData);

            $revenueChartMonthly = array_map(function($day) {
                return [
                    'name' => $day['day_name'],
                    'revenue' => (float) $day['revenue'],
                    'commission' => (float) $day['commission']
                ];
            }, $dailyDataForMonth);

            $revenueChartWeekly = array_map(function($day) {
                return [
                    'name' => $day['day_name'],
                    'revenue' => (float) $day['revenue'],
                    'commission' => (float) $day['commission']
                ];
            }, $dailyDataForWeek);

            // Prepare earnings chart data for all periods
            $earningsChartYearly = array_map(function($month) {
                return [
                    'name' => $month['month'],
                    'earning' => (float) $month['earnings']
                ];
            }, $monthlyEarnings);

            $earningsChartMonthly = array_map(function($day) {
                return [
                    'name' => $day['day_name'],
                    'earning' => (float) $day['earnings']
                ];
            }, $dailyDataForMonth);

            $earningsChartWeekly = array_map(function($day) {
                return [
                    'name' => $day['day_name'],
                    'earning' => (float) $day['earnings']
                ];
            }, $dailyDataForWeek);

            // Get recent withdrawal requests
            $recentWithdrawals = WithdrawalRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'amount' => $withdrawal->amount,
                        'status' => $withdrawal->status,
                        'requested_at' => $withdrawal->created_at->format('Y-m-d H:i:s'),
                        'processed_at' => $withdrawal->updated_at->format('Y-m-d H:i:s'),
                        'status_label' => ucfirst($withdrawal->status)
                    ];
                });

            // Prepare response data
            $responseData = [
                'summary_cards' => [
                    'total_revenue' => [
                        'value' => number_format($totalRevenue, 2),
                        'formatted_value' => '$' . number_format($totalRevenue, 2),
                        'chartData' => $revenueChartData
                    ],
                    'total_commission' => [
                        'value' => number_format($totalCommission, 2),
                        'formatted_value' => '$' . number_format($totalCommission, 2),
                        'chartData' => $commissionChartData
                    ],
                    'total_earning' => [
                        'value' => number_format($totalEarning, 2),
                        'formatted_value' => '$' . number_format($totalEarning, 2),
                        'chartData' => $earningChartData
                    ]
                ],
                'action_cards' => [
                    'available_to_withdraw' => [
                        'value' => number_format($availableToWithdraw, 2),
                        'formatted_value' => '$' . number_format($availableToWithdraw, 2),
                        'button_text' => 'Withdraw →',
                        'button_action' => 'withdraw'
                    ],
                    'total_withdrawal' => [
                        'value' => number_format($totalWithdrawn, 2),
                        'formatted_value' => '$' . number_format($totalWithdrawn, 2),
                        'button_text' => 'View History →',
                        'button_action' => 'view_history'
                    ]
                ],
                'charts' => [
                    'revenue_chart' => [
                        'yearly' => $revenueChartYearly,
                        'monthly' => $revenueChartMonthly,
                        'weekly' => $revenueChartWeekly
                    ],
                    'earnings_chart' => [
                        'yearly' => $earningsChartYearly,
                        'monthly' => $earningsChartMonthly,
                        'weekly' => $earningsChartWeekly
                    ]
                ],
                'recent_withdrawals' => $recentWithdrawals,
                'filters' => [
                    'year' => $year,
                    'available_years' => range(date('Y') - 5, date('Y'))
                ]
            ];

            return ApiResponseService::successResponse('Instructor earnings data retrieved successfully', $responseData);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting instructor earnings: ' . $e->getMessage());
            return ApiResponseService::errorResponse('Failed to load earnings data: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly revenue and commission data
     */
    private function getMonthlyRevenueData($userId, $year)
    {
        $months = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::createFromDate($year, $i, 1)->startOfDay();
            $endOfMonth = Carbon::createFromDate($year, $i, 1)->endOfMonth()->endOfDay();

            // Get revenue for this month
            $revenue = Order::whereHas('orderCourses', function($query) use ($userId) {
                $query->whereHas('course', function($courseQuery) use ($userId) {
                    $courseQuery->where('user_id', $userId);
                });
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_price');

            // Get commission for this month
            $commission = Commission::where('instructor_id', $userId)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('instructor_commission_amount');

            $months[] = [
                'month' => $monthNames[$i - 1],
                'month_number' => $i,
                'revenue' => (float) $revenue,
                'commission' => (float) $commission,
                'formatted_revenue' => '$' . number_format($revenue, 0),
                'formatted_commission' => '$' . number_format($commission, 0)
            ];
        }

        return $months;
    }

    /**
     * Get monthly earnings data
     */
    private function getMonthlyEarningsData($userId, $year)
    {
        $months = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::createFromDate($year, $i, 1)->startOfDay();
            $endOfMonth = Carbon::createFromDate($year, $i, 1)->endOfMonth()->endOfDay();

            // Get revenue for this month
            $revenue = Order::whereHas('orderCourses', function($query) use ($userId) {
                $query->whereHas('course', function($courseQuery) use ($userId) {
                    $courseQuery->where('user_id', $userId);
                });
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_price');

            // Get commission for this month
            $commission = Commission::where('instructor_id', $userId)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('instructor_commission_amount');

            $earnings = $revenue - $commission;

            $months[] = [
                'month' => $monthNames[$i - 1],
                'month_number' => $i,
                'earnings' => (float) $earnings,
                'formatted_earnings' => '$' . number_format($earnings, 0)
            ];
        }

        return $months;
    }

    /**
     * Get daily revenue data for a specific month (for monthly chart view)
     */
    private function getDailyRevenueData($userId, $year, $month)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dailyData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $startOfDay = Carbon::createFromDate($year, $month, $day)->startOfDay();
            $endOfDay = Carbon::createFromDate($year, $month, $day)->endOfDay();

            // Get revenue for this day
            $revenue = Order::whereHas('orderCourses', function($query) use ($userId) {
                $query->whereHas('course', function($courseQuery) use ($userId) {
                    $courseQuery->where('user_id', $userId);
                });
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_price');

            // Get commission for this day
            $commission = Commission::where('instructor_id', $userId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('instructor_commission_amount');

            $earnings = $revenue - $commission;

            $dailyData[] = [
                'day' => $day,
                'day_name' => $day,
                'revenue' => (float) $revenue,
                'commission' => (float) $commission,
                'earnings' => (float) $earnings,
                'formatted_revenue' => '$' . number_format($revenue, 0),
                'formatted_commission' => '$' . number_format($commission, 0),
                'formatted_earnings' => '$' . number_format($earnings, 0)
            ];
        }

        return $dailyData;
    }

    /**
     * Get weekly revenue data (for weekly chart view - 7 days)
     */
    private function getWeeklyRevenueData($userId, $year, $week)
    {
        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weeklyData = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            $startOfDay = $currentDay->startOfDay();
            $endOfDay = $currentDay->endOfDay();

            // Get revenue for this day
            $revenue = Order::whereHas('orderCourses', function($query) use ($userId) {
                $query->whereHas('course', function($courseQuery) use ($userId) {
                    $courseQuery->where('user_id', $userId);
                });
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_price');

            // Get commission for this day
            $commission = Commission::where('instructor_id', $userId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('instructor_commission_amount');

            $earnings = $revenue - $commission;

            $weeklyData[] = [
                'day' => $i + 1,
                'day_name' => $weekDays[$i],
                'revenue' => (float) $revenue,
                'commission' => (float) $commission,
                'earnings' => (float) $earnings,
                'formatted_revenue' => '$' . number_format($revenue, 0),
                'formatted_commission' => '$' . number_format($commission, 0),
                'formatted_earnings' => '$' . number_format($earnings, 0)
            ];
        }

        return $weeklyData;
    }

    /**
     * Get withdrawal details with pagination
     */
    public function getWithdrawalDetails(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponseService::unauthorizedResponse('User not authenticated');
            }

            if (!$user->hasRole(config('constants.SYSTEM_ROLES.INSTRUCTOR'))) {
                return ApiResponseService::validationError('User is not an instructor');
            }

            $instructor = $user->instructor_details;
            if (!$instructor) {
                return ApiResponseService::validationError('Instructor details not found');
            }

            // Get pagination parameters
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $search = $request->get('search', '');

            // Validate per_page parameter (max 50 records per page)
            if ($perPage > 50) {
                $perPage = 50;
            }

            // Ensure per_page is at least 1 to avoid division by zero
            if ($perPage < 1) {
                $perPage = 10;
            }

            // Calculate summary data
            $totalWithdrawn = WithdrawalRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');

            $totalEarning = $this->calculateTotalEarning($user->id);
            $availableToWithdraw = max(0, $totalEarning - $totalWithdrawn);

            // Build query with search
            $query = WithdrawalRequest::where('user_id', $user->id);

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('amount', 'LIKE', "%{$search}%")
                      ->orWhere('status', 'LIKE', "%{$search}%")
                      ->orWhere('account_holder_name', 'LIKE', "%{$search}%")
                      ->orWhere('account_number', 'LIKE', "%{$search}%")
                      ->orWhere('bank_name', 'LIKE', "%{$search}%");
                });
            }

            // Get withdrawal requests with pagination
            $withdrawals = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Format withdrawal data
            $withdrawalData = $withdrawals->map(function($withdrawal, $index) {
                $statusColors = [
                    'pending' => '#3B82F6',      // Blue
                    'approved' => '#10B981',     // Green
                    'successful' => '#10B981',   // Green
                    'failed' => '#EF4444',       // Red
                    'rejected' => '#EF4444'      // Red
                ];

                $statusLabels = [
                    'pending' => 'Pending',
                    'approved' => 'Successful',
                    'successful' => 'Successful',
                    'failed' => 'Failed',
                    'rejected' => 'Failed'
                ];

                return [
                    'id' => $withdrawal->id,
                    'transaction_id' => $withdrawal->id, // Using ID as transaction ID
                    'transaction_date' => $withdrawal->created_at->format('d F, Y'),
                    'amount' => $withdrawal->amount,
                    'formatted_amount' => '$' . number_format($withdrawal->amount, 0),
                    'status' => $withdrawal->status,
                    'status_label' => $statusLabels[$withdrawal->status] ?? ucfirst($withdrawal->status),
                    'status_color' => $statusColors[$withdrawal->status] ?? '#6B7280',
                    'requested_at' => $withdrawal->created_at->format('Y-m-d H:i:s'),
                    'processed_at' => $withdrawal->updated_at->format('Y-m-d H:i:s'),
                    'notes' => $withdrawal->notes,
                    'bank_details' => [
                        'account_holder_name' => $withdrawal->account_holder_name,
                        'account_number' => $withdrawal->account_number,
                        'bank_name' => $withdrawal->bank_name,
                        'routing_number' => $withdrawal->routing_number
                    ]
                ];
            });

            // Create pagination links
            $lastPage = $withdrawals->lastPage();
            $baseUrl = request()->url();
            $path = str_replace(request()->root(), '', $baseUrl);
            
            // Build query parameters for URLs
            $queryParams = request()->query();
            unset($queryParams['page']); // Remove page from query params
            
            $firstPageUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1]));
            $lastPageUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $lastPage]));
            $nextPageUrl = $page < $lastPage ? $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $page + 1])) : null;
            $prevPageUrl = $page > 1 ? $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $page - 1])) : null;
            
            // Create pagination links array
            $links = [];
            
            // Previous link
            $links[] = [
                'url' => $prevPageUrl,
                'label' => '&laquo; Previous',
                'active' => false
            ];
            
            // Page number links
            for ($i = 1; $i <= $lastPage; $i++) {
                $pageUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i]));
                $links[] = [
                    'url' => $pageUrl,
                    'label' => (string) $i,
                    'active' => $i == $page
                ];
            }
            
            // Next link
            $links[] = [
                'url' => $nextPageUrl,
                'label' => 'Next &raquo;',
                'active' => false
            ];

            $responseData = [
                'current_page' => (int) $page,
                'data' => $withdrawalData,
                'first_page_url' => $firstPageUrl,
                'from' => $withdrawals->firstItem(),
                'last_page' => $lastPage,
                'last_page_url' => $lastPageUrl,
                'links' => $links,
                'next_page_url' => $nextPageUrl,
                'path' => $path,
                'per_page' => (int) $perPage,
                'prev_page_url' => $prevPageUrl,
                'to' => $withdrawals->lastItem(),
                'total' => $withdrawals->total(),
                'summary_cards' => [
                    'total_withdrawal' => [
                        'value' => number_format($totalWithdrawn, 2),
                        'formatted_value' => '$' . number_format($totalWithdrawn, 2),
                        'icon' => 'withdrawal-icon'
                    ],
                    'available_to_withdraw' => [
                        'value' => number_format($availableToWithdraw, 2),
                        'formatted_value' => '$' . number_format($availableToWithdraw, 2),
                        'icon' => 'withdraw-icon'
                    ]
                ],
                'filters' => [
                    'search' => $search,
                    'per_page_options' => [10, 25, 50]
                ]
            ];

            return ApiResponseService::successResponse('Withdrawal details retrieved successfully', $responseData);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting withdrawal details: ' . $e->getMessage());
            return ApiResponseService::errorResponse('Failed to load withdrawal details: ' . $e->getMessage());
        }
    }

    /**
     * Get withdrawal history (legacy method)
     */
    public function getWithdrawalHistory(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponseService::unauthorizedResponse('User not authenticated');
            }

            if (!$user->hasRole(config('constants.SYSTEM_ROLES.INSTRUCTOR'))) {
                return ApiResponseService::validationError('User is not an instructor');
            }

            $instructor = $user->instructor_details;
            if (!$instructor) {
                return ApiResponseService::validationError('Instructor details not found');
            }

            // Get pagination parameters
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // Get withdrawal requests
            $withdrawals = WithdrawalRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Calculate total withdrawn amount (approved and completed withdrawals)
            $totalWithdraw = WithdrawalRequest::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'completed'])
                ->sum('amount');

            // Get current wallet balance
            $walletBalance = $user->wallet_balance ?? 0;

            $withdrawalData = $withdrawals->map(function($withdrawal) {
                $paymentDetails = $withdrawal->payment_details ?? [];
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'formatted_amount' => '$' . number_format($withdrawal->amount, 2),
                    'status' => $withdrawal->status,
                    'status_label' => ucfirst($withdrawal->status),
                    'requested_at' => $withdrawal->created_at->format('Y-m-d H:i:s'),
                    'processed_at' => $withdrawal->updated_at->format('Y-m-d H:i:s'),
                    'notes' => $withdrawal->notes,
                    'payment_method' => $withdrawal->payment_method,
                    'bank_details' => [
                        'account_holder_name' => $paymentDetails['account_holder_name'] ?? null,
                        'account_number' => $paymentDetails['account_number'] ?? null,
                        'bank_name' => $paymentDetails['bank_name'] ?? null,
                        'routing_number' => $paymentDetails['routing_number'] ?? ($paymentDetails['ifsc_code'] ?? null)
                    ]
                ];
            });

            return ApiResponseService::successResponse('Withdrawal history retrieved successfully', [
                'withdrawals' => $withdrawalData,
                'total_withdraw' => (float) $totalWithdraw,
                'wallet_balance' => (float) $walletBalance,
                'pagination' => [
                    'current_page' => $withdrawals->currentPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                    'last_page' => $withdrawals->lastPage(),
                    'from' => $withdrawals->firstItem(),
                    'to' => $withdrawals->lastItem(),
                    'has_more_pages' => $withdrawals->hasMorePages()
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting withdrawal history: ' . $e->getMessage());
            return ApiResponseService::errorResponse('Failed to load withdrawal history: ' . $e->getMessage());
        }
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponseService::unauthorizedResponse('User not authenticated');
            }

            if (!$user->hasRole(config('constants.SYSTEM_ROLES.INSTRUCTOR'))) {
                return ApiResponseService::validationError('User is not an instructor');
            }

            $instructor = $user->instructor_details;
            if (!$instructor) {
                return ApiResponseService::validationError('Instructor details not found');
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'account_holder_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'bank_name' => 'required|string|max:255',
                'routing_number' => 'required|string|max:50',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            // Check if amount is available
            $totalEarning = $this->calculateTotalEarning($user->id);
            $totalWithdrawn = WithdrawalRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');
            $availableToWithdraw = max(0, $totalEarning - $totalWithdrawn);

            if ($request->amount > $availableToWithdraw) {
                return ApiResponseService::validationError('Insufficient balance. Available to withdraw: $' . number_format($availableToWithdraw, 2));
            }

            // Create withdrawal request
            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'account_holder_name' => $request->account_holder_name,
                'account_number' => $request->account_number,
                'bank_name' => $request->bank_name,
                'routing_number' => $request->routing_number,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            return ApiResponseService::successResponse('Withdrawal request submitted successfully', [
                'withdrawal_id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'formatted_amount' => '$' . number_format($withdrawal->amount, 2),
                'status' => $withdrawal->status
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error requesting withdrawal: ' . $e->getMessage());
            return ApiResponseService::errorResponse('Failed to submit withdrawal request: ' . $e->getMessage());
        }
    }

    /**
     * Get instructor sales statistics (yearly, monthly, weekly)
     */
    public function getInstructorSalesStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponseService::unauthorizedResponse('User not authenticated');
            }

            // Check if user is an instructor
            if (!$user->hasRole(config('constants.SYSTEM_ROLES.INSTRUCTOR'))) {
                return ApiResponseService::validationError('User is not an instructor');
            }

            $instructor = $user->instructor_details;
            if (!$instructor) {
                return ApiResponseService::validationError('Instructor details not found');
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'period' => 'nullable|in:yearly,monthly,weekly',
                'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
                'month' => 'nullable|integer|min:1|max:12',
                'week' => 'nullable|integer|min:1|max:53'
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            $period = $request->get('period', 'yearly');
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('n'));
            $week = $request->get('week', date('W'));

            $data = [];

            switch ($period) {
                case 'yearly':
                    $data = $this->getYearlySalesData($user->id, $year);
                    break;
                case 'monthly':
                    $data = $this->getMonthlySalesData($user->id, $year, $month);
                    break;
                case 'weekly':
                    $data = $this->getWeeklySalesData($user->id, $year, $week);
                    break;
            }

            return ApiResponseService::successResponse('Sales statistics retrieved successfully', [
                'period' => $period,
                'year' => $year,
                'month' => $month,
                'week' => $week,
                'data' => $data,
                'summary' => $this->calculateSummary($data)
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting sales statistics: ' . $e->getMessage());
            return ApiResponseService::errorResponse('Failed to load sales statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get yearly sales data
     */
    private function getYearlySalesData($userId, $year)
    {
        $data = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::createFromDate($year, $i, 1)->startOfDay();
            $endOfMonth = Carbon::createFromDate($year, $i, 1)->endOfMonth()->endOfDay();

            $monthData = $this->getMonthData($userId, $startOfMonth, $endOfMonth, $monthNames[$i - 1], $i);
            $data[] = $monthData;
        }

        return $data;
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySalesData($userId, $year, $month)
    {
        $data = [];
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
        $daysInMonth = $endOfMonth->day;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $startOfDay = Carbon::createFromDate($year, $month, $i)->startOfDay();
            $endOfDay = Carbon::createFromDate($year, $month, $i)->endOfDay();

            $dayData = $this->getDayData($userId, $startOfDay, $endOfDay, $i);
            $data[] = $dayData;
        }

        return $data;
    }

    /**
     * Get weekly sales data
     */
    private function getWeeklySalesData($userId, $year, $week)
    {
        $data = [];
        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $startOfDay = $day->startOfDay();
            $endOfDay = $day->endOfDay();

            $dayData = $this->getDayData($userId, $startOfDay, $endOfDay, $day->day, $dayNames[$i]);
            $data[] = $dayData;
        }

        return $data;
    }

    /**
     * Get month data
     */
    private function getMonthData($userId, $startDate, $endDate, $monthName, $monthNumber)
    {
        // Get revenue for this month
        $revenue = Order::whereHas('orderCourses', function($query) use ($userId) {
            $query->whereHas('course', function($courseQuery) use ($userId) {
                $courseQuery->where('user_id', $userId);
            });
        })
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('final_price');

        // Get commission for this month
        $commission = Commission::where('instructor_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('instructor_commission_amount');

        $earnings = $revenue - $commission;

        return [
            'period' => $monthName,
            'period_number' => $monthNumber,
            'revenue' => (float) $revenue,
            'commission' => (float) $commission,
            'earnings' => (float) $earnings,
            'formatted_revenue' => '$' . number_format($revenue, 0),
            'formatted_commission' => '$' . number_format($commission, 0),
            'formatted_earnings' => '$' . number_format($earnings, 0),
            'date_range' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y')
        ];
    }

    /**
     * Get day data
     */
    private function getDayData($userId, $startDate, $endDate, $dayNumber, $dayName = null)
    {
        // Get revenue for this day
        $revenue = Order::whereHas('orderCourses', function($query) use ($userId) {
            $query->whereHas('course', function($courseQuery) use ($userId) {
                $courseQuery->where('user_id', $userId);
            });
        })
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('final_price');

        // Get commission for this day
        $commission = Commission::where('instructor_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('instructor_commission_amount');

        $earnings = $revenue - $commission;

        return [
            'period' => $dayName ?: 'Day ' . $dayNumber,
            'period_number' => $dayNumber,
            'revenue' => (float) $revenue,
            'commission' => (float) $commission,
            'earnings' => (float) $earnings,
            'formatted_revenue' => '$' . number_format($revenue, 0),
            'formatted_commission' => '$' . number_format($commission, 0),
            'formatted_earnings' => '$' . number_format($earnings, 0),
            'date' => $startDate->format('M d, Y')
        ];
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary($data)
    {
        $totalRevenue = array_sum(array_column($data, 'revenue'));
        $totalCommission = array_sum(array_column($data, 'commission'));
        $totalEarnings = array_sum(array_column($data, 'earnings'));
        
        $avgRevenue = count($data) > 0 ? $totalRevenue / count($data) : 0;
        $avgCommission = count($data) > 0 ? $totalCommission / count($data) : 0;
        $avgEarnings = count($data) > 0 ? $totalEarnings / count($data) : 0;

        $maxRevenue = max(array_column($data, 'revenue'));
        $maxCommission = max(array_column($data, 'commission'));
        $maxEarnings = max(array_column($data, 'earnings'));

        $minRevenue = min(array_column($data, 'revenue'));
        $minCommission = min(array_column($data, 'commission'));
        $minEarnings = min(array_column($data, 'earnings'));

        return [
            'totals' => [
                'revenue' => $totalRevenue,
                'commission' => $totalCommission,
                'earnings' => $totalEarnings,
                'formatted_revenue' => '$' . number_format($totalRevenue, 2),
                'formatted_commission' => '$' . number_format($totalCommission, 2),
                'formatted_earnings' => '$' . number_format($totalEarnings, 2)
            ],
            'averages' => [
                'revenue' => $avgRevenue,
                'commission' => $avgCommission,
                'earnings' => $avgEarnings,
                'formatted_revenue' => '$' . number_format($avgRevenue, 2),
                'formatted_commission' => '$' . number_format($avgCommission, 2),
                'formatted_earnings' => '$' . number_format($avgEarnings, 2)
            ],
            'maximums' => [
                'revenue' => $maxRevenue,
                'commission' => $maxCommission,
                'earnings' => $maxEarnings,
                'formatted_revenue' => '$' . number_format($maxRevenue, 2),
                'formatted_commission' => '$' . number_format($maxCommission, 2),
                'formatted_earnings' => '$' . number_format($maxEarnings, 2)
            ],
            'minimums' => [
                'revenue' => $minRevenue,
                'commission' => $minCommission,
                'earnings' => $minEarnings,
                'formatted_revenue' => '$' . number_format($minRevenue, 2),
                'formatted_commission' => '$' . number_format($minCommission, 2),
                'formatted_earnings' => '$' . number_format($minEarnings, 2)
            ]
        ];
    }

    /**
     * Get course analysis for instructor
     */
    public function getCourseAnalysis(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponseService::unauthorizedResponse('User not authenticated');
            }

            // Check if user is an instructor
            if (!$user->hasRole(config('constants.SYSTEM_ROLES.INSTRUCTOR'))) {
                return ApiResponseService::validationError('User is not an instructor');
            }

            $instructor = $user->instructor_details;
            if (!$instructor) {
                return ApiResponseService::validationError('Instructor details not found');
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'course_id' => 'nullable|exists:courses,id',
                'course_slug' => 'nullable|exists:courses,slug'
            ]);

            if ($validator->fails()) {
                return ApiResponseService::validationError($validator->errors()->first());
            }

            // Custom validation to ensure either course_id or course_slug is provided, but not both
            if (!$request->has('course_id') && !$request->has('course_slug')) {
                return ApiResponseService::validationError('Either course_id or course_slug must be provided');
            }

            if ($request->has('course_id') && $request->has('course_slug')) {
                return ApiResponseService::validationError('Provide either course_id or course_slug, not both');
            }

            // Determine course ID
            $courseId = $request->course_id;
            if ($request->has('course_slug')) {
                $course = \App\Models\Course\Course::where('slug', $request->course_slug)->first();
                if (!$course) {
                    return ApiResponseService::validationError('Course not found');
                }
                $courseId = $course->id;
            }

            // Get course details
            $course = \App\Models\Course\Course::where('id', $courseId)
                ->where('user_id', $user->id)
                ->first();

            if (!$course) {
                return ApiResponseService::validationError('Course not found or you do not have permission to view this course');
            }

            // Get course statistics
            $courseStats = $this->getCourseStatistics($courseId, $user->id);
            
            // Get revenue and earnings chart data
            $revenueChartData = $this->getCourseRevenueChartData($courseId, $user->id);
            $earningsChartData = $this->getCourseEarningsChartData($courseId, $user->id);

            // Get average rating and total ratings count
            $averageRating = \App\Models\Rating::where('rateable_id', $courseId)
                ->where('rateable_type', 'App\Models\Course\Course')
                ->avg('rating') ?? 0;
            $averageRating = round($averageRating, 2);
            
            $totalRatingsCount = \App\Models\Rating::where('rateable_id', $courseId)
                ->where('rateable_type', 'App\Models\Course\Course')
                ->count();

            $responseData = [
                'course_info' => [
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'short_description' => $course->short_description,
                    'thumbnail' => $course->thumbnail ? asset('storage/' . $course->thumbnail) : null,
                    'average_rating' => $averageRating,
                    'total_ratings_count' => $totalRatingsCount,
                    'price' => (float) $course->price,
                    'discount_price' => $course->discount_price ? (float) $course->discount_price : null
                ],
                'summary_cards' => $courseStats,
                'revenue_chart' => $revenueChartData,
                'earnings_chart' => $earningsChartData
            ];

            return ApiResponseService::successResponse('Course analysis retrieved successfully', $responseData);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting course analysis: ' . $e->getMessage());
            return ApiResponseService::errorResponse('Failed to load course analysis: ' . $e->getMessage());
        }
    }

    /**
     * Get course statistics
     */
    private function getCourseStatistics($courseId, $instructorId)
    {
        $year = now()->year;
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Total enrollments
        $totalEnrollments = UserCourseTrack::where('course_id', $courseId)->count();
        
        // Completed enrollments (using status column)
        $completedEnrollments = UserCourseTrack::where('course_id', $courseId)
            ->where('status', 'completed')
            ->count();
        
        // Completion rate
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0;
        
        // Total revenue from this course
        $totalRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->where('status', 'completed')
        ->sum('final_price');
        
        // Average rating (using polymorphic relationship)
        $averageRating = \App\Models\Rating::where('rateable_id', $courseId)
            ->where('rateable_type', 'App\Models\Course\Course')
            ->avg('rating') ?? 0;
        $averageRating = round($averageRating, 2);
        
        // Total ratings count
        $totalRatings = \App\Models\Rating::where('rateable_id', $courseId)
            ->where('rateable_type', 'App\Models\Course\Course')
            ->count();
        
        // Recent enrollments (last 30 days)
        $recentEnrollments = UserCourseTrack::where('course_id', $courseId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Calculate total commission (assuming 20% commission rate)
        $totalCommission = Commission::where('instructor_id', $instructorId)
            ->whereHas('order.orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->whereYear('created_at', $year)
            ->sum('instructor_commission_amount');
        
        // If no commission records found, calculate 20% of revenue
        if ($totalCommission == 0 && $totalRevenue > 0) {
            $totalCommission = $totalRevenue * 0.20;
        }
        
        // Calculate total earning (revenue - commission)
        $totalEarning = $totalRevenue - $totalCommission;

        // Get monthly revenue, commission, and earning chart data for current year
        $revenueChartData = [];
        $commissionChartData = [];
        $earningChartData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('final_price');
            
            // Calculate monthly commission (20% of revenue)
            $monthlyCommission = $monthlyRevenue * 0.20;
            $monthlyEarning = $monthlyRevenue - $monthlyCommission;
            
            $revenueChartData[] = [
                'name' => $monthNames[$month - 1],
                'earning' => (float) $monthlyRevenue
            ];
            
            $commissionChartData[] = [
                'name' => $monthNames[$month - 1],
                'earning' => (float) $monthlyCommission
            ];
            
            $earningChartData[] = [
                'name' => $monthNames[$month - 1],
                'earning' => (float) $monthlyEarning
            ];
        }

        return [
            'total_revenue' => [
                'value' => number_format($totalRevenue, 2),
                'formatted_value' => '$' . number_format($totalRevenue, 2),
                'chartData' => $revenueChartData
            ],
            'total_commission' => [
                'value' => number_format($totalCommission, 2),
                'formatted_value' => '$' . number_format($totalCommission, 2),
                'chartData' => $commissionChartData
            ],
            'total_earning' => [
                'value' => number_format($totalEarning, 2),
                'formatted_value' => '$' . number_format($totalEarning, 2),
                'chartData' => $earningChartData
            ],
        ];
    }

    /**
     * Get enrollment data over time
     */
    private function getEnrollmentData($courseId)
    {
        $enrollments = UserCourseTrack::where('course_id', $courseId)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $enrollment = $enrollments->where('date', $date)->first();
            $data[] = [
                'date' => $date,
                'enrollments' => $enrollment ? $enrollment->count : 0,
                'formatted_date' => now()->subDays($i)->format('M d')
            ];
        }

        return $data;
    }

    /**
     * Get course revenue data
     */
    private function getCourseRevenueData($courseId, $instructorId)
    {
        // Monthly revenue for last 12 months
        $revenueData = [];
        for ($i = 11; $i >= 0; $i--) {
            $startOfMonth = now()->subMonths($i)->startOfMonth();
            $endOfMonth = now()->subMonths($i)->endOfMonth();
            
            $monthlyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_price');
            
            $revenueData[] = [
                'month' => $startOfMonth->format('M Y'),
                'revenue' => $monthlyRevenue,
                'formatted_revenue' => '$' . number_format($monthlyRevenue, 0)
            ];
        }

        return $revenueData;
    }

    /**
     * Get course revenue chart data with yearly, monthly, weekly options
     */
    private function getCourseRevenueChartData($courseId, $instructorId)
    {
        $currentYear = now()->year;
        
        return [
            'yearly' => $this->getCourseYearlyRevenueData($courseId, $currentYear),
            'monthly' => $this->getCourseMonthlyRevenueData($courseId, $currentYear, now()->month),
            'weekly' => $this->getCourseWeeklyRevenueData($courseId, $currentYear, now()->weekOfYear)
        ];
    }

    /**
     * Get yearly revenue data for course (12 months)
     */
    private function getCourseYearlyRevenueData($courseId, $year)
    {
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::create($year, $i, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $i, 1)->endOfMonth();
            
            // Revenue from orders
            $monthlyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_price');
            
            // Commission (assuming 20% commission rate)
            $monthlyCommission = $monthlyRevenue * 0.20;
            
            $data[] = [
                'name' => $startOfMonth->format('M'),
                'revenue' => (float) $monthlyRevenue,
                'commission' => (float) $monthlyCommission
            ];
        }
        
        return $data;
    }

    /**
     * Get monthly revenue data for course (30 days)
     */
    private function getCourseMonthlyRevenueData($courseId, $year, $month)
    {
        $data = [];
        
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->endOfDay();
            
            // Revenue from orders
            $dailyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_price');
            
            // Commission (assuming 20% commission rate)
            $dailyCommission = $dailyRevenue * 0.20;
            
            $data[] = [
                'name' => $i,
                'revenue' => (float) $dailyRevenue,
                'commission' => (float) $dailyCommission
            ];
        }
        
        return $data;
    }

    /**
     * Get weekly revenue data for course (7 days)
     */
    private function getCourseWeeklyRevenueData($courseId, $year, $week)
    {
        $data = [];
        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        // Get the start of the week (Monday)
        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->endOfDay();
            
            // Revenue from orders
            $dailyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_price');
            
            // Commission (assuming 20% commission rate)
            $dailyCommission = $dailyRevenue * 0.20;
            
            $data[] = [
                'name' => $weekDays[$i],
                'revenue' => (float) $dailyRevenue,
                'commission' => (float) $dailyCommission
            ];
        }
        
        return $data;
    }

    /**
     * Get course earnings chart data with yearly, monthly, weekly options
     */
    private function getCourseEarningsChartData($courseId, $instructorId)
    {
        $currentYear = now()->year;
        
        return [
            'yearly' => $this->getCourseYearlyEarningsData($courseId, $currentYear),
            'monthly' => $this->getCourseMonthlyEarningsData($courseId, $currentYear, now()->month),
            'weekly' => $this->getCourseWeeklyEarningsData($courseId, $currentYear, now()->weekOfYear)
        ];
    }

    /**
     * Get yearly earnings data for course (12 months)
     */
    private function getCourseYearlyEarningsData($courseId, $year)
    {
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $startOfMonth = Carbon::create($year, $i, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $i, 1)->endOfMonth();
            
            // Revenue from orders
            $monthlyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('final_price');
            
            // Commission (assuming 20% commission rate)
            $monthlyCommission = $monthlyRevenue * 0.20;
            
            // Earnings = Revenue - Commission
            $monthlyEarnings = $monthlyRevenue - $monthlyCommission;
            
            $data[] = [
                'name' => $startOfMonth->format('M'),
                'earning' => (float) $monthlyEarnings
            ];
        }
        
        return $data;
    }

    /**
     * Get monthly earnings data for course (30 days)
     */
    private function getCourseMonthlyEarningsData($courseId, $year, $month)
    {
        $data = [];
        
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->endOfDay();
            
            // Revenue from orders
            $dailyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_price');
            
            // Commission (assuming 20% commission rate)
            $dailyCommission = $dailyRevenue * 0.20;
            
            // Earnings = Revenue - Commission
            $dailyEarnings = $dailyRevenue - $dailyCommission;
            
            $data[] = [
                'name' => $i,
                'earning' => (float) $dailyEarnings
            ];
        }
        
        return $data;
    }

    /**
     * Get weekly earnings data for course (7 days)
     */
    private function getCourseWeeklyEarningsData($courseId, $year, $week)
    {
        $data = [];
        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        // Get the start of the week (Monday)
        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->endOfDay();
            
            // Revenue from orders
            $dailyRevenue = Order::whereHas('orderCourses', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('final_price');
            
            // Commission (assuming 20% commission rate)
            $dailyCommission = $dailyRevenue * 0.20;
            
            // Earnings = Revenue - Commission
            $dailyEarnings = $dailyRevenue - $dailyCommission;
            
            $data[] = [
                'name' => $weekDays[$i],
                'earning' => (float) $dailyEarnings
            ];
        }
        
        return $data;
    }

    /**
     * Get course completion data
     */
    private function getCourseCompletionData($courseId)
    {
        $completions = UserCourseTrack::where('course_id', $courseId)
            ->where('status', 'completed')
            ->selectRaw('DATE(updated_at) as completion_date, COUNT(*) as count')
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('completion_date')
            ->orderBy('completion_date')
            ->get();

        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $completion = $completions->where('completion_date', $date)->first();
            $data[] = [
                'date' => $date,
                'completions' => $completion ? $completion->count : 0,
                'formatted_date' => now()->subDays($i)->format('M d')
            ];
        }

        return $data;
    }

    /**
     * Get course rating data
     */
    private function getCourseRatingData($courseId)
    {
        $ratings = \App\Models\Rating::where('rateable_id', $courseId)
            ->where('rateable_type', 'App\Models\Course\Course')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $rating = $ratings->where('rating', $i)->first();
            $ratingDistribution[] = [
                'rating' => $i,
                'count' => $rating ? $rating->count : 0,
                'percentage' => 0 // Will be calculated below
            ];
        }

        $totalRatings = $ratings->sum('count');
        foreach ($ratingDistribution as &$rating) {
            $rating['percentage'] = $totalRatings > 0 ? round(($rating['count'] / $totalRatings) * 100, 1) : 0;
        }

        return [
            'distribution' => $ratingDistribution,
            'total_ratings' => $totalRatings,
            'average_rating' => $totalRatings > 0 ? round($ratings->avg('rating'), 2) : 0
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($courseId)
    {
        $activities = collect();

        // Recent enrollments
        $recentEnrollments = UserCourseTrack::with('user')
            ->where('course_id', $courseId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($enrollment) {
                return [
                    'type' => 'enrollment',
                    'message' => $enrollment->user->name . ' enrolled in the course',
                    'date' => $enrollment->created_at,
                    'formatted_date' => $enrollment->created_at->diffForHumans()
                ];
            });

        // Recent completions
        $recentCompletions = UserCourseTrack::with('user')
            ->where('course_id', $courseId)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($completion) {
                return [
                    'type' => 'completion',
                    'message' => $completion->user->name . ' completed the course',
                    'date' => $completion->updated_at,
                    'formatted_date' => $completion->updated_at->diffForHumans()
                ];
            });

        // Recent ratings
        $recentRatings = \App\Models\Rating::with('user')
            ->where('rateable_id', $courseId)
            ->where('rateable_type', 'App\Models\Course\Course')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($rating) {
                return [
                    'type' => 'rating',
                    'message' => $rating->user->name . ' rated the course ' . $rating->rating . ' stars',
                    'date' => $rating->created_at,
                    'formatted_date' => $rating->created_at->diffForHumans()
                ];
            });

        $activities = $activities->merge($recentEnrollments)
            ->merge($recentCompletions)
            ->merge($recentRatings);

        return $activities->sortByDesc('date')->take(10)->values();
    }

    /**
     * Calculate total earning for instructor
     */
    private function calculateTotalEarning($userId)
    {
        $totalRevenue = Order::whereHas('orderCourses', function($query) use ($userId) {
            $query->whereHas('course', function($courseQuery) use ($userId) {
                $courseQuery->where('user_id', $userId);
            });
        })
        ->where('status', 'completed')
        ->sum('final_price');

        $totalCommission = Commission::where('instructor_id', $userId)
            ->sum('instructor_commission_amount');

        return $totalRevenue - $totalCommission;
    }
}
