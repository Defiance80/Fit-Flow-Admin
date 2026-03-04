@extends('layouts.app')
@section('title', __('Health Alerts'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-exclamation-triangle" style="color:#EF4444"></i> {{ __('Health Alerts') }}</h1>
        </div>
        
        <div class="section-body">
            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Filters</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="trainer_id" class="mr-2">Trainer:</label>
                                    <select name="trainer_id" class="form-control">
                                        <option value="">All Trainers</option>
                                        @foreach($trainers as $trainer)
                                            <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                {{ $trainer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group mr-3">
                                    <label for="severity" class="mr-2">Severity:</label>
                                    <select name="severity" class="form-control">
                                        <option value="">All Severities</option>
                                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                                        <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-3">
                                    <label for="acknowledged" class="mr-2">Status:</label>
                                    <select name="acknowledged" class="form-control">
                                        <option value="">All Alerts</option>
                                        <option value="0" {{ request('acknowledged') === '0' ? 'selected' : '' }}>Unacknowledged</option>
                                        <option value="1" {{ request('acknowledged') === '1' ? 'selected' : '' }}>Acknowledged</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                                <a href="{{ route('health.alerts.index') }}" class="btn btn-secondary">Clear</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Health Alerts</h4>
                            <div class="card-header-action">
                                <small class="text-muted">{{ $alerts->total() }} total alerts</small>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($alerts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th>Alert Type</th>
                                                <th>Value</th>
                                                <th>Normal Range</th>
                                                <th>Severity</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($alerts as $alert)
                                                @php
                                                    $severityColor = $alert->severity === 'critical' ? 'danger' : 'warning';
                                                    $normalRanges = [
                                                        'heart_rate' => '60-100 bpm',
                                                        'spo2' => '95-100%',
                                                        'respiratory_rate' => '12-20/min',
                                                        'temperature' => '97.0-99.0°F',
                                                        'hrv' => '>20ms'
                                                    ];
                                                    $normalRange = $normalRanges[$alert->alert_type] ?? 'N/A';
                                                    $dataSnapshot = json_decode($alert->data_snapshot, true);
                                                    $value = $dataSnapshot['value'] ?? 'N/A';
                                                    $unit = $dataSnapshot['unit'] ?? '';
                                                @endphp
                                                <tr class="{{ $alert->acknowledged ? '' : 'table-active' }}">
                                                    <td>
                                                        <strong>{{ $alert->client->name ?? 'Unknown Client' }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="text-capitalize">{{ str_replace('_', ' ', $alert->alert_type) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-{{ $severityColor }}">
                                                            <strong>{{ $value }}{{ $unit }}</strong>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $normalRange }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $severityColor }}">
                                                            {{ ucfirst($alert->severity) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $alert->created_at->format('M j, Y H:i') }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                                                    </td>
                                                    <td>
                                                        @if($alert->acknowledged)
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Acknowledged
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $alert->acknowledged_at->format('M j, H:i') }}
                                                            </small>
                                                        @else
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-exclamation"></i> Unacknowledged
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$alert->acknowledged)
                                                            <form method="POST" action="{{ route('health.alerts.acknowledge', $alert->id) }}" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-sm btn-success" 
                                                                        onclick="return confirm('Mark this alert as acknowledged?')">
                                                                    <i class="fas fa-check"></i> Acknowledge
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-muted">--</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-center">
                                    {{ $alerts->withQueryString()->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                                    <h5>No health alerts</h5>
                                    <p class="text-muted">All clients' health metrics are within normal ranges.</p>
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
