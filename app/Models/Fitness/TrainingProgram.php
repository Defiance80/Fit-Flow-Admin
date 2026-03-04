<?php

namespace App\Models\Fitness;

use App\Models\User;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trainer_id', 'facility_id', 'name', 'slug', 'description',
        'program_type', 'difficulty', 'duration_weeks', 'sessions_per_week',
        'thumbnail', 'is_template', 'is_active', 'price',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function phases()
    {
        return $this->hasMany(ProgramPhase::class)->orderBy('sort_order');
    }

    public function clients()
    {
        return $this->belongsToMany(User::class, 'client_programs', 'training_program_id', 'client_id')
            ->withPivot('status', 'start_date', 'end_date')
            ->withTimestamps();
    }
}
