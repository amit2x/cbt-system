<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class RankList extends BaseModel
{
    use HasAirport;
    protected $fillable = [
        'exam_id', 'batch_id', 'title', 'generated_date',
        'rank_data', 'total_students', 'average_score',
        'highest_score', 'lowest_score', 'median_score',
        'score_distribution', 'percentiles',
        'generated_by', 'is_published', 'published_at'
    ];

    protected $casts = [
        'rank_data' => 'array',
        'score_distribution' => 'array',
        'percentiles' => 'array',
        'generated_date' => 'date',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'average_score' => 'float',
        'highest_score' => 'float',
        'lowest_score' => 'float',
        'median_score' => 'float'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getRankForStudent($studentId)
    {
        foreach ($this->rank_data as $item) {
            if ($item['student_id'] == $studentId) {
                return $item;
            }
        }
        return null;
    }

    public function getTopRankers($limit = 10)
    {
        return array_slice($this->rank_data, 0, $limit);
    }

    public function publish()
    {
        $this->update([
            'is_published' => true,
            'published_at' => now()
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'is_published' => false,
            'published_at' => null
        ]);
    }

    public static function generateForExam(Exam $exam, $batchId = null, $generatedBy)
    {
        $query = Result::where('exam_id', $exam->id);

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $results = $query->orderBy('obtained_marks', 'desc')->get();

        if ($results->isEmpty()) {
            return null;
        }

        $rankData = [];
        foreach ($results as $index => $result) {
            $rankData[] = [
                'rank' => $index + 1,
                'student_id' => $result->student_id,
                'student_name' => $result->student->name,
                'marks' => $result->obtained_marks,
                'percentage' => $result->percentage,
                'time_taken' => $result->time_taken_seconds
            ];
        }

        $scores = $results->pluck('obtained_marks')->toArray();
        sort($scores);
        $count = count($scores);
        $median = $count % 2 == 0
            ? ($scores[$count/2 - 1] + $scores[$count/2]) / 2
            : $scores[floor($count/2)];

        // Calculate score distribution
        $distribution = [
            '90-100' => $results->where('percentage', '>=', 90)->count(),
            '75-89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
            '60-74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
            '40-59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
            'below-40' => $results->where('percentage', '<', 40)->count()
        ];

        return self::create([
            'exam_id' => $exam->id,
            'batch_id' => $batchId,
            'title' => $exam->title . ' - Rank List ' . now()->format('d M Y'),
            'generated_date' => now(),
            'rank_data' => $rankData,
            'total_students' => $results->count(),
            'average_score' => $results->avg('obtained_marks'),
            'highest_score' => $results->max('obtained_marks'),
            'lowest_score' => $results->min('obtained_marks'),
            'median_score' => $median,
            'score_distribution' => $distribution,
            'generated_by' => $generatedBy
        ]);
    }
}
