<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Commission;
use App\Models\Instructor;
use App\Services\HelperService;
use App\Services\WalletService;
use App\Models\Course\Course;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Calculate and create commission records after successful payment
     *
     * @param Order $order
     * @return void
     */
    public static function calculateCommissions(Order $order)
    {
        try {
            DB::beginTransaction();

            // Get commission settings
            $individualCommissionRate = (float) HelperService::systemSettings('individual_admin_commission') ?: 5.0;
            $teamCommissionRate = (float) HelperService::systemSettings('team_admin_commission') ?: 10.0;

            foreach ($order->orderCourses as $orderCourse) {
                $course = Course::with(['user', 'instructors'])->find($orderCourse->course_id);
                
                if (!$course) {
                    Log::warning("Course {$orderCourse->course_id} not found");
                    continue;
                }

                // Get course price (after discount if any)
                $coursePrice = $orderCourse->price; // This should be the discounted price if discount was applied
                $originalPrice = $course->price; // Original course price

                // Collect all instructors (course owner + additional instructors)
                $allInstructors = collect();
                
                // Add course owner if exists
                if ($course->user) {
                    $allInstructors->push($course->user);
                }
                
                // Add additional instructors (avoid duplicates)
                if ($course->instructors && $course->instructors->isNotEmpty()) {
                    foreach ($course->instructors as $instructor) {
                        if (!$allInstructors->contains('id', $instructor->id)) {
                            $allInstructors->push($instructor);
                        }
                    }
                }

                if ($allInstructors->isEmpty()) {
                    Log::warning("Course {$orderCourse->course_id} has no owner or instructors assigned");
                    continue;
                }

                // Process commission for each instructor of the course
                foreach ($allInstructors as $instructor) {
                    $instructorRecord = Instructor::where('user_id', $instructor->id)->first();
                    $instructorType = $instructorRecord ? $instructorRecord->type : 'individual';

                    // Determine commission rates based on instructor type
                    if ($instructorType === 'team') {
                        $adminCommissionRate = $teamCommissionRate;
                    } else {
                        $adminCommissionRate = $individualCommissionRate;
                    }

                    $instructorCommissionRate = 100 - $adminCommissionRate;

                    // Calculate commission amounts
                    $adminCommissionAmount = ($coursePrice * $adminCommissionRate) / 100;
                    $instructorCommissionAmount = ($coursePrice * $instructorCommissionRate) / 100;

                    // Create commission record
                    Commission::create([
                        'order_id' => $order->id,
                        'course_id' => $course->id,
                        'instructor_id' => $instructor->id,
                        'instructor_type' => $instructorType,
                        'course_price' => $originalPrice,
                        'discounted_price' => $coursePrice,
                        'admin_commission_rate' => $adminCommissionRate,
                        'admin_commission_amount' => $adminCommissionAmount,
                        'instructor_commission_rate' => $instructorCommissionRate,
                        'instructor_commission_amount' => $instructorCommissionAmount,
                        'status' => 'pending'
                    ]);

                    Log::info("Commission calculated for Order: {$order->id}, Course: {$course->id}, Instructor: {$instructor->id}", [
                        'admin_commission' => $adminCommissionAmount,
                        'instructor_commission' => $instructorCommissionAmount,
                        'instructor_type' => $instructorType
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commission calculation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mark commissions as paid and credit instructor wallets
     *
     * @param Order $order
     * @return void
     */
    public static function markCommissionsAsPaid(Order $order)
    {
        try {
            DB::beginTransaction();

            $commissions = Commission::where('order_id', $order->id)
                ->where('status', 'pending')
                ->with(['course', 'order'])
                ->get();

            foreach ($commissions as $commission) {
                
                WalletService::creditWallet(
                    $commission->instructor_id,
                    $commission->instructor_commission_amount,
                    'commission',
                    "Commission for course: {$commission->course->title} (Order #{$order->order_number})",
                    $commission->id,
                    'App\Models\Commission'
                );

                // Update commission status
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);

                // Send notification to instructor
                $instructor = User::find($commission->instructor_id);
                if ($instructor) {
                    $instructor->notify(new CommissionPaidNotification($commission));
                }

                Log::info("Wallet credited for instructor {$commission->instructor_id}", [
                    'order_id' => $order->id,
                    'commission_id' => $commission->id,
                    'amount' => $commission->instructor_commission_amount
                ]);
            }

            DB::commit();
            Log::info("Commissions marked as paid and wallets credited for Order: {$order->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process commission payments', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get total admin commission for an order
     *
     * @param int $orderId
     * @return float
     */
    public static function getTotalAdminCommission(int $orderId): float
    {
        return Commission::where('order_id', $orderId)
            ->sum('admin_commission_amount');
    }

    /**
     * Get total instructor commission for an order
     *
     * @param int $orderId
     * @return float
     */
    public static function getTotalInstructorCommission(int $orderId): float
    {
        return Commission::where('order_id', $orderId)
            ->sum('instructor_commission_amount');
    }

    /**
     * Get instructor commissions for a specific instructor
     *
     * @param int $instructorId
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getInstructorCommissions(int $instructorId, string $status = null)
    {
        $query = Commission::where('instructor_id', $instructorId)
            ->with(['order', 'course']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
