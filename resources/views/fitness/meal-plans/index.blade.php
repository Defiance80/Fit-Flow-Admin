@extends('layouts.app')
@section('title', __('Meal Plans'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-utensils" style="color:#0D9488"></i> {{ __('Meal Plans') }}</h1>
            <div class="section-header-button">
                <a href="{{ route('meal-plans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Meal Plan
                </a>
            </div>
        </div>
        
        <div class="section-body">
            <!-- Filter -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Filter by Trainer</h4>
                            <div class="card-header-action">
                                <form method="GET" class="d-flex">
                                    <select name="trainer_id" class="form-control" onchange="this.form.submit()" style="width: auto;">
                                        <option value="">All Trainers</option>
                                        @foreach($trainers as $trainer)
                                            <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                {{ $trainer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meal Plans Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Meal Plans</h4>
                            <div class="card-header-action">
                                <small class="text-muted">{{ $mealPlans->total() }} total plans</small>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($mealPlans->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Plan Name</th>
                                                <th>Client</th>
                                                <th>Trainer</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Created Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mealPlans as $plan)
                                                @php
                                                    $duration = $plan->start_date && $plan->end_date ? 
                                                        \Carbon\Carbon::parse($plan->start_date)->diffInDays(\Carbon\Carbon::parse($plan->end_date)) + 1 . ' days' 
                                                        : 'Not set';
                                                    $statusColor = $plan->is_active ? 'success' : 'secondary';
                                                    $statusText = $plan->is_active ? 'Active' : 'Inactive';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('meal-plans.show', $plan->id) }}" 
                                                           class="text-decoration-none font-weight-bold" 
                                                           style="color: #0D9488;">
                                                            {{ $plan->name }}
                                                        </a>
                                                        @if($plan->description)
                                                            <br>
                                                            <small class="text-muted">{{ \Str::limit($plan->description, 50) }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $plan->client->name ?? 'Unassigned' }}</strong>
                                                        @if($plan->client && $plan->client->email)
                                                            <br>
                                                            <small class="text-muted">{{ $plan->client->email }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $plan->trainer->name ?? 'Unassigned' }}
                                                    </td>
                                                    <td>
                                                        <span class="text-info">{{ $duration }}</span>
                                                        @if($plan->start_date)
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($plan->start_date)->format('M j') }} - 
                                                                {{ \Carbon\Carbon::parse($plan->end_date)->format('M j, Y') }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $statusColor }}">
                                                            {{ $statusText }}
                                                        </span>
                                                        @if($plan->goal)
                                                            <br>
                                                            <small class="text-muted">Goal: {{ $plan->goal }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small>{{ $plan->created_at->format('M j, Y') }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $plan->created_at->diffForHumans() }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                    type="button" data-toggle="dropdown">
                                                                Actions
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="{{ route('meal-plans.show', $plan->id) }}">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                                <a class="dropdown-item" href="{{ route('meal-plans.edit', $plan->id) }}">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                                @if($plan->is_active)
                                                                    <div class="dropdown-divider"></div>
                                                                    <form method="POST" action="{{ route('meal-plans.destroy', $plan->id) }}" 
                                                                          class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this meal plan?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            <i class="fas fa-times"></i> Deactivate
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-center">
                                    {{ $mealPlans->withQueryString()->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                                    <h5>No meal plans found</h5>
                                    <p class="text-muted">Create your first meal plan to get started.</p>
                                    <a href="{{ route('meal-plans.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Meal Plan
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
