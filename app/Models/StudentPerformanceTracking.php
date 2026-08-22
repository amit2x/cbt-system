<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class StudentPerformanceTracking extends BaseModel
{
    use HasAirport;
    protected $table = 'student_performance_tracking';

    protected $fillable = [
        'student_id', 'exam_id', 'batch_id',
        'accuracy_trend', 'speed_trend', 'consistency_score', 'improvement_rate',
        'strengths', 'weaknesses',
        'total_time_spent', 'exams_attempted', 'exams_passed', 'pass_rate',
        'predicted_score', 'prediction_confidence',
        'calculated_at'
    ];

    protected $casts = [
        'strengths' => 'array',
        'weaknesses' => 'array',
        'calculated_at' => 'datetime'
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

    // Helper methods
    public function getPerformanceLevelAttribute()
    {
        if ($this->accuracy_trend >= 80) return 'excellent';
        if ($this->accuracy_trend >= 60) return 'good';
        if ($this->accuracy_trend >= 40) return 'average';
        return 'needs_improvement';
    }

    public function getStrengthTopicsAttribute()
    {
        if (!$this->strengths) return [];
        return collect($this->strengths)->map(function($item) {
            return [
                'topic' => $item['topic'],
                'accuracy' => $item['accuracy'] . '%'
            ];
        });
    }

    public function getWeaknessTopicsAttribute()
    {
        if (!$this->weaknesses) return [];
        return collect($this->weaknesses)->map(function($item) {
            return [
                'topic' => $item['topic'],
                'accuracy' => $item['accuracy'] . '%'
            ];
        });
    }
}
