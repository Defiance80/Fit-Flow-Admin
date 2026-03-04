<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Stripe\Stripe;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Transaction;
use App\Models\WalletHistory;
use App\Helpers\NotificationHelper;
use App\Services\CommissionService;
use App\Services\HelperService;
use App\Services\ApiResponseService;
use Stripe\Checkout\Session as StripeSession;

    /**
     * Handle Stripe payment success callback.
     * This is called by Stripe after a successful payment.
     * URL: /stripe-callback
     */
class StripeController extends Controller
{
    /**
     * Get the base URL from settings or fallback to APP_URL
     */
    private function getBaseUrl()
    {
        $websiteUrl = HelperService::systemSettings('website_url');
        return $websiteUrl ?: env('APP_URL');
    }

    public function handleStripeCallback(Request $request)
{   
    $sessionId   = $request->query('session_id');
    $orderNumber = $request->query('order');
    $type = $request->query('type', 'web'); // Get type parameter, default to 'web'

    // ✅ Set your secret key (prefer settings/env, not hard-coded)
    Stripe::setApiKey(env('STRIPE_SECRET'));

    try {
        // Optionally expand payment_intent to read status/charges
        $checkoutSession = StripeSession::retrieve([
            'id' => $sessionId,
            'expand' => ['payment_intent', 'customer', 'customer_details'],
        ]);

        if ($checkoutSession->payment_status === 'paid') {
            // Find order by order_number or metadata
            $order = null;
            if ($orderNumber) {
                $order = Order::with('orderCourses')->where('order_number', $orderNumber)->first();
            }
            if (!$order && isset($checkoutSession->metadata->order_id)) {
                $order = Order::with('orderCourses')->find($checkoutSession->metadata->order_id);
            }

            if (!$order) {
                // Check type for response format
                if ($type === 'app') {
                return ApiResponseService::errorResponse('Order not found', [
                        'status' => 'order_not_found',
                    'redirect_url' => $this->getBaseUrl() . '/payment-status?status=order_not_found'
                ], 404);
                }
                return redirect($this->getBaseUrl() . '/payment-status?status=order_not_found');
            }

            // Amount is in the smallest unit; divide by 100 for most currencies
            $amountTotal = $checkoutSession->amount_total; // integer
            // If you support zero-decimal currencies, convert accordingly (you likely charge INR/USD so /100 is fine)

            $order->update([
                'status'         => 'completed',
                'is_payment'     => 1,
                'transaction_id' => $checkoutSession->payment_intent?->id ?? $checkoutSession->payment_intent,
            ]);

            Transaction::create([
                'user_id'        => $order->user_id,
                'order_id'       => $order->id,
                'transaction_id' => $checkoutSession->payment_intent?->id ?? $checkoutSession->payment_intent,
                'amount'         => $amountTotal / 100,
                'payment_method' => 'stripe',
                'status'         => 'completed',
                'message'        => 'Payment successful via Stripe',
            ]);
            
            // Create curriculum tracking entries for all curriculum items
            $order->load('user');
            if ($order->user) {
                \App\Services\OrderTrackingService::createCurriculumTrackingEntries($order, $order->user);
            }

            // ✅ Calculate and create commission records
            try {
                CommissionService::calculateCommissions($order);
                CommissionService::markCommissionsAsPaid($order);
                Log::info("Commission processing completed for Order: {$order->id}");
            } catch (\Exception $e) {
                Log::error("Commission processing failed for Order: {$order->id}", [
                    'error' => $e->getMessage()
                ]);
                // Don't fail the entire payment process if commission calculation fails
            }

            // Dispatch notification for completed order
            $order->load('user');
            if ($order->user) {
                dispatch(new \App\Jobs\SendOrderNotifications($order->fresh(), $order->user));
            }

            // Check type for response format
            if ($type === 'app') {
            return ApiResponseService::successResponse('Payment completed successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => 'completed',
                'transaction_id' => $checkoutSession->payment_intent?->id ?? $checkoutSession->payment_intent,
                'amount' => $amountTotal / 100,
                'redirect_url' => $this->getBaseUrl() . '/payment-status?status=completed'
            ]);
        }

            return redirect($this->getBaseUrl() . '/payment-status?status=completed');
        }

        // Payment failed - check type for response format
        if ($type === 'app') {
        return ApiResponseService::errorResponse('Payment failed', [
            'status' => 'payment_failed',
            'session_id' => $sessionId,
            'redirect_url' => $this->getBaseUrl() . '/payment-status?status=payment_failed'
        ], 400);
        }
        
        return redirect($this->getBaseUrl() . '/payment-status?status=payment_failed');
    } catch (\Throwable $e) {
        Log::error('Stripe callback error: '.$e->getMessage());
        ApiResponseService::logErrorResponse($e, 'Stripe callback error');
        
        // Check type for response format
        if ($type === 'app') {
        return ApiResponseService::errorResponse('Payment processing error occurred', [
            'status' => 'error',
            'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing payment',
            'redirect_url' => $this->getBaseUrl() . '/payment-status?status=error'
        ], 500, $e);
        }
        
        return redirect($this->getBaseUrl() . '/payment-status?status=error');
    }
}

    /**
     * Handle Stripe payment cancellation.
     * URL: /stripe-cancel
     */
    public function handleStripeCancel(Request $request)
    {
        $type = $request->query('type', 'web'); // Get type parameter, default to 'web'
        
        try {
            $orderNumber = $request->query('order');
            $order = null;
            
            if ($orderNumber) {
                $order = Order::where('order_number', $orderNumber)->first();
                if ($order) {
                    $order->update([
                        'status' => 'cancelled',
                    ]);
                }
            }

            // Check type for response format
            if ($type === 'app') {
            return ApiResponseService::successResponse('Payment cancelled', [
                'status' => 'cancelled',
                'order_id' => $order ? $order->id : null,
                'order_number' => $orderNumber,
                'redirect_url' => $this->getBaseUrl() . '/payment-status?status=cancelled'
            ]);
            }
            
            return redirect($this->getBaseUrl() . '/payment-status?status=cancelled');
        } catch (\Throwable $e) {
            Log::error('Stripe cancel error: '.$e->getMessage());
            ApiResponseService::logErrorResponse($e, 'Stripe cancel error');
            
            // Check type for response format
            if ($type === 'app') {
            return ApiResponseService::errorResponse('Error processing cancellation', [
                'status' => 'error',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing cancellation',
                'redirect_url' => $this->getBaseUrl() . '/payment-status?status=error'
            ], 500, $e);
            }
            
            return redirect($this->getBaseUrl() . '/payment-status?status=error');
        }
    }
}