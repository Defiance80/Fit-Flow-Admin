<?php

namespace App\Models\Course\CourseChapter\Lecture;

use App\Models\User;
use App\Services\FileService;
use App\Traits\HasChapterOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course\CourseChapter\CourseChapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseChapterLecture extends Model
{
    use HasFactory, SoftDeletes, HasChapterOrder;

    protected $fillable = [
        'user_id',
        'course_chapter_id',
        'title',
        'slug',
        'type',
        'file',
        'file_extension',
        'youtube_url',
        'hours',
        'minutes',
        'seconds',
        'description',
        'chapter_order',
        'is_active',
        'free_preview'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function chapter()
    {
        return $this->belongsTo(CourseChapter::class, 'course_chapter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resources()
    {
        return $this->hasMany(\App\Models\Course\CourseChapter\Lecture\LectureResource::class, 'lecture_id')->orderBy('order');
    }

    public function userTracks()
    {
        return $this->hasMany(LectureUserTrack::class, 'lecture_id');
    }

    public function getDurationAttribute()
    {
        return $this->hours * 3600 + $this->minutes * 60 + $this->seconds;
    }

    /**
     * Get File URl
     */
    public function getFileAttribute($value) {
        if($this->type == 'file'){
            return FileService::getFileUrl($value);
        }
        return $value;
    }
}
