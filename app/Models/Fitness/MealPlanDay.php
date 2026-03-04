<?php

namespace App\Models\Fitness;

use Illuminate\Database\Eloquent\Model;

class MealPlanDay extends Model
{
    protected $fillable = ['meal_plan_id', 'day_of_week', 'day_type', 'adjusted_calories'];

    public function mealPlan() { return $this->belongsTo(MealPlan::class); }
    public function meals() { return $this->hasMany(Meal::class)->orderBy('sort_order'); }
}
