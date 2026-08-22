<?php

namespace App\Models;

use App\Traits\AirportAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasAirport;
use Illuminate\Support\Facades\App;

class Question extends BaseModel
{
    use SoftDeletes, AirportAware, HasAirport;

    protected $fillable = [
        'airport_id',
        'question_text',
        'question_text_hi',
        'question_image',
        'question_image_alt',
        'question_image_alt_hi',     
        'explanation_image',        
        'explanation_image_alt',     
        'explanation_image_alt_hi', 
        'explanation',
        'explanation_hi',
        'type',
        'difficulty',
        'marks',
        'negative_marks',
        'tags',
        'subject_id',
        'created_by',
        'status'
    ];

    protected $casts = [
        'tags' => 'array',
        'marks' => 'integer',
        'negative_marks' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Boot method to auto-assign created_by
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($question) {
            if (empty($question->created_by)) {
                $question->created_by = auth()->id();
            }
        });

        // Delete images when question is deleted
        static::deleting(function ($question) {
            if ($question->isForceDeleting()) {
                $question->deleteImages();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the options for this question
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    /**
     * Get only correct options
     */
    public function correctOptions()
    {
        return $this->hasMany(QuestionOption::class)->where('is_correct', true)->orderBy('order');
    }

    /**
     * Get the user who created this question
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the subject/module for this question
     */
    public function subject()
    {
        return $this->belongsTo(Module::class, 'subject_id');
    }

    /**
     * Get the categories for this question
     */
    public function categories()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category')
                    ->withTimestamps();
    }

    /**
     * Get the exams that include this question
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
                    ->withPivot(['section_id', 'marks', 'negative_marks', 'order', 'time_limit_seconds'])
                    ->withTimestamps();
    }

    /**
     * Get the sections where this question appears
     */
    public function sections()
    {
        return $this->belongsToMany(ExamSection::class, 'exam_questions', 'question_id', 'section_id')
                    ->withPivot(['exam_id', 'marks', 'negative_marks', 'order'])
                    ->withTimestamps();
    }

    /**
     * Get the student answers for this question
     */
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    /**
     * Get the performance analytics for this question
     */
    public function performance()
    {
        return $this->hasOne(QuestionPerformance::class);
    }

    /**
     * Get the airport for this question
     */
    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the question display with image
     */
    public function getQuestionDisplayAttribute()
    {
        $display = $this->question_text;
        if ($this->question_image) {
            $display .= '<br><img src="' . asset('storage/' . $this->question_image) . '" alt="' . ($this->question_image_alt ?? 'Question image') . '" class="img-fluid mt-2" style="max-height: 200px;">';
        }
        return $display;
    }

    /**
     * Get the question image URL
     */
    public function getQuestionImageUrlAttribute()
    {
        return $this->question_image ? asset('storage/' . $this->question_image) : null;
    }

    /**
     * Get the formatted question text
     */
    public function getFormattedQuestionAttribute()
    {
        return nl2br(e($this->question_text));
    }

    /**
     * Get the difficulty badge class
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
     * Get the type badge class
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
     * Get the formatted type name
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
     * Get the formatted difficulty name
     */
    public function getDifficultyNameAttribute()
    {
        return ucfirst($this->difficulty);
    }

    /**
     * Get the status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary'
        ];

        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get the correct answer text
     */
    public function getCorrectAnswerTextAttribute()
    {
        if ($this->type === 'numerical') {
            $correctOption = $this->options()->where('is_correct', true)->first();
            return $correctOption ? $correctOption->option_text : 'N/A';
        }
        
        $correctOptions = $this->correctOptions;
        if ($correctOptions->count() === 1) {
            return $correctOptions->first()->option_text;
        }
        
        return $correctOptions->pluck('option_text')->implode(', ');
    }

    /**
     * Get the correct options count
     */
    public function getCorrectOptionsCountAttribute()
    {
        return $this->correctOptions()->count();
    }

    /**
     * Get if question has image
     */
    public function getHasImageAttribute()
    {
        return !empty($this->question_image);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active questions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive questions
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for specific difficulty
     */
    public function scopeDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific subject
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

    /**
     * Scope for questions with images
     */
    public function scopeWithImages($query)
    {
        return $query->whereNotNull('question_image');
    }

    /**
     * Scope for questions without images
     */
    public function scopeWithoutImages($query)
    {
        return $query->whereNull('question_image');
    }

    /**
     * Scope for single choice questions
     */
    public function scopeSingleChoice($query)
    {
        return $query->where('type', 'single_choice');
    }

    /**
     * Scope for multiple choice questions
     */
    public function scopeMultipleChoice($query)
    {
        return $query->where('type', 'multiple_choice');
    }

    /**
     * Scope for numerical questions
     */
    public function scopeNumerical($query)
    {
        return $query->where('type', 'numerical');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get correct options
     */
    public function getCorrectOptions()
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
     * Get the option letter (A, B, C, etc.)
     */
    public function getOptionLetter($index)
    {
        return chr(65 + $index);
    }

    /**
     * Delete associated images
     */
    public function deleteImages()
    {
        if ($this->question_image) {
            Storage::disk('public')->delete($this->question_image);
        }
        
        // Delete option images
        foreach ($this->options as $option) {
            if ($option->option_image) {
                Storage::disk('public')->delete($option->option_image);
            }
        }
    }

    /**
     * Get total options count
     */
    public function getTotalOptionsCount()
    {
        return $this->options()->count();
    }

    /**
     * Check if question has options
     */
    public function hasOptions()
    {
        return $this->options()->exists();
    }

    /**
     * Get question statistics
     */
    public function getStatistics()
    {
        return [
            'total_attempts' => $this->studentAnswers()->count(),
            'correct_attempts' => $this->studentAnswers()->where('is_correct', true)->count(),
            'incorrect_attempts' => $this->studentAnswers()->where('is_correct', false)->count(),
            'accuracy' => $this->getAccuracy(),
            'avg_time' => $this->studentAnswers()->avg('time_taken_seconds') ?? 0,
        ];
    }

    /**
     * Get accuracy percentage
     */
    public function getAccuracy()
    {
        $total = $this->studentAnswers()->count();
        if ($total === 0) {
            return 0;
        }
        
        $correct = $this->studentAnswers()->where('is_correct', true)->count();
        return round(($correct / $total) * 100, 2);
    }

    /**
     * Check if question is valid
     */
    public function isValid()
    {
        if ($this->type === 'single_choice' || $this->type === 'multiple_choice') {
            return $this->options()->count() >= 2 && $this->correctOptions()->exists();
        }
        
        if ($this->type === 'numerical') {
            return $this->options()->where('is_correct', true)->exists();
        }
        
        return false;
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors()
    {
        $errors = [];
        
        if ($this->type === 'single_choice' || $this->type === 'multiple_choice') {
            if ($this->options()->count() < 2) {
                $errors[] = 'Question must have at least 2 options';
            }
            if (!$this->correctOptions()->exists()) {
                $errors[] = 'Question must have at least one correct option';
            }
        }
        
        if ($this->type === 'numerical') {
            if (!$this->options()->where('is_correct', true)->exists()) {
                $errors[] = 'Numerical question must have a correct answer';
            }
        }
        
        return $errors;
    }
    
    
    
    
    // ==================== ACCESSORS FOR BILINGUAL CONTENT ====================

    /**
     * Get question text based on current locale
     */
    public function getQuestionTextAttribute($value)
    {
        $locale = App::getLocale();
        if ($locale === 'hi' && !empty($this->attributes['question_text_hi'])) {
            return $this->attributes['question_text_hi'];
        }
        return $value;
    }

    /**
     * Get explanation based on current locale
     */
    public function getExplanationAttribute($value)
    {
        $locale = App::getLocale();
        if ($locale === 'hi' && !empty($this->attributes['explanation_hi'])) {
            return $this->attributes['explanation_hi'];
        }
        return $value;
    }

    /**
     * Get question image alt text based on current locale
     */
    public function getQuestionImageAltAttribute($value)
    {
        $locale = App::getLocale();
        if ($locale === 'hi' && !empty($this->attributes['question_image_alt_hi'])) {
            return $this->attributes['question_image_alt_hi'];
        }
        return $value;
    }

    /**
     * Get explanation image alt text based on current locale
     */
    public function getExplanationImageAltAttribute($value)
    {
        $locale = App::getLocale();
        if ($locale === 'hi' && !empty($this->attributes['explanation_image_alt_hi'])) {
            return $this->attributes['explanation_image_alt_hi'];
        }
        return $value;
    }

    // ==================== FORMATTED DISPLAY WITH IMAGES ====================

    /**
     * Get full question display with images
     */
    // public function getQuestionDisplayAttribute()
    // {
    //     $display = '';
        
    //     // Question text
    //     if ($this->question_text) {
    //         $display .= $this->question_text;
    //     }
        
    //     // Question image
    //     if ($this->question_image) {
    //         $display .= '<br><img src="' . asset('storage/' . $this->question_image) . 
    //                     '" alt="' . $this->question_image_alt . '" class="img-fluid mt-2" style="max-height: 200px;">';
    //     }
        
    //     return $display;
    // }

    /**
     * Get full explanation display with images
     */
    public function getExplanationDisplayAttribute()
    {
        $display = '';
        
        // Explanation text
        if ($this->explanation) {
            $display .= $this->explanation;
        }
        
        // Explanation image
        if ($this->explanation_image) {
            $display .= '<br><img src="' . asset('storage/' . $this->explanation_image) . 
                        '" alt="' . $this->explanation_image_alt . '" class="img-fluid mt-2" style="max-height: 200px;">';
        }
        
        return $display;
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if question has explanation image
     */
    public function hasExplanationImage()
    {
        return !empty($this->explanation_image);
    }

    /**
     * Get explanation image URL
     */
    public function getExplanationImageUrlAttribute()
    {
        return $this->explanation_image ? asset('storage/' . $this->explanation_image) : null;
    }

    /**
     * Check if Hindi content is available
     */
    public function hasHindiContent()
    {
        return !empty($this->question_text_hi) || !empty($this->explanation_hi);
    }

    /**
     * Get all available languages for this question
     */
    public function getAvailableLanguages()
    {
        $languages = ['en' => 'English'];
        if ($this->hasHindiContent()) {
            $languages['hi'] = 'Hindi';
        }
        return $languages;
    }
}