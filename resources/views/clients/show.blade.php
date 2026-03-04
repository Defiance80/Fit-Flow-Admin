@extends('layouts.app')
@section('title', $client->name)
@push('style')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ $client->name }}</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a>
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning ml-1"><i class="fas fa-edit"></i> {{ __('Edit') }}</a>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="{{ $client->profile ?? asset('img/avatar/avatar-1.png') }}" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover">
                            <h5>{{ $client->name }}</h5>
                            <p class="text-muted">{{ $client->email }}</p>
                            @if($client->gender)<span class="badge badge-light">{{ ucfirst($client->gender) }}</span>@endif
                            @if($client->date_of_birth)<span class="badge badge-light">{{ $client->date_of_birth->age }} yrs</span>@endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="row text-center">
                                <div class="col-4"><strong>{{ $client->height_cm ?? '-' }}</strong><br><small class="text-muted">Height cm</small></div>
                                <div class="col-4"><strong>{{ $client->weight_kg ?? '-' }}</strong><br><small class="text-muted">Weight kg</small></div>
                                <div class="col-4"><strong>{{ $client->trainers->count() }}</strong><br><small class="text-muted">Trainers</small></div>
                            </div>
                        </div>
                    </div>
                    @if($client->fitness_goals)
                    <div class="card"><div class="card-header"><h4>{{ __('Fitness Goals') }}</h4></div>
                        <div class="card-body"><p>{{ $client->fitness_goals }}</p></div></div>
                    @endif
                    @if($client->medical_notes)
                    <div class="card"><div class="card-header"><h4><i class="fas fa-notes-medical text-danger"></i> {{ __('Medical Notes') }}</h4></div>
                        <div class="card-body"><p class="text-danger">{{ $client->medical_notes }}</p></div></div>
                    @endif
                    @if($client->emergency_contact_name)
                    <div class="card"><div class="card-header"><h4>{{ __('Emergency Contact') }}</h4></div>
                        <div class="card-body"><strong>{{ $client->emergency_contact_name }}</strong><br>{{ $client->emergency_contact_phone }}</div></div>
                    @endif
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>{{ __('Trainers') }}</h4></div>
                        <div class="card-body">
                            @forelse($client->trainers as $trainer)
                                <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                    <img src="{{ $trainer->profile ?? asset('img/avatar/avatar-1.png') }}" class="rounded-circle mr-3" width="40" height="40" style="object-fit:cover">
                                    <div><strong>{{ $trainer->name }}</strong><br><small class="text-muted">{{ $trainer->email }}</small></div>
                                    <span class="badge badge-{{ $trainer->pivot->status == 'active' ? 'success' : 'secondary' }} ml-auto">{{ ucfirst($trainer->pivot->status) }}</span>
                                </div>
                            @empty
                                <p class="text-muted">{{ __('No trainers assigned yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h4><i class="fas fa-heartbeat text-danger"></i> {{ __('Health Metrics') }}</h4></div>
                        <div class="card-body">
                            @if(isset($latestMetrics) && $latestMetrics->count())
                            <div class="row">
                                @foreach($latestMetrics as $metric)
                                <div class="col-md-4 mb-3">
                                    <div class="p-3 border rounded text-center">
                                        <small class="text-muted">{{ ucwords(str_replace('_', ' ', $metric->metric_type)) }}</small>
                                        <h4 class="mb-0" style="color: var(--primary)">{{ number_format($metric->value, 1) }}</h4>
                                        <small>{{ $metric->unit }}</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center py-3"><i class="fas fa-watch fa-2x mb-2 d-block"></i>{{ __('No health data synced yet. Connect a wearable device.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
