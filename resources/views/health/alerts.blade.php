@extends('layouts.app')
@section('title', __('Health Alerts'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1><i class="fas fa-bell" style="color:var(--warning)"></i> {{ __('Health Alerts') }}</h1></div>
        <div class="section-body">
            @forelse($alerts as $alert)
            <div class="card border-left-{{ $alert->severity == 'red' ? 'danger' : ($alert->severity == 'yellow' ? 'warning' : 'success') }}" style="border-left: 4px solid {{ $alert->severity == 'red' ? 'var(--danger)' : ($alert->severity == 'yellow' ? 'var(--warning)' : 'var(--success)') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge badge-{{ $alert->severity == 'red' ? 'danger' : ($alert->severity == 'yellow' ? 'warning' : 'success') }} mr-2">{{ strtoupper($alert->severity) }}</span>
                            <strong>{{ $alert->title }}</strong>
                            <span class="text-muted ml-2">— {{ $alert->client->name ?? 'Unknown' }}</span>
                            <p class="mt-2 mb-1">{{ $alert->message }}</p>
                            @if($alert->recommendation)<p class="text-muted small"><i class="fas fa-lightbulb"></i> <strong>Recommendation:</strong> {{ $alert->recommendation }}</p>@endif
                            <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                        </div>
                        <div>
                            @if(!$alert->acknowledged)
                            <form action="{{ route('health.alerts.acknowledge', $alert) }}" method="POST">@csrf
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-check"></i> Acknowledge</button>
                            </form>
                            @else
                            <span class="badge badge-light"><i class="fas fa-check"></i> Acknowledged</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="card"><div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>{{ __('No active alerts') }}</h5>
                <p class="text-muted">All clear! Health alerts will appear here when client metrics need attention.</p>
            </div></div>
            @endforelse
            <div class="text-center">{{ $alerts->links() }}</div>
        </div>
    </section>
</div>
@endsection
