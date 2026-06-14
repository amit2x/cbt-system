<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_instructors', 'user_id', 'course_id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'student_id');
    }

    // Relationships for instructor
    public function teachingCourses()
    {
        return $this->belongsToMany(Course::class, 'course_instructor', 'user_id', 'course_id')->withTimestamps();
    }

    public function teachingBatches()
    {
        return $this->hasMany(Batch::class, 'instructor_id');
    }

    // Relationships for student
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_student', 'student_id', 'batch_id')
            ->withPivot('status', 'enrolled_at')
            ->withTimestamps();
    }

    public function activeBatches()
    {
        return $this->batches()->wherePivot('status', 'active');
    }

    public function exams()
    {
        return $this->hasMany(StudentExam::class, 'student_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'student_id');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isInstructor()
    {
        return $this->hasRole('instructor');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($this->name);
    }
}
