<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\HasAirport;


class Course extends BaseModel
{
    use SoftDeletes, HasAirport;

    protected $fillable = [
        'title', 'slug', 'description', 'thumbnail', 'status', 'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];
    
    protected $appends = ['progress_percentage'];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            $course->slug = Str::slug($course->title);
        });
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * The instructors that belong to the course.
     * Using 'course_instructor' pivot table with 'user_id' as foreign key
     */
    public function instructors()
    {
        return $this->belongsToMany(User::class, 'course_instructor', 'course_id', 'user_id')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function getActiveBatchesCountAttribute()
    {
        return $this->batches()->where('status', 'ongoing')->count();
    }

    public function getTotalStudentsAttribute()
    {
        return $this->batches()
            ->withCount('students')
            ->get()
            ->sum('students_count');
    }
    
    /**
     * Get course progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        $totalModules = $this->modules()->count();
        if ($totalModules == 0) {
            return 0;
        }
        $activeModules = $this->modules()->where('status', 'active')->count();
        return round(($activeModules / $totalModules) * 100);
    }

    /**
     * Get progress color based on percentage
     */
    public function getProgressColorAttribute()
    {
        $progress = $this->progress_percentage;
        if ($progress >= 80) return 'success';
        if ($progress >= 50) return 'warning';
        return 'danger';
    }
}
