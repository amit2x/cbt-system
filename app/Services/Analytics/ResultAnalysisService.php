<?php

namespace App\Services\Analytics;

use App\Models\Result;
use App\Models\Exam;
use App\Models\User;
use App\Models\Batch;
use App\Models\StudentExam;
use Illuminate\Support\Facades\DB;

class ResultAnalysisService
{
    /**
     * Get exam analytics
     */
    public function getExamAnalytics($examId)
    {
        $results = Result::where('exam_id', $examId)->get();

        if ($results->isEmpty()) {
            return null;
        }

        $scores = $results->pluck('percentage')->toArray();
        sort($scores);

        return [
            'overview' => [
                'total_participants' => $results->count(),
                'average_score' => round($results->avg('percentage'), 2),
                'highest_score' => $results->max('percentage'),
                'lowest_score' => $results->min('percentage'),
                'median_score' => $this->calculateMedian($scores),
                'pass_rate' => $results->count() > 0
                    ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                    : 0
            ],
            'distribution' => [
                '90_100' => $results->where('percentage', '>=', 90)->count(),
                '75_89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
                '60_74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
                '40_59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
                'below_40' => $results->where('percentage', '<', 40)->count()
            ],
            'top_performers' => $results->sortByDesc('percentage')->take(10)->map(function($r) {
                return [
                    'student_name' => $r->student->name ?? 'Unknown',
                    'percentage' => $r->percentage,
                    'rank' => $r->rank
                ];
            })->values()->toArray()
        ];
    }

    /**
     * Get batch performance - FIXED
     */
    public function getBatchPerformance($batchId, $examId = null)
    {
        $query = Result::where('batch_id', $batchId);

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            return [
                'total_students' => 0,
                'passed' => 0,
                'failed' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'average_accuracy' => 0,
                'average_speed' => 0,
                'pass_percentage' => 0
            ];
        }

        return [
            'total_students' => $results->count(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'average_score' => round($results->avg('percentage'), 2),
            'highest_score' => $results->max('percentage'),
            'lowest_score' => $results->min('percentage'),
            'average_accuracy' => round($results->avg('accuracy_percentage'), 2),
            'average_speed' => round($results->avg('speed_per_minute'), 2),
            'pass_percentage' => $results->count() > 0
                ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                : 0
        ];
    }

    /**
     * Get student performance trends
     */
    public function getStudentTrends($studentId, $examType = null, $limit = 10)
    {
        $query = Result::where('student_id', $studentId)
            ->with('exam')
            ->orderBy('created_at', 'desc');

        if ($examType) {
            $query->whereHas('exam', function($q) use ($examType) {
                $q->where('type', $examType);
            });
        }

        $results = $query->take($limit)->get();

        $trends = [
            'labels' => $results->pluck('exam.title')->toArray(),
            'scores' => $results->pluck('percentage')->toArray(),
            'accuracy' => $results->pluck('accuracy_percentage')->toArray(),
            'speed' => $results->pluck('speed_per_minute')->toArray(),
            'average' => $results->avg('percentage'),
            'best' => $results->max('percentage'),
            'improvement' => $this->calculateImprovement($results)
        ];

        return $trends;
    }

    /**
     * Get question-wise analysis
     */
    public function getQuestionAnalysis($examId)
    {
        $studentExams = StudentExam::where('exam_id', $examId)
            ->where('status', 'completed')
            ->with('answers')
            ->get();

        $questions = [];

        foreach ($studentExams as $studentExam) {
            foreach ($studentExam->answers as $answer) {
                $qid = $answer->question_id;

                if (!isset($questions[$qid])) {
                    $questions[$qid] = [
                        'question_id' => $qid,
                        'total_attempts' => 0,
                        'correct' => 0,
                        'incorrect' => 0,
                        'unanswered' => 0,
                        'avg_time' => 0
                    ];
                }

                $questions[$qid]['total_attempts']++;

                if ($answer->is_correct) {
                    $questions[$qid]['correct']++;
                } elseif ($answer->selected_options || $answer->answer_text) {
                    $questions[$qid]['incorrect']++;
                } else {
                    $questions[$qid]['unanswered']++;
                }

                $questions[$qid]['avg_time'] += $answer->time_taken_seconds ?? 0;
            }
        }

        // Calculate averages
        foreach ($questions as &$q) {
            if ($q['total_attempts'] > 0) {
                $q['avg_time'] = round($q['avg_time'] / $q['total_attempts']);
                $q['difficulty_index'] = round(($q['incorrect'] / $q['total_attempts']) * 100, 2);
            }
        }

        return array_values($questions);
    }

