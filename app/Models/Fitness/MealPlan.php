<?php

namespace App\Models\Fitness;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'trainer_id', 'name', 'description', 'goal',
        'daily_calories', 'protein_g', 'carbs_g', 'fats_g', 'fiber_g',
        'dietary_restrictions', 'allergies', 'start_date', 'end_date',
        'is_active', 'is_template', 'notes',
    ];

    protected $casts = [
        'dietary_restrictions' => 'array',
        'allergies' => 'array',
        'is_active' => 'boolean',
        'is_template' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function trainer() { return $this->belongsTo(User::class, 'trainer_id'); }
    public function days() { return $this->hasMany(MealPlanDay::class); }
}
