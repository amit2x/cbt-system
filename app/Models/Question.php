<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_text', 'explanation', 'type', 'difficulty',
        'marks', 'negative_marks', 'tags', 'subject_id', 'created_by', 'status'
    ];

    protected $casts = [
        'tags' => 'array',
        'marks' => 'integer',
        'negative_marks' => 'float'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (empty($question->created_by)) {
                $question->created_by = auth()->id();
            }
        });
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function correctOptions()
    {
        return $this->hasMany(QuestionOption::class)->where('is_correct', true);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subject()
    {
        return $this->belongsTo(Module::class, 'subject_id');
    }

    public function categories()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
                    ->withPivot(['marks', 'negative_marks', 'order'])
                    ->withTimestamps();
    }

    /**
     * Get correct options based on question type
     */
    public function getCorrectOptionsAttribute()
    {
        return $this->options()->where('is_correct', true)->get();
    }

    /**
     * Check if answer is correct for single choice
     */
    public function isCorrectSingle($optionId)
    {
        return $this->options()
                    ->where('id', $optionId)
                    ->where('is_correct', true)
                    ->exists();
    }

    /**
     * Check if answer is correct for multiple choice
     */
    public function isCorrectMultiple(array $optionIds)
    {
        $correctOptions = $this->options()
                               ->where('is_correct', true)
                               ->pluck('id')
                               ->sort()
                               ->values()
                               ->toArray();

        sort($optionIds);

        return $optionIds == $correctOptions;
    }

    /**
     * Check if answer is correct for numerical
     */
    public function isCorrectNumerical($answer, $tolerance = 0.001)
    {
        $correctOption = $this->options()->where('is_correct', true)->first();
        if (!$correctOption) {
            return false;
        }

        return abs(floatval($answer) - floatval($correctOption->option_text)) <= $tolerance;
    }

    /**
     * Get formatted question for display
     */
    public function getFormattedQuestionAttribute()
    {
        return nl2br(e($this->question_text));
    }

    /**
     * Get difficulty badge class
     */
    public function getDifficultyBadgeAttribute()
    {
        return match($this->difficulty) {
            'easy' => 'success',
            'medium' => 'warning',
            'hard' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeAttribute()
    {
        return match($this->type) {
            'single_choice' => 'info',
            'multiple_choice' => 'primary',
            'numerical' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get formatted type name
     */
    public function getTypeNameAttribute()
    {
        return match($this->type) {
            'single_choice' => 'Single Choice',
            'multiple_choice' => 'Multiple Choice',
            'numerical' => 'Numerical',
            default => $this->type
        };
    }

    /**
     * Get formatted difficulty name
     */
    public function getDifficultyNameAttribute()
    {
        return ucfirst($this->difficulty);
    }

    /**
     * Scope for active questions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for difficulty
     */
    public function scopeDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope for type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for subject
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('question_text', 'LIKE', "%{$search}%")
              ->orWhere('explanation', 'LIKE', "%{$search}%")
              ->orWhere('tags', 'LIKE', "%{$search}%");
        });
    }


    // Helper method to get correct options
    public function getCorrectOptions()
    {
        return $this->options()->where('is_correct', true)->get();
    }

}
