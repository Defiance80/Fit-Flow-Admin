<?php

namespace App\Models\Course;

use App\Models\Tag;
use App\Models\User;
use App\Models\Tax;
use App\Models\Category;
use App\Models\Rating;
use App\Models\PromoCode;
use App\Services\FileService;
use App\Services\HelperService;
use App\Traits\ProtectsDemoData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course\CourseChapter\CourseChapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model {
    use HasFactory, SoftDeletes, ProtectsDemoData;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'thumbnail',
        'intro_video',
        'user_id',
        'level',
        'course_type',
        'status',
        'price',
        'discount_price',
        'category_id',
        'is_active',
        'sequential_access',
        'certificate_enabled',
        'certificate_fee',
        'approval_status',
        'language_id',
        'meta_title',
        'meta_image',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'sequential_access' => 'boolean',
        'certificate_enabled' => 'boolean',
        'price'            => 'decimal:2',
        'discount_price'   => 'decimal:2',
        'certificate_fee'  => 'decimal:2',
    ];

    protected $appends = ['total_tax_percentage', 'display_price', 'display_discount_price', 'tax_amount'];

    protected $with = ['taxes'];

    protected static function boot()
    {
        parent::boot();
        
        static::forceDeleting(function ($course) {
            FileService::delete($course->thumbnail);
            FileService::delete($course->intro_video);
            FileService::delete($course->meta_image);
            $course->learnings()->delete();
            $course->requirements()->delete();
            $course->chapters()->delete();
            $course->tags()->detach();
            $course->instructors()->detach();
        });
    }
    /**
     * Get the user who owns the course.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category that owns the course.
     */
    public function category() {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the chapters for the course.
     */
    public function chapters() {
        return $this->hasMany(CourseChapter::class)->orderBy('chapter_order');
    }

    /**
     * Get the learnings for the course.
     */
    public function learnings(){
        return $this->hasMany(CourseLearning::class, 'course_id', 'id');
    }

    /**
     * Get the requirements for the course.
     */
    public function requirements(){
        return $this->hasMany(CourseRequirement::class, 'course_id', 'id');
    }

    /**
     * Get the tags for the course.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tags', 'course_id', 'tag_id');
    }

    /**
     * Get the instructors for the course.
     */
    public function instructors()
    {
        return $this->belongsToMany(User::class, 'course_instructors', 'course_id', 'user_id');
    }

    /**
     * Get the team members for the course.
     */
    public function team_members()
    {
        return $this->hasManyThrough(
            \App\Models\TeamMember::class,
            \App\Models\Instructor::class,
            'user_id', // Foreign key on instructors table
            'instructor_id', // Foreign key on team_members table
            'user_id', // Local key on courses table
            'id' // Local key on instructors table
        )->where('team_members.status', 'approved');
    }

    /**
     * Get all team members for the course (regardless of status).
     */
    public function all_team_members()
    {
        return $this->hasManyThrough(
            \App\Models\TeamMember::class,
            \App\Models\Instructor::class,
            'user_id', // Foreign key on instructors table
            'instructor_id', // Foreign key on team_members table
            'user_id', // Local key on courses table
            'id' // Local key on instructors table
        );
    }

    /**
     * Get the language for the course.
     */
    public function language()
    {
        return $this->belongsTo(CourseLanguage::class, 'language_id');
    }


    /**
     * Check if course has a discount
     */
    public function getHasDiscountAttribute() {
        return !is_null($this->discount_price) && $this->discount_price < $this->price;
    }

    public function getThumbnailAttribute($value){
        // Return full URL for course thumbnail if it exists
        // Don't fall back to default logo - return null if course has no thumbnail
        if(!empty($value)){
            // Always return full URL, regardless of file existence
            // This ensures API responses always have full URLs
            return FileService::getFileUrl($value);
        }
        // Return null if course has no thumbnail (don't use default logo)
        return null;
    }

    public function getMetaImageAttribute($value){
        if(!empty($value)){
            // Always return full URL, regardless of file existence
            // This ensures API responses always have full URLs
            return FileService::getFileUrl($value);
        }
        // Always return full URL for default logo
        $defaultLogo = HelperService::getDefaultLogo();
        // Ensure default logo is always a full URL
        if($defaultLogo && !filter_var($defaultLogo, FILTER_VALIDATE_URL)){
            return FileService::getFileUrl($defaultLogo);
        }
        return $defaultLogo;
    }

    public function getIntroVideoAttribute($value){
        return $value ? FileService::getFileUrl($value) : null;
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'course_tax')->withTimestamps();
    }

    public function getTotalTaxPercentageAttribute()
    {
        // Get ALL active taxes from tax table (not just linked to course)
        return Tax::where('is_active', 1)->sum('percentage');
    }

    public function getDisplayPriceAttribute()
    {
        $taxType = HelperService::systemSettings('tax_type'); // 'inclusive' or 'exclusive'
        $price = $this->price ?? 0;
        $totalTaxPercentage = $this->getTotalTaxPercentageAttribute();

        if ($taxType === 'inclusive') {
            // If tax is inclusive, add tax amount to price
            $taxAmount = ($price * $totalTaxPercentage) / 100;
            return round($price + $taxAmount, 2);
        } else {
            // If tax is exclusive, price remains same (tax shown separately)
            return round($price, 2);
        }
    }

    public function getDisplayDiscountPriceAttribute()
    {
        $taxType = HelperService::systemSettings('tax_type');
        $discountPrice = $this->discount_price ?? 0;
        $totalTaxPercentage = $this->getTotalTaxPercentageAttribute();

        if ($taxType === 'inclusive') {
            // If tax is inclusive, add tax amount to discount price
            if ($discountPrice > 0) {
                $taxAmount = ($discountPrice * $totalTaxPercentage) / 100;
                return round($discountPrice + $taxAmount, 2);
            }
            return round($discountPrice, 2);
        } else {
            // If tax is exclusive, discount price remains same (tax shown separately)
            return round($discountPrice, 2);
        }
    }

    public function getTaxAmountAttribute()
    {
        $taxType = HelperService::systemSettings('tax_type');
        $totalTaxPercentage = $this->getTotalTaxPercentageAttribute();
        
        // Use discount_price if available, otherwise use price
        $basePrice = $this->discount_price ?? $this->price ?? 0;
        
        if ($totalTaxPercentage > 0 && $basePrice > 0) {
            // Calculate tax amount from base price
            return round(($basePrice * $totalTaxPercentage) / 100, 2);
        }
        
        return 0;
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_course');
    }

    public function wishlistedByUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists', 'course_id', 'user_id')
                    ->withTimestamps();
    }

    public function wishlists()
    {
        return $this->hasMany(\App\Models\Wishlist::class, 'course_id', 'id');
    }

    public function orderCourses()
    {
        return $this->hasMany(\App\Models\OrderCourse::class, 'course_id', 'id');
    }

    public function getEnrolledStudents()
    {
        return User::whereHas('orders.orderCourses', function($query) {
            $query->where('course_id', $this->id)
                  ->whereHas('order', function($orderQuery) {
                      $orderQuery->where('status', 'completed');
                  });
        })->get();
    }

    public function views()
    {
        return $this->hasMany(\App\Models\CourseView::class);
    }

    public function getViewCountAttribute()
    {
        return $this->views()->count();
    }

    public function getUniqueViewCountAttribute()
    {
        return $this->views()->distinct('ip_address')->count();
    }

}
