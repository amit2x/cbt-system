<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctoringImage extends Model
{
    protected $fillable = [
        'student_exam_id', 'student_id', 'exam_id', 'monitoring_session_id',
        'image_path', 'thumbnail_path', 'image_size', 'metadata',
        'capture_type', 'is_suspicious', 'suspicious_reason',
        'reviewed', 'reviewed_by', 'reviewed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_suspicious' => 'boolean',
        'reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
        'image_size' => 'integer'
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function monitoringSession()
    {
        return $this->belongsTo(LiveMonitoringSession::class, 'monitoring_session_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeUnreviewed($query)
    {
        return $query->where('reviewed', false);
    }

    public function scopeReviewed($query)
    {
        return $query->where('reviewed', true);
    }

    // Helper methods
    public function markAsSuspicious($reason = null)
    {
        $this->update([
            'is_suspicious' => true,
            'suspicious_reason' => $reason
        ]);
    }

    public function markAsReviewed($reviewerId)
    {
        $this->update([
            'reviewed' => true,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now()
        ]);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : $this->image_url;
    }
}
