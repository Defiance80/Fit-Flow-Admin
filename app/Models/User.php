<?php

namespace App\Models;

use App\Models\Course\Course;
use App\Models\Cart;
use App\Models\Rating;
use App\Traits\ProtectsDemoData;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasPermissions, ProtectsDemoData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'mobile',
        'password',
        'is_active',
        'country_code',
        'profile',
        'wallet_balance',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
    ];


    public function getProfileAttribute($image) {
        if (!empty($image) && !filter_var($image, FILTER_VALIDATE_URL)) {
            return url(Storage::url($image));
        }
        return $image;
    }

    /**
     * Get the courses for the user.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'user_id', 'id');
    }
    /**
     * Get the instructor process status for the user.
     */
    public function instructor_details()
    {
        return $this->hasOne(Instructor::class);
    }

    /**
     * Get the instructor process status for the user.
     */
    public function getInstructorProcessStatusAttribute()
    {
        return $this->instructor_details->status ?? 'pending';
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(\App\Models\Wishlist::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlistCourses()
    {
        return $this->belongsToMany(
            Course::class,
            'wishlists',         // Pivot table name
            'user_id',           // Foreign key on the pivot table for this model
            'course_id'          // Foreign key on the pivot table for related model
        );
    }

    public function trackedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user_tracks')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function assignedRole()
    {
        return $this->belongsTo(Role::class);
    }

    public function walletHistories()
    {
        return $this->hasMany(WalletHistory::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function processedRefunds()
    {
        return $this->hasMany(RefundRequest::class, 'processed_by');
    }

    /**
     * Get the ratings given by the user.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    

    // ===== FIT FLOW RELATIONSHIPS =====

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function ownedFacility()
    {
        return $this->hasOne(Facility::class, 'owner_id');
    }

    /**
     * Clients this trainer manages
     */
    public function clients()
    {
        return $this->belongsToMany(User::class, 'trainer_clients', 'trainer_id', 'client_id')
            ->withPivot('status', 'subscribed_at', 'notes')
            ->withTimestamps();
    }

    /**
     * Trainers this client is subscribed to
     */
    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_clients', 'client_id', 'trainer_id')
            ->withPivot('status', 'subscribed_at')
            ->withTimestamps();
    }

    public function trainingPrograms()
    {
        return $this->hasMany(\App\Models\Fitness\TrainingProgram::class, 'trainer_id');
    }

    public function healthMetrics()
    {
        return $this->hasMany(\App\Models\Health\HealthMetric::class);
    }

    public function healthAlerts()
    {
        return $this->hasMany(\App\Models\Health\HealthAlert::class, 'client_id');
    }

    public function trainerAlerts()
    {
        return $this->hasMany(\App\Models\Health\HealthAlert::class, 'trainer_id');
    }

    public function mealPlans()
    {
        return $this->hasMany(\App\Models\Fitness\MealPlan::class, 'client_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'trainer_id');
    }

    public function clientSchedules()
    {
        return $this->hasMany(Schedule::class, 'client_id');
    }

    public function saasSubscription()
    {
        return $this->hasOne(SaasSubscription::class);
    }

    public function isTrainer(): bool
    {
        return $this->user_role === 'trainer';
    }

    public function isClient(): bool
    {
        return $this->user_role === 'client';
    }

    public function isFacilityOwner(): bool
    {
        return $this->user_role === 'facility_owner';
    }

    public function isIndependent(): bool
    {
        return $this->isTrainer() && $this->is_independent;
    }

    /**
     * Generate unique invite code for trainer
     */
    public static function generateInviteCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('invite_code', $code)->exists());
        return $code;
    }
}
