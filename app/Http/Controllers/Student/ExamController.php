<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\StudentExam;
use App\Services\Exam\ExamEngineService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    use ApiResponse;

    protected $examEngine;

    public function __construct(ExamEngineService $examEngine)
    {
        $this->examEngine = $examEngine;
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    /**
     * List available exams for student
     */
    public function index()
    {
        $student = auth()->user();

        // Get exams from student's batches
        $batchIds = $student->batches()
            ->wherePivot('status', 'active')
            ->pluck('batches.id');

        $exams = Exam::whereIn('batch_id', $batchIds)
            ->orWhereHas('batches', function ($q) use ($batchIds) {
                $q->whereIn('batches.id', $batchIds);
            })
            ->where('status', 'published')
            ->with(['course', 'batch'])
            ->get();

        // Get student's exam attempts
        $attemptedExams = StudentExam::where('student_id', $student->id)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->get()
            ->keyBy('exam_id');

        return view('student.exams.index', compact('exams', 'attemptedExams'));
    }

    /**
     * Show exam instructions
     */
    public function instructions(Exam $exam)
    {
        $student = auth()->user();

        // Check if student can take exam
        if (! $exam->canBeTakenBy($student->id)) {
            return redirect()->route('student.exams.index')
                ->with('error', 'You cannot take this exam. It may not be available or you have exhausted your attempts.');
        }

        // Check previous attempts
        $attempts = StudentExam::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->count();

        return view('student.exams.instructions', compact('exam', 'attempts'));
    }

    /**
     * Start exam
     */
    public function start(Request $request, Exam $exam)
    {
        try {
            $student = auth()->user();

            // Check if student can take exam
            if (! $exam->canBeTakenBy($student->id)) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'You cannot take this exam.');
            }

            // Get batch ID
            $batchId = null;
            if ($exam->batch_id) {
                // Check if student belongs to this batch
                $belongsToBatch = $student->batches()
                    ->where('batches.id', $exam->batch_id)
                    ->wherePivot('status', 'active')
                    ->exists();

                if (! $belongsToBatch) {
                    return redirect()->route('student.exams.index')
                        ->with('error', 'You are not enrolled in the batch for this exam.');
                }
                $batchId = $exam->batch_id;
            }

            // Check if already have an active exam session
            $existingExam = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->whereIn('status', ['started', 'in_progress'])
                ->first();

            if ($existingExam) {
                return redirect()->route('student.exam.take', $existingExam->id)
                    ->with('warning', 'You already have an ongoing exam session. Continuing from where you left off.');
            }

            // Start new exam
            $studentExam = $this->examEngine->startExam($exam, $student->id, $batchId);

            if (! $studentExam) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Failed to start exam. Please try again.');
            }

            return redirect()->route('student.exam.take', $studentExam->id)
                ->with('success', 'Exam started successfully. Good luck!');

        } catch (\Exception $e) {
            Log::error('Exam start error: '.$e->getMessage(), [
                'exam_id' => $exam->id,
                'student_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('student.exams.index')
                ->with('error', 'Error starting exam: '.$e->getMessage());
        }
    }

    /**
     * Take exam
     */
    /**
     * Take exam
     */
    public function take($studentExamId)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->with(['exam', 'exam.questions'])
                ->find($studentExamId);

            if (! $studentExam) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Exam session not found.');
            }

            // Validate exam access
            $access = $this->examEngine->validateExamAccess($studentExam);
            if (! $access['allowed']) {
                return redirect()->route('student.exams.index')
                    ->with('error', $access['reason']);
            }

            // Get questions in order
            $questionOrder = $studentExam->question_order ??
                            $studentExam->exam->questions()->orderBy('order')->pluck('questions.id')->toArray();

            if (empty($questionOrder)) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'No questions found for this exam.');
            }

            $questions = Question::whereIn('id', $questionOrder)
                ->with('options')
                ->get()
                ->keyBy('id');

            // Get saved answers
            $answers = $studentExam->answers()->get()->keyBy('question_id');

            return view('student.exams.take', compact('studentExam', 'questions', 'answers', 'questionOrder'));

        } catch (\Exception $e) {
            Log::error('Take exam error: '.$e->getMessage(), [
                'student_exam_id' => $studentExamId,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('student.exams.index')
                ->with('error', 'Error loading exam: '.$e->getMessage());
        }
    }

    /**
     * Start exam
     */
    // public function start(Request $request, Exam $exam)
    // {
    //     try {
    //         $student = auth()->user();

    //         // Get batch ID if student is in multiple batches for this exam
    //         $batchId = null;
    //         if ($exam->batch_id) {
    //             $batchId = $exam->batch_id;
    //         } else {
    //             $batch = $student->batches()
    //                 ->whereIn('batches.id', $exam->batches->pluck('id'))
    //                 ->first();
    //             $batchId = $batch->id ?? null;
    //         }

    //         $studentExam = $this->examEngine->startExam($exam, $student->id, $batchId);

    //         if ($request->wantsJson()) {
    //             return $this->successResponse([
    //                 'student_exam_id' => $studentExam->id,
    //                 'redirect' => route('student.exams.take', $studentExam->id)
    //             ], 'Exam started successfully');
    //         }

    //         return redirect()->route('student.exams.take', $studentExam->id);
    //     } catch (\Exception $e) {
    //         dd($e->getMessage());
    //         return redirect()->back()->with('error', 'Error starting exam: ' . $e->getMessage());
    //     }
    // }

    /**
     * Take exam
     */
    // public function take($studentExamId)
    // {
    //     $studentExam = StudentExam::with(['exam', 'exam.questions', 'answers'])
    //         ->where('student_id', auth()->id())
    //         ->findOrFail($studentExamId);

    //     // Validate exam access
    //     $access = $this->examEngine->validateExamAccess($studentExam);
    //     if (!$access['allowed']) {
    //         return redirect()->route('student.exams.result', $studentExamId)
    //             ->with('error', $access['reason']);
    //     }

    //     // Get exam state
    //     $state = $this->examEngine->getExamState($studentExam);

    //     return view('student.exams.take', compact('state'));
    // }

    /**
     * Get question via AJAX
     */
    // public function getQuestion(Request $request, $studentExamId, $questionId)
    // {
    //     try {
    //         $studentExam = StudentExam::where('student_id', auth()->id())
    //             ->findOrFail($studentExamId);

    //         // Navigate to question
    //         $this->examEngine->navigateToQuestion($studentExam, $questionId);

    //         $answer = $studentExam->answers()
    //             ->where('question_id', $questionId)
    //             ->with('question.options')
    //             ->first();

    //         if (! $answer) {
    //             return $this->errorResponse('Question not found', 404);
    //         }

    //         $question = $answer->question;

    //         // Randomize options if enabled
    //         $options = $question->options;
    //         if ($studentExam->exam->randomize_options) {
    //             $options = $options->shuffle();
    //         }

    //         return $this->successResponse([
    //             'id' => $question->id,
    //             'text' => $question->question_text,
    //             'type' => $question->type,
    //             'options' => $options->map(function ($opt) use ($answer) {
    //                 return [
    //                     'id' => $opt->id,
    //                     'text' => $opt->option_text,
    //                     'order' => $opt->order,
    //                     'selected' => $answer->selected_options &&
    //                         in_array($opt->id, $answer->selected_options),
    //                 ];
    //             }),
    //             'answer' => $answer->answer_text,
    //             'is_marked_for_review' => $answer->is_marked_for_review,
    //             'explanation' => $question->explanation,
    //             'marks' => $question->marks,
    //             'negative_marks' => $question->negative_marks,
    //         ], 'Question loaded');
    //     } catch (\Exception $e) {
    //         return $this->errorResponse($e->getMessage(), 500);
    //     }
    // }

    /**
     * Get question via AJAX
     */
    public function getQuestion(Request $request, $studentExam, $question)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->findOrFail($studentExam);

            $answer = $studentExam->answers()
                ->where('question_id', $question)
                ->with('question.options')
                ->first();

            if (! $answer) {
                return $this->errorResponse('Question not found', 404);
            }

            $question = $answer->question;

            // Randomize options if enabled
            $options = $question->options;
            if ($studentExam->exam->randomize_options) {
                $options = $options->shuffle();
            }

            return $this->successResponse([
                'id' => $question->id,
                'text' => $question->question_text,
                'type' => $question->type,
                'options' => $options->map(function ($opt) use ($answer) {
                    return [
                        'id' => $opt->id,
                        'text' => $opt->option_text,
                        'order' => $opt->order,
                        'selected' => $answer->selected_options &&
                            in_array($opt->id, $answer->selected_options),
                    ];
                }),
                'answer' => $answer->answer_text,
                'is_marked_for_review' => $answer->is_marked_for_review,
                'explanation' => $question->explanation,
                'marks' => $question->marks,
                'negative_marks' => $question->negative_marks,
            ], 'Question loaded');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Save answer via AJAX
     */
    public function saveAnswer(Request $request, $studentExamId)
    {
        try {
            $request->validate([
                'question_id' => 'required|exists:questions,id',
                'answer' => 'nullable',
                'is_marked_for_review' => 'boolean',
            ]);

            $studentExam = StudentExam::where('student_id', auth()->id())
                ->whereIn('status', ['started', 'in_progress'])
                ->findOrFail($studentExamId);

            $answer = $this->examEngine->saveAnswer(
                $studentExam,
                $request->question_id,
                $request->answer,
                $request->boolean('is_marked_for_review')
            );

            return $this->successResponse([
                'answered_count' => $studentExam->fresh()->answered_questions,
                'marked_count' => $studentExam->fresh()->marked_for_review_count,
                'remaining_seconds' => $studentExam->fresh()->remaining_seconds,
            ], 'Answer saved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Mark for review
     */
    public function markForReview(Request $request, $studentExamId)
    {
        try {
            $request->validate([
                'question_id' => 'required|exists:questions,id',
            ]);

            $studentExam = StudentExam::where('student_id', auth()->id())
                ->findOrFail($studentExamId);

            $this->examEngine->markForReview($studentExam, $request->question_id);

            return $this->successResponse([
                'marked_count' => $studentExam->fresh()->marked_for_review_count,
            ], 'Question '.($request->mark ? 'marked' : 'unmarked').' for review');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Submit exam
     */
    public function submit(Request $request, $studentExamId)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->whereIn('status', ['started', 'in_progress'])
                ->findOrFail($studentExamId);

            $studentExam = $this->examEngine->submitExam($studentExam);

            if ($request->wantsJson()) {
                return $this->successResponse([
                    'result_id' => $studentExam->result->id,
                    'redirect' => route('student.exam.result', $studentExamId),
                ], 'Exam submitted successfully');
            }

            return redirect()->route('student.exam.result', $studentExamId)
                ->with('success', 'Exam submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Show exam result
     */
    public function result($studentExamId)
    {
        $studentExam = StudentExam::with(['exam', 'result'])
            ->where('student_id', auth()->id())
            ->findOrFail($studentExamId);

        $summary = $this->examEngine->getExamSummary($studentExam);

        return view('student.exams.result', compact('studentExam', 'summary'));
    }

    /**
     * Record tab switch
     */
    public function recordTabSwitch(Request $request, $studentExamId)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->whereIn('status', ['started', 'in_progress'])
                ->findOrFail($studentExamId);

            $this->examEngine->recordTabSwitch($studentExam);

            return $this->successResponse([
                'tab_switch_count' => $studentExam->fresh()->tab_switch_count,
            ], 'Tab switch recorded');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get remaining time
     */
    public function getRemainingTime($studentExamId)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->findOrFail($studentExamId);

            return $this->successResponse([
                'remaining_seconds' => $studentExam->remaining_seconds,
                'formatted_time' => $studentExam->formatted_time_remaining,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
