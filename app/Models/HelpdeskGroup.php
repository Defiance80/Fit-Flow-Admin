<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HelpdeskGroup extends Model {
    use SoftDeletes;
    protected $fillable = ['name','slug','description','image','row_order', 'is_active','is_private'];
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $maxSortOrder = static::max('row_order') ?? 0;
            $model->row_order = $maxSortOrder + 1;
            
            // Generate slug if not provided
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            // Update slug if name is changed
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }
    public function requests() {
        return $this->hasMany(HelpdeskGroupRequest::class, 'group_id');
    }
}
