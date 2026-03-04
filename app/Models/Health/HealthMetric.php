<?php

namespace App\Models\Health;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    protected $fillable = [
        'user_id', 'metric_type', 'value', 'unit', 'source', 'recorded_at', 'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'recorded_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    public function scopeRecent($query, int $days = 14)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}
