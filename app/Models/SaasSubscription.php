<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaasSubscription extends Model
{
    protected $fillable = [
        'user_id', 'facility_id', 'tier', 'billing_cycle', 'price', 'setup_fee',
        'setup_fee_paid', 'stripe_customer_id', 'stripe_subscription_id', 'status',
        'max_clients', 'max_trainers', 'storage_mb',
        'trial_ends_at', 'current_period_start', 'current_period_end', 'cancelled_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'setup_fee_paid' => 'boolean',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function facility() { return $this->belongsTo(Facility::class); }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function hasCapacity(string $type): bool
    {
        if ($type === 'clients') {
            $current = TrainerClient::where('trainer_id', $this->user_id)->active()->count();
            return $current < $this->max_clients;
        }
        return true;
    }
}
