<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerClient extends Model
{
    protected $fillable = [
        'trainer_id', 'client_id', 'facility_id', 'status',
        'permissions', 'notes', 'subscribed_at', 'cancelled_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'subscribed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
