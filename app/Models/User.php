<?php

namespace App\Models;

use App\Traits\AirportAware;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
// use App\Traits\HasAirport; //Dont add in this model cause recurssion
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;


class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, AirportAware, LogsActivity ,AuthenticationLoggable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 
    'organization_name','address', 'preferred_locale','roll_no',
    'avatar', 'is_active', 'airport_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];


// ==================== LANGUAGE HELPERS ====================

    /**
     * Get user's preferred locale
     */
    public function getPreferredLocale()
    {
        return $this->preferred_locale ?? 'en';
    }

    /**
     * Set user's preferred locale
     */
    public function setPreferredLocale($locale)
    {
        $this->update(['preferred_locale' => $locale]);
    }

    /**
     * Check if user prefers Hindi
     */
    public function prefersHindi()
    {
        return $this->preferred_locale === 'hi';
    }

/**
     * Get the activity log options.
     * ✅ For v5.0.0, this method is required
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            // ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $userName = Auth::user()?->name ?? 'System';
                return match($eventName) {
                    'created' => "{$userName} created user: {$this->name} ({$this->email})",
                    'updated' => "{$userName} updated user: {$this->name} ({$this->email})",
                    'deleted' => "{$userName} deleted user: {$this->name} ({$this->email})",
                    'restored' => "{$userName} restored user: {$this->name} ({$this->email})",
                    default => "{$userName} performed {$eventName} on user: {$this->name}",
                };
            })
            ->useLogName('user')
            ->logOnly(['name', 'email', 'phone', 'is_active', 'airport_id', 'preferred_locale'])
            ->logExcept(['updated_at', 'created_at', 'deleted_at', 'password', 'remember_token']);
    }
protected static function booted()
{
    static::creating(function ($user) {

        if (
            auth()->check() &&
            empty($user->airport_id)
        ) {
            $user->airport_id = auth()->user()->airport_id;
        }

    });
}
    
    public function scopeCurrentAirport($query)
    {
        if (!auth()->check()) {
            return $query;
        }
    
        if (auth()->user()->hasRole('Super Admin')) {
            return $query;
        }
    
        return $query->where('airport_id', auth()->user()->airport_id);
    }

    
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
    
    /**
     * Get the user's notifications
     */
    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications for the user
     */
    public function unreadNotifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')
                    ->whereNull('read_at')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notification count
     */
    public function unreadNotificationCount()
    {
        return $this->unreadNotifications()->count();
    }
    

    public function getExamStatistics()
    {
        $results = $this->results;
        
        return [
            'total_exams' => $results->count(),
            'average_score' => round($results->avg('percentage'), 2),
            'pass_rate' => $results->count() > 0 
                ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                : 0,
            'accuracy' => round($results->avg('accuracy_percentage'), 2),
            'total_passed' => $results->where('status', 'passed')->count(),
            'total_failed' => $results->where('status', 'failed')->count(),
        ];
    }

}
