@extends('layouts.app')

@section('title', __('Training Programs'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __('Training Programs') }}</h1>
            <div class="section-header-button">
                <a href="{{ route('programs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Create New Program') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <!-- Filter Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Search & Filter') }}</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('programs.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{ __('Search') }}</label>
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="{{ __('Program name...') }}" 
                                                   value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{ __('Difficulty') }}</label>
                                            <select name="difficulty" class="form-control">
                                                <option value="">{{ __('All Levels') }}</option>
                                                <option value="beginner" {{ request('difficulty') == 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                                                <option value="intermediate" {{ request('difficulty') == 'intermediate' ? 'selected' : '' }}>{{ __('Intermediate') }}</option>
                                                <option value="advanced" {{ request('difficulty') == 'advanced' ? 'selected' : '' }}>{{ __('Advanced') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> {{ __('Filter') }}
                                                </button>
                                                <a href="{{ route('programs.index') }}" class="btn btn-secondary">
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

            <!-- Programs Grid -->
            <div class="row">
                @forelse($programs as $program)
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{ $program->name }}</h4>
                                <div class="card-header-action">
                                    <div class="dropdown">
                                        <a href="#" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">{{ __('Actions') }}</a>
                                        <div class="dropdown-menu">
                                            <a href="{{ route('programs.show', $program) }}" class="dropdown-item">
                                                <i class="fas fa-eye"></i> {{ __('View') }}
                                            </a>
                                            <a href="{{ route('programs.edit', $program) }}" class="dropdown-item">
                                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('programs.destroy', $program) }}" 
                                                  onsubmit="return confirm('{{ __('Are you sure you want to deactivate this program?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-ban"></i> {{ __('Deactivate') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge-{{ $program->difficulty === 'beginner' ? 'success' : ($program->difficulty === 'intermediate' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($program->difficulty) }}
                                        </span>
                                        <span class="badge badge-{{ $program->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($program->status) }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-user-tie mr-1"></i> 
                                        @if($program->trainer)
                                            {{ $program->trainer->first_name }} {{ $program->trainer->last_name }}
                                        @else
                                            <span class="text-danger">{{ __('No trainer') }}</span>
                                        @endif
                                    </p>
                                    
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-calendar-week mr-1"></i> 
                                        {{ $program->duration_weeks }} {{ __('weeks') }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-dumbbell mr-1"></i>
                                        {{ $program->sessions_per_week }}x/{{ __('week') }}
                                    </p>
                                    
                                    @if($program->description)
                                        <p class="mb-2">{{ Str::limit($program->description, 100) }}</p>
                                    @endif
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-list mr-1"></i>
                                            {{ $program->phases->count() }} {{ __('phases') }}
                                        </small>
                                    </div>
                                    <div>
                                        <small class="text-muted">{{ $program->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-dumbbell text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">{{ __('No Programs Found') }}</h5>
                                <p class="text-muted">{{ __('No training programs match your current filters.') }}</p>
                                <a href="{{ route('programs.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('Create First Program') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($programs->hasPages())
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-footer">
                                {{ $programs->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
