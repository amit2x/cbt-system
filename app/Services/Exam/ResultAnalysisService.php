<?php

namespace App\Services\Exam;

use App\Models\Result;
use App\Models\StudentExam;
use App\Models\RankList;
use Illuminate\Support\Facades\DB;

class ResultAnalysisService
{
    /**
     * Calculate and store result
     */
    public function calculateResult(StudentExam $studentExam)
    {
        $exam = $studentExam->exam;
        $answers = $studentExam->answers()->with('question')->get();

        // Calculate basic metrics
        $totalQuestions = $answers->count();
        $correct = $answers->where('is_correct', true)->count();
        $incorrect = $answers->where('is_correct', false)
            ->filter(function($answer) {
                return $answer->selected_options || $answer->answer_text;
            })->count();
        $unanswered = $totalQuestions - $correct - $incorrect;
        $markedForReview = $answers->where('is_marked_for_review', true)->count();

        // Calculate marks
        $totalMarks = $exam->total_marks;
        $obtainedMarks = $answers->sum('marks_obtained');

        // Calculate percentage
        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        // Determine status
        $status = $percentage >= $exam->passing_percentage ? 'passed' : 'failed';

        // Calculate time metrics
        $timeTakenSeconds = $studentExam->total_seconds_taken ?? now()->diffInSeconds($studentExam->started_at);
        $speedPerMinute = $timeTakenSeconds > 0
            ? round(($correct / ($timeTakenSeconds / 60)), 2)
            : 0;

        // Calculate sectional analysis
        $sectionalAnalysis = $this->calculateSectionalAnalysis($answers, $exam);

        // Calculate topic analysis
        $topicAnalysis = $this->calculateTopicAnalysis($answers);

        // Calculate difficulty analysis
        $difficultyAnalysis = $this->calculateDifficultyAnalysis($answers);

        // Calculate accuracy by type
        $accuracyByType = $this->calculateAccuracyByType($answers);

        // Calculate accuracy percentage
        $accuracyPercentage = $totalQuestions > 0
            ? round(($correct / $totalQuestions) * 100, 2)
            : 0;

        // Create or update result
        $result = Result::updateOrCreate(
            ['student_exam_id' => $studentExam->id],
            [
                'student_id' => $studentExam->student_id,
                'exam_id' => $exam->id,
                'batch_id' => $studentExam->batch_id,
                'total_marks' => $totalMarks,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $percentage,
                'status' => $status,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correct,
                'incorrect_answers' => $incorrect,
                'unanswered' => $unanswered,
                'marked_for_review' => $markedForReview,
                'time_taken_seconds' => $timeTakenSeconds,
                'speed_per_minute' => $speedPerMinute,
                'sectional_analysis' => $sectionalAnalysis,
                'topic_analysis' => $topicAnalysis,
                'difficulty_analysis' => $difficultyAnalysis,
                'accuracy_by_type' => $accuracyByType,
                'accuracy_percentage' => $accuracyPercentage
            ]
        );

        // Generate rank
        $result->generateRank();

        // Store section-wise results
        $this->storeSectionResults($result, $answers, $exam);

        return $result->fresh();
    }

    /**
     * Calculate sectional analysis
     */
    protected function calculateSectionalAnalysis($answers, $exam)
    {
        $sections = $exam->sections;
        $analysis = [];

        foreach ($sections as $section) {
            $sectionAnswers = $answers->filter(function($answer) use ($section) {
                return $answer->question->subject_id == $section->id;
            });

            if ($sectionAnswers->isEmpty()) {
                continue;
            }

            $total = $sectionAnswers->count();
            $correct = $sectionAnswers->where('is_correct', true)->count();
            $incorrect = $sectionAnswers->where('is_correct', false)
                ->filter(function($a) {
                    return $a->selected_options || $a->answer_text;
                })->count();
            $unanswered = $total - $correct - $incorrect;

            $totalMarks = $sectionAnswers->sum(function($a) {
                return $a->question->marks;
            });
            $obtainedMarks = $sectionAnswers->sum('marks_obtained');

            $analysis[] = [
                'section_id' => $section->id,
                'section_name' => $section->title,
                'total_questions' => $total,
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'total_marks' => $totalMarks,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0,
                'accuracy' => $total > 0 ? round(($correct / $total) * 100, 2) : 0
            ];
        }

        return $analysis;
    }

    /**
     * Calculate topic analysis
     */
    // protected function calculateTopicAnalysis($answers)
    // {
    //     $topics = [];

