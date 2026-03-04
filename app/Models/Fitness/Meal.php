<?php

namespace App\Models\Fitness;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'meal_plan_day_id', 'meal_type', 'name', 'description',
        'calories', 'protein_g', 'carbs_g', 'fats_g',
        'recipe_url', 'preparation_notes', 'sort_order',
    ];

    public function day() { return $this->belongsTo(MealPlanDay::class, 'meal_plan_day_id'); }
}
