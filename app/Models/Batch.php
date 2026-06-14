<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'course_id', 'instructor_id', 'start_date',
        'end_date', 'capacity', 'description', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'batch_student', 'batch_id', 'student_id')
                    ->withPivot('status', 'enrolled_at')
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

        if ($total <= 0) return 0;

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
}