    //     foreach ($answers as $answer) {
    //         if ($answer->question->tags) {
    //             foreach ($answer->question->tags as $tag) {
    //                 if (!isset($topics[$tag])) {
    //                     $topics[$tag] = [
    //                         'name' => $tag,
    //                         'total' => 0,
    //                         'correct' => 0,
    //                         'incorrect' => 0,
    //                         'unanswered' => 0
    //                     ];
    //                 }

    //                 $topics[$tag]['total']++;
    //                 if ($answer->is_correct) {
    //                     $topics[$tag]['correct']++;
    //                 } elseif ($answer->selected_options || $answer->answer_text) {
    //                     $topics[$tag]['incorrect']++;
    //                 } else {
    //                     $topics[$tag]['unanswered']++;
    //                 }
    //             }
    //         }
    //     }

    //     // Calculate percentages
    //     foreach ($topics as &$topic) {
    //         $topic['accuracy'] = $topic['total'] > 0
    //             ? round(($topic['correct'] / $topic['total']) * 100, 2)
    //             : 0;
    //     }

    //     return array_values($topics);
    // }

/**
 * Calculate topic analysis - FIXED
 */
protected function calculateTopicAnalysis($answers)
{
    $topics = [];

    foreach ($answers as $answer) {
        // Get the question
        $question = $answer->question;
        if (!$question) continue;
        
        // Get tags - handle both string and array formats
        $tags = $question->tags;
        
        // If tags is a JSON string, decode it
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }
        
        // If tags is null or empty, use a default topic name
        if (empty($tags) || !is_array($tags)) {
            $tags = ['General'];
        }
        
        foreach ($tags as $tag) {
            // Skip empty tags
            if (empty($tag) || $tag === '') continue;
            
            $topicKey = trim($tag);
            
            if (!isset($topics[$topicKey])) {
                $topics[$topicKey] = [
                    'name' => $topicKey,
                    'total' => 0,
                    'correct' => 0,
                    'incorrect' => 0,
                    'unanswered' => 0,
                    'accuracy' => 0
                ];
            }

            $topics[$topicKey]['total']++;
            
            // Check if answer is correct
            if ($answer->is_correct) {
                $topics[$topicKey]['correct']++;
            } elseif ($answer->selected_options || $answer->answer_text) {
                $topics[$topicKey]['incorrect']++;
            } else {
                $topics[$topicKey]['unanswered']++;
            }
        }
    }

    // Calculate accuracy and ensure integer values
    foreach ($topics as &$topic) {
        // Ensure values are integers
        $topic['total'] = (int) $topic['total'];
        $topic['correct'] = (int) $topic['correct'];
        $topic['incorrect'] = (int) $topic['incorrect'];
        $topic['unanswered'] = (int) $topic['unanswered'];
        
        // Calculate accuracy
        $topic['accuracy'] = $topic['total'] > 0 
            ? round(($topic['correct'] / $topic['total']) * 100, 2) 
            : 0;
    }

    // Sort by total questions descending
    usort($topics, function($a, $b) {
        return $b['total'] - $a['total'];
    });

