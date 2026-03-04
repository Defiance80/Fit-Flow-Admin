@extends('layouts.app')

@section('title', __('Clients'))

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/datatables/media/css/jquery.dataTables.min.css') }}">
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Clients') }}</h1>
            <div class="section-header-button">
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add New Client') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <!-- Search and Filter -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Search & Filter') }}</h4>
                            <div class="card-header-action">
                                <button class="btn btn-secondary" data-toggle="collapse" data-target="#searchFilter">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body collapse" id="searchFilter">
                            <form method="GET" action="{{ route('clients.index') }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{ __('Search') }}</label>
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="{{ __('Name or email...') }}" 
                                                   value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{ __('Trainer') }}</label>
                                            <select name="trainer_id" class="form-control">
                                                <option value="">{{ __('All Trainers') }}</option>
                                                @foreach($trainers as $trainer)
                                                    <option value="{{ $trainer->id }}" 
                                                        {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                        {{ $trainer->first_name }} {{ $trainer->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="d-flex">
                                                <button type="submit" class="btn btn-primary mr-2">
                                                    <i class="fas fa-search"></i> {{ __('Search') }}
                                                </button>
                                                <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> {{ __('Clear') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('All Clients') }} ({{ $clients->total() }})</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Trainer') }}</th>
                                            <th>{{ __('Programs') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Last Activity') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($clients as $client)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-2">
                                                            @if($client->profile_photo)
                                                                <img src="{{ Storage::url($client->profile_photo) }}" alt="{{ $client->first_name }}">
                                                            @else
                                                                <div class="avatar-initial bg-success text-white">
                                                                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>{{ $client->first_name }} {{ $client->last_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">ID: {{ $client->id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $client->email }}</td>
                                                <td>
                                                    @if($client->trainerClients->count() > 0)
                                                        @foreach($client->trainerClients as $trainerClient)
                                                            @if($trainerClient->trainer)
                                                                <span class="badge badge-info mb-1">
                                                                    {{ $trainerClient->trainer->first_name }} {{ $trainerClient->trainer->last_name }}
                                                                </span><br>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">{{ __('No trainer assigned') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($client->clientPrograms)
                                                        <span class="badge badge-warning">{{ $client->clientPrograms->count() }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($client->status === 'active')
                                                        <span class="badge badge-success">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ ucfirst($client->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $client->updated_at->diffForHumans() }}</small>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="#" data-toggle="dropdown" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </a>
                                                        <div class="dropdown-menu">
                                                            <a href="{{ route('clients.show', $client) }}" class="dropdown-item">
                                                                <i class="fas fa-eye"></i> {{ __('View') }}
                                                            </a>
                                                            <a href="{{ route('clients.edit', $client) }}" class="dropdown-item">
                                                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <form method="POST" action="{{ route('clients.destroy', $client) }}" 
                                                                  onsubmit="return confirm('{{ __('Are you sure you want to deactivate this client?') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-ban"></i> {{ __('Deactivate') }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <p class="mt-4 mb-4">{{ __('No clients found.') }}</p>
                                                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> {{ __('Add First Client') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($clients->hasPages())
                            <div class="card-footer">
                                {{ $clients->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
@endpush
