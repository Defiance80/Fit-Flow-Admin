@extends('layouts.app')
@section('title', __('Edit Trainer'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ __('Edit Trainer') }}: {{ $trainer->name }}</h1></div>
        <div class="section-body">
            <div class="row"><div class="col-lg-8"><div class="card">
                <div class="card-header"><h4>{{ __('Trainer Information') }}</h4></div>
                <div class="card-body">
                    <form action="{{ route('instructor.update', $trainer) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Name') }} *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $trainer->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Email') }} *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $trainer->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                        </div>
                        <div class="form-group"><label>{{ __('Bio') }}</label><textarea name="bio" class="form-control" rows="3">{{ old('bio', $trainer->bio) }}</textarea></div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Specializations') }}</label>
                                <input type="text" name="specializations" class="form-control" placeholder="strength, yoga, nutrition" value="{{ old('specializations', is_array($trainer->specializations) ? implode(', ', $trainer->specializations) : $trainer->specializations) }}">
                                <small class="text-muted">Comma separated</small></div></div>
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Certifications') }}</label>
                                <input type="text" name="certifications" class="form-control" placeholder="NASM-CPT, ACE" value="{{ old('certifications', is_array($trainer->certifications) ? implode(', ', $trainer->certifications) : $trainer->certifications) }}">
                                <small class="text-muted">Comma separated</small></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Facility') }}</label>
                                <select name="facility_id" class="form-control">
                                    <option value="">{{ __('Independent') }}</option>
                                    @foreach($facilities as $f)<option value="{{ $f->id }}" {{ old('facility_id', $trainer->facility_id)==$f->id?'selected':'' }}>{{ $f->name }}</option>@endforeach
                                </select></div></div>
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Type') }}</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="is_independent" value="1" class="custom-control-input" id="isIndependent" {{ old('is_independent', $trainer->is_independent)?'checked':'' }}>
                                    <label class="custom-control-label" for="isIndependent">{{ __('Independent Trainer') }}</label>
                                </div></div></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Update Trainer') }}</button>
                        <a href="{{ route('instructor.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
                    </form>
                </div>
            </div></div></div>
        </div>
    </section>
</div>
@endsection
