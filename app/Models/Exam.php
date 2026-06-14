<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'type', 'category',
        'course_id', 'batch_id',
        'duration_minutes', 'total_marks', 'passing_percentage',
        'negative_marking', 'randomize_questions', 'randomize_options',
        'show_result_immediately', 'allow_review', 'attempts_allowed',
        'start_time', 'end_time', 'is_scheduled',
        'status', 'instructions', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_result_immediately' => 'boolean',
        'allow_review' => 'boolean',
        'is_scheduled' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_marks' => 'integer',
        'duration_minutes' => 'integer',
        'negative_marking' => 'float',
        'passing_percentage' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exam) {
            $exam->slug = Str::slug($exam->title);
            if (empty($exam->created_by)) {
                $exam->created_by = auth()->id();
            }
        });

        static::updating(function ($exam) {
            if ($exam->isDirty('title')) {
                $exam->slug = Str::slug($exam->title);
            }
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'exam_batches')
            ->withPivot(['available_from', 'available_until'])
            ->withTimestamps();
    }

    /**
     * Get the sections for this exam
     */
    public function sections()
    {
        return $this->hasMany(ExamSection::class)->orderBy('order');
    }

    /**
     * Get the questions for this exam through the pivot table
     * Removed pivot_order reference - use 'order' instead
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['id', 'section_id', 'order', 'marks', 'negative_marks', 'time_limit_seconds'])
            ->withTimestamps()
            ->orderBy('order'); // Changed from 'pivot_order' to 'order'
    }

    /**
     * Get questions grouped by section
     */
    public function getQuestionsBySection()
    {
        return $this->questions()
            ->get()
            ->groupBy('pivot.section_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function studentExams()
    {
        return $this->hasMany(StudentExam::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true)
            ->where('start_time', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('is_scheduled', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('is_scheduled', true)
            ->where('end_time', '<', now());
    }

    public function scopeForBatch($query, $batchId)
    {
        return $query->where(function ($q) use ($batchId) {
            $q->where('batch_id', $batchId)
                ->orWhereHas('batches', function ($subQ) use ($batchId) {
                    $subQ->where('batches.id', $batchId);
                });
        });
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper Methods
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'published' => 'success',
            'in_progress' => 'warning',
            'completed' => 'info',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getTypeBadgeAttribute()
    {
        return match ($this->type) {
            'mock_test' => 'info',
            'full_exam' => 'primary',
            'quiz' => 'success',
            'practice' => 'warning',
            default => 'secondary'
        };
    }

    public function getTypeNameAttribute()
    {
        return match ($this->type) {
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice',
            default => ucfirst($this->type)
        };
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} minutes";
    }

    public function getQuestionCountAttribute()
    {
        return $this->questions()->count();
    }

    public function getTotalStudentsAttribute()
    {
        if ($this->batch_id) {
            return $this->batch->students()->count();
        }

        if ($this->course_id) {
            return $this->course->batches()
                ->withCount('students')
                ->get()
                ->sum('students_count');
        }

        return 0;
    }

    public function getAttemptedCountAttribute()
    {
        return $this->studentExams()->where('status', 'completed')->count();
    }

    public function getAverageScoreAttribute()
    {
        return $this->results()->avg('percentage') ?? 0;
    }

    public function getHighestScoreAttribute()
    {
        return $this->results()->max('percentage') ?? 0;
    }

    public function getLowestScoreAttribute()
    {
        return $this->results()->min('percentage') ?? 0;
    }

    public function getPassingCountAttribute()
    {
        return $this->results()
            ->where('percentage', '>=', $this->passing_percentage)
            ->count();
    }

    public function getFailingCountAttribute()
    {
        return $this->results()
            ->where('percentage', '<', $this->passing_percentage)
            ->count();
    }

    public function getIsAvailableAttribute()
    {
        if (! $this->is_scheduled) {
            return $this->status === 'published';
        }

        return now()->between($this->start_time, $this->end_time);
    }

    public function getTimeRemainingAttribute()
    {
        if (! $this->is_scheduled || $this->start_time > now()) {
            return null;
        }

        if ($this->end_time < now()) {
            return 'Expired';
        }

        return now()->diff($this->end_time)->format('%H:%I:%S');
    }

    /**
     * Update total marks for the exam
     */
    public function updateTotalMarks()
    {
        $total = $this->questions()->sum('marks') ?? 0;
        $this->update(['total_marks' => $total]);

        return $total;
    }

    public function canBePublished()
    {
        return $this->questions()->count() > 0;
    }

    public function canBeTakenBy($studentId)
    {
        // Check if student is in batch
        if ($this->batch_id) {
            $inBatch = \DB::table('batch_student')
                ->where('batch_id', $this->batch_id)
                ->where('student_id', $studentId)
                ->where('status', 'active')
                ->exists();

            if (! $inBatch) {
                return false;
            }
        }

        // Check attempt limit
        $attempts = $this->studentExams()
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->count();

        if ($attempts >= $this->attempts_allowed) {
            return false;
        }

        // Check availability
        return $this->is_available;
    }
}
