<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    // protected $fillable = [
    //     'student_exam_id', 'student_id', 'exam_id', 'batch_id',
    //     'total_marks', 'obtained_marks', 'percentage', 'status',
    //     'total_questions', 'correct_answers', 'incorrect_answers', 'unanswered', 'marked_for_review',
    //     'time_taken_seconds', 'speed_per_minute',
    //     'rank', 'percentile', 'total_participants',
    //     'sectional_analysis', 'topic_analysis', 'difficulty_analysis', 'accuracy_by_type',
    //     'accuracy_percentage', 'speed_score', 'consistency_score',
    //     'certificate_url', 'certificate_generated_at'
    // ];
    
    
     protected $fillable = [
        'student_exam_id', 'student_id', 'exam_id', 'batch_id',
        'total_questions', 'total_marks', 'obtained_marks', 'percentage', 'status',
        'correct_answers', 'incorrect_answers', 'unanswered', 'marked_for_review',
        'time_taken_seconds', 'speed_per_minute',
        'rank', 'percentile', 'total_participants',
        'sectional_analysis', 'topic_analysis', 'difficulty_analysis', 'accuracy_by_type',
        'accuracy_percentage', 'speed_score', 'consistency_score',
        'certificate_url', 'certificate_generated_at'
    ];

    protected $casts = [
        'total_marks' => 'float',
        'obtained_marks' => 'float',
        'percentage' => 'float',
        'correct_answers' => 'integer',
        'incorrect_answers' => 'integer',
        'unanswered' => 'integer',
        'time_taken_seconds' => 'integer',
        'rank' => 'integer',
        'percentile' => 'float',
        'sectional_analysis' => 'array',
        'topic_analysis' => 'array',
        'difficulty_analysis' => 'array',
        'accuracy_by_type' => 'array',
        'accuracy_percentage' => 'float'
    ];


    

    // protected $fillable = [
    //     'student_exam_id', 'student_id', 'exam_id', 'batch_id',
    //     'total_questions', 'total_marks', 'obtained_marks', 'percentage', 'status',
    //     'correct_answers', 'incorrect_answers', 'unanswered', 'marked_for_review',
    //     'time_taken_seconds', 'speed_per_minute',
    //     'rank', 'percentile', 'total_participants',
    //     'sectional_analysis', 'topic_analysis', 'difficulty_analysis', 'accuracy_by_type',
    //     'accuracy_percentage', 'speed_score', 'consistency_score',
    //     'certificate_url', 'certificate_generated_at',
    // ];

    // protected $casts = [
    //     'total_marks' => 'float',
    //     'obtained_marks' => 'float',
    //     'percentage' => 'float',
    //     'speed_per_minute' => 'float',
    //     'accuracy_percentage' => 'float',
    //     'speed_score' => 'float',
    //     'consistency_score' => 'float',
    //     'percentile' => 'float',
    //     'sectional_analysis' => 'array',
    //     'topic_analysis' => 'array',
    //     'difficulty_analysis' => 'array',
    //     'accuracy_by_type' => 'array',
    //     'certificate_generated_at' => 'datetime',
    // ];

    protected $appends = [
        'formatted_time',
        'performance_level',
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

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

    public function sections()
    {
        return $this->hasMany(ResultSection::class);
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // Accessors

    
    /**
 * Get formatted time taken
 */
public function getFormattedTimeAttribute()
{
    $seconds = $this->time_taken_seconds ?? 0;
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
    }
    return sprintf("%02d:%02d", $minutes, $secs);
}

    public function getPerformanceLevelAttribute()
    {
        if ($this->percentage >= 90) {
            return 'Excellent';
        }
        if ($this->percentage >= 75) {
            return 'Very Good';
        }
        if ($this->percentage >= 60) {
            return 'Good';
        }
        if ($this->percentage >= 40) {
            return 'Average';
        }

        return 'Needs Improvement';
    }

    public function getAccuracyAttribute()
    {
        if ($this->total_questions == 0) {
            return 0;
        }

        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    // Helper methods
    public function generateRank()
    {
        $betterResults = self::where('exam_id', $this->exam_id)
            ->where('obtained_marks', '>', $this->obtained_marks)
            ->count();

        $totalParticipants = self::where('exam_id', $this->exam_id)->count();

        $rank = $betterResults + 1;
        $percentile = $totalParticipants > 0
            ? round((($totalParticipants - $rank + 1) / $totalParticipants) * 100, 2)
            : 0;

        $this->update([
            'rank' => $rank,
            'percentile' => $percentile,
            'total_participants' => $totalParticipants,
        ]);

        return $rank;
    }

    public function generateCertificate()
    {
        if ($this->status !== 'passed') {
            return null;
        }

        // Certificate generation logic will go here
        // This could use a package like barryvdh/laravel-dompdf

        $this->update([
            'certificate_generated_at' => now(),
        ]);

        return $this->certificate_url;
    }

    public function getTopicWiseAnalysis()
    {
        $answers = $this->studentExam->answers()->with('question')->get();

        $topics = [];
        foreach ($answers as $answer) {
            if ($answer->question->tags) {
                foreach ($answer->question->tags as $tag) {
                    if (! isset($topics[$tag])) {
                        $topics[$tag] = [
                            'total' => 0,
                            'correct' => 0,
                            'incorrect' => 0,
                            'unanswered' => 0,
                            'time_spent' => 0,
                        ];
                    }

                    $topics[$tag]['total']++;
                    if ($answer->is_correct) {
                        $topics[$tag]['correct']++;
                    } elseif ($answer->selected_options || $answer->answer_text) {
                        $topics[$tag]['incorrect']++;
                    } else {
                        $topics[$tag]['unanswered']++;
                    }
                    $topics[$tag]['time_spent'] += $answer->time_taken_seconds ?? 0;
                }
            }
        }

        return $topics;
    }

    public function getDifficultyWiseAnalysis()
    {
        $answers = $this->studentExam->answers()->with('question')->get();

        $difficulties = [
            'easy' => ['total' => 0, 'correct' => 0, 'incorrect' => 0],
            'medium' => ['total' => 0, 'correct' => 0, 'incorrect' => 0],
            'hard' => ['total' => 0, 'correct' => 0, 'incorrect' => 0],
        ];

        foreach ($answers as $answer) {
            $difficulty = $answer->question->difficulty;
            $difficulties[$difficulty]['total']++;

            if ($answer->is_correct) {
                $difficulties[$difficulty]['correct']++;
            } elseif ($answer->selected_options || $answer->answer_text) {
                $difficulties[$difficulty]['incorrect']++;
            }
        }

        return $difficulties;
    }
}
