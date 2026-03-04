<?php

namespace App\Models\Health;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HealthAlert extends Model
{
    protected $fillable = [
        'client_id', 'trainer_id', 'severity', 'alert_type',
        'title', 'message', 'data_snapshot', 'recommendation',
        'acknowledged', 'acknowledged_at',
    ];

    protected $casts = [
        'data_snapshot' => 'array',
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function scopeUnacknowledged($query)
    {
        return $query->where('acknowledged', false);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }
}
