@extends('layouts.app')
@section('title', __('Schedule'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> {{ __('Schedule') }}</h1>
        <div class="section-header-button"><a href="{{ route('schedules.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('Book Session') }}</a></div></div>
    <div class="section-body">
        <div class="row mb-3"><div class="col-12">
            <form method="GET" class="d-flex"><select name="trainer_id" class="form-control mr-2" style="max-width:200px" onchange="this.form.submit()"><option value="">All Trainers</option>@foreach($trainers ?? [] as $t)<option value="{{ $t->id }}" {{ request('trainer_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select>
            <select name="status" class="form-control mr-2" style="max-width:150px" onchange="this.form.submit()"><option value="">All Status</option>@foreach(['scheduled','confirmed','completed','cancelled','no_show'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></form>
        </div></div>
        @forelse($schedules as $date => $daySchedules)
        <h6 class="text-muted mb-2">{{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }}</h6>
        @foreach($daySchedules as $schedule)
        <div class="card mb-2"><div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-center" style="min-width:60px"><h5 class="mb-0" style="color:var(--primary)">{{ $schedule->start_at->format('g:i') }}</h5><small class="text-muted">{{ $schedule->start_at->format('A') }}</small></div>
                    <div><strong>{{ $schedule->title }}</strong>
                        <br><small><i class="fas fa-user-tie"></i> {{ $schedule->trainer->name ?? 'N/A' }} @if($schedule->client) → <i class="fas fa-user"></i> {{ $schedule->client->name }} @endif</small>
                        <br><small class="text-muted"><i class="fas fa-{{ $schedule->is_virtual ? 'video' : 'map-marker-alt' }}"></i> {{ $schedule->is_virtual ? 'Virtual' : ($schedule->location ?? 'TBD') }} · {{ $schedule->start_at->format('g:i A') }} - {{ $schedule->end_at->format('g:i A') }}</small>
                    </div>
                </div>
                <div><span class="badge badge-{{ $schedule->status == 'completed' ? 'success' : ($schedule->status == 'cancelled' ? 'danger' : ($schedule->status == 'confirmed' ? 'primary' : 'warning')) }}">{{ ucfirst(str_replace('_',' ',$schedule->status)) }}</span>
                    <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-warning ml-1"><i class="fas fa-edit"></i></a></div>
            </div>
        </div></div>
        @endforeach
        @empty
        <div class="card"><div class="card-body text-center py-5"><i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i><h5>{{ __('No sessions scheduled') }}</h5><a href="{{ route('schedules.create') }}" class="btn btn-primary mt-2">{{ __('Book First Session') }}</a></div></div>
        @endforelse
    </div>
</section></div>
@endsection
