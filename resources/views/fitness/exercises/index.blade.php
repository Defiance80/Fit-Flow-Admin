@extends('layouts.app')
@section('title', __('Exercise Library'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ __('Exercise Library') }}</h1>
            <div class="section-header-button"><a href="{{ route('exercises.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('Add Exercise') }}</a></div>
        </div>
        <div class="section-body">
            <div class="row mb-3">
                <div class="col-12">
                    <form method="GET" class="d-flex">
                        <select name="category" class="form-control mr-2" style="max-width:200px" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach(['strength','cardio','flexibility','balance','plyometric','olympic','bodyweight','machine'] as $cat)
                            <option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                        <select name="difficulty" class="form-control mr-2" style="max-width:200px" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            @foreach(['beginner','intermediate','advanced'] as $d)
                            <option value="{{ $d }}" {{ request('difficulty')==$d?'selected':'' }}>{{ ucfirst($d) }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" class="form-control mr-2" placeholder="Search exercises..." value="{{ request('search') }}">
                        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="row">
            @forelse($exercises as $exercise)
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            @if($exercise->thumbnail)
                                <img src="{{ asset('storage/'.$exercise->thumbnail) }}" class="rounded mb-2" style="height:80px;object-fit:cover">
                            @else
                                <div class="rounded mb-2 d-flex align-items-center justify-content-center" style="height:80px;background:linear-gradient(135deg,var(--primary),var(--secondary))">
                                    <i class="fas fa-running fa-2x text-white"></i>
                                </div>
                            @endif
                            <h6>{{ $exercise->name }}</h6>
                            <span class="badge badge-primary">{{ ucfirst($exercise->category) }}</span>
                            <span class="badge badge-light">{{ ucfirst($exercise->difficulty) }}</span>
                            @if($exercise->muscle_groups)
                            <div class="mt-2">@foreach($exercise->muscle_groups as $mg)<small class="badge badge-outline-primary mr-1">{{ $mg }}</small>@endforeach</div>
                            @endif
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('exercises.edit', $exercise) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('exercises.destroy', $exercise) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="card"><div class="card-body text-center py-5">
                    <i class="fas fa-running fa-3x text-muted mb-3"></i><h5>{{ __('No exercises yet') }}</h5>
                    <a href="{{ route('exercises.create') }}" class="btn btn-primary mt-2">{{ __('Add Your First Exercise') }}</a>
                </div></div></div>
            @endforelse
            </div>
            <div class="text-center">{{ $exercises->links() }}</div>
        </div>
    </section>
</div>
@endsection
