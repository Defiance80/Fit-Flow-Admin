@extends('layouts.app')
@section('title', __('Dashboard'))
@section('main')
@php
    // ================ ORIGINAL eLMS DASHBOARD DATA ================
    
    // Row 1: Total Users, Total Courses, Total Earnings, Total Enrollments
    try { 
        \ = \App\Models\User::count(); 
        \ = \App\Models\User::whereDate('created_at', '<', now()->startOfMonth())->count();
        \ = \ > 0 ? round(((\ - \) / \) * 100, 1) : 0;
    } catch (\Exception \) { \ = 0; \ = 0; }
    
    try { 
        \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \App\Models\Course::count() : 0; 
        \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \App\Models\Course::whereDate('created_at', '<', now()->startOfMonth())->count() : 0;
        \ = \ > 0 ? round(((\ - \) / \) * 100, 1) : 0;
    } catch (\Exception \) { \ = 0; \ = 0; }
    
    try { 
        \ = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \Illuminate\Support\Facades\DB::table('orders')->where('status', 'completed')->sum('total_amount') : 0; 
        \ = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \Illuminate\Support\Facades\DB::table('orders')->where('status', 'completed')->whereDate('created_at', '<', now()->startOfMonth())->sum('total_amount') : 0;
        \ = \ > 0 ? round(((\ - \) / \) * 100, 1) : 0;
    } catch (\Exception \) { \ = 0; \ = 0; }
    
    try { 
        \ = \Illuminate\Support\Facades\Schema::hasTable('enrollments') ? \Illuminate\Support\Facades\DB::table('enrollments')->count() : 0; 
        \ = \Illuminate\Support\Facades\Schema::hasTable('enrollments') ? \Illuminate\Support\Facades\DB::table('enrollments')->whereDate('created_at', '<', now()->startOfMonth())->count() : 0;
        \ = \ > 0 ? round(((\ - \) / \) * 100, 1) : 0;
    } catch (\Exception \) { \ = 0; \ = 0; }
    
    // Row 2: Total Instructors, Active Courses, Pending Approvals, Total Categories
    try { \ = \App\Models\User::where('user_role', 'instructor')->count(); } catch (\Exception \) { \ = 0; }
    try { \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'published')->count() : 0; } catch (\Exception \) { \ = 0; }
    try { \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'pending')->count() : 0; } catch (\Exception \) { \ = 0; }
    try { \ = \Illuminate\Support\Facades\Schema::hasTable('categories') ? \Illuminate\Support\Facades\DB::table('categories')->count() : 0; } catch (\Exception \) { \ = 0; }
    
    // Recent Activities
    try { 
        \ = \App\Models\User::latest()->limit(5)->get(); 
        \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->latest('created_at')->limit(3)->get() : collect();
        \ = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \Illuminate\Support\Facades\DB::table('orders')->latest('created_at')->limit(3)->get() : collect();
    } catch (\Exception \) { \ = collect(); \ = collect(); \ = collect(); }
    
    // Course Statistics
    try {
        \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'published')->count() : 0;
        \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'draft')->count() : 0;
        \ = \Illuminate\Support\Facades\Schema::hasTable('lectures') ? \Illuminate\Support\Facades\DB::table('lectures')->count() : 0;
        \ = \Illuminate\Support\Facades\Schema::hasTable('quizzes') ? \Illuminate\Support\Facades\DB::table('quizzes')->count() : 0;
        \ = \Illuminate\Support\Facades\Schema::hasTable('ratings') ? \Illuminate\Support\Facades\DB::table('ratings')->avg('rating') : 0;
        \ = \Illuminate\Support\Facades\Schema::hasTable('assignments') ? \Illuminate\Support\Facades\DB::table('assignments')->count() : 0;
    } catch (\Exception \) { 
        \ = 0; \ = 0; \ = 0; \ = 0; \ = 0; \ = 0; 
    }
    
    // User & Engagement Statistics
    try {
        \ = \App\Models\User::where('is_active', 1)->count();
        \ = \App\Models\User::whereDate('created_at', '>=', now()->startOfMonth())->count();
        \ = \Illuminate\Support\Facades\Schema::hasTable('discussions') ? \Illuminate\Support\Facades\DB::table('discussions')->count() : 0;
        \ = \Illuminate\Support\Facades\Schema::hasTable('quiz_attempts') ? \Illuminate\Support\Facades\DB::table('quiz_attempts')->count() : 0;
        \ = \App\Models\User::where('user_role', 'instructor')->where('status', 'pending')->count();
        \ = \Illuminate\Support\Facades\Schema::hasTable('support_tickets') ? \Illuminate\Support\Facades\DB::table('support_tickets')->where('status', 'open')->count() : 0;
    } catch (\Exception \) { 
        \ = 0; \ = 0; \ = 0; \ = 0; \ = 0; \ = 0; 
    }
    
    // Top Instructors
    try {
        \ = \App\Models\User::where('user_role', 'instructor')
            ->withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->limit(5)
            ->get();
    } catch (\Exception \) { \ = collect(); }
    
    // Most Popular Courses
    try {
        \ = \Illuminate\Support\Facades\Schema::hasTable('courses') ? 
            \Illuminate\Support\Facades\DB::table('courses')
                ->select('courses.*', \Illuminate\Support\Facades\DB::raw('COUNT(enrollments.id) as enrollment_count'))
                ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->groupBy('courses.id')
                ->orderBy('enrollment_count', 'desc')
                ->limit(5)
                ->get() : collect();
    } catch (\Exception \) { \ = collect(); }
    
    // Chart Data for Revenue & Enrollment Trends (last 12 months)
    try {
        \ = [];
        \ = [];
        \ = [];
        
        for (\ = 11; \ >= 0; \--) {
            \ = now()->subMonths(\);
            \[] = \->format('M Y');
            
            \ = \Illuminate\Support\Facades\Schema::hasTable('orders') ? 
                \Illuminate\Support\Facades\DB::table('orders')
                    ->where('status', 'completed')
                    ->whereYear('created_at', \->year)
                    ->whereMonth('created_at', \->month)
                    ->sum('total_amount') : 0;
            \[] = \;
            
            \ = \Illuminate\Support\Facades\Schema::hasTable('enrollments') ? 
                \Illuminate\Support\Facades\DB::table('enrollments')
                    ->whereYear('created_at', \->year)
                    ->whereMonth('created_at', \->month)
                    ->count() : 0;
            \[] = \;
        }
    } catch (\Exception \) { 
        \ = []; \ = []; \ = []; 
    }

    // ================ FITNESS DATA (Keeping some) ================
    try { \ = \App\Models\User::where('user_role', 'trainer')->count(); } catch (\Exception \) { \ = 0; }
    try { \ = \App\Models\User::where('user_role', 'client')->count(); } catch (\Exception \) { \ = 0; }
