@extends('layouts.app')

@section('title', __('Trainer Profile'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('trainers.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>{{ __('Trainer Profile') }}</h1>
            <div class="section-header-button">
                <a href="{{ route('trainers.edit', $trainer) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ __('Edit Profile') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($trainer->profile_photo)
                                    <img src="{{ Storage::url($trainer->profile_photo) }}" 
                                         alt="{{ $trainer->first_name }}" 
                                         class="rounded-circle" width="120" height="120">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                         style="width: 120px; height: 120px; font-size: 48px;">
                                        {{ strtoupper(substr($trainer->first_name, 0, 1) . substr($trainer->last_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h4>{{ $trainer->first_name }} {{ $trainer->last_name }}</h4>
                            <p class="text-muted">{{ $trainer->email }}</p>
                            <div class="mb-2">
                                @if($trainer->status === 'active')
                                    <span class="badge badge-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ ucfirst($trainer->status) }}</span>
                                @endif
                            </div>
                            @if($trainer->facility)
                                <p><i class="fas fa-building mr-2"></i>{{ $trainer->facility->name }}</p>
                            @elseif($trainer->is_independent)
                                <p><i class="fas fa-user mr-2"></i>{{ __('Independent Trainer') }}</p>
                            @endif
                            <p><strong>{{ __('Invite Code:') }}</strong> {{ $trainer->invite_code }}</p>
                        </div>
                    </div>

                    <!-- Stats Card -->
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Statistics') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ __('Clients') }}</span>
                                    <strong class="text-primary">{{ $clientsCount }}</strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ __('Programs') }}</span>
                                    <strong class="text-success">{{ $programsCount }}</strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ __('Upcoming Sessions') }}</span>
                                    <strong class="text-info">{{ $upcomingSessionsCount }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Bio Card -->
                    @if($trainer->bio)
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Bio') }}</h4>
                        </div>
                        <div class="card-body">
                            <p>{{ $trainer->bio }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Specializations and Certifications -->
                    <div class="row">
                        @if($trainer->specializations)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ __('Specializations') }}</h4>
                                </div>
                                <div class="card-body">
                                    <p>{{ $trainer->specializations }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($trainer->certifications)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ __('Certifications') }}</h4>
                                </div>
                                <div class="card-body">
                                    <p>{{ $trainer->certifications }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Recent Clients -->
                    @if($trainer->trainerClients->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Recent Clients') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trainer->trainerClients->take(5) as $trainerClient)
                                            <tr>
                                                <td>
                                                    @if($trainerClient->client)
                                                        {{ $trainerClient->client->first_name }} {{ $trainerClient->client->last_name }}
                                                    @else
                                                        <em class="text-muted">{{ __('Client not found') }}</em>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($trainerClient->client)
                                                        {{ $trainerClient->client->email }}
                                                    @endif
                                                </td>
                                                <td>{{ $trainerClient->start_date ? $trainerClient->start_date->format('M j, Y') : '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $trainerClient->status === 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($trainerClient->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
