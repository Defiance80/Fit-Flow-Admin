@extends('layouts.app')
@section('title', __('Meal Plans'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ __('Meal Plans') }}</h1>
        <div class="section-header-button"><a href="{{ route('meal-plans.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('Create Plan') }}</a></div></div>
    <div class="section-body">
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-striped"><thead><tr>
                <th>{{ __('Name') }}</th><th>{{ __('Client') }}</th><th>{{ __('Trainer') }}</th><th>{{ __('Goal') }}</th><th>{{ __('Calories') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th>
            </tr></thead><tbody>
            @forelse($mealPlans as $plan)
            <tr><td><strong>{{ $plan->name }}</strong></td>
                <td>{{ $plan->client->name ?? 'N/A' }}</td>
                <td>{{ $plan->trainer->name ?? 'N/A' }}</td>
                <td><span class="badge badge-light">{{ ucfirst(str_replace('_',' ',$plan->goal)) }}</span></td>
                <td>{{ $plan->daily_calories ?? '-' }} kcal<br><small class="text-muted">P:{{ $plan->protein_g ?? '-' }}g C:{{ $plan->carbs_g ?? '-' }}g F:{{ $plan->fats_g ?? '-' }}g</small></td>
                <td><span class="badge badge-{{ $plan->is_active ? 'success' : 'secondary' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td><a href="{{ route('meal-plans.show', $plan) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('meal-plans.edit', $plan) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a></td>
            </tr>
            @empty<tr><td colspan="7" class="text-center py-4">No meal plans yet.</td></tr>@endforelse
            </tbody></table>
        </div></div><div class="card-footer">{{ $mealPlans->links() }}</div></div>
    </div>
</section></div>
@endsection
