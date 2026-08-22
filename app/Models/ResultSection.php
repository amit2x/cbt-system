<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class ResultSection extends BaseModel
{
    use HasAirport;
    protected $fillable = [
        'result_id', 'section_id', 'section_name',
        'total_questions', 'correct', 'incorrect', 'unanswered',
        'total_marks', 'obtained_marks', 'percentage',
        'time_spent_seconds', 'time_percentage', 'accuracy'
    ];

    protected $casts = [
        'percentage' => 'float',
        'accuracy' => 'float',
        'time_percentage' => 'float'
    ];

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function section()
    {
        return $this->belongsTo(ExamSection::class, 'section_id');
    }

    public function getFormattedTimeAttribute()
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        return sprintf("%d:%02d", $minutes, $seconds);
    }
}
