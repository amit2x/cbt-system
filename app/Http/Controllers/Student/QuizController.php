<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use App\Models\Question;
use App\Services\Exam\ExamEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    protected $examEngine;

    public function __construct(ExamEngineService $examEngine)
    {
        $this->examEngine = $examEngine;
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    /**
     * Display available quizzes for student
     */
    // public function index()
    // {
    //     $student = auth()->user();
        
    //     // Get batch IDs where student is enrolled
    //     $batchIds = $student->batches()->wherePivot('status', 'active')->pluck('batches.id');
        
    //     // Get quizzes for those batches
    //     $quizzes = Exam::where('type', 'quiz')
    //         ->whereIn('batch_id', $batchIds)
    //         ->where('status', 'published')
    //         ->with(['batch.course'])
    //         ->latest()
    //         ->get()
    //         ->map(function($quiz) use ($student) {
    //             // Check if student has attempted
    //             $attempt = StudentExam::where('student_id', $student->id)
    //                 ->where('exam_id', $quiz->id)
    //                 ->first();
                
    //             $quiz->student_attempt = $attempt;
    //             $quiz->can_attempt = !$attempt || 
    //                 ($attempt->status === 'paused' && !$attempt->submitted_at);
    //             $quiz->is_completed = $attempt && $attempt->submitted_at;
                
    //             return $quiz;
    //         });

    //     // Separate active and completed
    //     $activeQuizzes = $quizzes->where('can_attempt', true)->where('is_completed', false);
    //     $completedQuizzes = $quizzes->where('is_completed', true);

    //     return view('student.quizzes.index', compact('activeQuizzes', 'completedQuizzes'));
    // }
    
    public function index()
{
    $student = auth()->user();
    
    $batchIds = $student->batches()->wherePivot('status', 'active')->pluck('batches.id');
    
    $quizzes = Exam::where('type', 'quiz')
        ->whereIn('batch_id', $batchIds)
        ->where('status', 'published')
        ->with(['batch.course'])
        ->withCount('questions')  // ✅ Add question count
        ->latest()
        ->get()
        ->map(function($quiz) use ($student) {
            $attempt = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $quiz->id)
                ->with('result')  // ✅ Eager load result
                ->first();
            
            $quiz->student_attempt = $attempt;
            $quiz->can_attempt = !$attempt || 
                ($attempt->status === 'paused' && !$attempt->submitted_at);
            $quiz->is_completed = $attempt && $attempt->submitted_at;
            
            return $quiz;
        });

    $activeQuizzes = $quizzes->where('can_attempt', true)->where('is_completed', false);
    $completedQuizzes = $quizzes->where('is_completed', true);

    return view('student.quizzes.index', compact('activeQuizzes', 'completedQuizzes'));
}

    /**
     * Show quiz instructions
     */
    public function instructions(Exam $exam)
    {
        
        if ($exam->type !== 'quiz') {
            abort(404);
        }

        $student = auth()->user();
        
        // Check if already attempted
        $existingAttempt = StudentExam::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->whereNotNull('submitted_at')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('student.quiz.result', $existingAttempt->id)
                ->with('info', 'You have already completed this quiz.');
        }

         $quiz = $exam->load(['batch.course', 'questions']);
        
        return view('student.quizzes.instructions', compact('quiz'));
    }

    /**
     * Start quiz
     */
    public function start(Request $request, Exam $exam)
    {
        $quiz = $exam;
        if ($quiz->type !== 'quiz') {
            abort(404);
        }

        try {
            $student = auth()->user();

            // Check if can take quiz
            $batchIds = $student->batches()->wherePivot('status', 'active')->pluck('batches.id');
            if (!$batchIds->contains($quiz->batch_id)) {
                return redirect()->route('student.quizzes.index')
                    ->with('error', 'You are not enrolled in this batch.');
            }

            // Check for existing attempt
            $existingAttempt = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $quiz->id)
                ->whereNull('submitted_at')
                ->first();

            if ($existingAttempt) {
                // Resume existing attempt
                return redirect()->route('student.quiz.take', $existingAttempt->id)
                    ->with('info', 'Resuming your quiz...');
            }

            // Check if already completed
            $completedAttempt = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $quiz->id)
                ->whereNotNull('submitted_at')
                ->first();

            if ($completedAttempt) {
                return redirect()->route('student.quiz.result', $completedAttempt->id)
                    ->with('info', 'You have already completed this quiz.');
            }

            // Start new quiz session
            $studentExam = $this->examEngine->startExam($quiz, $student->id, $quiz->batch_id);

            if (!$studentExam) {
                return redirect()->route('student.quizzes.index')
                    ->with('error', 'Failed to start quiz.');
            }

            return redirect()->route('student.quiz.take', $studentExam->id)
                ->with('success', 'Quiz started! Good luck!');

        } catch (\Exception $e) {
            Log::error('Quiz start error: ' . $e->getMessage());
            return redirect()->route('student.quizzes.index')
                ->with('error', 'Error starting quiz.');
        }
    }

    /**
     * Take quiz - Show quiz interface
     */
    public function take(Request $request, $studentExamId)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->with(['exam', 'exam.questions'])
                ->findOrFail($studentExamId);

            if ($studentExam->submitted_at) {
                return redirect()->route('student.quiz.result', $studentExam->id)
                    ->with('info', 'Quiz already submitted.');
            }

            if ($studentExam->exam->type !== 'quiz') {
                abort(404);
            }

            // Get question order
            $questionOrder = $studentExam->question_order ?? 
                $studentExam->exam->questions()->orderBy('order')->pluck('questions.id')->toArray();

            if (empty($questionOrder)) {
                return redirect()->route('student.quizzes.index')
                    ->with('error', 'No questions found.');
            }

            // Cache questions
            $cacheKey = "student_quiz_questions_{$studentExamId}";
            $cachedQuestions = Cache::remember($cacheKey, 60 * 60, function () use ($questionOrder) {
                return Question::whereIn('id', $questionOrder)
                    ->with('options')
                    ->get()
                    ->keyBy('id')
                    ->map(function($q) {
                        return [
                            'id' => $q->id,
                            'question_text' => $q->question_text,
                            'type' => $q->type,
                            'marks' => $q->marks,
                            'options' => $q->options->map(fn($opt) => [
                                'id' => $opt->id,
                                'option_text' => $opt->option_text,
                                'order' => $opt->order,
                            ])->toArray()
                        ];
                    })
                    ->toArray();
            });

            // Get answers
            $answers = $studentExam->answers()->get()->keyBy('question_id');

            // Build questions data
            $questionsData = [];
            foreach ($questionOrder as $questionId) {
                if (isset($cachedQuestions[$questionId])) {
                    $q = $cachedQuestions[$questionId];
                    $answer = $answers[$questionId] ?? null;
                    
                    $questionsData[$questionId] = [
                        'id' => $q['id'],
                        'text' => $q['question_text'],
                        'type' => $q['type'],
                        'marks' => $q['marks'],
                        'image' => null,
                        'options' => collect($q['options'])->map(function($opt) use ($answer) {
                            return [
                                'id' => $opt['id'],
                                'text' => $opt['option_text'],
                                'selected' => $answer && in_array($opt['id'], $answer->selected_options ?? [])
                            ];
                        })->toArray(),
                        'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
                        'selected_options' => $answer ? $answer->selected_options : [],
                    ];
                }
            }

            // Build answers data
            $answersData = [];
            foreach ($questionOrder as $qId) {
                $answer = $answers[$qId] ?? null;
                $answersData[$qId] = [
                    'is_visited' => $answer ? $answer->is_visited : false,
                    'selected_options' => $answer ? $answer->selected_options : [],
                    'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
                ];
            }

            $startQuestionId = $studentExam->current_question_id ?? $questionOrder[0];
            $remainingSeconds = $studentExam->remaining_seconds;

            // Update status
            if ($studentExam->status === 'started') {
                $studentExam->update(['status' => 'in_progress']);
            }

            return view('student.quizzes.take', compact(
                'studentExam', 'questionOrder', 'startQuestionId',
                'remainingSeconds', 'questionsData', 'answersData'
            ));

        } catch (\Exception $e) {
            Log::error('Quiz take error: ' . $e->getMessage());
            return redirect()->route('student.quizzes.index')
                ->with('error', 'Error loading quiz.');
        }
    }

    /**
     * Get question via AJAX
     */
    public function getQuestion(Request $request, $studentExam, $question)
    {
        try {
            $studentExamModel = StudentExam::where('student_id', auth()->id())->findOrFail($studentExam);
            
            if ($studentExamModel->submitted_at) {
                return response()->json(['status' => 'error', 'message' => 'Quiz already submitted.'], 403);
            }

            $cacheKey = "student_quiz_questions_{$studentExam}";
            $cachedQuestions = Cache::get($cacheKey);

            if (!$cachedQuestions || !isset($cachedQuestions[$question])) {
                return response()->json(['status' => 'error', 'message' => 'Question not found.'], 404);
            }

            $answer = $studentExamModel->answers()->where('question_id', $question)->first();
            $questionData = $cachedQuestions[$question];

            $options = collect($questionData['options'])->map(function($opt) use ($answer) {
                return [
                    'id' => $opt['id'],
                    'text' => $opt['option_text'],
                    'selected' => $answer && in_array($opt['id'], $answer->selected_options ?? [])
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $questionData['id'],
                    'text' => $questionData['question_text'],
                    'type' => $questionData['type'],
                    'marks' => $questionData['marks'],
                    'options' => $options,
                    'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
                    'selected_options' => $answer ? $answer->selected_options : [],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save answer
     */
    public function saveAnswer(Request $request, $studentExam)
    {
        $studentExamModel = StudentExam::where('student_id', auth()->id())->findOrFail($studentExam);
        
        if ($studentExamModel->submitted_at) {
            return response()->json(['status' => 'error'], 403);
        }

        $answer = $studentExamModel->answers()->firstOrNew([
            'question_id' => $request->question_id
        ]);

        $answer->selected_options = $request->answer ?? [];
        $answer->is_marked_for_review = $request->is_marked_for_review ?? false;
        $answer->is_visited = true;
        $answer->last_saved_at = now();
        $answer->save_count = ($answer->save_count ?? 0) + 1;
        $answer->save();

        return response()->json(['status' => 'success', 'data' => $answer]);
    }

    /**
     * Mark for review
     */
    public function markForReview(Request $request, $studentExam)
    {
        $studentExamModel = StudentExam::where('student_id', auth()->id())->findOrFail($studentExam);
        
        $answer = $studentExamModel->answers()->firstOrNew([
            'question_id' => $request->question_id
        ]);
        $answer->is_marked_for_review = $request->mark;
        $answer->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Submit quiz
     */
    public function submit(Request $request, $studentExam)
    {
        $studentExamModel = StudentExam::where('student_id', auth()->id())->findOrFail($studentExam);
        
        if ($studentExamModel->submitted_at) {
            return response()->json(['status' => 'error', 'message' => 'Already submitted.'], 403);
        }

        $studentExamModel->update([
            'submitted_at' => now(),
            'status' => 'completed',
            'remaining_seconds' => $request->remaining_seconds ?? 0,
        ]);

        // Calculate results
        $this->calculateQuizResults($studentExamModel);

        return response()->json([
            'status' => 'success',
            'data' => [
                'redirect' => route('student.quiz.result', $studentExamModel->id)
            ]
        ]);
    }

    /**
     * Show quiz result
     */
    public function result($studentExam)
    {
        $studentExamModel = StudentExam::where('student_id', auth()->id())
            ->with(['exam', 'exam.questions', 'answers'])
            ->findOrFail($studentExam);

        if (!$studentExamModel->submitted_at) {
            return redirect()->route('student.quiz.take', $studentExamModel->id);
        }

        // Get result if exists
        $result = \App\Models\Result::where('exam_id', $studentExamModel->exam_id)
            ->where('student_id', auth()->id())
            ->first();

        return view('student.quizzes.result', compact('studentExamModel', 'result'));
    }

    /**
     * Save current question position
     */
    public function saveCurrentQuestion(Request $request, $studentExam)
    {
        StudentExam::where('student_id', auth()->id())
            ->where('id', $studentExam)
            ->update(['current_question_id' => $request->question_id]);
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Sync timer
     */
    public function syncTimer(Request $request, $studentExam)
    {
        StudentExam::where('student_id', auth()->id())
            ->where('id', $studentExam)
            ->update(['remaining_seconds' => $request->remaining_seconds]);
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Calculate quiz results
     */
    private function calculateQuizResults($studentExam)
    {
        $questions = $studentExam->exam->questions;
        $totalMarks = 0;
        $obtainedMarks = 0;
        $correctCount = 0;
        $incorrectCount = 0;
        $skippedCount = 0;

        foreach ($questions as $question) {
            $answer = $studentExam->answers()->where('question_id', $question->id)->first();
            
            if (!$answer || empty($answer->selected_options)) {
                $skippedCount++;
                continue;
            }

            $isCorrect = $this->checkAnswer($question, $answer);
            
            if ($isCorrect) {
                $correctCount++;
                $obtainedMarks += $question->pivot->marks ?? $question->marks;
            } else {
                $incorrectCount++;
                $obtainedMarks -= ($question->pivot->negative_marks ?? 0);
            }
            
            $totalMarks += $question->pivot->marks ?? $question->marks;
            $answer->update(['is_correct' => $isCorrect]);
        }

        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
        
        // Create or update result
        \App\Models\Result::updateOrCreate(
            [
                'exam_id' => $studentExam->exam_id,
                'student_id' => $studentExam->student_id,
            ],
            [
                'total_marks' => $totalMarks,
                'obtained_marks' => max(0, $obtainedMarks),
                'percentage' => $percentage,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'skipped_count' => $skippedCount,
                'status' => $percentage >= ($studentExam->exam->passing_percentage ?? 40) ? 'passed' : 'failed',
                'time_taken_seconds' => ($studentExam->exam->duration_minutes * 60) - ($studentExam->remaining_seconds ?? 0),
                'submitted_at' => now(),
            ]
        );
    }

    /**
     * Check if answer is correct
     */
    private function checkAnswer($question, $answer)
    {
        if ($question->type === 'numerical') {
            return false; // Numerical questions need manual checking or specific logic
        }

        // For MCQ, check if selected options match correct options
        $correctOptions = $question->options()->where('is_correct', true)->pluck('id')->toArray();
        $selectedOptions = $answer->selected_options ?? [];
        
        sort($correctOptions);
        sort($selectedOptions);
        
        return $correctOptions === $selectedOptions;
    }
}