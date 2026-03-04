@extends('layouts.app')
@section('title', __('Health Metrics'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-heartbeat" style="color:#0D9488"></i> {{ __('Health Metrics Dashboard') }}</h1>
        </div>
        
        <div class="section-body">
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon" style="background:#0D9488">
                            <i class="far fa-user"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Clients Tracked</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalClients }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon" style="background:#EF4444">
                            <i class="far fa-exclamation-triangle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Alerts This Week</h4>
                            </div>
                            <div class="card-body">
                                {{ $alertsThisWeek }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon" style="background:#10B981">
                            <i class="far fa-heart"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Avg Heart Rate</h4>
                            </div>
                            <div class="card-body">
                                {{ $avgHeartRate ? $avgHeartRate . ' bpm' : '--' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trainer Filter -->
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

            <!-- Client Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Client Health Metrics</h4>
                            <div class="card-header-action">
                                <small class="text-muted">
                                    Normal ranges: HR 60-100 bpm | SpO₂ 95-100% | Respiratory 12-20/min | Temp 97.0-99.0°F | HRV >20ms
                                </small>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(count($clientsWithMetrics) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Client Name</th>
                                                <th>Trainer</th>
                                                <th>Heart Rate</th>
                                                <th>SpO₂</th>
                                                <th>Sleep Duration</th>
                                                <th>Steps</th>
                                                <th>Last Updated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($clientsWithMetrics as $item)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('health.metrics.show', $item['client']->id) }}" 
                                                           class="text-decoration-none font-weight-bold" 
                                                           style="color: #0D9488;">
                                                            {{ $item['client']->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $item['trainer_name'] }}</td>
                                                    <td>
                                                        @php
                                                            $hr = $item['heart_rate'];
                                                            $colorClass = $hr ? \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('heart_rate', $hr->value) : 'text-muted';
                                                        @endphp
                                                        <span class="{{ $colorClass }}">
                                                            {{ $hr ? $hr->value . ' bpm' : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $spo2 = $item['spo2'];
                                                            $colorClass = $spo2 ? \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('spo2', $spo2->value) : 'text-muted';
                                                        @endphp
                                                        <span class="{{ $colorClass }}">
                                                            {{ $spo2 ? $spo2->value . '%' : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $sleep = $item['sleep_duration'];
                                                        @endphp
                                                        <span class="text-info">
                                                            {{ $sleep ? $sleep->value . 'h' : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $steps = $item['steps'];
                                                        @endphp
                                                        <span class="text-primary">
                                                            {{ $steps ? number_format($steps->value) : '--' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($item['latest_update'])
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($item['latest_update'])->diffForHumans() }}
                                                            </small>
                                                        @else
                                                            <small class="text-muted">No data</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
                                    <h5>No health metrics recorded yet</h5>
                                    <p class="text-muted">Health data from Apple Watch, Google Fit, Fitbit and other wearables will appear here.</p>
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
