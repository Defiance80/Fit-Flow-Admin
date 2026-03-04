<?php

namespace App\Models\Fitness;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'instructions', 'category',
        'muscle_groups', 'equipment', 'difficulty', 'video_url',
        'thumbnail', 'is_global', 'created_by',
    ];

    protected $casts = [
        'muscle_groups' => 'array',
        'equipment' => 'array',
        'is_global' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAvailableTo($query, $userId)
    {
        return $query->where('is_global', true)->orWhere('created_by', $userId);
    }
}
