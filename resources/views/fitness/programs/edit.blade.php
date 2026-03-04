@extends('layouts.app')
@section('title', __('Edit Program'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ __('Edit') }}: {{ $program->name }}</h1></div>
        <div class="section-body">
            <form action="{{ route('programs.update', $program) }}" method="POST">
                @csrf @method('PUT')
                <div class="row"><div class="col-lg-8"><div class="card">
                    <div class="card-header"><h4>{{ __('Program Details') }}</h4></div>
                    <div class="card-body">
                        <div class="form-group"><label>{{ __('Name') }} *</label><input type="text" name="name" class="form-control" value="{{ old('name', $program->name) }}" required></div>
                        <div class="form-group"><label>{{ __('Description') }}</label><textarea name="description" class="form-control" rows="4">{{ old('description', $program->description) }}</textarea></div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>{{ __('Type') }}</label>
                                <select name="program_type" class="form-control">@foreach(['strength','cardio','flexibility','hybrid','sport_specific','rehabilitation','weight_loss','muscle_gain','general_fitness'] as $type)<option value="{{ $type }}" {{ old('program_type',$program->program_type)==$type?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$type)) }}</option>@endforeach</select></div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('Difficulty') }}</label>
                                <select name="difficulty" class="form-control">@foreach(['beginner','intermediate','advanced','all_levels'] as $d)<option value="{{ $d }}" {{ old('difficulty',$program->difficulty)==$d?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$d)) }}</option>@endforeach</select></div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('Duration (weeks)') }}</label><input type="number" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', $program->duration_weeks) }}"></div></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Update') }}</button>
                        <a href="{{ route('programs.show', $program) }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
                    </div>
                </div></div></div>
            </form>
        </div>
    </section>
</div>
@endsection
