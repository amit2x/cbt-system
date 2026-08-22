<?php

namespace App\Services\Analytics;

use App\Models\Exam;
use App\Models\User;
use App\Models\Batch;
use App\Models\Result;
use App\Models\Question;
use App\Models\StudentExam;
use App\Models\StudentPerformanceTracking;
use App\Models\QuestionPerformance;
use App\Models\AnalyticsReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdvancedAnalyticsService
{
    /**
     * Generate comprehensive exam analytics
     */
    public function generateExamAnalytics(Exam $exam)
    {
        $results = Result::where('exam_id', $exam->id)->get();
        $studentExams = StudentExam::where('exam_id', $exam->id)->get();

        if ($results->isEmpty()) {
            return null;
        }

        // Basic statistics
        $scores = $results->pluck('percentage')->toArray();
        $times = $results->pluck('time_taken_seconds')->toArray();

        $analytics = [
            'overview' => [
                'total_participants' => $results->count(),
                'total_eligible' => $exam->total_students,
                'participation_rate' => $exam->total_students > 0
                    ? round(($results->count() / $exam->total_students) * 100, 2)
                    : 0,
                'average_score' => round($results->avg('percentage'), 2),
                'median_score' => $this->calculateMedian($scores),
                'standard_deviation' => $this->calculateStdDev($scores),
                'highest_score' => $results->max('percentage'),
                'lowest_score' => $results->min('percentage'),
                'score_range' => round($results->max('percentage') - $results->min('percentage'), 2)
            ],
            'distribution' => [
                '90_100' => $results->where('percentage', '>=', 90)->count(),
                '75_89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
                '60_74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
                '40_59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
                'below_40' => $results->where('percentage', '<', 40)->count()
            ],
            'performance_metrics' => [
                'passed' => $results->where('status', 'passed')->count(),
                'failed' => $results->where('status', 'failed')->count(),
                'pass_rate' => $results->count() > 0
                    ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                    : 0,
                'average_accuracy' => round($results->avg('accuracy_percentage'), 2),
                'average_speed' => round($results->avg('speed_per_minute'), 2),
                'average_time_taken' => round(collect($times)->avg() / 60, 2), // in minutes
                'fastest_time' => round(collect($times)->min() / 60, 2),
                'slowest_time' => round(collect($times)->max() / 60, 2)
            ]
        ];

        // Add trend analysis if multiple attempts
        if ($exam->attempts_allowed > 1) {
            $analytics['trends'] = $this->analyzeAttemptTrends($exam);
        }

        return $analytics;
    }

    /**
     * Generate student performance profile
     */
    // public function generateStudentProfile($studentId, $batchId = null)
    // {
    //     $query = Result::where('student_id', $studentId);

    //     if ($batchId) {
    //         $query->where('batch_id', $batchId);
    //     }

    //     $results = $query->with('exam')->orderBy('created_at')->get();

    //     if ($results->isEmpty()) {
    //         return null;
    //     }

    //     // Calculate performance metrics
    //     $scores = $results->pluck('percentage')->toArray();
    //     $accuracies = $results->pluck('accuracy_percentage')->toArray();
    //     $speeds = $results->pluck('speed_per_minute')->toArray();

    //     // Identify strengths and weaknesses
    //     $topicPerformance = $this->analyzeTopicPerformance($studentId);
    //     $strengths = collect($topicPerformance)->filter(function($item) {
    //         return $item['accuracy'] >= 70;
    //     })->values()->toArray();

    //     $weaknesses = collect($topicPerformance)->filter(function($item) {
    //         return $item['accuracy'] < 50;
    //     })->values()->toArray();

    //     // Calculate improvement rate
    //     $improvementRate = $this->calculateImprovementRate($scores);

    //     // Predict future performance
    //     $prediction = $this->predictPerformance($scores);

    //     $profile = [
    //         'student_info' => [
    //             'total_exams' => $results->count(),
    //             'exams_passed' => $results->where('status', 'passed')->count(),
    //             'exams_failed' => $results->where('status', 'failed')->count(),
    //             'pass_rate' => round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2),
    //             'total_time_spent' => round($results->sum('time_taken_seconds') / 3600, 1) . ' hours'
    //         ],
    //         'performance' => [
    //             'average_score' => round(collect($scores)->avg(), 2),
    //             'highest_score' => collect($scores)->max(),
    //             'lowest_score' => collect($scores)->min(),
    //             'average_accuracy' => round(collect($accuracies)->avg(), 2),
    //             'average_speed' => round(collect($speeds)->avg(), 2),
    //             'consistency_score' => $this->calculateConsistencyScore($scores),
    //             'improvement_rate' => $improvementRate
    //         ],
    //         'strengths' => $strengths,
    //         'weaknesses' => $weaknesses,
    //         'topic_analysis' => $topicPerformance,
    //         'trends' => [
    //             'scores' => $results->pluck('percentage')->toArray(),
    //             'accuracies' => $results->pluck('accuracy_percentage')->toArray(),
    //             'dates' => $results->pluck('created_at')->map(function($date) {
    //                 return $date->format('d M');
    //             })->toArray()
    //         ],
    //         'prediction' => $prediction
    //     ];

    //     // Update or create tracking record
    //     $this->updatePerformanceTracking($studentId, $batchId, $profile);

    //     return $profile;
    // }
    
        /**
     * Generate student performance profile
     */
    // public function generateStudentProfile($studentId, $batchId = null)
    // {
    //     $query = Result::where('student_id', $studentId);

    //     if ($batchId) {
    //         $query->where('batch_id', $batchId);
    //     }

    //     $results = $query->with('exam')->orderBy('created_at')->get();

    //     if ($results->isEmpty()) {
    //         return null;
    //     }

    //     // Calculate performance metrics
    //     $scores = $results->pluck('percentage')->toArray();
    //     $accuracies = $results->pluck('accuracy_percentage')->toArray();
    //     $speeds = $results->pluck('speed_per_minute')->toArray();

    //     // Identify strengths and weaknesses
    //     $topicPerformance = $this->analyzeTopicPerformance($studentId);
    //     $strengths = collect($topicPerformance)->filter(function($item) {
    //         return $item['accuracy'] >= 70;
    //     })->values()->toArray();

    //     $weaknesses = collect($topicPerformance)->filter(function($item) {
    //         return $item['accuracy'] < 50;
    //     })->values()->toArray();

    //     // Calculate improvement rate
    //     $improvementRate = $this->calculateImprovementRate($scores);

    //     // Predict future performance
    //     $prediction = $this->predictPerformance($scores);

    //     // FIX 1: Calculate raw hours as a float value for the database tracking
    //     $rawHours = round($results->sum('time_taken_seconds') / 3600, 2);

    //     $profile = [
    //         'student_info' => [
    //             'total_exams' => $results->count(),
    //             'exams_passed' => $results->where('status', 'passed')->count(),
    //             'exams_failed' => $results->where('status', 'failed')->count(),
    //             'pass_rate' => round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2),
    //             'total_time_spent_raw' => $rawHours, // Added for database updates
    //             'total_time_spent' => round($rawHours, 1) . ' hours' // Kept clean for frontend UI
    //         ],
    //         'performance' => [
    //             'average_score' => round(collect($scores)->avg(), 2),
    //             'highest_score' => collect($scores)->max(),
    //             'lowest_score' => collect($scores)->min(),
    //             'average_accuracy' => round(collect($accuracies)->avg(), 2),
    //             'average_speed' => round(collect($speeds)->avg(), 2),
    //             'consistency_score' => $this->calculateConsistencyScore($scores),
    //             'improvement_rate' => $improvementRate
    //         ],
    //         // FIX 2: Convert arrays to JSON strings if database fields do not automatically cast arrays
    //         'strengths' => json_encode($strengths),
    //         'weaknesses' => json_encode($weaknesses),
    //         'topic_analysis' => $topicPerformance,
    //         'trends' => [
    //             'scores' => $results->pluck('percentage')->toArray(),
    //             'accuracies' => $results->pluck('accuracy_percentage')->toArray(),
    //             'dates' => $results->pluck('created_at')->map(function($date) {
    //                 return $date->format('d M');
    //             })->toArray()
    //         ],
    //         'prediction' => $prediction
    //     ];

    //     // Update or create tracking record
    //     $this->updatePerformanceTracking($studentId, $batchId, $profile);

    //     return $profile;
    // }
    
    /**
 * Generate student performance profile
 */
public function generateStudentProfile($studentId, $batchId = null)
{
    $query = Result::where('student_id', $studentId);

    if ($batchId) {
        $query->where('batch_id', $batchId);
    }

    $results = $query->with('exam')->orderBy('created_at')->get();

    if ($results->isEmpty()) {
        return null;
    }

    // Calculate performance metrics
    $scores = $results->pluck('percentage')->toArray();
    $accuracies = $results->pluck('accuracy_percentage')->toArray();
    $speeds = $results->pluck('speed_per_minute')->toArray();

    // Identify strengths and weaknesses
    $topicPerformance = $this->analyzeTopicPerformance($studentId);
    
    $strengths = collect($topicPerformance)->filter(function($item) {
        return ($item['accuracy'] ?? 0) >= 70 && ($item['total'] ?? 0) >= 2;
    })->values()->toArray();

    $weaknesses = collect($topicPerformance)->filter(function($item) {
        return ($item['accuracy'] ?? 0) < 50 && ($item['total'] ?? 0) >= 2;
    })->values()->toArray();

    // Calculate improvement rate
    $improvementRate = $this->calculateImprovementRate($scores);

    // Predict future performance
    $prediction = $this->predictPerformance($scores);

    $rawHours = round($results->sum('time_taken_seconds') / 3600, 2);

    $profile = [
        'student_info' => [
            'total_exams' => $results->count(),
            'exams_passed' => $results->where('status', 'passed')->count(),
            'exams_failed' => $results->where('status', 'failed')->count(),
            'pass_rate' => round(($results->where('status', 'passed')->count() / max($results->count(), 1)) * 100, 2),
            'total_time_spent_raw' => $rawHours,
            'total_time_spent' => round($rawHours, 1) . ' hours'
        ],
        'performance' => [
            'average_score' => round(collect($scores)->avg(), 2),
            'highest_score' => collect($scores)->max() ?? 0,
            'lowest_score' => collect($scores)->min() ?? 0,
            'average_accuracy' => round(collect($accuracies)->avg(), 2),
            'average_speed' => round(collect($speeds)->avg(), 2),
            'consistency_score' => $this->calculateConsistencyScore($scores),
            'improvement_rate' => $improvementRate
        ],
        // ✅ Store as arrays (not JSON strings) - they will be encoded when saving to database
        'strengths' => $strengths,
        'weaknesses' => $weaknesses,
        'topic_analysis' => $topicPerformance,
        'trends' => [
            'scores' => $results->pluck('percentage')->toArray(),
            'accuracies' => $results->pluck('accuracy_percentage')->toArray(),
            'dates' => $results->pluck('created_at')->map(function($date) {
                return $date->format('d M');
            })->toArray()
        ],
        'prediction' => $prediction
    ];

    // Update or create tracking record (this will encode arrays to JSON)
    $this->updatePerformanceTracking($studentId, $batchId, $profile);

    return $profile;
}

/**
 * Update performance tracking for student
 */
protected function updatePerformanceTracking($studentId, $batchId, $profile)
{
    StudentPerformanceTracking::updateOrCreate(
        ['student_id' => $studentId, 'batch_id' => $batchId],
        [
            'accuracy_trend' => $profile['performance']['average_accuracy'] ?? 0,
            'speed_trend' => $profile['performance']['average_speed'] ?? 0,
            'consistency_score' => $profile['performance']['consistency_score'] ?? 0,
            'improvement_rate' => $profile['performance']['improvement_rate'] ?? 0,
            // ✅ Encode to JSON for database storage
            'strengths' => json_encode($profile['strengths'] ?? []),
            'weaknesses' => json_encode($profile['weaknesses'] ?? []),
            'total_time_spent' => $profile['student_info']['total_time_spent_raw'] ?? 0,
            'exams_attempted' => $profile['student_info']['total_exams'] ?? 0,
            'exams_passed' => $profile['student_info']['exams_passed'] ?? 0,
            'pass_rate' => $profile['student_info']['pass_rate'] ?? 0,
            'predicted_score' => $profile['prediction']['predicted_score'] ?? null,
            'prediction_confidence' => $profile['prediction']['confidence'] ?? null,
            'calculated_at' => now()
        ]
    );
}


    /**
     * Generate batch performance report
     */
    public function generateBatchReport(Batch $batch, $dateFrom = null, $dateTo = null)
    {
        $query = Result::where('batch_id', $batch->id);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $results = $query->with(['student', 'exam'])->get();

        if ($results->isEmpty()) {
            return null;
        }

        $students = $batch->students()->count();
        $activeStudents = $results->pluck('student_id')->unique()->count();

        $report = [
            'batch_info' => [
                'name' => $batch->name,
                'course' => $batch->course->title,
                'instructor' => $batch->instructor->name,
                'total_students' => $students,
                'active_students' => $activeStudents,
                'participation_rate' => $students > 0 ? round(($activeStudents / $students) * 100, 2) : 0
            ],
            'overall_performance' => [
                'total_exams' => $results->count(),
                'average_score' => round($results->avg('percentage'), 2),
                'highest_score' => $results->max('percentage'),
                'lowest_score' => $results->min('percentage'),
                'pass_rate' => $results->count() > 0
                    ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                    : 0
            ],
            'exam_breakdown' => $this->getExamBreakdown($results),
            'student_performance_distribution' => $this->getStudentDistribution($results, $students),
            'top_performers' => $this->getTopPerformers($results, 10),
            'needs_improvement' => $this->getNeedsImprovement($results, 10)
        ];

        // Add trend analysis
        $report['trends'] = $this->analyzeBatchTrends($results);

        return $report;
    }

    /**
     * Generate question performance analysis
     */
    public function generateQuestionAnalysis($questionId, $examId = null)
    {
        $query = DB::table('student_answers')
            ->join('student_exams', 'student_answers.student_exam_id', '=', 'student_exams.id')
            ->where('student_answers.question_id', $questionId);

        if ($examId) {
            $query->where('student_exams.exam_id', $examId);
        }

        $answers = $query->select(
            'student_answers.*',
            'student_exams.exam_id',
            'student_exams.student_id'
        )->get();

        $question = Question::find($questionId);

        if ($answers->isEmpty()) {
            return null;
        }

        $total = $answers->count();
        $correct = $answers->where('is_correct', true)->count();
        $incorrect = $answers->where('is_correct', false)
            ->filter(function($a) {
                return $a->selected_options || $a->answer_text;
            })->count();
        $unanswered = $total - $correct - $incorrect;

        // Calculate time metrics
        $times = $answers->pluck('time_taken_seconds')->filter();
        $avgTime = $times->avg();
        $minTime = $times->min();
        $maxTime = $times->max();

        // Calculate difficulty index (percentage who got it wrong)
        $difficultyIndex = $total > 0 ? round(($incorrect / $total) * 100, 2) : 0;

        // Option analysis for MCQs
        $optionAnalysis = null;
        if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
            $optionAnalysis = $this->analyzeOptions($questionId, $answers);
        }

        // Calculate discrimination index
        $discriminationIndex = $this->calculateDiscriminationIndex($questionId, $answers);

        // Determine effectiveness
        $effectivenessScore = ($correct / max($total, 1)) * 100;
        $effectivenessLevel = $this->determineEffectiveness($effectivenessScore, $difficultyIndex);

        // Update or create question performance record
        QuestionPerformance::updateOrCreate(
            ['question_id' => $questionId, 'exam_id' => $examId],
            [
                'total_attempts' => $total,
                'correct_attempts' => $correct,
                'incorrect_attempts' => $incorrect,
                'accuracy_rate' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
                'avg_time_seconds' => $avgTime,
                'min_time_seconds' => $minTime,
                'max_time_seconds' => $maxTime,
                'difficulty_index' => $difficultyIndex,
                'discrimination_index' => $discriminationIndex,
                'option_analysis' => $optionAnalysis,
                'effectiveness_score' => $effectivenessScore,
                'effectiveness_level' => $effectivenessLevel
            ]
        );

        return [
            'question_id' => $questionId,
            'question_text' => substr(strip_tags($question->question_text), 0, 200),
            'type' => $question->type_name,
            'difficulty' => $question->difficulty_name,
            'metrics' => [
                'total_attempts' => $total,
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'accuracy_rate' => round(($correct / max($total, 1)) * 100, 2),
                'difficulty_index' => $difficultyIndex,
                'discrimination_index' => $discriminationIndex,
                'avg_time' => gmdate('i:s', $avgTime),
                'effectiveness_level' => $effectivenessLevel
            ],
            'option_analysis' => $optionAnalysis
        ];
    }

    /**
     * Generate overall system analytics
     */
    public function generateOverallAnalytics($dateFrom = null, $dateTo = null)
    {
        $query = Result::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $results = $query->get();
        $totalExams = Exam::count();
        $totalStudents = User::role('student')->count();
        $activeStudents = User::role('student')
            ->whereHas('results', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
            })->count();

        $analytics = [
            'overview' => [
                'total_exams' => $totalExams,
                'total_results' => $results->count(),
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'engagement_rate' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0,
                'average_score' => round($results->avg('percentage'), 2),
                'pass_rate' => $results->count() > 0
                    ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                    : 0
            ],
            'daily_activity' => $this->getDailyActivity($dateFrom, $dateTo),
            'popular_exams' => $this->getPopularExams(),
            'performance_by_course' => $this->getPerformanceByCourse(),
            'student_growth' => $this->getStudentGrowth(),
            'difficulty_distribution' => $this->getDifficultyDistribution()
        ];

        return $analytics;
    }

    /**
     * Save analytics report
     */
    public function saveReport($type, $title, $data, $filters = [], $userId = null)
    {
        $report = AnalyticsReport::create([
            'report_type' => $type,
            'title' => $title,
            'filters' => $filters,
            'data' => $data,
            'summary' => $this->generateSummary($data),
            'charts' => $this->generateChartData($data),
            'generated_by' => $userId ?? auth()->id(),
            'exam_id' => $filters['exam_id'] ?? null,
            'batch_id' => $filters['batch_id'] ?? null,
            'student_id' => $filters['student_id'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null
        ]);

        return $report;
    }

    /**
     * Update performance tracking for student
     */
    // protected function updatePerformanceTracking($studentId, $batchId, $profile)
    // {
    //     StudentPerformanceTracking::updateOrCreate(
    //         ['student_id' => $studentId, 'batch_id' => $batchId],
    //         [
    //             'accuracy_trend' => $profile['performance']['average_accuracy'],
    //             'speed_trend' => $profile['performance']['average_speed'],
    //             'consistency_score' => $profile['performance']['consistency_score'],
    //             'improvement_rate' => $profile['performance']['improvement_rate'],
    //             'strengths' => $profile['strengths'],
    //             'weaknesses' => $profile['weaknesses'],
    //             'total_time_spent' => $profile['student_info']['total_time_spent_raw'],
    //             'exams_attempted' => $profile['student_info']['total_exams'],
    //             'exams_passed' => $profile['student_info']['exams_passed'],
    //             'pass_rate' => $profile['student_info']['pass_rate'],
    //             'predicted_score' => $profile['prediction']['predicted_score'] ?? null,
    //             'prediction_confidence' => $profile['prediction']['confidence'] ?? null,
    //             'calculated_at' => now()
    //         ]
    //     );
    // }

    /**
     * Calculate median
     */
    protected function calculateMedian($array)
    {
        $count = count($array);
        if ($count == 0) return 0;

        sort($array);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        }
        return $array[$middle];
    }

    /**
     * Calculate standard deviation
     */
    protected function calculateStdDev($array)
    {
        $count = count($array);
        if ($count < 2) return 0;

        $mean = array_sum($array) / $count;
        $variance = 0;

        foreach ($array as $value) {
            $variance += pow($value - $mean, 2);
        }

        $variance /= $count;
        return round(sqrt($variance), 2);
    }

    /**
     * Analyze topic performance
     */
    // protected function analyzeTopicPerformance($studentId)
    // {
    //     $answers = DB::table('student_answers')
    //         ->join('student_exams', 'student_answers.student_exam_id', '=', 'student_exams.id')
    //         ->join('questions', 'student_answers.question_id', '=', 'questions.id')
    //         ->where('student_exams.student_id', $studentId)
    //         ->select(
    //             'questions.tags',
    //             'student_answers.is_correct',
    //             'questions.id as question_id'
    //         )
    //         ->get();

    //     $topics = [];

    //     foreach ($answers as $answer) {
    //         if ($answer->tags) {
    //             $tags = json_decode($answer->tags, true) ?? [];
    //             foreach ($tags as $tag) {
    //                 if (!isset($topics[$tag])) {
    //                     $topics[$tag] = ['total' => 0, 'correct' => 0];
    //                 }
    //                 $topics[$tag]['total']++;
    //                 if ($answer->is_correct) {
    //                     $topics[$tag]['correct']++;
    //                 }
    //             }
    //         }
    //     }

    //     $result = [];
    //     foreach ($topics as $topic => $data) {
    //         $result[] = [
    //             'topic' => $topic,
    //             'total' => $data['total'],
    //             'correct' => $data['correct'],
    //             'accuracy' => round(($data['correct'] / max($data['total'], 1)) * 100, 2)
    //         ];
    //     }

    //     usort($result, function($a, $b) {
    //         return $b['accuracy'] <=> $a['accuracy'];
    //     });

    //     return $result;
    // }
    
    /**
 * Analyze topic performance - FIXED
 */
