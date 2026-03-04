@extends('layouts.app')

@section('title', __('Add New Trainer'))

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('instructor.index') }}" class="btn btn-icon">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1>{{ __('Add New Trainer') }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Trainer Information') }}</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('instructor.store') }}" enctype="multipart/form-data">
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
                                            <label>{{ __('Profile Photo') }}</label>
                                            <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" 
                                                   accept="image/*">
                                            @error('profile_photo')
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

                                <div class="form-group">
                                    <label>{{ __('Bio') }}</label>
                                    <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" 
                                              rows="4" placeholder="{{ __('Brief professional bio...') }}">{{ old('bio') }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Specializations') }}</label>
                                    <textarea name="specializations" class="form-control @error('specializations') is-invalid @enderror" 
                                              rows="3" placeholder="{{ __('e.g., Weight loss, Strength training, Yoga...') }}">{{ old('specializations') }}</textarea>
                                    @error('specializations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Certifications') }}</label>
                                    <textarea name="certifications" class="form-control @error('certifications') is-invalid @enderror" 
                                              rows="3" placeholder="{{ __('Professional certifications and qualifications...') }}">{{ old('certifications') }}</textarea>
                                    @error('certifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>{{ __('Facility') }}</label>
                                            <select name="facility_id" class="form-control @error('facility_id') is-invalid @enderror">
                                                <option value="">{{ __('Select Facility (Optional)') }}</option>
                                                @foreach($facilities as $facility)
                                                    <option value="{{ $facility->id }}" 
                                                        {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                                        {{ $facility->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('facility_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="form-check">
                                                <input type="checkbox" name="is_independent" value="1" class="form-check-input" 
                                                       {{ old('is_independent') ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    {{ __('Independent Trainer') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Create Trainer') }}
                                    </button>
                                    <a href="{{ route('instructor.index') }}" class="btn btn-secondary ml-2">
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
