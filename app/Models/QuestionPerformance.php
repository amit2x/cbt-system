<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class QuestionPerformance extends BaseModel
{
    use HasAirport;
    protected $table = 'question_performance';

    protected $fillable = [
        'question_id', 'exam_id',
        'total_attempts', 'correct_attempts', 'incorrect_attempts', 'accuracy_rate',
        'avg_time_seconds', 'min_time_seconds', 'max_time_seconds',
        'difficulty_index', 'discrimination_index',
        'option_analysis', 'effectiveness_score', 'effectiveness_level'
    ];

    protected $casts = [
        'option_analysis' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Helper methods
    public function getFormattedAvgTimeAttribute()
    {
        $minutes = floor($this->avg_time_seconds / 60);
        $seconds = $this->avg_time_seconds % 60;
        return sprintf("%d:%02d", $minutes, $seconds);
    }

    public function getDifficultyLevelAttribute()
    {
        if ($this->difficulty_index >= 70) return 'hard';
        if ($this->difficulty_index >= 40) return 'medium';
        return 'easy';
    }

    public function getEffectivenessBadgeAttribute()
    {
        return match($this->effectiveness_level) {
            'good' => 'success',
            'average' => 'warning',
            'poor' => 'danger',
            default => 'secondary'
        };
    }

    public function getOptionDistributionAttribute()
    {
        if (!$this->option_analysis) return [];

        $total = $this->total_attempts;
        return collect($this->option_analysis)->map(function($count, $option) use ($total) {
            return [
                'option' => $option,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0
            ];
        })->values();
    }
    
    // ✅ Ensure option_analysis is always valid JSON
    public function setOptionAnalysisAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['option_analysis'] = null;
            return;
        }

        // If it's already a string, try to decode/encode
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $this->attributes['option_analysis'] = json_encode($decoded);
                return;
            }
            // If not valid JSON, wrap it
            $this->attributes['option_analysis'] = json_encode(['value' => $value]);
            return;
        }

        // If it's an array, encode it
        if (is_array($value)) {
            $this->attributes['option_analysis'] = json_encode($value);
            return;
        }

        // Fallback: convert to string and encode
        $this->attributes['option_analysis'] = json_encode(['value' => (string) $value]);
    }

    public function getOptionAnalysisAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
