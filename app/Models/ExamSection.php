<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSection extends Model
{
    protected $fillable = [
        'exam_id', 'title', 'description', 'order',
        'duration_minutes', 'question_count', 'total_marks',
        'negative_marking', 'randomize_questions', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'randomize_questions' => 'boolean',
    ];

    protected $appends = ['questions_count'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the questions for this section - removed pivot_order
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions', 'section_id', 'question_id')
            ->withPivot(['order', 'marks', 'negative_marks', 'time_limit_seconds'])
            ->withTimestamps()
            ->orderBy('order');
    }

    /**
     * Get the count of questions in this section
     */
    public function getQuestionsCountAttribute()
    {
        return $this->questions()->count();
    }

    /**
     * Get the count of questions in this section (for eager loading)
     */
    public function questionsCount()
    {
        return $this->belongsToMany(Question::class, 'exam_questions', 'section_id', 'question_id')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('section_id');
    }

    /**
     * Boot the model to set up event hooks
     */
    protected static function boot()
    {
        parent::boot();

        // When a section is deleted, update related exam_questions
        static::deleting(function ($section) {
            \DB::table('exam_questions')
                ->where('section_id', $section->id)
                ->update(['section_id' => null]);
        });
    }

    public function getFormattedDurationAttribute()
    {
        if (! $this->duration_minutes) {
            return 'Use exam duration';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} minutes";
    }

    public function updateTotalMarks()
    {
        $total = $this->questions()->sum('marks') ?? 0;
        $this->update([
            'total_marks' => $total,
            'question_count' => $this->questions()->count(),
        ]);

        return $total;
    }
}
