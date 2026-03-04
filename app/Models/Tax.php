<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasFactory;
        
    use SoftDeletes;

    protected $fillable = [
        'name',
        'percentage',
        'is_active',
        'is_default',
        'is_inclusive',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_tax');
    }

}