@extends('layouts.app')
@section('title', __('Book Session'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ __('Book Session') }}</h1></div>
    <div class="section-body"><div class="row"><div class="col-lg-8"><div class="card"><div class="card-header"><h4>{{ __('Session Details') }}</h4></div><div class="card-body">
        <form action="{{ route('schedules.store') }}" method="POST">@csrf
            <div class="form-group"><label>{{ __('Title') }} *</label><input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Upper Body Strength"></div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>{{ __('Trainer') }} *</label><select name="trainer_id" class="form-control" required><option value="">Select...</option>@foreach($trainers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group"><label>{{ __('Client') }}</label><select name="client_id" class="form-control"><option value="">Select...</option>@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>{{ __('Date & Time') }} *</label><input type="datetime-local" name="start_at" class="form-control" required></div></div>
                <div class="col-md-4"><div class="form-group"><label>{{ __('End Time') }} *</label><input type="datetime-local" name="end_at" class="form-control" required></div></div>
                <div class="col-md-4"><div class="form-group"><label>{{ __('Type') }}</label><select name="type" class="form-control">@foreach(['session','consultation','assessment','group_class'] as $t)<option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach</select></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>{{ __('Location') }}</label><input type="text" name="location" class="form-control" placeholder="Gym name or address"></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('Virtual?') }}</label><div class="custom-control custom-checkbox mt-2"><input type="checkbox" name="is_virtual" value="1" class="custom-control-input" id="isVirtual"><label class="custom-control-label" for="isVirtual">Virtual session</label></div></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('Recurrence') }}</label><select name="recurrence" class="form-control"><option value="none">None</option><option value="weekly">Weekly</option><option value="biweekly">Bi-weekly</option></select></div></div>
            </div>
            <div class="form-group"><label>{{ __('Notes') }}</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Book Session') }}</button>
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
        </form>
    </div></div></div></div></div>
</section></div>
@endsection