    /**
     * Calculate median
     */
    protected function calculateMedian($array)
    {
        $count = count($array);
        if ($count == 0) return 0;

        $middle = floor($count / 2);
        if ($count % 2 == 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        }
        return $array[$middle];
    }

    /**
     * Calculate improvement
     */
    protected function calculateImprovement($results)
    {
        if ($results->count() < 2) {
            return 0;
        }

        $first = $results->last()->percentage ?? 0;
        $last = $results->first()->percentage ?? 0;

        return round($last - $first, 2);
    }

    /**
     * Get exam breakdown by batch
     */
    public function getExamBreakdownByBatch($examId)
    {
        $results = Result::where('exam_id', $examId)
            ->with('batch')
            ->get()
            ->groupBy('batch_id');

        $breakdown = [];

        foreach ($results as $batchId => $batchResults) {
            $batch = Batch::find($batchId);
            $breakdown[] = [
                'batch_name' => $batch->name ?? 'Unknown',
                'total_students' => $batchResults->count(),
                'passed' => $batchResults->where('status', 'passed')->count(),
                'failed' => $batchResults->where('status', 'failed')->count(),
                'average_score' => round($batchResults->avg('percentage'), 2),
                'pass_rate' => $batchResults->count() > 0
                    ? round(($batchResults->where('status', 'passed')->count() / $batchResults->count()) * 100, 2)
                    : 0
            ];
        }

        return $breakdown;
    }

    /**
     * Get student exam summary
     */
    public function getStudentExamSummary($studentId, $examId)
    {
        $result = Result::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->with(['exam', 'sections'])
            ->first();

        if (!$result) {
            return null;
        }

        return [
            'result' => $result,
            'section_analysis' => $result->sections,
            'score_breakdown' => [
                'total_marks' => $result->total_marks,
                'obtained_marks' => $result->obtained_marks,
                'percentage' => $result->percentage,
                'status' => $result->status
            ],
            'answer_stats' => [
                'correct' => $result->correct_answers,
                'incorrect' => $result->incorrect_answers,
                'unanswered' => $result->unanswered,
                'marked_for_review' => $result->marked_for_review
            ]
        ];
    }

    /**
     * Get batch comparison
     */
    public function getBatchComparison($batchIds)
    {
        $comparison = [];

        foreach ($batchIds as $batchId) {
            $batch = Batch::find($batchId);
            if (!$batch) continue;

            $performance = $this->getBatchPerformance($batchId);

            $comparison[] = [
                'batch_id' => $batchId,
                'batch_name' => $batch->name,
                'total_students' => $performance['total_students'],
                'average_score' => $performance['average_score'],
                'pass_rate' => $performance['pass_percentage'],
                'highest_score' => $performance['highest_score']
            ];
        }

        return $comparison;
    }

    /**
     * Get performance distribution
     */
    public function getPerformanceDistribution($examId = null, $batchId = null)
    {
        $query = Result::query();

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $results = $query->get();

        return [
            'excellent' => $results->where('percentage', '>=', 80)->count(),
            'good' => $results->where('percentage', '>=', 60)->where('percentage', '<', 80)->count(),
            'average' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
            'poor' => $results->where('percentage', '<', 40)->count()
        ];
    }

    /**
     * Get time analysis
     */
    public function getTimeAnalysis($examId)
    {
        $results = Result::where('exam_id', $examId)->get();

        return [
            'average_time' => round($results->avg('time_taken_seconds') / 60, 2),
            'fastest_time' => round($results->min('time_taken_seconds') / 60, 2),
            'slowest_time' => round($results->max('time_taken_seconds') / 60, 2),
            'time_distribution' => [
                'under_30_min' => $results->where('time_taken_seconds', '<', 1800)->count(),
                '30_60_min' => $results->where('time_taken_seconds', '>=', 1800)->where('time_taken_seconds', '<', 3600)->count(),
                'over_60_min' => $results->where('time_taken_seconds', '>=', 3600)->count()
            ]
        ];
    }

