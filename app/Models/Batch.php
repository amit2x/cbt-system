<?php

namespace App\Models;

use App\Traits\AirportAware;
use App\Traits\HasAirport;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends BaseModel
{
    use AirportAware, HasAirport,  SoftDeletes;

    protected $fillable = [
        'name', 'course_id', 'airport_id', 'instructor_id', 'start_date',
        'end_date', 'capacity', 'description', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // public function students()
    // {
    //     return $this->belongsToMany(User::class, 'batch_student', 'batch_id', 'student_id')
    //         ->withPivot('status', 'enrolled_at')
    //         ->withTimestamps();
    // }

    // Since we have added a custom pivot model (BatchStudent), we can specify it in the relationship definition. This allows us to access additional attributes and methods defined in the pivot model.

    public function students()
    {
        return $this->belongsToMany(User::class, 'batch_student', 'batch_id', 'student_id')
            ->using(BatchStudent::class)   // Use custom pivot model
            ->withPivot('airport_id', 'status', 'enrolled_at')
            ->withTimestamps();
    }

    public function activeStudents()
    {
        return $this->students()->wherePivot('status', 'active');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function getProgressAttribute()
    {
        $total = $this->start_date->diffInDays($this->end_date);
        $elapsed = $this->start_date->diffInDays(now());

        if ($total <= 0) {
            return 0;
        }

        $progress = ($elapsed / $total) * 100;

        return min(100, max(0, $progress));
    }

    public function getAvailableSeatsAttribute()
    {
        return $this->capacity - $this->activeStudents()->count();
    }

    public function getIsFullAttribute()
    {
        return $this->available_seats <= 0;
    }

    public function getPerformanceStats()
    {
        $results = Result::where('batch_id', $this->id)->get();
        $totalStudents = $this->students()->count();
        $activeStudents = $results->pluck('student_id')->unique()->count();

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'participation_rate' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0,
            'average_score' => round($results->avg('percentage'), 2),
            'pass_rate' => $results->count() > 0
                ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                : 0,
        ];
    }
}
