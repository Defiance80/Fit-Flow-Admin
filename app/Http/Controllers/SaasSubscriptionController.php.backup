<?php

namespace App\Http\Controllers;

use App\Models\SaasSubscription;
use App\Models\User;
use Illuminate\Http\Request;

class SaasSubscriptionController extends Controller
{
    /**
     * Display a listing of the subscriptions.
     */
    public function index(Request $request)
    {
        $query = SaasSubscription::with(['user']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subQ) use ($search) {
                    $subQ->where('first_name', 'LIKE', "%{$search}%")
                         ->orWhere('last_name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                });
            });
        }

        // Filter by tier
        if ($request->has('tier') && !empty($request->tier)) {
            $query->where('tier', $request->tier);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by billing cycle
        if ($request->has('billing_cycle') && !empty($request->billing_cycle)) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate summary statistics
        $totalRevenue = SaasSubscription::where('status', 'active')->sum('monthly_price');
        $activeSubscriptions = SaasSubscription::where('status', 'active')->count();
        $trialSubscriptions = SaasSubscription::where('status', 'trialing')->count();
        $churnedThisMonth = SaasSubscription::where('status', 'cancelled')
            ->whereMonth('updated_at', now()->month)
            ->count();

        return view('subscriptions.index', compact(
            'subscriptions', 'totalRevenue', 'activeSubscriptions', 'trialSubscriptions', 'churnedThisMonth'
        ), [
            'type_menu' => 'subscriptions'
        ]);
    }

    /**
     * Display the specified subscription.
     */
    public function show(SaasSubscription $subscription)
    {
        $subscription->load(['user']);
        
        // Get subscription history/changes
        $paymentHistory = []; // This would typically come from a payments/transactions table
        
        // Calculate subscription metrics
        $daysRemaining = $subscription->current_period_end ? 
            now()->diffInDays($subscription->current_period_end, false) : 0;
            
        $nextBillingAmount = $subscription->billing_cycle === 'monthly' ? 
            $subscription->monthly_price : 
            ($subscription->monthly_price * 12 * 0.9); // 10% annual discount

        return view('subscriptions.show', compact(
            'subscription', 'paymentHistory', 'daysRemaining', 'nextBillingAmount'
        ), [
            'type_menu' => 'subscriptions'
        ]);
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, SaasSubscription $subscription)
    {
        $validatedData = $request->validate([
            'tier' => 'required|in:starter,professional,enterprise',
            'status' => 'required|in:trialing,active,past_due,cancelled,paused',
            'billing_cycle' => 'required|in:monthly,yearly',
            'monthly_price' => 'required|numeric|min:0|max:999.99',
            'max_trainers' => 'nullable|integer|min:1|max:1000',
            'max_clients' => 'nullable|integer|min:1|max:10000',
            'max_facilities' => 'nullable|integer|min:1|max:100',
            'features' => 'nullable|json',
            'notes' => 'nullable|string'
        ]);

        try {
            // Update current period end if changing billing cycle
            if ($request->billing_cycle !== $subscription->billing_cycle) {
                if ($request->billing_cycle === 'yearly') {
                    $validatedData['current_period_end'] = now()->addYear();
                } else {
                    $validatedData['current_period_end'] = now()->addMonth();
                }
            }

            $subscription->update($validatedData);

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Subscription updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update subscription: ' . $e->getMessage());
        }
    }
}