    return array_values($topics);
}
    /**
     * Calculate difficulty analysis
     */
    protected function calculateDifficultyAnalysis($answers)
    {
        $difficulties = [
            'easy' => ['total' => 0, 'correct' => 0, 'incorrect' => 0, 'unanswered' => 0],
            'medium' => ['total' => 0, 'correct' => 0, 'incorrect' => 0, 'unanswered' => 0],
            'hard' => ['total' => 0, 'correct' => 0, 'incorrect' => 0, 'unanswered' => 0]
        ];

        foreach ($answers as $answer) {
            $difficulty = $answer->question->difficulty;

            $difficulties[$difficulty]['total']++;

            if ($answer->is_correct) {
                $difficulties[$difficulty]['correct']++;
            } elseif ($answer->selected_options || $answer->answer_text) {
                $difficulties[$difficulty]['incorrect']++;
            } else {
                $difficulties[$difficulty]['unanswered']++;
            }
        }

        // Calculate accuracy for each difficulty
        foreach ($difficulties as $key => &$data) {
            $data['accuracy'] = $data['total'] > 0
                ? round(($data['correct'] / $data['total']) * 100, 2)
                : 0;
        }

        return $difficulties;
    }

    /**
     * Calculate accuracy by question type
     */
    protected function calculateAccuracyByType($answers)
    {
        $types = [];

        foreach ($answers as $answer) {
            $type = $answer->question->type;

            if (!isset($types[$type])) {
                $types[$type] = [
                    'type' => $type,
                    'total' => 0,
                    'correct' => 0,
                    'incorrect' => 0,
                    'unanswered' => 0
                ];
            }

            $types[$type]['total']++;

            if ($answer->is_correct) {
                $types[$type]['correct']++;
            } elseif ($answer->selected_options || $answer->answer_text) {
                $types[$type]['incorrect']++;
            } else {
                $types[$type]['unanswered']++;
            }
        }

        foreach ($types as &$data) {
            $data['accuracy'] = $data['total'] > 0
                ? round(($data['correct'] / $data['total']) * 100, 2)
                : 0;
        }

        return array_values($types);
    }

    /**
     * Store section-wise results
     */
    protected function storeSectionResults($result, $answers, $exam)
    {
        $sections = $exam->sections;

        foreach ($sections as $section) {
            $sectionAnswers = $answers->filter(function($answer) use ($section) {
                return $answer->question->subject_id == $section->id;
            });

            if ($sectionAnswers->isEmpty()) {
                continue;
            }

            $total = $sectionAnswers->count();
            $correct = $sectionAnswers->where('is_correct', true)->count();
            $incorrect = $sectionAnswers->where('is_correct', false)
                ->filter(function($a) {
                    return $a->selected_options || $a->answer_text;
                })->count();
            $unanswered = $total - $correct - $incorrect;

            $totalMarks = $sectionAnswers->sum(function($a) {
                return $a->question->marks;
            });
            $obtainedMarks = $sectionAnswers->sum('marks_obtained');

            $timeSpent = $sectionAnswers->sum('time_taken_seconds');
            $totalTime = $answers->sum('time_taken_seconds');
            $timePercentage = $totalTime > 0 ? round(($timeSpent / $totalTime) * 100, 2) : 0;

            $result->sections()->create([
                'section_id' => $section->id,
                'section_name' => $section->title,
                'total_questions' => $total,
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'total_marks' => $totalMarks,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0,
                'time_spent_seconds' => $timeSpent,
                'time_percentage' => $timePercentage,
                'accuracy' => $total > 0 ? round(($correct / $total) * 100, 2) : 0
            ]);
        }
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
     * Calculate improvement trend
     */
    protected function calculateImprovement($results)
    {
        if ($results->count() < 2) {
            return 0;
        }

        $first = $results->last()->percentage;
        $last = $results->first()->percentage;

        return round($last - $first, 2);
    }

    /**
     * Get batch performance summary
     */
    public function getBatchPerformance($batchId, $examId = null)
    {
        $query = Result::where('batch_id', $batchId);

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        $results = $query->get();

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
                'standard_deviation' => $this->calculateStdDev($scores),
                'pass_rate' => round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
            ],
            'distribution' => [
                '90-100' => $results->where('percentage', '>=', 90)->count(),
                '75-89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
                '60-74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
                '40-59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
                'below-40' => $results->where('percentage', '<', 40)->count()
            ],
            'top_performers' => $results->sortByDesc('percentage')->take(10)->map(function($r) {
                return [
                    'student_name' => $r->student->name,
                    'percentage' => $r->percentage,
                    'rank' => $r->rank
                ];
            })->values()->toArray(),
            'time_analysis' => [
                'average_time' => round($results->avg('time_taken_seconds') / 60, 2),
                'fastest_time' => round($results->min('time_taken_seconds') / 60, 2),
                'slowest_time' => round($results->max('time_taken_seconds') / 60, 2)
            ],
            'question_analysis' => $this->getQuestionAnalysis($examId)
        ];
    }

    /**
     * Get question-wise analysis
     */
    protected function getQuestionAnalysis($examId)
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
                $q['discrimination_index'] = $this->calculateDiscriminationIndex($examId, $q['question_id']);
            }
        }

        return array_values($questions);
    }

    /**
     * Calculate discrimination index for a question
     */
    protected function calculateDiscriminationIndex($examId, $questionId)
    {
        $results = Result::where('exam_id', $examId)
            ->orderBy('percentage', 'desc')
            ->get();

        if ($results->count() < 10) {
            return 0;
        }

        $top27 = $results->take(ceil($results->count() * 0.27));
        $bottom27 = $results->take(-ceil($results->count() * 0.27));

        $topCorrect = 0;
        $bottomCorrect = 0;

        foreach ($top27 as $result) {
            $answer = $result->studentExam->answers()
                ->where('question_id', $questionId)
                ->first();
            if ($answer && $answer->is_correct) {
                $topCorrect++;
            }
        }

        foreach ($bottom27 as $result) {
            $answer = $result->studentExam->answers()
                ->where('question_id', $questionId)
                ->first();
            if ($answer && $answer->is_correct) {
                $bottomCorrect++;
            }
        }

        $discrimination = ($topCorrect - $bottomCorrect) / $top27->count();

        return round($discrimination * 100, 2);
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
}
