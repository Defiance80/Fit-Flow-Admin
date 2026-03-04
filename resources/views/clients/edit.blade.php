@extends('layouts.app')
@section('title', __('Edit Client'))
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header"><h1>{{ __('Edit Client') }}: {{ $client->name }}</h1></div>
        <div class="section-body">
            <div class="row"><div class="col-lg-8"><div class="card">
                <div class="card-header"><h4>{{ __('Client Information') }}</h4></div>
                <div class="card-body">
                    <form action="{{ route('clients.update', $client) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Name') }} *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $client->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Email') }} *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $client->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Phone') }}</label>
                                <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $client->mobile) }}"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>{{ __('Gender') }}</label>
                                <select name="gender" class="form-control">
                                    <option value="">--</option>
                                    @foreach(['male','female','other','prefer_not_to_say'] as $g)<option value="{{ $g }}" {{ old('gender', $client->gender)==$g?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$g)) }}</option>@endforeach
                                </select></div></div>
                            <div class="col-md-3"><div class="form-group"><label>{{ __('DOB') }}</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $client->date_of_birth?->format('Y-m-d')) }}"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>{{ __('Height (cm)') }}</label><input type="number" step="0.1" name="height_cm" class="form-control" value="{{ old('height_cm', $client->height_cm) }}"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>{{ __('Weight (kg)') }}</label><input type="number" step="0.1" name="weight_kg" class="form-control" value="{{ old('weight_kg', $client->weight_kg) }}"></div></div>
                        </div>
                        <div class="form-group"><label>{{ __('Fitness Goals') }}</label><textarea name="fitness_goals" class="form-control" rows="3">{{ old('fitness_goals', $client->fitness_goals) }}</textarea></div>
                        <div class="form-group"><label>{{ __('Medical Notes') }}</label><textarea name="medical_notes" class="form-control" rows="2">{{ old('medical_notes', $client->medical_notes) }}</textarea></div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Emergency Contact') }}</label><input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $client->emergency_contact_name) }}"></div></div>
                            <div class="col-md-6"><div class="form-group"><label>{{ __('Emergency Phone') }}</label><input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $client->emergency_contact_phone) }}"></div></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Update Client') }}</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
                    </form>
                </div>
            </div></div></div>
        </div>
    </section>
</div>
@endsection
