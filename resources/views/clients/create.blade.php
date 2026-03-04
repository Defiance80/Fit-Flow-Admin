@extends('layouts.app')

@section('title', __('Add New Client'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('clients.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>{{ __('Add New Client') }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Client Information') }}</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('First Name') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                                                   value="{{ old('first_name') }}" required>
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                                   value="{{ old('last_name') }}" required>
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Email') }} <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                                   value="{{ old('email') }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Date of Birth') }}</label>
                                            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                                   value="{{ old('date_of_birth') }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Password') }} <span class="text-danger">*</span></label>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                                   required>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                                            <input type="password" name="password_confirmation" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Trainer') }}</label>
                                            <select name="trainer_id" class="form-control @error('trainer_id') is-invalid @enderror">
                                                <option value="">{{ __('Select Trainer (Optional)') }}</option>
                                                @foreach($trainers as $trainer)
                                                    <option value="{{ $trainer->id }}" 
                                                        {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                        {{ $trainer->first_name }} {{ $trainer->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('trainer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">{{ __('Or use trainer invite code below') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Trainer Invite Code') }}</label>
                                            <input type="text" name="invite_code" class="form-control @error('invite_code') is-invalid @enderror" 
                                                   value="{{ old('invite_code') }}" placeholder="{{ __('e.g., ABC12345') }}">
                                            @error('invite_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">{{ __('Leave blank if trainer selected above') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Fitness Goals') }}</label>
                                    <textarea name="fitness_goals" class="form-control @error('fitness_goals') is-invalid @enderror" 
                                              rows="3" placeholder="{{ __('What does the client want to achieve?') }}">{{ old('fitness_goals') }}</textarea>
                                    @error('fitness_goals')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Medical Notes') }}</label>
                                    <textarea name="medical_notes" class="form-control @error('medical_notes') is-invalid @enderror" 
                                              rows="3" placeholder="{{ __('Any medical conditions, allergies, or restrictions...') }}">{{ old('medical_notes') }}</textarea>
                                    @error('medical_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Emergency Contact Name') }}</label>
                                            <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                                   value="{{ old('emergency_contact_name') }}">
                                            @error('emergency_contact_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Emergency Contact Phone') }}</label>
                                            <input type="text" name="emergency_contact_phone" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                                   value="{{ old('emergency_contact_phone') }}">
                                            @error('emergency_contact_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Profile Photo') }}</label>
                                    <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" 
                                           accept="image/*">
                                    @error('profile_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Create Client') }}
                                    </button>
                                    <a href="{{ route('clients.index') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i> {{ __('Cancel') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
