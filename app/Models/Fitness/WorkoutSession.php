<?php

namespace App\Models\Fitness;

use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    protected $fillable = [
        'program_phase_id', 'name', 'sort_order',
        'estimated_duration_min', 'warmup_notes', 'cooldown_notes', 'notes',
    ];

    public function phase()
    {
        return $this->belongsTo(ProgramPhase::class, 'program_phase_id');
    }

    public function exercises()
    {
        return $this->hasMany(WorkoutExercise::class)->orderBy('sort_order');
    }
}
