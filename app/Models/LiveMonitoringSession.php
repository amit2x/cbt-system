<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAirport;

class LiveMonitoringSession extends BaseModel
{
    use HasAirport;
    protected $fillable = [
        'airport_id',
        'exam_id',
        'instructor_id',
        'batch_id',
        'session_name',
        'started_at',
        'ended_at',
        'status',
        'track_tab_switches',
        'track_mouse_events',
        'enable_proctoring',
        'screenshot_interval',
        'alert_settings',
        'total_students',
        'active_students',
        'warning_count',
        'terminated_count'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'track_tab_switches' => 'boolean',
        'track_mouse_events' => 'boolean',
        'enable_proctoring' => 'boolean',
        'alert_settings' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function proctoringImages(): HasMany
    {
        return $this->hasMany(ProctoringImage::class);
    }

    public function studentActivities(): HasMany
    {
        return $this->hasMany(StudentActivityLog::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    // ==================== ACCESSORS ====================

    public function getDurationAttribute()
    {
        if (!$this->started_at) {
            return 0;
        }
        $end = $this->ended_at ?? now();
        return $this->started_at->diffInSeconds($end);
    }

    public function getFormattedDurationAttribute()
    {
        $seconds = $this->duration;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    public function getParticipationRateAttribute()
    {
        if ($this->total_students == 0) {
            return 0;
        }
        return round(($this->active_students / $this->total_students) * 100, 2);
    }

    // ==================== HELPER METHODS ====================

    public function start()
    {
        $this->update([
            'status' => 'active',
            'started_at' => now()
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

    public function end()
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now()
        ]);
    }

    public function getStudentStats()
    {
        return [
            'total' => $this->total_students,
            'active' => $this->active_students,
            'warning_count' => $this->warning_count,
            'terminated_count' => $this->terminated_count,
            'participation_rate' => $this->participation_rate
        ];
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function shouldCaptureScreenshot()
    {
        if (!$this->enable_proctoring || !$this->isActive()) {
            return false;
        }
        return true;
    }
}