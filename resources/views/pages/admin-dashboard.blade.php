@extends('layouts.app')
@section('title', __('Dashboard'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Dashboard') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">{{ __('Dashboard') }}</div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0D9488, #10B981)">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Trainers') }}</h4></div>
                        <div class="card-body">
                            @try
                                {{ \App\Models\User::where('user_role', 'trainer')->count() }}
                            @catch(\Exception $e)
                                <span class="text-muted">--</span>
                            @endtry
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #1E293B, #334155)">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Clients') }}</h4></div>
                        <div class="card-body">
                            @try
                                {{ \App\Models\User::where('user_role', 'client')->count() }}
                            @catch(\Exception $e)
                                <span class="text-muted">--</span>
                            @endtry
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #F59E0B, #FBBF24)">
                        <i class="fas fa-dumbbell text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Active Programs') }}</h4></div>
                        <div class="card-body">
                            @try
                                @php
                                    $activePrograms = DB::table('programs')->where('status', 'active')->count();
                                @endphp
                                {{ $activePrograms }}
                            @catch(\Exception $e)
                                <span class="text-muted">--</span>
                            @endtry
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0D9488, #1E293B)">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Active Subscriptions') }}</h4></div>
                        <div class="card-body">
                            @try
                                @php
                                    $activeSubscriptions = DB::table('subscriptions')->where('status', 'active')->count();
                                @endphp
                                {{ $activeSubscriptions }}
                            @catch(\Exception $e)
                                <span class="text-muted">--</span>
                            @endtry
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-bolt"></i> {{ __('Quick Actions') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <a href="{{ route('admin.trainers.create') ?? '#' }}" class="btn btn-lg btn-block" style="background: #0D9488; color: white; border: none;">
                                    <i class="fas fa-user-tie"></i><br>
                                    {{ __('Add Trainer') }}
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <a href="{{ route('admin.clients.create') ?? '#' }}" class="btn btn-lg btn-block" style="background: #1E293B; color: white; border: none;">
                                    <i class="fas fa-user-plus"></i><br>
                                    {{ __('Add Client') }}
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <a href="{{ route('admin.programs.create') ?? '#' }}" class="btn btn-lg btn-block" style="background: #F59E0B; color: white; border: none;">
                                    <i class="fas fa-dumbbell"></i><br>
                                    {{ __('Create Program') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-activity"></i> {{ __('Recent Activity') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            @try
                                @php
                                    $recentUsers = \App\Models\User::latest()->limit(5)->get();
                                @endphp
                                @forelse($recentUsers as $user)
                                    <div class="activity-item d-flex align-items-center mb-3">
                                        <div class="activity-icon" style="background: #0D9488; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $user->name }}</strong> {{ __('joined as') }} {{ ucfirst($user->user_role ?? 'user') }}
                                            <br><small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-clock fa-2x mb-2 d-block" style="color: #0D9488;"></i>
                                        {{ __('No recent activity') }}
                                    </div>
                                @endforelse
                            @catch(\Exception $e)
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block" style="color: #F59E0B;"></i>
                                    {{ __('Unable to load recent activity') }}
                                </div>
                            @endtry
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-chart-line"></i> {{ __('System Status') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h3 style="color: #10B981;">
                                        @try
                                            {{ \App\Models\User::whereDate('created_at', today())->count() }}
                                        @catch(\Exception $e)
                                            --
                                        @endtry
                                    </h3>
                                    <p class="mb-0">{{ __('New Users Today') }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h3 style="color: #F59E0B;">
                                        @try
                                            {{ \App\Models\User::where('user_role', 'trainer')->whereDate('created_at', '>=', now()->subDays(7))->count() }}
                                        @catch(\Exception $e)
                                            --
                                        @endtry
                                    </h3>
                                    <p class="mb-0">{{ __('New Trainers This Week') }}</p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Database Status') }}</span>
                            <span class="badge badge-success">{{ __('Connected') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>
@endsection