protected function analyzeTopicPerformance($studentId)
{
    $answers = DB::table('student_answers')
        ->join('student_exams', 'student_answers.student_exam_id', '=', 'student_exams.id')
        ->join('questions', 'student_answers.question_id', '=', 'questions.id')
        ->where('student_exams.student_id', $studentId)
        ->select(
            'questions.tags',
            'student_answers.is_correct',
            'questions.id as question_id'
        )
        ->get();

    $topics = [];

    foreach ($answers as $answer) {
        // Handle tags - could be JSON, array, or null
        $tags = $this->parseTags($answer->tags);
        
        if (empty($tags)) {
            // If no tags, use 'General' as default
            $tags = ['General'];
        }
        
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) continue;
            
            if (!isset($topics[$tag])) {
                $topics[$tag] = ['total' => 0, 'correct' => 0];
            }
            $topics[$tag]['total']++;
            if ($answer->is_correct) {
                $topics[$tag]['correct']++;
            }
        }
    }

    $result = [];
    foreach ($topics as $topic => $data) {
        $accuracy = $data['total'] > 0 
            ? round(($data['correct'] / $data['total']) * 100, 2) 
            : 0;
            
        $result[] = [
            'topic' => $topic,
            'total' => $data['total'],
            'correct' => $data['correct'],
            'incorrect' => $data['total'] - $data['correct'],
            'accuracy' => $accuracy
        ];
    }

    // Sort by total questions descending, then by accuracy
    usort($result, function($a, $b) {
        if ($a['total'] == $b['total']) {
            return $b['accuracy'] <=> $a['accuracy'];
        }
        return $b['total'] <=> $a['total'];
    });

    return $result;
}

