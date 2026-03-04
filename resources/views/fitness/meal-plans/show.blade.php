@extends('layouts.app')
@section('title', $mealPlan->name)
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ $mealPlan->name }}</h1>
        <div class="section-header-breadcrumb"><a href="{{ route('meal-plans.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a><a href="{{ route('meal-plans.edit', $mealPlan) }}" class="btn btn-warning ml-1"><i class="fas fa-edit"></i> Edit</a></div></div>
    <div class="section-body">
        <div class="row">
            <div class="col-md-4"><div class="card"><div class="card-body">
                <p><strong>Client:</strong> {{ $mealPlan->client->name ?? 'N/A' }}</p>
                <p><strong>Goal:</strong> {{ ucfirst(str_replace('_',' ',$mealPlan->goal)) }}</p>
                <hr>
                <div class="row text-center">
                    <div class="col-3"><h5 style="color:var(--primary)">{{ $mealPlan->daily_calories ?? '-' }}</h5><small>kcal</small></div>
                    <div class="col-3"><h5 style="color:var(--danger)">{{ $mealPlan->protein_g ?? '-' }}g</h5><small>Protein</small></div>
                    <div class="col-3"><h5 style="color:var(--warning)">{{ $mealPlan->carbs_g ?? '-' }}g</h5><small>Carbs</small></div>
                    <div class="col-3"><h5 style="color:var(--success)">{{ $mealPlan->fats_g ?? '-' }}g</h5><small>Fats</small></div>
                </div>
                @if($mealPlan->notes)<hr><p class="text-muted">{{ $mealPlan->notes }}</p>@endif
            </div></div></div>
            <div class="col-md-8">
                @forelse($mealPlan->days as $day)
                <div class="card"><div class="card-header" style="background:var(--primary);color:#fff;border-radius:3px 3px 0 0"><h4 style="color:#fff">{{ ucfirst($day->day_of_week) }} <span class="badge badge-light">{{ ucfirst($day->day_type) }}</span></h4></div>
                    <div class="card-body">@forelse($day->meals as $meal)
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <div><span class="badge badge-primary mr-2">{{ ucfirst(str_replace('_',' ',$meal->meal_type)) }}</span><strong>{{ $meal->name }}</strong>
                                @if($meal->description)<br><small class="text-muted">{{ $meal->description }}</small>@endif</div>
                            <div class="text-right"><small>{{ $meal->calories ?? '-' }} kcal</small><br><small class="text-muted">P:{{ $meal->protein_g ?? '-' }} C:{{ $meal->carbs_g ?? '-' }} F:{{ $meal->fats_g ?? '-' }}</small></div>
                        </div>
                    @empty<p class="text-muted">No meals added for this day.</p>@endforelse</div></div>
                @empty<div class="card"><div class="card-body text-center py-5"><i class="fas fa-utensils fa-3x text-muted mb-3"></i><h5>No days configured yet</h5></div></div>@endforelse
            </div>
        </div>
    </div>
</section></div>
@endsection
