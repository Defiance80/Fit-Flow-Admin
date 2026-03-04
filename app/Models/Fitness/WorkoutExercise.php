<?php

namespace App\Models\Fitness;

use Illuminate\Database\Eloquent\Model;

class WorkoutExercise extends Model
{
    protected $fillable = [
        'workout_session_id', 'exercise_id', 'sort_order',
        'sets', 'reps', 'weight', 'rest_seconds', 'tempo', 'rpe_target', 'notes',
    ];

    public function session()
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
