<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trainer_id', 'client_id', 'facility_id', 'title', 'description',
        'type', 'start_at', 'end_at', 'location', 'is_virtual', 'meeting_url',
        'status', 'recurrence', 'recurrence_rule', 'parent_schedule_id',
        'notes', 'cancellation_reason',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_virtual' => 'boolean',
    ];

    public function trainer() { return $this->belongsTo(User::class, 'trainer_id'); }
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
    public function parent() { return $this->belongsTo(self::class, 'parent_schedule_id'); }
    public function occurrences() { return $this->hasMany(self::class, 'parent_schedule_id'); }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())->orderBy('start_at');
    }
}
