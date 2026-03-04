@extends('layouts.app')
@section('title', $facility->name)
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ $facility->name }}</h1></div>
    <div class="section-body"><div class="row">
        <div class="col-md-4"><div class="card"><div class="card-body">
            <p>{{ $facility->description }}</p><hr>
            <p><i class="fas fa-map-marker-alt"></i> {{ $facility->address }}, {{ $facility->city }}, {{ $facility->state }}</p>
            @if($facility->phone)<p><i class="fas fa-phone"></i> {{ $facility->phone }}</p>@endif
            @if($facility->email)<p><i class="fas fa-envelope"></i> {{ $facility->email }}</p>@endif
            <span class="badge badge-primary">{{ ucfirst($facility->subscription_tier) }}</span>
        </div></div></div>
        <div class="col-md-8"><div class="card"><div class="card-header"><h4>{{ __('Trainers') }}</h4></div><div class="card-body">
            @forelse($facility->trainers as $trainer)
            <div class="d-flex align-items-center mb-2 p-2 border rounded">
                <img src="{{ $trainer->profile ?? asset('img/avatar/avatar-1.png') }}" class="rounded-circle mr-3" width="40" height="40" style="object-fit:cover">
                <div><strong>{{ $trainer->name }}</strong><br><small class="text-muted">{{ $trainer->email }}</small></div>
                <span class="ml-auto badge badge-light">{{ $trainer->clients()->count() }} clients</span>
            </div>
            @empty<p class="text-muted">No trainers in this facility yet.</p>@endforelse
        </div></div></div>
    </div></div>
</section></div>
@endsection
