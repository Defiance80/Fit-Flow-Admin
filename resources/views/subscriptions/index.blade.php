@extends('layouts.app')
@section('title', __('Subscriptions'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1><i class="fas fa-credit-card" style="color:var(--success)"></i> {{ __('Subscriptions') }}</h1></div>
    <div class="section-body"><div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-striped"><thead><tr>
            <th>{{ __('User') }}</th><th>{{ __('Tier') }}</th><th>{{ __('Status') }}</th><th>{{ __('Billing') }}</th><th>{{ __('Price') }}</th><th>{{ __('Clients') }}</th><th>{{ __('Period') }}</th>
        </tr></thead><tbody>
        @forelse($subscriptions as $sub)
        <tr><td>{{ $sub->user->name ?? 'N/A' }}</td>
            <td><span class="badge badge-{{ $sub->tier == 'enterprise' ? 'danger' : ($sub->tier == 'premium' ? 'warning' : ($sub->tier == 'pro' ? 'primary' : 'light')) }}">{{ ucfirst($sub->tier) }}</span></td>
            <td><span class="badge badge-{{ $sub->status == 'active' ? 'success' : ($sub->status == 'trialing' ? 'info' : 'secondary') }}">{{ ucfirst($sub->status) }}</span></td>
            <td>{{ ucfirst($sub->billing_cycle) }}</td>
            <td>${{ number_format($sub->price, 2) }}/mo</td>
            <td>{{ $sub->max_clients }}</td>
            <td><small>{{ $sub->current_period_start?->format('M d') }} - {{ $sub->current_period_end?->format('M d, Y') }}</small></td>
        </tr>
        @empty<tr><td colspan="7" class="text-center py-4">No subscriptions yet.</td></tr>@endforelse
        </tbody></table>
    </div></div><div class="card-footer">{{ $subscriptions->links() }}</div></div></div>
</section></div>
@endsection
