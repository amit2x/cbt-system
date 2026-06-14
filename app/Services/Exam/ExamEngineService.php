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
    public function saveAnswer(StudentExam $studentExam, $questionId, $answer, $isMarkedForReview = false)
    {
        try {
            $studentAnswer = $studentExam->answers()
                ->where('question_id', $questionId)
                ->first();

            if (! $studentAnswer) {
                throw new \Exception('Question not found in exam');
            }

            // Calculate time taken for this question
            $timeTaken = $studentAnswer->last_saved_at
                ? now()->diffInSeconds($studentAnswer->last_saved_at)
                : 0;

            // Save answer
            $studentAnswer->saveAnswer($answer, $isMarkedForReview);

            // Update time taken
            if ($timeTaken > 0) {
                $studentAnswer->update([
                    'time_taken_seconds' => ($studentAnswer->time_taken_seconds ?? 0) + $timeTaken,
                ]);
            }

            // Update remaining time in cache
            $this->updateRemainingTime($studentExam);

            // Log answer saved
            $studentExam->log('answer_saved', 'Answer saved for question', [
                'question_id' => $questionId,
                'is_marked_for_review' => $isMarkedForReview,
            ]);

            return $studentAnswer;
        } catch (\Exception $e) {
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
     * Submit exam
     */
    // public function submitExam(StudentExam $studentExam)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Calculate final marks for all answers
    //         $totalMarks = 0;
    //         $correctCount = 0;
    //         $incorrectCount = 0;

    //         foreach ($studentExam->answers as $answer) {
    //             $marks = $answer->calculateMarks();
    //             $totalMarks += $marks;

    //             if ($answer->is_correct) {
    //                 $correctCount++;
    //             } elseif ($answer->selected_options || $answer->answer_text) {
    //                 $incorrectCount++;
    //             }
    //         }

    //         $unansweredCount = $studentExam->total_questions -
    //             ($correctCount + $incorrectCount);

    //         // Update student exam
    //         $studentExam->update([
    //             'submitted_at' => now(),
    //             'status' => 'completed',
    //             'remaining_seconds' => 0,
    //             'total_seconds_taken' => now()->diffInSeconds($studentExam->started_at),
    //             'obtained_marks' => $totalMarks,
    //             'percentage' => $studentExam->exam->total_marks > 0
    //                 ? round(($totalMarks / $studentExam->exam->total_marks) * 100, 2)
    //                 : 0,
    //             'correct_answers' => $correctCount,
    //             'incorrect_answers' => $incorrectCount,
    //             'unanswered' => $unansweredCount
    //         ]);

    //         // Create result record
    //         $result = $studentExam->result()->create([
    //             'student_id' => $studentExam->student_id,
    //             'exam_id' => $studentExam->exam_id,
    //             'total_marks' => $studentExam->exam->total_marks,
    //             'obtained_marks' => $totalMarks,
    //             'percentage' => $studentExam->percentage,
    //             'correct_answers' => $correctCount,
    //             'incorrect_answers' => $incorrectCount,
    //             'unanswered' => $unansweredCount,
    //             'status' => $totalMarks >= ($studentExam->exam->passing_percentage / 100 * $studentExam->exam->total_marks)
    //                 ? 'passed'
    //                 : 'failed'
    //         ]);

    //         // Calculate rank
    //         $this->calculateRank($result);

    //         // Log exam submission
    //         $studentExam->log('exam_submit', 'Exam submitted successfully', [
    //             'obtained_marks' => $totalMarks,
    //             'percentage' => $studentExam->percentage,
    //             'correct' => $correctCount,
    //             'incorrect' => $incorrectCount,
    //             'unanswered' => $unansweredCount
    //         ]);

    //         DB::commit();

    //         return $studentExam->load('result');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
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

            foreach ($answers as $answer) {
                $marks = $answer->calculateMarks();
                $totalMarks += $marks;

                if ($answer->is_correct) {
                    $correctCount++;
                } elseif ($answer->selected_options || $answer->answer_text) {
                    $incorrectCount++;
                }
            }

            $unansweredCount = $studentExam->total_questions - ($correctCount + $incorrectCount);

            // Calculate percentage
            $examTotalMarks = $studentExam->exam->total_marks;
            $percentage = $examTotalMarks > 0
                ? round(($totalMarks / $examTotalMarks) * 100, 2)
                : 0;

            // Determine status
            $status = $percentage >= $studentExam->exam->passing_percentage ? 'passed' : 'failed';

            // Update student exam
            $studentExam->update([
                'submitted_at' => now(),
                'status' => 'completed',
                'remaining_seconds' => 0,
                'total_seconds_taken' => now()->diffInSeconds($studentExam->started_at),
                'obtained_marks' => $totalMarks,
                'percentage' => $percentage,
                'correct_answers' => $correctCount,
                'incorrect_answers' => $incorrectCount,
                'unanswered' => $unansweredCount,
            ]);

            // Create result record with all required fields
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
                'time_taken_seconds' => $studentExam->total_seconds_taken ?? now()->diffInSeconds($studentExam->started_at),
            ]);

            // Calculate rank
            $this->calculateRank($result);

            // Log exam submission
            $studentExam->log('exam_submit', 'Exam submitted successfully', [
                'obtained_marks' => $totalMarks,
                'percentage' => $percentage,
                'correct' => $correctCount,
                'incorrect' => $incorrectCount,
                'unanswered' => $unansweredCount,
            ]);

            DB::commit();

            return $studentExam->fresh()->load('result');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exam submission error: '.$e->getMessage(), [
                'student_exam_id' => $studentExam->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