@endphp
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Dashboard') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">{{ __('Dashboard') }}</div>
            </div>
        </div>

        {{-- Row 1: Main KPIs --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0D9488, #10B981)">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Users') }}</h4></div>
                        <div class="card-body">
                            {{ number_format(\) }}
                            @if(\ > 0)
                                <small class="text-success"><i class="fas fa-arrow-up"></i> {{ \ }}%</small>
                            @elseif(\ < 0)
                                <small class="text-danger"><i class="fas fa-arrow-down"></i> {{ abs(\) }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #1E293B, #334155)">
                        <i class="fas fa-graduation-cap text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Courses') }}</h4></div>
                        <div class="card-body">
                            {{ number_format(\) }}
                            @if(\ > 0)
                                <small class="text-success"><i class="fas fa-arrow-up"></i> {{ \ }}%</small>
                            @elseif(\ < 0)
                                <small class="text-danger"><i class="fas fa-arrow-down"></i> {{ abs(\) }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #F59E0B, #FBBF24)">
                        <i class="fas fa-dollar-sign text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Earnings') }}</h4></div>
                        <div class="card-body">
                            ${{ number_format(\, 2) }}
                            @if(\ > 0)
                                <small class="text-success"><i class="fas fa-arrow-up"></i> {{ \ }}%</small>
                            @elseif(\ < 0)
                                <small class="text-danger"><i class="fas fa-arrow-down"></i> {{ abs(\) }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon" style="background: linear-gradient(135deg, #8B5CF6, #A855F7)">
                        <i class="fas fa-user-graduate text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Enrollments') }}</h4></div>
                        <div class="card-body">
                            {{ number_format(\) }}
                            @if(\ > 0)
                                <small class="text-success"><i class="fas fa-arrow-up"></i> {{ \ }}%</small>
                            @elseif(\ < 0)
                                <small class="text-danger"><i class="fas fa-arrow-down"></i> {{ abs(\) }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Secondary KPIs --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-chalkboard-teacher text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Instructors') }}</h4></div>
                        <div class="card-body">{{ number_format(\) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Active Courses') }}</h4></div>
                        <div class="card-body">{{ number_format(\) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Pending Approvals') }}</h4></div>
                        <div class="card-body">{{ number_format(\) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="fas fa-tags text-white"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>{{ __('Total Categories') }}</h4></div>
                        <div class="card-body">{{ number_format(\) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue & Enrollment Trends Chart --}}
        @if(count(\) > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-chart-line"></i> {{ __('Revenue & Enrollment Trends') }}</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="trendsChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Row 3: Recent Activities & Course Statistics --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-history"></i> {{ __('Recent Activities') }}</h4>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        {{-- Recent Users --}}
                        @foreach(\ as \)
                            <div class="d-flex align-items-center mb-3">
                                <div style="background: #0D9488; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; flex-shrink: 0;">
                                    <i class="fas fa-user fa-sm"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ \->name }}</strong> {{ __('registered as') }} {{ ucfirst(\->user_role ?? 'user') }}
                                    <br><small class="text-muted">{{ \->created_at ? \->created_at->diffForHumans() : '' }}</small>
                                </div>
                            </div>
                        @endforeach
                        
                        {{-- Recent Courses --}}
                        @foreach(\ as \)
                            <div class="d-flex align-items-center mb-3">
                                <div style="background: #1E293B; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; flex-shrink: 0;">
                                    <i class="fas fa-graduation-cap fa-sm"></i>
                                </div>
                                <div class="flex-grow-1">
                                    {{ __('Course created:') }} <strong>{{ \->title ?? 'Untitled Course' }}</strong>
                                    <br><small class="text-muted">{{ \->created_at ? \Carbon\Carbon::parse(\->created_at)->diffForHumans() : '' }}</small>
                                </div>
                            </div>
                        @endforeach
                        
                        {{-- Recent Orders --}}
                        @foreach(\ as \)
                            <div class="d-flex align-items-center mb-3">
                                <div style="background: #F59E0B; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; flex-shrink: 0;">
                                    <i class="fas fa-shopping-cart fa-sm"></i>
                                </div>
                                <div class="flex-grow-1">
                                    {{ __('Order placed:') }} <strong>${{ number_format(\->total_amount ?? 0, 2) }}</strong>
                                    <br><small class="text-muted">{{ \->created_at ? \Carbon\Carbon::parse(\->created_at)->diffForHumans() : '' }}</small>
                                </div>
                            </div>
                        @endforeach
                        
                        @if(\->isEmpty() && \->isEmpty() && \->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clock fa-2x mb-2 d-block" style="color: #0D9488;"></i>
                                {{ __('No recent activity') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-chart-pie"></i> {{ __('Course Statistics') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <h4 style="color: #10B981;">{{ number_format(\) }}</h4>
                                <p class="mb-0">{{ __('Published Courses') }}</p>
                            </div>
                            <div class="col-6">
                                <h4 style="color: #F59E0B;">{{ number_format(\) }}</h4>
                                <p class="mb-0">{{ __('Draft Courses') }}</p>
                            </div>
                        </div>
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <h4 style="color: #8B5CF6;">{{ number_format(\) }}</h4>
                                <p class="mb-0">{{ __('Total Lectures') }}</p>
                            </div>
                            <div class="col-6">
                                <h4 style="color: #EF4444;">{{ number_format(\) }}</h4>
                                <p class="mb-0">{{ __('Total Quizzes') }}</p>
                            </div>
                        </div>
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <h4 style="color: #06B6D4;">{{ number_format(\, 1) }}</h4>
                                <p class="mb-0">{{ __('Average Rating') }}</p>
                            </div>
                            <div class="col-6">
                                <h4 style="color: #84CC16;">{{ number_format(\) }}</h4>
                                <p class="mb-0">{{ __('Assignments') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: User Engagement & Top Content --}}
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-users"></i> {{ __('User Engagement') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('Active Users') }}</span>
                                <strong>{{ number_format(\) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('New This Month') }}</span>
                                <strong>{{ number_format(\) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('Discussions') }}</span>
                                <strong>{{ number_format(\) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('Quiz Attempts') }}</span>
                                <strong>{{ number_format(\) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('Pending Instructors') }}</span>
                                <strong class="text-warning">{{ number_format(\) }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('Open Tickets') }}</span>
                                <strong class="text-danger">{{ number_format(\) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-star"></i> {{ __('Top Instructors') }}</h4>
                    </div>
                    <div class="card-body">
                        @forelse(\ as \)
                            <div class="d-flex align-items-center mb-3">
                                <div style="background: #1E293B; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ \->name }}</strong>
                                    <br><small class="text-muted">{{ \->courses_count }} {{ __('courses') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-user-tie fa-2x mb-2 d-block" style="color: #0D9488;"></i>
                                {{ __('No instructors yet') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-trophy"></i> {{ __('Popular Courses') }}</h4>
                    </div>
                    <div class="card-body">
                        @forelse(\ as \)
                            <div class="d-flex align-items-center mb-3">
                                <div style="background: #F59E0B; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ \Str::limit(\->title ?? 'Untitled', 25) }}</strong>
                                    <br><small class="text-muted">{{ \->enrollment_count }} {{ __('enrollments') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-graduation-cap fa-2x mb-2 d-block" style="color: #0D9488;"></i>
                                {{ __('No courses yet') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- FITNESS SUMMARY CARD --}}
        @if(\ > 0 || \ > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 style="color: #0D9488;"><i class="fas fa-dumbbell"></i> {{ __('Fitness Overview') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-lg-6">
                                <h3 style="color: #0D9488;">{{ number_format(\) }}</h3>
                                <p class="mb-0">{{ __('Fitness Trainers') }}</p>
                            </div>
                            <div class="col-lg-6">
                                <h3 style="color: #1E293B;">{{ number_format(\) }}</h3>
                                <p class="mb-0">{{ __('Fitness Clients') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </section>
</div>

{{-- Chart.js for Revenue & Enrollment Trends --}}
@if(count(\) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(\),
            datasets: [{
                label: 'Revenue ($)',
                data: @json(\),
                borderColor: '#0D9488',
                backgroundColor: 'rgba(13, 148, 136, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Enrollments',
                data: @json(\),
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue ($)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Enrollments'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>
@endif
@endsection
