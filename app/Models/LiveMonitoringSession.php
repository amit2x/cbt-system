<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveMonitoringSession extends Model
{
    protected $fillable = [
        'exam_id', 'instructor_id', 'batch_id', 'session_name',
        'started_at', 'ended_at', 'status',
        'track_tab_switches', 'track_mouse_events', 'enable_proctoring',
        'screenshot_interval', 'alert_settings',
        'total_students', 'active_students', 'warning_count', 'terminated_count'
    ];

    protected $casts = [
        'alert_settings' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'track_tab_switches' => 'boolean',
        'track_mouse_events' => 'boolean',
        'enable_proctoring' => 'boolean',
        'screenshot_interval' => 'integer'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(StudentActivityLog::class, 'monitoring_session_id');
    }

    public function proctoringImages()
    {
        return $this->hasMany(ProctoringImage::class, 'monitoring_session_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function end()
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now()
        ]);
    }

    public function pause()
    {
        $this->update(['status' => 'paused']);
    }

    public function resume()
    {
        $this->update(['status' => 'active']);
    }

    public function getDurationAttribute()
    {
        $end = $this->ended_at ?? now();
        return $end->diffInMinutes($this->started_at);
    }

    public function getActiveStudentsList()
    {
        return StudentExam::where('exam_id', $this->exam_id)
            ->where('batch_id', $this->batch_id)
            ->whereIn('status', ['started', 'in_progress'])
            ->with('student')
            ->get();
    }

    public function getWarningStats()
    {
        return [
            'tab_switches' => $this->activityLogs()
                ->where('activity_type', 'tab_switch')
                ->count(),
            'copy_attempts' => $this->activityLogs()
                ->where('activity_type', 'copy_attempt')
                ->count(),
            'window_blurs' => $this->activityLogs()
                ->where('activity_type', 'window_blur')
                ->count(),
            'critical_alerts' => $this->activityLogs()
                ->where('severity', 'critical')
                ->count()
        ];
    }
}
