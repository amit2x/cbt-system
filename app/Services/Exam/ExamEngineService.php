<?php

namespace App\Services\Exam;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Result;
use App\Models\StudentAnswer;
use App\Models\StudentExam;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamEngineService
{
    /**
     * Start exam for student
     */
    public function startExam(Exam $exam, $studentId, $batchId = null)
    {
        // Check if already started
        $existingExam = StudentExam::where('student_id', $studentId)
            ->where('exam_id', $exam->id)
            ->whereIn('status', ['started', 'in_progress'])
            ->first();

        if ($existingExam) {
            return $existingExam;
        }

        DB::beginTransaction();
        try {
            // Get questions in order
            $questionOrder = $this->getQuestionOrder($exam);

            // Create student exam record
            $studentExam = StudentExam::create([
                'student_id' => $studentId,
                'exam_id' => $exam->id,
                'batch_id' => $batchId,
                'started_at' => now(),
                'remaining_seconds' => $exam->duration_minutes * 60,
                'status' => 'started',
                'question_order' => $questionOrder,
                'total_questions' => count($questionOrder),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'is_proctored' => true,
            ]);

            // Initialize answers for all questions
            foreach ($questionOrder as $questionId) {
                StudentAnswer::create([
                    'student_exam_id' => $studentExam->id,
                    'question_id' => $questionId,
                    'is_visited' => false,
                    'is_marked_for_review' => false,
                    'last_saved_at' => now(),
                    'save_count' => 0,
                ]);
            }

            // Log exam start
            $studentExam->log('exam_start', 'Student started exam');

            DB::commit();

            return $studentExam->load(['exam', 'exam.questions']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get question order based on exam settings
     */
    protected function getQuestionOrder(Exam $exam)
    {
        $questions = $exam->questions()->pluck('questions.id')->toArray();

        if ($exam->randomize_questions) {
            shuffle($questions);
        }

        return $questions;
    }

    /**
     * Get current exam state
     */
    public function getExamState(StudentExam $studentExam)
    {
        $answers = $studentExam->answers()->with('question')->get();

        $questions = [];
        foreach ($studentExam->question_order as $index => $questionId) {
            $answer = $answers->firstWhere('question_id', $questionId);
            $question = $answer->question;

            $questions[] = [
                'id' => $questionId,
                'index' => $index,
                'text' => $question->question_text,
                'type' => $question->type,
                'options' => $question->options->map(function ($opt) use ($answer) {
                    return [
                        'id' => $opt->id,
                        'text' => $opt->option_text,
                        'order' => $opt->order,
                        'selected' => $answer && in_array($opt->id, $answer->selected_options ?? []),
                    ];
                }),
                'answer' => $answer->answer_text,
                'is_visited' => $answer->is_visited,
                'is_marked_for_review' => $answer->is_marked_for_review,
                'time_taken' => $answer->time_taken_seconds,
            ];
        }

        return [
            'exam' => [
                'id' => $studentExam->exam->id,
                'title' => $studentExam->exam->title,
                'duration' => $studentExam->exam->duration_minutes,
                'total_marks' => $studentExam->exam->total_marks,
            ],
            'student_exam' => [
                'id' => $studentExam->id,
                'remaining_seconds' => $studentExam->remaining_seconds,
                'status' => $studentExam->status,
                'started_at' => $studentExam->started_at,
                'answered_count' => $studentExam->answered_questions,
                'marked_count' => $studentExam->marked_for_review_count,
                'not_visited_count' => $studentExam->not_visited_count,
                'total_questions' => $studentExam->total_questions,
            ],
            'questions' => $questions,
            'question_order' => $studentExam->question_order,
        ];
    }

    /**
     * Save answer
     */
    // public function saveAnswer(StudentExam $studentExam, $questionId, $answer, $isMarkedForReview = false)
    // {
    //     try {
    //         $studentAnswer = $studentExam->answers()
    //             ->where('question_id', $questionId)
    //             ->first();

    //         if (! $studentAnswer) {
    //             throw new \Exception('Question not found in exam');
    //         }

    //         // Calculate time taken for this question
    //         $timeTaken = $studentAnswer->last_saved_at
    //             ? now()->diffInSeconds($studentAnswer->last_saved_at)
    //             : 0;

    //         // Save answer
    //         $studentAnswer->saveAnswer($answer, $isMarkedForReview);

    //         // Update time taken
    //         if ($timeTaken > 0) {
    //             $studentAnswer->update([
    //                 'time_taken_seconds' => ($studentAnswer->time_taken_seconds ?? 0) + $timeTaken,
    //             ]);
    //         }

    //         // Update remaining time in cache
    //         $this->updateRemainingTime($studentExam);

    //         // Log answer saved
    //         $studentExam->log('answer_saved', 'Answer saved for question', [
    //             'question_id' => $questionId,
    //             'is_marked_for_review' => $isMarkedForReview,
    //         ]);

    //         return $studentAnswer;
    //     } catch (\Exception $e) {
    //         throw $e;
    //     }
    // }
    
    public function saveAnswer(StudentExam $studentExam, $questionId, $answer, $isMarkedForReview = false)
{
    try {
        $studentAnswer = $studentExam->answers()
            ->where('question_id', $questionId)
            ->first();

        if (! $studentAnswer) {
            \Log::error("Exam Error: Question {$questionId} not found in exam {$studentExam->id}");
            throw new \Exception('Question not found in exam');
        }

        // Log initial state for debugging
        \Log::info("Debug SaveAnswer Start:", [
            'student_answer_id' => $studentAnswer->id,
            'db_last_saved_at' => $studentAnswer->last_saved_at,
            'db_time_taken_seconds' => $studentAnswer->time_taken_seconds
        ]);

        // Delegate all saving and time calculations to the model method to avoid duplicate calculations
        $studentAnswer->saveAnswer($answer, $isMarkedForReview);

        // Update remaining time in cache
        $this->updateRemainingTime($studentExam);

        // Log answer saved
        $studentExam->log('answer_saved', 'Answer saved for question', [
            'question_id' => $questionId,
            'is_marked_for_review' => $isMarkedForReview,
        ]);

        return $studentAnswer;
    } catch (\Exception $e) {
        \Log::error("Exception in saveAnswer: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
}



    /**
     * Mark question for review
     */
    public function markForReview(StudentExam $studentExam, $questionId)
    {
        $studentAnswer = $studentExam->answers()
            ->where('question_id', $questionId)
            ->first();

        if ($studentAnswer) {
            $studentAnswer->markForReview();

            $studentExam->log('info', 'Question marked for review', [
                'question_id' => $questionId,
            ]);
        }

        return $studentExam->fresh();
    }

    /**
     * Navigate to question
     */
    public function navigateToQuestion(StudentExam $studentExam, $questionId)
    {
        $studentAnswer = $studentExam->answers()
            ->where('question_id', $questionId)
            ->first();

        if ($studentAnswer && ! $studentAnswer->is_visited) {
            $studentAnswer->update(['is_visited' => true]);
            $studentExam->updateState();

            $studentExam->log('question_navigation', 'Navigated to question', [
                'question_id' => $questionId,
            ]);
        }

        return $studentExam->fresh();
    }


/**
 * Working Submit exam
 */
// public function submitExam(StudentExam $studentExam)
// {
//     DB::beginTransaction();
//     try {
//         // Calculate final marks for all answers
//         $totalMarks = 0;
//         $correctCount = 0;
//         $incorrectCount = 0;
//         $unansweredCount = 0;
        
//         $answers = $studentExam->answers()->with('question')->get();
        
//         // Log for debugging
//         \Log::info('Submitting exam', [
//             'student_exam_id' => $studentExam->id,
//             'total_questions' => $studentExam->total_questions,
//             'answers_count' => $answers->count()
//         ]);
        
//         foreach ($answers as $answer) {
//             // Calculate marks for each answer
//             $marks = $answer->calculateMarks();
//             $totalMarks += $marks;
            
//             if ($answer->is_correct === true) {
//                 $correctCount++;
//             } elseif ($answer->is_correct === false) {
//                 $incorrectCount++;
//             }
//         }

//         // Calculate unanswered questions
//         $answeredCount = $correctCount + $incorrectCount;
//         $unansweredCount = $studentExam->total_questions - $answeredCount;

//         // Get exam total marks using the model method
//         $examTotalMarks = $studentExam->exam->total_marks;      
        
//         // Calculate percentage
//         $percentage = $examTotalMarks > 0 
//             ? round(($totalMarks / $examTotalMarks) * 100, 2)
//             : 0;

//         // Determine status
//         $passingPercentage = $studentExam->exam->passing_percentage ?? 40;
//         $status = $percentage >= $passingPercentage ? 'passed' : 'failed';

//         // 1. Parse started_at safely using Carbon
//         $startedAt = $studentExam->started_at;
//         Log::info('Exam was Started at: ' . $startedAt);
        
//         if (is_string($startedAt)) {
//             $startedAt = \Carbon\Carbon::parse($startedAt);
//         }
        
//         // 2. Parse submitted_at safely (Fallback to now)
//         $submittedAt = $studentExam->submitted_at;
//         if (is_string($submittedAt)) {
//             $submittedAt = \Carbon\Carbon::parse($submittedAt);
//         } else {
//             $submittedAt = $submittedAt ?? now();
//         }
//         Log::info('Exam Submitted at: ' . $submittedAt);
        
//         // 3. FIX: Explicitly pass true as the 2nd argument to diffInSeconds to force absolute (positive) value
//         $timeTakenSeconds = $startedAt->diffInSeconds($submittedAt, true);
        
//         // Fallback logic if the difference evaluates to 0
//         if ($timeTakenSeconds == 0 && $studentExam->remaining_seconds > 0) {
//             $examDuration = $studentExam->exam->duration_minutes * 60;
//             $timeTakenSeconds = max(0, $examDuration - $studentExam->remaining_seconds);
//         }
        
//         // Force integer cast to prevent floating-point decimals in your log
//         $timeTakenSeconds = (int) max(0, $timeTakenSeconds);
        
//         Log::info('Total Time Taken: ' . $timeTakenSeconds);
        
//         // dd($timeTakenSeconds);
        
        

//         // Calculate Speed Per Minute
//         // Speed = (Correct Answers + Incorrect Answers) / Time Taken in Minutes
//         $totalAttempted = $correctCount + $incorrectCount;
//         $timeTakenMinutes = $timeTakenSeconds > 0 ? $timeTakenSeconds / 60 : 1; // Avoid division by zero
//         $speedPerMinute = round($totalAttempted / $timeTakenMinutes, 2);
    
    
        
//         // Log the calculated values
//         \Log::info('Calculated exam values', [
//             'examTotalMarks' => $examTotalMarks,
//             'totalMarks' => $totalMarks,
//             'percentage' => $percentage,
//             'timeTakenSeconds' => $timeTakenSeconds,
//             'totalAttempted' => $totalAttempted,
//             'speedPerMinute' => $speedPerMinute,
//             'correctCount' => $correctCount,
//             'incorrectCount' => $incorrectCount,
//             'unansweredCount' => $unansweredCount
//         ]);

//         // Update student exam - IMPORTANT: Use the submitted_at we calculated
//         $studentExam->update([
//             'submitted_at' => $submittedAt,
//             'status' => 'completed',
//             'remaining_seconds' => 0,
//             'total_seconds_taken' => $timeTakenSeconds,
//             'obtained_marks' => $totalMarks,
//             'percentage' => $percentage,
//             'correct_answers' => $correctCount,
//             'incorrect_answers' => $incorrectCount,
//             'unanswered' => $unansweredCount
//         ]);

//         // Create result record
//         $result = Result::create([
//             'student_exam_id' => $studentExam->id,
//             'student_id' => $studentExam->student_id,
//             'exam_id' => $studentExam->exam_id,
//             'batch_id' => $studentExam->batch_id,
//             'total_questions' => $studentExam->total_questions,
//             'total_marks' => $examTotalMarks,
//             'obtained_marks' => $totalMarks,
//             'percentage' => $percentage,
//             'status' => $status,
//             'correct_answers' => $correctCount,
//             'incorrect_answers' => $incorrectCount,
//             'unanswered' => $unansweredCount,
//             'time_taken_seconds' => $timeTakenSeconds,
//             'speed_per_minute' => $speedPerMinute
//         ]);

//         // Calculate rank
//         $this->calculateRank($result);

//         // Log exam submission
//         \Log::info('Exam submitted successfully', [
//             'student_exam_id' => $studentExam->id,
//             'total_marks' => $totalMarks,
//             'exam_total_marks' => $examTotalMarks,
//             'percentage' => $percentage,
//             'status' => $status,
//             'correct' => $correctCount,
//             'incorrect' => $incorrectCount,
//             'unanswered' => $unansweredCount,
//             'time_taken' => $timeTakenSeconds,
//             'speed_per_minute' => $speedPerMinute

//         ]);

//         DB::commit();
        
//         return $studentExam->fresh()->load('result');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('Exam submission error: ' . $e->getMessage(), [
//             'student_exam_id' => $studentExam->id,
//             'trace' => $e->getTraceAsString()
//         ]);
//         throw $e;
//     }
// }

/**
 * Submit exam
 */
public function submitExam(StudentExam $studentExam)
{
    DB::beginTransaction();
    try {
        // Calculate final marks for all answers
        $totalMarks = 0;
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        
        $answers = $studentExam->answers()->with('question')->get();
        
        // Arrays for analytics
        $sectionalData = [];
        $topicData = [];
        $difficultyData = [
            'easy' => ['total' => 0, 'correct' => 0, 'incorrect' => 0, 'unanswered' => 0],
            'medium' => ['total' => 0, 'correct' => 0, 'incorrect' => 0, 'unanswered' => 0],
            'hard' => ['total' => 0, 'correct' => 0, 'incorrect' => 0, 'unanswered' => 0]
        ];
        $typeData = [
            'single_choice' => ['total' => 0, 'correct' => 0, 'incorrect' => 0],
            'multiple_choice' => ['total' => 0, 'correct' => 0, 'incorrect' => 0],
            'numerical' => ['total' => 0, 'correct' => 0, 'incorrect' => 0]
        ];
        $questionTimeData = [];
        
        foreach ($answers as $answer) {
            // Calculate marks for each answer
            $marks = $answer->calculateMarks();
            $totalMarks += $marks;
            
            $question = $answer->question;
            
            // Track question time
            $questionTimeData[] = [
                'question_id' => $question->id,
                'time_taken' => $answer->time_taken_seconds ?? 0,
                'is_correct' => $answer->is_correct
            ];
            
            // Count correct/incorrect
            if ($answer->is_correct === true) {
                $correctCount++;
            } elseif ($answer->is_correct === false) {
                $incorrectCount++;
            }
            
            // Sectional Analysis (by subject/module)
            if ($question->subject_id) {
                $subjectName = $question->subject->title ?? 'Unknown';
                if (!isset($sectionalData[$subjectName])) {
                    $sectionalData[$subjectName] = [
                        'total' => 0,
                        'correct' => 0,
                        'incorrect' => 0,
                        'unanswered' => 0
                    ];
                }
                $sectionalData[$subjectName]['total']++;
                if ($answer->is_correct === true) {
                    $sectionalData[$subjectName]['correct']++;
                } elseif ($answer->is_correct === false) {
                    $sectionalData[$subjectName]['incorrect']++;
                } else {
                    $sectionalData[$subjectName]['unanswered']++;
                }
            }
            
            // Difficulty Analysis
            $difficulty = $question->difficulty ?? 'medium';
            $difficultyData[$difficulty]['total']++;
            if ($answer->is_correct === true) {
                $difficultyData[$difficulty]['correct']++;
            } elseif ($answer->is_correct === false) {
                $difficultyData[$difficulty]['incorrect']++;
            } else {
                $difficultyData[$difficulty]['unanswered']++;
            }
            
            // Type Analysis
            $type = $question->type ?? 'single_choice';
            $typeData[$type]['total']++;
            if ($answer->is_correct === true) {
                $typeData[$type]['correct']++;
            } elseif ($answer->is_correct === false) {
                $typeData[$type]['incorrect']++;
            }
            
            // Topic Analysis (from tags)
            if ($question->tags) {
                foreach ($question->tags as $tag) {
                    if (!isset($topicData[$tag])) {
                        $topicData[$tag] = [
                            'total' => 0,
                            'correct' => 0,
                            'incorrect' => 0
                        ];
                    }
                    $topicData[$tag]['total']++;
                    if ($answer->is_correct === true) {
                        $topicData[$tag]['correct']++;
                    } elseif ($answer->is_correct === false) {
                        $topicData[$tag]['incorrect']++;
                    }
                }
            }
        }

        // Calculate unanswered questions
        $answeredCount = $correctCount + $incorrectCount;
        $unansweredCount = $studentExam->total_questions - $answeredCount;

        // Get exam total marks
        $examTotalMarks = $studentExam->exam->total_marks;      
        
        // Calculate percentage
        $percentage = $examTotalMarks > 0 
            ? round(($totalMarks / $examTotalMarks) * 100, 2)
            : 0;

        // Determine status
        $passingPercentage = $studentExam->exam->passing_percentage ?? 40;
        $status = $percentage >= $passingPercentage ? 'passed' : 'failed';

        // Calculate time taken
        $startedAt = $studentExam->started_at;
        if (is_string($startedAt)) {
            $startedAt = \Carbon\Carbon::parse($startedAt);
        }

        $submittedAt = $studentExam->submitted_at;
        if (is_string($submittedAt)) {
            $submittedAt = \Carbon\Carbon::parse($submittedAt);
        } else {
            $submittedAt = $submittedAt ?? now();
        }

        $timeTakenSeconds = $startedAt->diffInSeconds($submittedAt, true);
        
        if ($timeTakenSeconds == 0 && $studentExam->remaining_seconds > 0) {
            $examDuration = $studentExam->exam->duration_minutes * 60;
            $timeTakenSeconds = max(0, $examDuration - $studentExam->remaining_seconds);
        }

        $timeTakenSeconds = (int) max(0, $timeTakenSeconds);

        // Calculate Speed Per Minute
        $totalAttempted = $correctCount + $incorrectCount;
        $timeTakenMinutes = $timeTakenSeconds > 0 ? $timeTakenSeconds / 60 : 1;
        $speedPerMinute = round($totalAttempted / $timeTakenMinutes, 2);

        // Calculate Accuracy Percentage
        $accuracyPercentage = $answeredCount > 0 
            ? round(($correctCount / $answeredCount) * 100, 2)
            : 0;

        // Calculate Consistency Score (based on section performance)
        $consistencyScore = $this->calculateConsistencyScore($sectionalData);

        // Calculate Speed Score (based on average time per question)
        $speedScore = $this->calculateSpeedScore($questionTimeData);

        // Log the calculated values
        \Log::info('Calculated exam values', [
            'examTotalMarks' => $examTotalMarks,
            'totalMarks' => $totalMarks,
            'percentage' => $percentage,
            'timeTakenSeconds' => $timeTakenSeconds,
            'speedPerMinute' => $speedPerMinute,
            'accuracyPercentage' => $accuracyPercentage,
            'consistencyScore' => $consistencyScore,
            'speedScore' => $speedScore
        ]);

        // Update student exam
        $studentExam->update([
            'submitted_at' => $submittedAt,
            'status' => 'completed',
            'remaining_seconds' => 0,
            'total_seconds_taken' => $timeTakenSeconds,
            'obtained_marks' => $totalMarks,
            'percentage' => $percentage,
            'correct_answers' => $correctCount,
            'incorrect_answers' => $incorrectCount,
            'unanswered' => $unansweredCount
        ]);

        // Create result record with all analytics
        $result = Result::create([
            'student_exam_id' => $studentExam->id,
            'student_id' => $studentExam->student_id,
            'exam_id' => $studentExam->exam_id,
            'batch_id' => $studentExam->batch_id,
            'total_questions' => $studentExam->total_questions,
            'total_marks' => $examTotalMarks,
            'obtained_marks' => $totalMarks,
            'percentage' => $percentage,
            'status' => $status,
            'correct_answers' => $correctCount,
            'incorrect_answers' => $incorrectCount,
            'unanswered' => $unansweredCount,
            'marked_for_review' => $answers->where('is_marked_for_review', true)->count(),
            'time_taken_seconds' => $timeTakenSeconds,
            'speed_per_minute' => $speedPerMinute,
            'accuracy_percentage' => $accuracyPercentage,
            'consistency_score' => $consistencyScore,
            'speed_score' => $speedScore,
            'sectional_analysis' => $sectionalData,
            'topic_analysis' => $topicData,
            'difficulty_analysis' => $difficultyData,
            'accuracy_by_type' => $typeData
        ]);

        // Calculate rank
        $this->calculateRank($result);

        // Log exam submission
        \Log::info('Exam submitted successfully', [
            'student_exam_id' => $studentExam->id,
            'total_marks' => $totalMarks,
            'exam_total_marks' => $examTotalMarks,
            'percentage' => $percentage,
            'status' => $status,
            'correct' => $correctCount,
            'incorrect' => $incorrectCount,
            'unanswered' => $unansweredCount,
            'time_taken' => $timeTakenSeconds,
            'speed_per_minute' => $speedPerMinute
        ]);

        DB::commit();
        
        return $studentExam->fresh()->load('result');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Exam submission error: ' . $e->getMessage(), [
            'student_exam_id' => $studentExam->id,
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

/**
 * Calculate consistency score (0-100)
 */
protected function calculateConsistencyScore($sectionalData)
{
    if (empty($sectionalData) || count($sectionalData) < 2) {
        return 100;
    }
    
    $scores = [];
    foreach ($sectionalData as $section) {
        $total = $section['total'];
        if ($total > 0) {
            $accuracy = round(($section['correct'] / $total) * 100, 2);
            $scores[] = $accuracy;
        }
    }
    
    if (count($scores) < 2) return 100;
    
    // Calculate standard deviation of section scores
    $mean = array_sum($scores) / count($scores);
    $variance = 0;
    foreach ($scores as $score) {
        $variance += pow($score - $mean, 2);
    }
    $stdDev = sqrt($variance / count($scores));
    
    // Convert to consistency score (100 - normalized std dev)
    $maxStdDev = 50; // Maximum possible std dev
    $consistency = 100 - min(100, ($stdDev / $maxStdDev) * 100);
    return round($consistency, 2);
}

/**
 * Calculate speed score (0-100)
 */
protected function calculateSpeedScore($questionTimeData)
{
    if (empty($questionTimeData)) return 0;
    
    $times = array_column($questionTimeData, 'time_taken');
    $avgTime = array_sum($times) / count($times);
    
    // Average time should ideally be 30-60 seconds per question
    $idealTime = 45; // 45 seconds per question is ideal
    $timeRatio = $avgTime > 0 ? $idealTime / $avgTime : 0;
    
    // Speed score: 100 if faster than ideal, 50 if at ideal, 0 if very slow
    $speedScore = min(100, $timeRatio * 100);
    return round($speedScore, 2);
}

    /**
     * Auto-submit expired exams
     */
    public function autoSubmitExpiredExams()
    {
        $expiredExams = StudentExam::where('status', 'started')
            ->where('remaining_seconds', '<=', 0)
            ->orWhere(function ($query) {
                $query->whereHas('exam', function ($q) {
                    $q->where('is_scheduled', true)
                        ->where('end_time', '<', now());
                })->whereIn('status', ['started', 'in_progress']);
            })
            ->get();

        foreach ($expiredExams as $studentExam) {
            $this->submitExam($studentExam);
            $studentExam->log('exam_expire', 'Exam auto-submitted due to time expiration');
        }

        return $expiredExams->count();
    }

    /**
     * Update remaining time
     */
    protected function updateRemainingTime(StudentExam $studentExam)
    {
        $elapsedSeconds = now()->diffInSeconds($studentExam->started_at);
        $remainingSeconds = max(0, ($studentExam->exam->duration_minutes * 60) - $elapsedSeconds);

        $studentExam->update(['remaining_seconds' => $remainingSeconds]);

        return $remainingSeconds;
    }

    /**
     * Calculate rank for result
     */
    protected function calculateRank($result)
    {
        $betterResults = DB::table('results')
            ->where('exam_id', $result->exam_id)
            ->where('obtained_marks', '>', $result->obtained_marks)
            ->count();

        $rank = $betterResults + 1;

        $result->update(['rank' => $rank]);

        return $rank;
    }

    /**
     * Validate exam access
     */
    public function validateExamAccess(StudentExam $studentExam)
    {
        // Check if exam is already submitted
        if ($studentExam->status === 'completed') {
            return [
                'allowed' => false,
                'reason' => 'Exam already submitted',
            ];
        }

        // Check if exam is expired
        if ($studentExam->status === 'expired') {
            return [
                'allowed' => false,
                'reason' => 'Exam has expired',
            ];
        }

        // Check remaining time
        if ($studentExam->remaining_seconds <= 0) {
            return [
                'allowed' => false,
                'reason' => 'Time is up',
            ];
        }

        // Check if exam is still available (for scheduled exams)
        if ($studentExam->exam->is_scheduled) {
            if (now() < $studentExam->exam->start_time) {
                return [
                    'allowed' => false,
                    'reason' => 'Exam has not started yet',
                ];
            }

            if (now() > $studentExam->exam->end_time) {
                return [
                    'allowed' => false,
                    'reason' => 'Exam has ended',
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Record tab switch
     */
    public function recordTabSwitch(StudentExam $studentExam)
    {
        $studentExam->recordTabSwitch();

        // Check if exceeded limit
        if ($studentExam->tab_switch_count >= 5) {
            // Log warning but don't terminate automatically
            $studentExam->log('security', 'Excessive tab switching detected', [
                'count' => $studentExam->tab_switch_count,
            ]);
        }

        return $studentExam;
    }

    /**
     * Get exam summary for student
     */
    public function getExamSummary(StudentExam $studentExam)
    {
        $answers = $studentExam->answers()->with('question')->get();

        $sectionAnalysis = [];
        $topicAnalysis = [];

        foreach ($answers as $answer) {
            $question = $answer->question;

            // Section analysis
            if ($question->subject_id) {
                if (! isset($sectionAnalysis[$question->subject_id])) {
                    $sectionAnalysis[$question->subject_id] = [
                        'name' => $question->subject->title,
                        'total' => 0,
                        'correct' => 0,
                        'incorrect' => 0,
                        'unanswered' => 0,
                    ];
                }

                $sectionAnalysis[$question->subject_id]['total']++;

                if ($answer->is_correct) {
                    $sectionAnalysis[$question->subject_id]['correct']++;
                } elseif ($answer->selected_options || $answer->answer_text) {
                    $sectionAnalysis[$question->subject_id]['incorrect']++;
                } else {
                    $sectionAnalysis[$question->subject_id]['unanswered']++;
                }
            }

            // Topic analysis (based on tags)
            if ($question->tags) {
                foreach ($question->tags as $tag) {
                    if (! isset($topicAnalysis[$tag])) {
                        $topicAnalysis[$tag] = [
                            'name' => $tag,
                            'total' => 0,
                            'correct' => 0,
                        ];
                    }

                    $topicAnalysis[$tag]['total']++;
                    if ($answer->is_correct) {
                        $topicAnalysis[$tag]['correct']++;
                    }
                }
            }
        }

        return [
            'overall' => [
                'total_questions' => $studentExam->total_questions,
                'answered' => $studentExam->answered_questions,
                'correct' => $studentExam->correct_answers,
                'incorrect' => $studentExam->incorrect_answers,
                'unanswered' => $studentExam->unanswered,
                'marks_obtained' => $studentExam->obtained_marks,
                'total_marks' => $studentExam->exam->total_marks,
                'percentage' => $studentExam->percentage,
                'time_taken' => $studentExam->total_seconds_taken,
            ],
            'section_analysis' => array_values($sectionAnalysis),
            'topic_analysis' => array_values($topicAnalysis),
        ];
    }
}