    /**
     * Get question difficulty distribution
     */
    public function getDifficultyDistribution($examId)
    {
        $studentExams = StudentExam::where('exam_id', $examId)
            ->where('status', 'completed')
            ->with('answers.question')
            ->get();

        $distribution = [
            'easy' => ['total' => 0, 'correct' => 0],
            'medium' => ['total' => 0, 'correct' => 0],
            'hard' => ['total' => 0, 'correct' => 0]
        ];

        foreach ($studentExams as $studentExam) {
            foreach ($studentExam->answers as $answer) {
                $difficulty = $answer->question->difficulty ?? 'medium';
                $distribution[$difficulty]['total']++;
                if ($answer->is_correct) {
                    $distribution[$difficulty]['correct']++;
                }
            }
        }

        // Calculate accuracy for each difficulty
        foreach ($distribution as $key => &$data) {
            $data['accuracy'] = $data['total'] > 0 
                ? round(($data['correct'] / $data['total']) * 100, 2)
                : 0;
        }

        return $distribution;
    }

    /**
     * Get student progress over time
     */
    public function getStudentProgress($studentId, $limit = 10)
    {
        $results = Result::where('student_id', $studentId)
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();

        $progress = [];

        foreach ($results as $index => $result) {
            $progress[] = [
                'attempt' => $index + 1,
                'exam' => $result->exam->title ?? 'Unknown',
                'score' => $result->percentage,
                'accuracy' => $result->accuracy_percentage,
                'speed' => $result->speed_per_minute,
                'date' => $result->created_at->format('d M Y')
            ];
        }

        return $progress;
    }

    /**
     * Get student weak topics
     */
    public function getStudentWeakTopics($studentId, $limit = 5)
    {
        $results = Result::where('student_id', $studentId)
            ->with('exam')
            ->get();

        $topicPerformance = [];

        foreach ($results as $result) {
            $sectionalAnalysis = $result->sectional_analysis ?? [];
            foreach ($sectionalAnalysis as $section) {
                $topic = $section['section_name'] ?? 'Unknown';
                if (!isset($topicPerformance[$topic])) {
                    $topicPerformance[$topic] = ['total' => 0, 'correct' => 0];
                }
                $topicPerformance[$topic]['total'] += $section['total_questions'] ?? 0;
                $topicPerformance[$topic]['correct'] += $section['correct'] ?? 0;
            }
        }

        $weakTopics = [];
        foreach ($topicPerformance as $topic => $data) {
            $accuracy = $data['total'] > 0 
                ? round(($data['correct'] / $data['total']) * 100, 2)
                : 0;
            if ($accuracy < 50) {
                $weakTopics[] = [
                    'topic' => $topic,
                    'accuracy' => $accuracy,
                    'total_questions' => $data['total'],
                    'correct_answers' => $data['correct']
                ];
            }
        }

        usort($weakTopics, function($a, $b) {
            return $a['accuracy'] <=> $b['accuracy'];
        });

        return array_slice($weakTopics, 0, $limit);
    }

    /**
     * Get exam performance summary
     */
    public function getExamPerformanceSummary($examId)
    {
        $exam = Exam::find($examId);
        if (!$exam) {
            return null;
        }

        $results = Result::where('exam_id', $examId)->get();

        return [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'type' => $exam->type,
                'total_questions' => $exam->questions()->count(),
                'total_marks' => $exam->total_marks,
                'passing_percentage' => $exam->passing_percentage
            ],
            'participants' => [
                'total' => $results->count(),
                'passed' => $results->where('status', 'passed')->count(),
                'failed' => $results->where('status', 'failed')->count(),
                'pass_rate' => $results->count() > 0
                    ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                    : 0
            ],
            'scores' => [
                'average' => round($results->avg('percentage'), 2),
                'highest' => $results->max('percentage'),
                'lowest' => $results->min('percentage'),
                'median' => $this->calculateMedian($results->pluck('percentage')->toArray())
            ],
            'accuracy' => [
                'average' => round($results->avg('accuracy_percentage'), 2),
                'highest' => $results->max('accuracy_percentage'),
                'lowest' => $results->min('accuracy_percentage')
            ]
        ];
    }
}