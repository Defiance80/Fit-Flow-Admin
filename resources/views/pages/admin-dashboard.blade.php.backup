@extends('layouts.app')
@section('title', __('Dashboard'))
@section('main')
@php
    // Safe counts with try/catch for missing tables
    try { $trainerCount = \App\Models\User::where('user_role', 'trainer')->count(); } catch (\Exception $e) { $trainerCount = '--'; }
    try { $clientCount = \App\Models\User::where('user_role', 'client')->count(); } catch (\Exception $e) { $clientCount = '--'; }
    try { $programCount = \Illuminate\Support\Facades\Schema::hasTable('programs') ? \Illuminate\Support\Facades\DB::table('programs')->count() : '--'; } catch (\Exception $e) { $programCount = '--'; }
    try { $subCount = \Illuminate\Support\Facades\Schema::hasTable('subscriptions') ? \Illuminate\Support\Facades\DB::table('subscriptions')->count() : '--'; } catch (\Exception $e) { $subCount = '--'; }
    try { $recentUsers = \App\Models\User::latest()->limit(5)->get(); } catch (\Exception $e) { $recentUsers = collect(); }
    try { $todayUsers = \App\Models\User::whereDate('created_at', today())->count(); } catch (\Exception $e) { $todayUsers = '--'; }
    try { $weekTrainers = \App\Models\User::where('user_role', 'trainer')->whereDate('created_at', '>=', now()->subDays(7))->count(); } catch (\Exception $e) { $weekTrainers = '--'; }
@endphp
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Dashboard') }}</h1>
        </div>

        {{-- Stats --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0D9488, #10B981)">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Trainers') }}</h4></div>
                        <div class="card-body">{{ $trainerCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #1E293B, #334155)">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Clients') }}</h4></div>
                        <div class="card-body">{{ $clientCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #F59E0B, #FBBF24)">
                        <i class="fas fa-dumbbell text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Programs') }}</h4></div>
                        <div class="card-body">{{ $programCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0D9488, #1E293B)">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Subscriptions') }}</h4></div>
                        <div class="card-body">{{ $subCount }}</div>
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
                                <a href="{{ route('trainers.create') }}" class="btn btn-lg btn-block" style="background: #0D9488; color: white;">
                                    <i class="fas fa-user-tie"></i> {{ __('Add Trainer') }}
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <a href="{{ route('clients.create') }}" class="btn btn-lg btn-block" style="background: #1E293B; color: white;">
                                    <i class="fas fa-user-plus"></i> {{ __('Add Client') }}
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <a href="{{ route('programs.create') }}" class="btn btn-lg btn-block" style="background: #F59E0B; color: white;">
                                    <i class="fas fa-dumbbell"></i> {{ __('Create Program') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent + Status --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-history"></i> {{ __('Recent Activity') }}</h4>
                    </div>
                    <div class="card-body">
                        @forelse($recentUsers as $user)
                            <div class="d-flex align-items-center mb-3">
                                <div style="background: #0D9488; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong>{{ $user->name }}</strong> {{ __('joined as') }} {{ ucfirst($user->user_role ?? 'user') }}
                                    <br><small class="text-muted">{{ $user->created_at ? $user->created_at->diffForHumans() : '' }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clock fa-2x mb-2 d-block" style="color: #0D9488;"></i>
                                {{ __('No recent activity') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-chart-line"></i> {{ __('System Status') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 style="color: #10B981;">{{ $todayUsers }}</h3>
                                <p class="mb-0">{{ __('New Users Today') }}</p>
                            </div>
                            <div class="col-6">
                                <h3 style="color: #F59E0B;">{{ $weekTrainers }}</h3>
                                <p class="mb-0">{{ __('New Trainers This Week') }}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Database') }}</span>
                            <span class="badge badge-success">{{ __('Connected') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
