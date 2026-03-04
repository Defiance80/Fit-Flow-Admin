<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\FileService;

class FeatureSectionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_section_id',
        'image',
    ];

    public function getImageAttribute($value)
    {
        if($value){
            return FileService::getFileUrl($value);
        }
        return null;
    }

    public function featureSection()
    {
        return $this->belongsTo(FeatureSection::class);
    }
}