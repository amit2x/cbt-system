<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExam extends Model
{
    protected $fillable = [
        'student_id', 'exam_id', 'batch_id',
        'started_at', 'submitted_at', 'remaining_seconds', 'total_seconds_taken',
        'question_order', 'answers_state', 'marked_for_review',
        'status', 'ip_address', 'user_agent', 'tab_switch_log', 'tab_switch_count', 'is_proctored',
        'total_questions', 'answered_questions', 'marked_for_review_count', 'not_visited_count',
        'obtained_marks', 'percentage', 'correct_answers', 'incorrect_answers', 'unanswered',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'question_order' => 'array',
        'answers_state' => 'array',
        'marked_for_review' => 'array',
        'tab_switch_log' => 'array',
        'is_proctored' => 'boolean',
        'remaining_seconds' => 'integer',
        'total_seconds_taken' => 'integer',
        'total_questions' => 'integer',
        'answered_questions' => 'integer',
        'marked_for_review_count' => 'integer',
        'not_visited_count' => 'integer',
        'obtained_marks' => 'float',
        'percentage' => 'float',
        'correct_answers' => 'integer',
        'incorrect_answers' => 'integer',
        'unanswered' => 'integer',
    ];

    protected $appends = [
        'formatted_time_remaining',
        'progress_percentage',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function logs()
    {
        return $this->hasMany(ExamLog::class);
    }

    public function result()
    {
        return $this->hasOne(Result::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['started', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Helper Methods
    public function getFormattedTimeRemainingAttribute()
    {
        $seconds = $this->remaining_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_questions == 0) {
            return 0;
        }

        return round(($this->answered_questions / $this->total_questions) * 100, 2);
    }

    public function isActive()
    {
        return in_array($this->status, ['started', 'in_progress']);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function getQuestionIndex($questionId)
    {
        if (! $this->question_order) {
            return null;
        }

        return array_search($questionId, $this->question_order);
    }

    public function getCurrentQuestion()
    {
        if (! $this->question_order) {
            return null;
        }

        // Get first unanswered question or first question
        foreach ($this->question_order as $questionId) {
            $answer = $this->answers()->where('question_id', $questionId)->first();
            if (! $answer || ! $answer->is_visited) {
                return $questionId;
            }
        }

        return $this->question_order[0] ?? null;
    }

    public function updateState()
    {
        $answers = $this->answers;

        $answered = $answers->whereNotNull('selected_options')->count();
        $markedForReview = $answers->where('is_marked_for_review', true)->count();
        $visited = $answers->where('is_visited', true)->count();

        $this->update([
            'answers_state' => [
                'answered' => $answered,
                'marked_for_review' => $markedForReview,
                'visited' => $visited,
                'not_visited' => $this->total_questions - $visited,
                'not_answered' => $this->total_questions - $answered,
            ],
            'answered_questions' => $answered,
            'marked_for_review_count' => $markedForReview,
            'not_visited_count' => $this->total_questions - $visited,
        ]);
    }

    public function log($type, $message, $metadata = null)
    {
        return $this->logs()->create([
            'student_id' => $this->student_id,
            'exam_id' => $this->exam_id,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function recordTabSwitch()
    {
        $this->increment('tab_switch_count');

        $log = $this->tab_switch_log ?? [];
        $log[] = [
            'time' => now()->toDateTimeString(),
            'count' => $this->tab_switch_count,
        ];

        $this->update(['tab_switch_log' => $log]);

        $this->log('tab_switch', 'Student switched tab', [
            'switch_count' => $this->tab_switch_count,
        ]);
    }
}
