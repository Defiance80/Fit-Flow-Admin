@extends('layouts.app')
@section('title', __('Create Meal Plan'))
@section('main')
<div class="main-content"><section class="section">
    <div class="section-header"><h1>{{ __('Create Meal Plan') }}</h1></div>
    <div class="section-body"><form action="{{ route('meal-plans.store') }}" method="POST">@csrf
        <div class="row"><div class="col-lg-8"><div class="card"><div class="card-header"><h4>{{ __('Plan Details') }}</h4></div><div class="card-body">
            <div class="form-group"><label>{{ __('Plan Name') }} *</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. High Protein Cut"></div>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>{{ __('Client') }} *</label><select name="client_id" class="form-control" required><option value="">Select...</option>@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group"><label>{{ __('Goal') }}</label><select name="goal" class="form-control">@foreach(['weight_loss','muscle_gain','maintenance','performance','general_health'] as $g)<option value="{{ $g }}">{{ ucfirst(str_replace('_',' ',$g)) }}</option>@endforeach</select></div></div>
            </div>
            <div class="row">
                <div class="col-md-3"><div class="form-group"><label>{{ __('Calories/day') }}</label><input type="number" name="daily_calories" class="form-control" value="{{ old('daily_calories') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('Protein (g)') }}</label><input type="number" name="protein_g" class="form-control" value="{{ old('protein_g') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('Carbs (g)') }}</label><input type="number" name="carbs_g" class="form-control" value="{{ old('carbs_g') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label>{{ __('Fats (g)') }}</label><input type="number" name="fats_g" class="form-control" value="{{ old('fats_g') }}"></div></div>
            </div>
            <div class="form-group"><label>{{ __('Description/Notes') }}</label><textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea></div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> {{ __('Create Plan') }}</button>
            <a href="{{ route('meal-plans.index') }}" class="btn btn-secondary btn-lg">{{ __('Cancel') }}</a>
        </div></div></div></div>
    </form></div>
</section></div>
@endsection
