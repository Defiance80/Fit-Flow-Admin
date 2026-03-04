@extends('layouts.app')
@section('title', __('Facilities'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ __('Facilities') }}</h1>
        <div class="section-header-button"><a href="{{ route('facilities.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('Add Facility') }}</a></div></div>
    <div class="section-body">
        <div class="row">
        @forelse($facilities as $facility)
        <div class="col-md-4"><div class="card">
            <div class="card-body text-center">
                @if($facility->logo)<img src="{{ asset('storage/'.$facility->logo) }}" class="mb-2" style="max-height:60px">@else<i class="fas fa-building fa-3x mb-2" style="color:var(--primary)"></i>@endif
                <h5>{{ $facility->name }}</h5>
                <p class="text-muted small">{{ $facility->address }}@if($facility->city), {{ $facility->city }}@endif</p>
                <span class="badge badge-{{ $facility->subscription_tier == 'enterprise' ? 'danger' : ($facility->subscription_tier == 'premium' ? 'warning' : 'primary') }}">{{ ucfirst($facility->subscription_tier) }}</span>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <small><i class="fas fa-user-tie"></i> {{ $facility->trainers_count ?? 0 }} trainers</small>
                    <div><a href="{{ route('facilities.show', $facility) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a></div>
                </div>
            </div>
        </div></div>
        @empty
        <div class="col-12"><div class="card"><div class="card-body text-center py-5"><i class="fas fa-building fa-3x text-muted mb-3"></i><h5>{{ __('No facilities yet') }}</h5><a href="{{ route('facilities.create') }}" class="btn btn-primary mt-2">{{ __('Add Facility') }}</a></div></div></div>
        @endforelse
        </div>
    </div>
</section></div>
@endsection
