@extends('layouts.app')
@section('title', __('Health Metrics Detail'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('health.metrics.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>{{ $client->name }} - Health Metrics</h1>
        </div>
        
        <div class="section-body">
            <!-- Current Vitals Cards -->
            <div class="row">
                @php
                    $vitals = [
                        ['type' => 'heart_rate', 'label' => 'Heart Rate', 'unit' => 'bpm', 'icon' => 'fa-heart', 'color' => '#EF4444'],
                        ['type' => 'spo2', 'label' => 'SpO₂', 'unit' => '%', 'icon' => 'fa-lungs', 'color' => '#3B82F6'],
                        ['type' => 'temperature', 'label' => 'Temperature', 'unit' => '°F', 'icon' => 'fa-thermometer-half', 'color' => '#F59E0B'],
                        ['type' => 'hrv', 'label' => 'HRV', 'unit' => 'ms', 'icon' => 'fa-wave-square', 'color' => '#10B981'],
                        ['type' => 'sleep_duration', 'label' => 'Sleep Duration', 'unit' => 'h', 'icon' => 'fa-moon', 'color' => '#6366F1'],
                        ['type' => 'steps', 'label' => 'Steps', 'unit' => '', 'icon' => 'fa-shoe-prints', 'color' => '#8B5CF6']
                    ];
                @endphp
                
                @foreach($vitals as $vital)
                    @php
                        $current = $latestMetrics->get($vital['type']);
                        $previous = isset($previousMetrics[$vital['type']]) ? $previousMetrics[$vital['type']] : null;
                        $trend = null;
                        $trendIcon = '';
                        
                        if ($current && $previous) {
                            if ($current->value > $previous->value) {
                                $trend = 'up';
                                $trendIcon = 'fa-arrow-up text-success';
                            } elseif ($current->value < $previous->value) {
                                $trend = 'down';
                                $trendIcon = 'fa-arrow-down text-danger';
                            } else {
                                $trend = 'same';
                                $trendIcon = 'fa-minus text-muted';
                            }
                        }
                        
                        $colorClass = $current ? \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass($vital['type'], $current->value) : 'text-muted';
                    @endphp
                    
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon" style="background: {{ $vital['color'] }}">
                                <i class="fas {{ $vital['icon'] }}"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>{{ $vital['label'] }}</h4>
                                    @if($trendIcon)
                                        <div class="text-right">
                                            <i class="fas {{ $trendIcon }}"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if($current)
                                        <span class="{{ $colorClass }}">
                                            {{ $vital['type'] === 'steps' ? number_format($current->value) : $current->value }}{{ $vital['unit'] }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $current->recorded_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">No data</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Health Metrics History</h4>
                            <div class="card-header-action">
                                <small class="text-muted">Latest 30 readings</small>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(count($metricsHistory) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date/Time</th>
                                                <th>Heart Rate</th>
                                                <th>Respiratory Rate</th>
                                                <th>Temperature</th>
                                                <th>SpO₂</th>
                                                <th>Sleep Duration</th>
                                                <th>Steps</th>
                                                <th>Calories</th>
                                                <th>HRV</th>
                                                <th>Source</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($metricsHistory as $datetime => $metrics)
                                                @php
                                                    $metricsByType = $metrics->keyBy('metric_type');
                                                    $mainSource = $metrics->first()->source ?? 'Manual';
                                                    $sourceBadgeColor = 'secondary';
                                                    if ($mainSource === 'Apple Health') $sourceBadgeColor = 'dark';
                                                    elseif ($mainSource === 'Google Fit') $sourceBadgeColor = 'success';
                                                    elseif ($mainSource === 'Fitbit') $sourceBadgeColor = 'primary';
                                                    elseif ($mainSource === 'Manual') $sourceBadgeColor = 'info';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <small>{{ \Carbon\Carbon::parse($datetime)->format('M j, Y H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        @php $hr = $metricsByType->get('heart_rate'); @endphp
                                                        @if($hr)
                                                            <span class="{{ \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('heart_rate', $hr->value) }}">
                                                                {{ $hr->value }} bpm
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $rr = $metricsByType->get('respiratory_rate'); @endphp
                                                        @if($rr)
                                                            <span class="{{ \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('respiratory_rate', $rr->value) }}">
                                                                {{ $rr->value }}/min
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $temp = $metricsByType->get('temperature'); @endphp
                                                        @if($temp)
                                                            <span class="{{ \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('temperature', $temp->value) }}">
                                                                {{ $temp->value }}°F
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $spo2 = $metricsByType->get('spo2'); @endphp
                                                        @if($spo2)
                                                            <span class="{{ \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('spo2', $spo2->value) }}">
                                                                {{ $spo2->value }}%
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $sleep = $metricsByType->get('sleep_duration'); @endphp
                                                        @if($sleep)
                                                            <span class="text-info">{{ $sleep->value }}h</span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $steps = $metricsByType->get('steps'); @endphp
                                                        @if($steps)
                                                            <span class="text-primary">{{ number_format($steps->value) }}</span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $calories = $metricsByType->get('calories'); @endphp
                                                        @if($calories)
                                                            <span class="text-warning">{{ $calories->value }}</span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $hrv = $metricsByType->get('hrv'); @endphp
                                                        @if($hrv)
                                                            <span class="{{ \App\Http\Controllers\Health\HealthMetricController::getMetricColorClass('hrv', $hrv->value) }}">
                                                                {{ $hrv->value }}ms
                                                            </span>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $sourceBadgeColor }}">{{ $mainSource }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h5>No health metrics found</h5>
                                    <p class="text-muted">No health data has been recorded for this client yet.</p>
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
