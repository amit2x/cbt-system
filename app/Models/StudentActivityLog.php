<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentActivityLog extends Model
{
    protected $fillable = [
        'student_exam_id', 'student_id', 'exam_id', 'monitoring_session_id',
        'activity_type', 'activity_data', 'description', 'severity', 'occurred_at'
    ];

    protected $casts = [
        'activity_data' => 'array',
        'occurred_at' => 'datetime'
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

    // Scopes
    public function scopeWarnings($query)
    {
        return $query->where('severity', 'warning');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeRecent($query, $minutes = 5)
    {
        return $query->where('occurred_at', '>=', now()->subMinutes($minutes));
    }

    // Helper methods
    public function getFormattedDataAttribute()
    {
        switch ($this->activity_type) {
            case 'tab_switch':
                return "Switched tabs/count: " . ($this->activity_data['count'] ?? 1);
            case 'window_blur':
                return "Left exam window for " . ($this->activity_data['duration'] ?? 'unknown') . " seconds";
            case 'copy_attempt':
                return "Attempted to copy content";
            case 'devtools_open':
                return "Developer tools were opened";
            default:
                return $this->description ?? 'No additional data';
        }
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'info' => 'info',
            'warning' => 'warning',
            'critical' => 'danger',
            default => 'secondary'
        };
    }
}
