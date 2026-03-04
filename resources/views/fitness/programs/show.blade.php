@extends('layouts.app')
@section('title', $program->name)
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ $program->name }}</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('programs.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                <a href="{{ route('programs.edit', $program) }}" class="btn btn-warning ml-1"><i class="fas fa-edit"></i> Edit</a>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="badge badge-primary mb-2">{{ ucfirst(str_replace('_',' ',$program->program_type)) }}</span>
                            <span class="badge badge-light mb-2">{{ ucfirst($program->difficulty) }}</span>
                            <p>{{ $program->description }}</p>
                            <hr>
                            <div><strong>Trainer:</strong> {{ $program->trainer->name ?? 'N/A' }}</div>
                            <div><strong>Duration:</strong> {{ $program->duration_weeks ?? 'N/A' }} weeks</div>
                            <div><strong>Sessions/week:</strong> {{ $program->sessions_per_week ?? 'N/A' }}</div>
                            @if($program->price)<div><strong>Price:</strong> ${{ number_format($program->price, 2) }}</div>@endif
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    @forelse($program->phases as $phase)
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; border-radius: 3px 3px 0 0;">
                            <h4 style="color:#fff">{{ $phase->name }}</h4>
                            @if($phase->duration_days)<span class="badge badge-light">{{ $phase->duration_days }} days</span>@endif
                        </div>
                        <div class="card-body">
                            @forelse($phase->workoutSessions as $session)
                            <div class="border rounded p-3 mb-3">
                                <h6><i class="fas fa-dumbbell" style="color:var(--success)"></i> {{ $session->name }}
                                    @if($session->estimated_duration_min)<small class="text-muted ml-2">~{{ $session->estimated_duration_min }} min</small>@endif
                                </h6>
                                @if($session->exercises->count())
                                <table class="table table-sm table-bordered mt-2">
                                    <thead class="thead-light"><tr><th>Exercise</th><th>Sets</th><th>Reps</th><th>Weight</th><th>Rest</th></tr></thead>
                                    <tbody>
                                    @foreach($session->exercises as $we)
                                    <tr><td>{{ $we->exercise->name ?? 'N/A' }}</td><td>{{ $we->sets }}</td><td>{{ $we->reps }}</td><td>{{ $we->weight ?? '-' }}</td><td>{{ $we->rest_seconds ? $we->rest_seconds.'s' : '-' }}</td></tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                            </div>
                            @empty
                            <p class="text-muted">No workout sessions in this phase yet.</p>
                            @endforelse
                        </div>
                    </div>
                    @empty
                    <div class="card"><div class="card-body text-center py-5"><i class="fas fa-layer-group fa-3x text-muted mb-3"></i><h5>No phases added yet</h5><p class="text-muted">Edit this program to add phases, sessions, and exercises.</p></div></div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
