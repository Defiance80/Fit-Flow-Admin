@extends('layouts.app')

@section('title', __('Trainers'))

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/datatables/media/css/jquery.dataTables.min.css') }}">
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Trainers') }}</h1>
            <div class="section-header-button">
                <a href="{{ route('trainers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add New Trainer') }}
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
                            <form method="GET" action="{{ route('trainers.index') }}">
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
                                            <label>{{ __('Facility') }}</label>
                                            <select name="facility_id" class="form-control">
                                                <option value="">{{ __('All Facilities') }}</option>
                                                @foreach($facilities as $facility)
                                                    <option value="{{ $facility->id }}" 
                                                        {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                                        {{ $facility->name }}
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
                                                <a href="{{ route('trainers.index') }}" class="btn btn-secondary">
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

            <!-- Trainers List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('All Trainers') }} ({{ $trainers->total() }})</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Facility') }}</th>
                                            <th>{{ __('Specializations') }}</th>
                                            <th>{{ __('Clients') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($trainers as $trainer)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-2">
                                                            @if($trainer->profile_photo)
                                                                <img src="{{ Storage::url($trainer->profile_photo) }}" alt="{{ $trainer->first_name }}">
                                                            @else
                                                                <div class="avatar-initial bg-primary text-white">
                                                                    {{ strtoupper(substr($trainer->first_name, 0, 1) . substr($trainer->last_name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>{{ $trainer->first_name }} {{ $trainer->last_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">ID: {{ $trainer->id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $trainer->email }}</td>
                                                <td>
                                                    @if($trainer->facility)
                                                        <span class="badge badge-info">{{ $trainer->facility->name }}</span>
                                                    @elseif($trainer->is_independent)
                                                        <span class="badge badge-warning">{{ __('Independent') }}</span>
                                                    @else
                                                        <span class="text-muted">{{ __('Not Assigned') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($trainer->specializations)
                                                        <small>{{ Str::limit($trainer->specializations, 50) }}</small>
                                                    @else
                                                        <span class="text-muted">{{ __('Not specified') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $trainer->trainerClients->count() }}</span>
                                                </td>
                                                <td>
                                                    @if($trainer->status === 'active')
                                                        <span class="badge badge-success">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ ucfirst($trainer->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="#" data-toggle="dropdown" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </a>
                                                        <div class="dropdown-menu">
                                                            <a href="{{ route('trainers.show', $trainer) }}" class="dropdown-item">
                                                                <i class="fas fa-eye"></i> {{ __('View') }}
                                                            </a>
                                                            <a href="{{ route('trainers.edit', $trainer) }}" class="dropdown-item">
                                                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <form method="POST" action="{{ route('trainers.destroy', $trainer) }}" 
                                                                  onsubmit="return confirm('{{ __('Are you sure you want to deactivate this trainer?') }}')">
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
                                                    <p class="mt-4 mb-4">{{ __('No trainers found.') }}</p>
                                                    <a href="{{ route('trainers.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> {{ __('Add First Trainer') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($trainers->hasPages())
                            <div class="card-footer">
                                {{ $trainers->appends(request()->query())->links() }}
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
    <script src="{{ asset('library/jquery-ui-dist/jquery-ui.min.js') }}"></script>
@endpush
