<?php

namespace App\Models\Health;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BiometricConsent extends Model
{
    protected $fillable = [
        'client_id', 'trainer_id', 'metric_type', 'consented', 'consented_at', 'revoked_at',
    ];

    protected $casts = [
        'consented' => 'boolean',
        'consented_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('consented', true)->whereNull('revoked_at');
    }
}
