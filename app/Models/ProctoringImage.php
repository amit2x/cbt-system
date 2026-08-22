<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasAirport;

class ProctoringImage extends BaseModel
{
    use HasAirport;
    protected $fillable = [
        'airport_id',
        'student_exam_id',
        'student_id',
        'exam_id',
        'monitoring_session_id',
        'image_path',
        'thumbnail_path',
        'image_size',
        'metadata',
        'capture_type',
        'is_suspicious',
        'suspicious_reason',
        'reviewed',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_suspicious' => 'boolean',
        'reviewed' => 'boolean',
        'image_size' => 'integer',
        'reviewed_at' => 'datetime'
    ];

    // ==================== RELATIONSHIPS ====================

    public function studentExam(): BelongsTo
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function monitoringSession(): BelongsTo
    {
        return $this->belongsTo(LiveMonitoringSession::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== SCOPES ====================

    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeNotSuspicious($query)
    {
        return $query->where('is_suspicious', false);
    }

    public function scopeReviewed($query)
    {
        return $query->where('reviewed', true);
    }

    public function scopeNotReviewed($query)
    {
        return $query->where('reviewed', false);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('capture_type', 'automatic');
    }

    public function scopeManual($query)
    {
        return $query->where('capture_type', 'manual');
    }

    // ==================== ACCESSORS ====================

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    public function getFormattedSizeAttribute()
    {
        $size = $this->image_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getSuspiciousStatusAttribute()
    {
        if (!$this->is_suspicious) {
            return ['label' => 'Normal', 'color' => 'success', 'icon' => 'bi-check-circle'];
        }
        return ['label' => 'Suspicious', 'color' => 'danger', 'icon' => 'bi-exclamation-triangle'];
    }

    public function getReviewStatusAttribute()
    {
        if ($this->reviewed) {
            return ['label' => 'Reviewed', 'color' => 'info', 'icon' => 'bi-check-circle'];
        }
        return ['label' => 'Pending', 'color' => 'warning', 'icon' => 'bi-clock'];
    }

    // ==================== HELPER METHODS ====================

    public function markAsSuspicious($reason = null)
    {
        $this->update([
            'is_suspicious' => true,
            'suspicious_reason' => $reason
        ]);
    }

    public function markAsReviewed($reviewerId = null)
    {
        $this->update([
            'reviewed' => true,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now()
        ]);
    }

    public function isSuspicious()
    {
        return $this->is_suspicious;
    }

    public function isReviewed()
    {
        return $this->reviewed;
    }
}