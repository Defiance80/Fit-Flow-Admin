<?php

namespace App\Models\Health;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InsightLog extends Model
{
    protected $fillable = [
        'client_id', 'trainer_id', 'insight_type', 'data_snapshot',
        'recommendation', 'applied', 'trainer_response',
    ];

    protected $casts = [
        'data_snapshot' => 'array',
        'applied' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }
}
