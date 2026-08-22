<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAirport;


class ExamQuestion extends BaseModel
{
    use HasAirport;
    
    protected $table = 'exam_questions';

    protected $fillable = [
        'exam_id',
        'section_id',
        'question_id',
        'order',
        'marks',
        'negative_marks',
        'time_limit_seconds',
    ];

    /**
     * Exam relationship
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Question relationship
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Section relationship
     */
    public function section()
    {
        return $this->belongsTo(ExamSection::class, 'section_id');
    }
}