<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Feedback extends Model
{
    protected $fillable = [
        'instructor_id', 'batch_id', 'course_id', 'code',
        'type', 'questions', 'status', 'expires_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feedback) {
            if (empty($feedback->code)) {
                $feedback->code = strtoupper(Str::random(6));
            }
        });
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function responses()
    {
        return $this->hasMany(FeedbackResponse::class);
    }

    /**
     * Check if feedback is active
     */
    public function isActive()
    {
        return $this->status === 'active' &&
               (! $this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Get response count
     */
    public function getResponseCountAttribute()
    {
        return $this->responses()->count();
    }

    /**
     * Check if a student has already responded
     */
    public function hasResponded($studentId)
    {
        return $this->responses()->where('student_id', $studentId)->exists();
    }

    /**
     * Scope for active feedback
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for a specific batch
     */
    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope for pending student feedback
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->whereDoesntHave('responses', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
    }
}
