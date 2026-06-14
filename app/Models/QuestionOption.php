<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id', 'option_text', 'is_correct', 'order'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get formatted option text
     */
    public function getFormattedOptionAttribute()
    {
        return nl2br(e($this->option_text));
    }

    /**
     * Get option letter (A, B, C, etc.)
     */
    public function getLetterAttribute()
    {
        return chr(65 + $this->order);
    }
}