/**
 * Parse tags from various formats
 */
protected function parseTags($tags)
{
    if (is_null($tags)) {
        return [];
    }
    
    if (is_array($tags)) {
        return $tags;
    }
    
    if (is_string($tags)) {
        // Try JSON decode
        $decoded = json_decode($tags, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        
        // Try comma-separated
        if (strpos($tags, ',') !== false) {
            return array_map('trim', explode(',', $tags));
        }
        
        // Single tag
        return [trim($tags)];
    }
    
    return [];
}

    /**
     * Calculate improvement rate
     */
    protected function calculateImprovementRate($scores)
    {
        if (count($scores) < 2) return 0;

        $first = $scores[0];
        $last = $scores[count($scores) - 1];

        if ($first == 0) return $last > 0 ? 100 : 0;

        return round((($last - $first) / $first) * 100, 2);
    }

    /**
     * Calculate consistency score
     */
    protected function calculateConsistencyScore($scores)
    {
        if (count($scores) < 2) return 100;

        $mean = array_sum($scores) / count($scores);
        $variance = 0;

        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }

        $stdDev = sqrt($variance / count($scores));
        $maxPossibleStdDev = 50; // Assuming scores are percentages

        $consistency = 100 - min(100, ($stdDev / $maxPossibleStdDev) * 100);
        return round($consistency, 2);
    }

    /**
     * Predict future performance
     */
    protected function predictPerformance($scores)
    {
        if (count($scores) < 3) {
            return [
                'predicted_score' => null,
                'confidence' => 'low'
            ];
        }

        // Simple linear regression for prediction
        $x = range(1, count($scores));
        $y = $scores;

        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $nextX = $n + 1;
        $predicted = $slope * $nextX + $intercept;
        $predicted = max(0, min(100, round($predicted, 2)));

        // Calculate confidence based on R-squared
        $meanY = $sumY / $n;
        $ssTotal = 0;
        $ssResidual = 0;

        for ($i = 0; $i < $n; $i++) {
            $predictedY = $slope * $x[$i] + $intercept;
            $ssTotal += pow($y[$i] - $meanY, 2);
            $ssResidual += pow($y[$i] - $predictedY, 2);
        }

        $rSquared = 1 - ($ssResidual / max($ssTotal, 1));

        $confidence = 'low';
        if ($rSquared > 0.7) $confidence = 'high';
        elseif ($rSquared > 0.4) $confidence = 'medium';

        return [
            'predicted_score' => $predicted,
            'confidence' => $confidence,
            'r_squared' => round($rSquared, 2)
        ];
    }

    /**
     * Analyze attempt trends
     */
    protected function analyzeAttemptTrends($exam)
    {
        $studentExams = StudentExam::where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->orderBy('student_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('student_id');

        $firstAttemptScores = [];
        $secondAttemptScores = [];
        $improvements = [];

        foreach ($studentExams as $studentId => $attempts) {
            if ($attempts->count() >= 2) {
                $firstAttemptScores[] = $attempts[0]->percentage;
                $secondAttemptScores[] = $attempts[1]->percentage;
                $improvements[] = $attempts[1]->percentage - $attempts[0]->percentage;
            }
        }

        return [
            'students_with_multiple_attempts' => count($firstAttemptScores),
            'avg_first_attempt' => collect($firstAttemptScores)->avg(),
            'avg_second_attempt' => collect($secondAttemptScores)->avg(),
            'avg_improvement' => collect($improvements)->avg(),
            'improvement_distribution' => [
                'improved' => collect($improvements)->filter(function($imp) { return $imp > 0; })->count(),
                'declined' => collect($improvements)->filter(function($imp) { return $imp < 0; })->count(),
                'no_change' => collect($improvements)->filter(function($imp) { return $imp == 0; })->count()
            ]
        ];
    }

    /**
     * Get exam breakdown from results
     */
    protected function getExamBreakdown($results)
    {
        $exams = $results->groupBy('exam_id');
        $breakdown = [];

        foreach ($exams as $examId => $examResults) {
            $exam = Exam::find($examId);
            $breakdown[] = [
                'exam_id' => $examId,
                'exam_title' => $exam->title ?? 'Unknown',
                'attempts' => $examResults->count(),
                'average_score' => round($examResults->avg('percentage'), 2),
                'pass_rate' => round(($examResults->where('status', 'passed')->count() / $examResults->count()) * 100, 2)
            ];
        }

        return $breakdown;
    }

    /**
     * Get student distribution
     */
    protected function getStudentDistribution($results, $totalStudents)
    {
        $studentScores = $results->groupBy('student_id')
            ->map(function($studentResults) {
                return $studentResults->avg('percentage');
            });

        return [
            'above_80' => $studentScores->filter(function($score) { return $score >= 80; })->count(),
            '60_to_79' => $studentScores->filter(function($score) { return $score >= 60 && $score < 80; })->count(),
            '40_to_59' => $studentScores->filter(function($score) { return $score >= 40 && $score < 60; })->count(),
            'below_40' => $studentScores->filter(function($score) { return $score < 40; })->count(),
            'inactive' => $totalStudents - $studentScores->count()
        ];
    }

    /**
     * Get top performers
     */
    protected function getTopPerformers($results, $limit)
    {
        $studentAverages = $results->groupBy('student_id')
            ->map(function($studentResults) {
                return [
                    'student_id' => $studentResults->first()->student_id,
                    'student_name' => $studentResults->first()->student->name,
                    'average_score' => round($studentResults->avg('percentage'), 2),
                    'exams_taken' => $studentResults->count(),
                    'best_score' => $studentResults->max('percentage')
                ];
            })
            ->sortByDesc('average_score')
            ->take($limit)
            ->values();

        return $studentAverages;
    }

    /**
     * Get students needing improvement
     */
    protected function getNeedsImprovement($results, $limit)
    {
        $studentAverages = $results->groupBy('student_id')
            ->map(function($studentResults) {
                return [
                    'student_id' => $studentResults->first()->student_id,
                    'student_name' => $studentResults->first()->student->name,
                    'average_score' => round($studentResults->avg('percentage'), 2),
                    'exams_taken' => $studentResults->count(),
                    'trend' => $this->calculateTrend($studentResults->pluck('percentage')->toArray())
                ];
            })
            ->filter(function($student) {
                return $student['average_score'] < 50;
            })
            ->sortBy('average_score')
            ->take($limit)
            ->values();

        return $studentAverages;
    }

    /**
     * Calculate trend
     */
    protected function calculateTrend($scores)
    {
        if (count($scores) < 2) return 'stable';

        $first = $scores[0];
        $last = $scores[count($scores) - 1];

        if ($last - $first > 5) return 'improving';
        if ($first - $last > 5) return 'declining';
        return 'stable';
    }

    /**
     * Analyze batch trends
     */
    protected function analyzeBatchTrends($results)
    {
        $dailyAverages = $results->groupBy(function($result) {
            return $result->created_at->format('Y-m-d');
        })->map(function($dayResults) {
            return [
                'date' => $dayResults->first()->created_at->format('d M'),
                'average' => round($dayResults->avg('percentage'), 2),
                'count' => $dayResults->count()
            ];
        })->values();

        return $dailyAverages;
    }

    /**
     * Analyze options for MCQ
     */
    protected function analyzeOptions($questionId, $answers)
    {
        $options = Question::find($questionId)->options;
        $analysis = [];

        foreach ($options as $option) {
            $count = $answers->filter(function($answer) use ($option) {
                $selected = json_decode($answer->selected_options ?? '[]', true);
                return in_array($option->id, $selected);
            })->count();

            $analysis['option_' . $option->id] = [
                'option_text' => substr($option->option_text, 0, 50),
                'count' => $count,
                'is_correct' => $option->is_correct
            ];
        }

        return $analysis;
    }

    /**
     * Calculate discrimination index
     */
    protected function calculateDiscriminationIndex($questionId, $answers)
    {
        // Get student scores
        $studentScores = [];
        foreach ($answers as $answer) {
            $studentExam = StudentExam::find($answer->student_exam_id);
            if ($studentExam && $studentExam->result) {
                $studentScores[$answer->student_id] = $studentExam->result->percentage;
            }
        }

        if (count($studentScores) < 10) return 0;

        // Sort students by score
        arsort($studentScores);
        $students = array_keys($studentScores);
        $total = count($students);

        $top27 = array_slice($students, 0, ceil($total * 0.27));
        $bottom27 = array_slice($students, -ceil($total * 0.27));

        $topCorrect = 0;
        $bottomCorrect = 0;

        foreach ($answers as $answer) {
            if (in_array($answer->student_id, $top27) && $answer->is_correct) {
                $topCorrect++;
            }
            if (in_array($answer->student_id, $bottom27) && $answer->is_correct) {
                $bottomCorrect++;
            }
        }

        $topP = $topCorrect / max(count($top27), 1);
        $bottomP = $bottomCorrect / max(count($bottom27), 1);

        return round($topP - $bottomP, 2);
    }

    /**
     * Determine effectiveness level
     */
    protected function determineEffectiveness($accuracy, $difficulty)
    {
        if ($accuracy >= 70 && $difficulty >= 50) return 'good';
        if ($accuracy >= 50) return 'average';
        return 'poor';
    }

    /**
     * Get daily activity
     */
    protected function getDailyActivity($dateFrom, $dateTo)
    {
        $query = Result::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as attempts'),
            DB::raw('AVG(percentage) as avg_score')
        );

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => \Carbon\Carbon::parse($item->date)->format('d M'),
                    'attempts' => $item->attempts,
                    'avg_score' => round($item->avg_score, 2)
                ];
            });
    }

    /**
     * Get popular exams
     */
    protected function getPopularExams()
    {
        return Exam::withCount('studentExams')
            ->having('student_exams_count', '>', 0)
            ->orderBy('student_exams_count', 'desc')
            ->take(10)
            ->get()
            ->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'attempts' => $exam->student_exams_count,
                    'avg_score' => round(Result::where('exam_id', $exam->id)->avg('percentage'), 2)
                ];
            });
    }

    /**
     * Get performance by course
     */
    protected function getPerformanceByCourse()
    {
        return DB::table('courses')
            ->join('exams', 'courses.id', '=', 'exams.course_id')
            ->join('results', 'exams.id', '=', 'results.exam_id')
            ->select(
                'courses.id',
                'courses.title',
                DB::raw('COUNT(DISTINCT results.id) as attempts'),
                DB::raw('AVG(results.percentage) as avg_score'),
                DB::raw('COUNT(DISTINCT results.student_id) as students')
            )
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('attempts', 'desc')
            ->get();
    }

    /**
     * Get student growth
     */
    protected function getStudentGrowth()
    {
        return User::role('student')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => \Carbon\Carbon::parse($item->date)->format('d M'),
                    'count' => $item->count
                ];
            });
    }

    /**
     * Get difficulty distribution
     */
    protected function getDifficultyDistribution()
    {
        $questions = Question::select('difficulty', DB::raw('COUNT(*) as count'))
            ->groupBy('difficulty')
            ->get()
            ->keyBy('difficulty');

        return [
            'easy' => $questions['easy']->count ?? 0,
            'medium' => $questions['medium']->count ?? 0,
            'hard' => $questions['hard']->count ?? 0
        ];
    }

    /**
     * Generate summary from data
     */
    protected function generateSummary($data)
    {
        $summary = [];

        if (isset($data['overview'])) {
            $summary['total_records'] = $data['overview']['total_participants'] ?? $data['overview']['total_results'] ?? 0;
            $summary['average'] = $data['overview']['average_score'] ?? null;
            $summary['highest'] = $data['overview']['highest_score'] ?? null;
            $summary['lowest'] = $data['overview']['lowest_score'] ?? null;
        }

        return $summary;
    }

    /**
     * Generate chart data
     */
    protected function generateChartData($data)
    {
        $charts = [];

        if (isset($data['distribution'])) {
            $charts['distribution'] = [
                'type' => 'pie',
                'labels' => array_keys($data['distribution']),
                'values' => array_values($data['distribution'])
            ];
        }

        if (isset($data['trends'])) {
            $charts['trends'] = [
                'type' => 'line',
                'labels' => $data['trends']['dates'] ?? [],
                'values' => $data['trends']['scores'] ?? []
            ];
        }

        return $charts;
    }
}
