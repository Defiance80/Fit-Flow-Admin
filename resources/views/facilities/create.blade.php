@extends('layouts.app')
@section('title', __('Add Facility'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ __('Add Facility') }}</h1></div>
    <div class="section-body"><div class="row"><div class="col-lg-8"><div class="card"><div class="card-body">
        <form action="{{ route('facilities.store') }}" method="POST">@csrf
            <div class="form-group"><label>{{ __('Facility Name') }} *</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>{{ __('Description') }}</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>{{ __('Address') }}</label><input type="text" name="address" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('City') }}</label><input type="text" name="city" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('State') }}</label><input type="text" name="state" class="form-control"></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>{{ __('Phone') }}</label><input type="text" name="phone" class="form-control"></div></div>
                <div class="col-md-4"><div class="form-group"><label>{{ __('Email') }}</label><input type="email" name="email" class="form-control"></div></div>
                <div class="col-md-4"><div class="form-group"><label>{{ __('Website') }}</label><input type="url" name="website" class="form-control"></div></div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Create Facility') }}</button>
            <a href="{{ route('facilities.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
        </form>
    </div></div></div></div></div>
</section></div>
@endsection
