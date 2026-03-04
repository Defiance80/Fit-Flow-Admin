@extends('layouts.app')
@section('title', __('Create Training Program'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ __('Create Training Program') }}</h1></div>
        <div class="section-body">
            <form action="{{ route('programs.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-8"><div class="card">
                        <div class="card-header"><h4>{{ __('Program Details') }}</h4></div>
                        <div class="card-body">
                            <div class="form-group"><label>{{ __('Program Name') }} *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. 12-Week Strength Builder">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="form-group"><label>{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea></div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>{{ __('Program Type') }}</label>
                                    <select name="program_type" class="form-control">
                                        @foreach(['strength','cardio','flexibility','hybrid','sport_specific','rehabilitation','weight_loss','muscle_gain','general_fitness'] as $type)
                                        <option value="{{ $type }}" {{ old('program_type')==$type?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$type)) }}</option>
                                        @endforeach
                                    </select></div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('Difficulty') }}</label>
                                    <select name="difficulty" class="form-control">
                                        @foreach(['beginner','intermediate','advanced','all_levels'] as $d)
                                        <option value="{{ $d }}" {{ old('difficulty')==$d?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$d)) }}</option>
                                        @endforeach
                                    </select></div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('Duration (weeks)') }}</label>
                                    <input type="number" name="duration_weeks" class="form-control" value="{{ old('duration_weeks') }}"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>{{ __('Sessions/Week') }}</label>
                                    <input type="number" name="sessions_per_week" class="form-control" value="{{ old('sessions_per_week') }}"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('Trainer') }}</label>
                                    <select name="trainer_id" class="form-control">
                                        @foreach($trainers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                                    </select></div></div>
                                <div class="col-md-4"><div class="form-group"><label>{{ __('Price') }}</label>
                                    <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}"></div></div></div>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" name="is_template" value="1" class="custom-control-input" id="isTemplate" {{ old('is_template')?'checked':'' }}>
                                <label class="custom-control-label" for="isTemplate">{{ __('Save as reusable template') }}</label>
                            </div>
                        </div>
                    </div></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Create Program') }}</button>
                <a href="{{ route('programs.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
            </form>
        </div>
    </section>
</div>
@endsection
