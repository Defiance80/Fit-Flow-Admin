<?php

namespace App\Models\Course\CourseChapter;

use App\Models\Course\Course;
use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course\UserCourseChapterTrack;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Course\CourseChapter\Quiz\CourseChapterQuiz;
use App\Models\Course\CourseChapter\Lecture\CourseChapterLecture;
use App\Models\Course\CourseChapter\Resource\CourseChapterResource;
use App\Models\Course\CourseChapter\Assignment\CourseChapterAssignment;

class CourseChapter extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'user_id',
        'title',
        'slug',
        'description',
        'is_active',
        'chapter_order',
        'type'
    ];

    protected $casts = [
        'free_preview' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'total_duration'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->chapter_order = $model->max('chapter_order') + 1;
            $model->slug = HelperService::generateUniqueSlug(CourseChapter::class, $model->title);
        });
        static::updating(function ($model) {
            $model->slug = HelperService::generateUniqueSlug(CourseChapter::class, $model->title, $model->id);
        });
    }

    /**
     * Get the course that owns the chapter.
     */
    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function lectures()
    {
        return $this->hasMany(CourseChapterLecture::class, 'course_chapter_id', 'id');
    }

    public function resources()
    {
        return $this->hasMany(CourseChapterResource::class, 'course_chapter_id', 'id');
    }

    public function quizzes()
    {
        return $this->hasMany(CourseChapterQuiz::class, 'course_chapter_id', 'id');
    }

    public function assignments()
    {
        return $this->hasMany(CourseChapterAssignment::class, 'course_chapter_id', 'id');
    }

    public function userTracks()
    {
        return $this->hasMany(UserCourseChapterTrack::class, 'course_chapter_id', 'id');
    }

    /**
     * Get count of active lectures.
     */
    public function getActiveLecturesCountAttribute() {
        return $this->lectures()->where('is_active', true)->count();
    }


    /**
     * Get total duration of lectures in this chapter.
     */
    public function getLectureDurationAttribute() {
        $lectures = $this->lectures()->get();
        $totalDuration = 0;
        foreach($lectures as $lecture) {
            $totalDuration += $lecture->duration;
        }
        return $totalDuration;
    }

    public function getQuizDurationAttribute() {
        $quizzes = $this->quizzes()->get();
        $totalDuration = 0;
        foreach($quizzes as $quiz) {
            $totalDuration += $quiz->duration;
        }
        return $totalDuration;
    }

    public function getTotalDurationAttribute() {
        return $this->lecture_duration + $this->quiz_duration;
    }

    public function getAllCurriculumDataAttribute() {
        $showTrashed = request('show_deleted', false);

        $lecturesQuery = $this->lectures()->with('resources');
        $resourcesQuery = $this->resources();
        $quizzesQuery = $this->quizzes()->with('resources');
        $assignmentsQuery = $this->assignments()->with('resources');

        if ($showTrashed) {
            $lecturesQuery->withTrashed();
            $resourcesQuery->withTrashed();
            $quizzesQuery->withTrashed();
            $assignmentsQuery->withTrashed();
        }

        // Get course information for level, course_type, and instructor
        $course = $this->course;
        $instructors = $course->instructors()->pluck('name')->join(', ');

        $lectures = $lecturesQuery->get()->map(function ($item) use ($course, $instructors) {
            $item->formatted_duration = HelperService::getFormattedDuration($item->duration ?? 0);
            $item->curriculum_type = config('constants.CURRICULUM_TYPES.LECTURE');
            $item->level = $course->level;
            $item->course_type = $course->course_type;
            $item->instructor = $instructors;
            return $item;
        });
        $resources = $resourcesQuery->get()->map(function ($item) use ($course, $instructors) {
            $item->formatted_duration = HelperService::getFormattedDuration($item->duration ?? 0);
            $item->curriculum_type = config('constants.CURRICULUM_TYPES.RESOURCE');
            $item->level = $course->level;
            $item->course_type = $course->course_type;
            $item->instructor = $instructors;
            return $item;
        });
        $quizzes = $quizzesQuery->get()->map(function ($item) use ($course, $instructors) {
            $item->formatted_duration = HelperService::getFormattedDuration($item->duration ?? 0);
            $item->curriculum_type = config('constants.CURRICULUM_TYPES.QUIZ');
            $item->level = $course->level;
            $item->course_type = $course->course_type;
            $item->instructor = $instructors;
            return $item;
        });
        $assignments = $assignmentsQuery->get()->map(function ($item) use ($course, $instructors) {
            $item->formatted_duration = null;
            $item->curriculum_type = config('constants.CURRICULUM_TYPES.ASSIGNMENT');
            $item->level = $course->level;
            $item->course_type = $course->course_type;
            $item->instructor = $instructors;
            return $item;
        });
        $data = collect(
            array_merge($lectures->toArray(), $resources->toArray(), $quizzes->toArray(), $assignments->toArray())
        );
        return $data->sortBy('chapter_order')->values();
    }
}
