<?php

namespace App\Models\Health;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HealthBaseline extends Model
{
    protected $fillable = [
        'user_id', 'metric_type', 'avg_value', 'std_deviation',
        'min_value', 'max_value', 'sample_count', 'window_days', 'calculated_at',
    ];

    protected $casts = [
        'avg_value' => 'decimal:2',
        'std_deviation' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
