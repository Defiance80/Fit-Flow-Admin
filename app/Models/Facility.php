<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'address', 'city', 'state',
        'zip', 'country', 'phone', 'email', 'website', 'logo', 'cover_image',
        'timezone', 'subscription_tier', 'is_active', 'settings', 'branding',
    ];

    protected $casts = [
        'settings' => 'array',
        'branding' => 'array',
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function trainers()
    {
        return $this->hasMany(User::class, 'facility_id')->where('user_role', 'trainer');
    }

    public function clients()
    {
        return $this->hasManyThrough(
            User::class, TrainerClient::class,
            'facility_id', 'id', 'id', 'client_id'
        );
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function subscription()
    {
        return $this->hasOne(SaasSubscription::class);
    }

    public function tenantConfig()
    {
        return $this->hasOne(TenantConfig::class);
    }

    public function trainingPrograms()
    {
        return $this->hasMany(TrainingProgram::class);
    }
}
