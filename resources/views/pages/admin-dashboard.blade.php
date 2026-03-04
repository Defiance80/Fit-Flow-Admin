@extends('layouts.app')
@section('title', __('Dashboard'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Dashboard') }}</h1>
        </div>

        {{-- Quick Stats --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, var(--primary), var(--secondary))">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Trainers') }}</h4></div>
                        <div class="card-body">{{ \App\Models\User::where('user_role', 'trainer')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #42A5F5, var(--primary))">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Clients') }}</h4></div>
                        <div class="card-body">{{ \App\Models\User::where('user_role', 'client')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, var(--success), #FFD700)">
                        <i class="fas fa-dumbbell text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Active Programs') }}</h4></div>
                        <div class="card-body">{{ \App\Models\Fitness\TrainingProgram::where('is_active', true)->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0D47A1, var(--primary))">
                        <i class="fas fa-building text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Facilities') }}</h4></div>
                        <div class="card-body">{{ \App\Models\Facility::count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Health Alerts --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-bell" style="color:var(--warning)"></i> {{ __('Recent Health Alerts') }}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('health.alerts.index') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @php $alerts = \App\Models\Health\HealthAlert::with('client')->where('acknowledged', false)->orderByRaw("FIELD(severity, 'red', 'yellow', 'green')")->limit(5)->get(); @endphp
                        @forelse($alerts as $alert)
                        <div class="p-3 border-bottom d-flex align-items-center">
                            <span class="badge badge-{{ $alert->severity == 'red' ? 'danger' : ($alert->severity == 'yellow' ? 'warning' : 'success') }} mr-3">{{ strtoupper($alert->severity) }}</span>
                            <div>
                                <strong>{{ $alert->title }}</strong> — {{ $alert->client->name ?? 'Unknown' }}
                                <br><small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                            {{ __('All clear — no active alerts') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Today's Schedule --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> {{ __("Today's Schedule") }}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @php $todaySchedules = \App\Models\Schedule::with(['trainer','client'])->whereDate('start_at', today())->orderBy('start_at')->limit(5)->get(); @endphp
                        @forelse($todaySchedules as $sched)
                        <div class="p-3 border-bottom d-flex align-items-center">
                            <div class="mr-3 text-center" style="min-width:50px">
                                <h6 class="mb-0" style="color:var(--primary)">{{ $sched->start_at->format('g:i') }}</h6>
                                <small>{{ $sched->start_at->format('A') }}</small>
                            </div>
                            <div>
                                <strong>{{ $sched->title }}</strong>
                                <br><small class="text-muted">{{ $sched->trainer->name ?? '' }} @if($sched->client) → {{ $sched->client->name }} @endif</small>
                            </div>
                            <span class="badge badge-{{ $sched->status == 'confirmed' ? 'primary' : 'warning' }} ml-auto">{{ ucfirst($sched->status) }}</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-2 d-block" style="color:var(--primary)"></i>
                            {{ __('No sessions scheduled for today') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h4>{{ __('Quick Actions') }}</h4></div>
                    <div class="card-body">
                        <a href="{{ route('trainers.create') }}" class="btn btn-primary mr-2 mb-2"><i class="fas fa-user-tie"></i> {{ __('Add Trainer') }}</a>
                        <a href="{{ route('clients.create') }}" class="btn btn-info mr-2 mb-2"><i class="fas fa-user-plus"></i> {{ __('Add Client') }}</a>
                        <a href="{{ route('programs.create') }}" class="btn btn-success mr-2 mb-2"><i class="fas fa-dumbbell"></i> {{ __('Create Program') }}</a>
                        <a href="{{ route('schedules.create') }}" class="btn btn-warning mr-2 mb-2"><i class="fas fa-calendar-plus"></i> {{ __('Book Session') }}</a>
                        <a href="{{ route('exercises.create') }}" class="btn btn-secondary mr-2 mb-2"><i class="fas fa-running"></i> {{ __('Add Exercise') }}</a>
                        <a href="{{ route('meal-plans.create') }}" class="btn btn-dark mr-2 mb-2"><i class="fas fa-utensils"></i> {{ __('Create Meal Plan') }}</a>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>
@endsection
