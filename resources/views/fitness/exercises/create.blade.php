@extends('layouts.app')
@section('title', __('Add Exercise'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ __('Add Exercise') }}</h1></div>
        <div class="section-body"><div class="row"><div class="col-lg-8"><div class="card">
            <div class="card-header"><h4>{{ __('Exercise Details') }}</h4></div>
            <div class="card-body">
                <form action="{{ route('exercises.store') }}" method="POST">@csrf
                    <div class="form-group"><label>{{ __('Name') }} *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label>{{ __('Description') }}</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea></div>
                    <div class="form-group"><label>{{ __('Instructions') }}</label><textarea name="instructions" class="form-control" rows="4" placeholder="Step-by-step instructions...">{{ old('instructions') }}</textarea></div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>{{ __('Category') }}</label>
                            <select name="category" class="form-control">@foreach(['strength','cardio','flexibility','balance','plyometric','olympic','bodyweight','machine','other'] as $cat)<option value="{{ $cat }}" {{ old('category')==$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>@endforeach</select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>{{ __('Difficulty') }}</label>
                            <select name="difficulty" class="form-control">@foreach(['beginner','intermediate','advanced'] as $d)<option value="{{ $d }}" {{ old('difficulty')==$d?'selected':'' }}>{{ ucfirst($d) }}</option>@endforeach</select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>{{ __('Video URL') }}</label><input type="url" name="video_url" class="form-control" value="{{ old('video_url') }}" placeholder="YouTube/Vimeo link"></div></div>
                    </div>
                    <div class="form-group"><label>{{ __('Muscle Groups') }}</label><input type="text" name="muscle_groups" class="form-control" value="{{ old('muscle_groups') }}" placeholder="chest, triceps, shoulders"><small class="text-muted">Comma separated</small></div>
                    <div class="form-group"><label>{{ __('Equipment') }}</label><input type="text" name="equipment" class="form-control" value="{{ old('equipment') }}" placeholder="barbell, bench, dumbbells"><small class="text-muted">Comma separated</small></div>
                    <div class="custom-control custom-checkbox mb-3"><input type="checkbox" name="is_global" value="1" class="custom-control-input" id="isGlobal" {{ old('is_global')?'checked':'' }}><label class="custom-control-label" for="isGlobal">{{ __('Available to all trainers') }}</label></div>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Save Exercise') }}</button>
                    <a href="{{ route('exercises.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div></div></div></div>
    </section>
</div>
@endsection
