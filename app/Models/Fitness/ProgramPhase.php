<?php

namespace App\Models\Fitness;

use Illuminate\Database\Eloquent\Model;

class ProgramPhase extends Model
{
    protected $fillable = ['training_program_id', 'name', 'sort_order', 'duration_days', 'notes'];

    public function program()
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class)->orderBy('sort_order');
    }
}
