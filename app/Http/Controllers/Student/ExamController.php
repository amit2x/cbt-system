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
use \Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response; 

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

    // /**
    //  * List available exams for student
    //  */
    // public function index()
    // {
    //     $student = auth()->user();

    //     // Get exams from student's batches
    //     $batchIds = $student->batches()
    //         ->wherePivot('status', 'active')
    //         ->pluck('batches.id');

    //     $exams = Exam::whereIn('batch_id', $batchIds)
    //         ->orWhereHas('batches', function ($q) use ($batchIds) {
    //             $q->whereIn('batches.id', $batchIds);
    //         })
    //         ->where('status', 'published')
    //         ->with(['course', 'batch'])
    //         ->get();

    //     // Get student's exam attempts
    //     $attemptedExams = StudentExam::where('student_id', $student->id)
    //         ->whereIn('exam_id', $exams->pluck('id'))
    //         ->get()
    //         ->keyBy('exam_id');

    //     return view('student.exams.index', compact('exams', 'attemptedExams'));
    // }
    
    // public function index()
    // {
    //     $student = auth()->user();
    
    //     $batchIds = $student->batches()
    //         ->wherePivot('status', 'active')
    //         ->pluck('batches.id');
    
    //     $exams = Exam::where(function ($query) use ($batchIds) {
    
    //             $query->whereIn('batch_id', $batchIds)
    //                 ->orWhereHas('batches', function ($q) use ($batchIds) {
    //                     $q->whereIn('batches.id', $batchIds);
    //                 });
    
    //         })
    //         ->where('status', 'published')
    //         ->with(['course', 'batch'])
    //         ->get();
    
    //     $studentExams = StudentExam::where('student_id', $student->id)
    //         ->with('exam')
    //         ->latest()
    //         ->get();
    
    //     $ongoingExams = $studentExams
    //         ->whereIn('status', ['started', 'in_progress' , 'paused']);
        
    
    //     $completedExams = $studentExams
    //         ->where('status', 'completed');
    
    //     $attemptedExams = $studentExams
    //         ->keyBy('exam_id');
            
            
    //     // dd($attemptedExams);
    
    //     return view(
    //         'student.exams.index',
    //         compact(
    //             'exams',
    //             'ongoingExams',
    //             'completedExams',
    //             'attemptedExams'
    //         )
    //     );
    // }
    
    public function index()
{
    $student = auth()->user();

    $batchIds = $student->batches()
        ->wherePivot('status', 'active')
        ->pluck('batches.id');

    // Regular Exams
    $exams = Exam::where(function ($query) use ($batchIds) {
            $query->whereIn('batch_id', $batchIds)
                ->orWhereHas('batches', function ($q) use ($batchIds) {
                    $q->whereIn('batches.id', $batchIds);
                });
        })
        ->where('status', 'published')
        ->where('type', '!=', 'quiz')  // ✅ Exclude quizzes from exams
        ->whereHas('batch', function ($q) {
            $today = now()->toDateString();
        
            $q->whereDate('start_date', '<=', $today)
              ->whereDate('end_date', '>=', $today);
        })
        ->with(['course', 'batch'])
        ->get();

    // ✅ Quizzes - Separate query
    $quizzes = Exam::where(function ($query) use ($batchIds) {
            $query->whereIn('batch_id', $batchIds);
        })
        ->where('status', 'published')
        ->where('type', 'quiz')  // ✅ Only quizzes
        ->whereHas('batch', function ($q) {
            $today = now()->toDateString();
        
            $q->whereDate('start_date', '<=', $today)
              ->whereDate('end_date', '>=', $today);
        })
        ->with(['batch.course'])
        ->withCount('questions')
        ->get();

    // $studentExams = StudentExam::where('student_id', $student->id)
    //     ->with('exam')
    //     ->latest()
    //     ->get();
        
    
    $studentExams = StudentExam::where('student_id', $student->id)
    ->whereRelation('exam', 'status', 'published')
    ->whereHas('batch', function ($q) {
        $today = now()->toDateString();
    
        $q->whereDate('start_date', '<=', $today)
          ->whereDate('end_date', '>=', $today);
    })
    // ->whereRelation('exam', 'type', '!=', 'quiz')
    ->with('exam')
    ->latest()
    ->get();
    
    
    /***
     * For Knowledge
     */
     
     // Standard filter (Equals)
    // Model::whereRelation('relationshipName', 'column', 'value');
    
    // // Filter with a custom operator
    // Model::whereRelation('relationshipName', 'column', 'operator', 'value');



    $ongoingExams = $studentExams->whereIn('status', ['started', 'in_progress', 'paused']);
    
    // dd($ongoingExams);
    
    $completedExams = $studentExams->where('status', 'completed');

    $attemptedExams = $studentExams->keyBy('exam_id');
    
    // ✅ Quiz-specific data
    $ongoingQuizzes = $ongoingExams->filter(function($se) {
        return $se->exam && $se->exam->type === 'quiz';
    });
    
    $completedQuizzes = $completedExams->filter(function($se) {
        return $se->exam && $se->exam->type === 'quiz';
    });
    
    $quizAttempts = $studentExams->filter(function($se) {
        return $se->exam && $se->exam->type === 'quiz';
    })->keyBy('exam_id');
    
    // ✅ Check if quizzes exist
    $hasQuizzes = $quizzes->count() > 0 || $ongoingQuizzes->count() > 0 || $completedQuizzes->count() > 0;

    return view(
        'student.exams.index',
        compact(
            'exams',
            'quizzes',
            'ongoingExams',
            'completedExams',
            'attemptedExams',
            'ongoingQuizzes',
            'completedQuizzes',
            'quizAttempts',
            'hasQuizzes'
        )
    );
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
        // $attempts = StudentExam::where('student_id', $student->id)
        //     ->where('exam_id', $exam->id)
        //     ->count();
        
        // Base query for this student's attempts on this exam
        $attemptsQuery = StudentExam::where('student_id', $student->id)
            ->where('exam_id', $exam->id);
    
        // Get total count
        $attempts = $attemptsQuery->count();
            
         $is_saved = $attemptsQuery->clone()
                ->where('is_saved_exit', true)
                ->whereNotNull('saved_at')
                ->exists();
                
        return view('student.exams.instructions', compact('exam', 'attempts', 'is_saved'));
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
        
                // ✅ Check for ANY existing exam (started, in_progress, paused)
            $existingExam = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->whereIn('status', ['started', 'in_progress', 'paused'])
                ->whereNull('submitted_at')
                ->first();
    
            if ($existingExam) {
                // ✅ Set session marker for proper access
                session(['exam_access_' . $existingExam->id => true]);
                session(['exam_access_time_' . $existingExam->id => now()->timestamp]);
                
                // Update status to in_progress if paused or started
                if ($existingExam->status !== 'in_progress') {
                    $existingExam->update([
                        'status' => 'in_progress',
                    ]);
                }
                
                return redirect()->route('student.exam.take', $existingExam->id)
                    ->with('warning', 'You already have an ongoing exam session. Continuing from where you left off.');
            }
            

            // Start new exam
            $studentExam = $this->examEngine->startExam($exam, $student->id, $batchId);


            if (! $studentExam) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Failed to start exam. Please try again.');
            }else{
                    $studentExam->update([
                        'status' => 'in_progress',
                    ]);
                
            }

            // ✅ Set session marker for proper access
            session(['exam_access_' . $studentExam->id => true]);
            session(['exam_access_time_' . $studentExam->id => now()->timestamp]);
            
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
     * Resume exam
     */
     public function resume(StudentExam $studentExam)
{
    if ($studentExam->student_id !== auth()->id()) {

        Log::warning(
            'Unauthorized Exam Access by '.auth()->id()
        );

        abort(403);
    }

    if (! in_array(
        $studentExam->status,
        ['started', 'in_progress' , 'paused']
    )) {

        return redirect()
            ->route('student.exams.index')
            ->with(
                'error',
                'This exam cannot be resumed.'
            );
    }

    return redirect()->route(
        'student.exams.instructions',
        $studentExam->exam_id
    );
}
     //direct acces to exams.
    //  public function resume(StudentExam $studentExam)
    // {
        
    //     if ($studentExam->student_id !== auth()->id()) {
    //         Log::info(' Unauthorized Exam Access by '. auth()->id());
    //         abort(403);
    //     }
    
    //     if (!in_array($studentExam->status, [
    //         'started',
    //         'in_progress'
    //     ])) {
    
    //         return redirect()
    //             ->route('student.exams.index')
    //             ->with(
    //                 'error',
    //                 'This exam cannot be resumed.'
    //             );
    //     }
    
    //     $studentExam->update([
    //         'is_saved_exit' => false
    //     ]);
    
    //     return redirect()->route(
    //         'student.exam.take',
    //         $studentExam->id
    //     );
    // }
    /**
     * Take exam
     */
    // public function take($studentExamId)
    // {
    //     try {
            
            
    //         $studentExam = StudentExam::where('student_id', auth()->id())
    //             ->with(['exam', 'exam.questions'])
    //             ->find($studentExamId);

    //         if (! $studentExam) {
    //             return redirect()->route('student.exams.index')
    //                 ->with('error', 'Exam session not found.');
    //         }
            
    //         if ($studentExam->submitted_at || in_array($studentExam->status,['submitted', 'completed'])) {
    //             return redirect()
    //                 ->route('student.exams.index')
    //                 ->with('error','Exam already submitted.');
    //         }

    //         // Validate exam access
    //         $access = $this->examEngine->validateExamAccess($studentExam);
    //         if (! $access['allowed']) {
    //             return redirect()->route('student.exams.index')
    //                 ->with('error', $access['reason']);
    //         }

    //         // Get questions in order
    //         $questionOrder = $studentExam->question_order ??
    //                         $studentExam->exam->questions()->orderBy('order')->pluck('questions.id')->toArray();

    //         if (empty($questionOrder)) {
    //             return redirect()->route('student.exams.index')
    //                 ->with('error', 'No questions found for this exam.');
    //         }

    //         $questions = Question::whereIn('id', $questionOrder)
    //             ->with('options')
    //             ->get()
    //             ->keyBy('id');

    //         // Get saved answers
    //         $answers = $studentExam->answers()->get()->keyBy('question_id');
            
            
    //         //Timer
    //         $remainingSeconds = $studentExam->remaining_seconds;
            
            
    //         $startQuestionId = $studentExam->current_question_id ?? $questionOrder[0];
            

    //         return view('student.exams.take', compact('studentExam', 'questions', 'answers', 'questionOrder','startQuestionId','remainingSeconds'));

    //     } catch (\Exception $e) {
    //         Log::error('Take exam error: '.$e->getMessage(), [
    //             'student_exam_id' => $studentExamId,
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return redirect()->route('student.exams.index')
    //             ->with('error', 'Error loading exam: '.$e->getMessage());
    //     }
    // }

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
 * TAKE with cache
 */
 
 /**
 * Take exam
 */
// public function take($studentExamId)
// {
//     try {
//         $studentExam = StudentExam::where('student_id', auth()->id())
//             ->with(['exam', 'exam.questions'])
//             ->find($studentExamId);

//         if (! $studentExam) {
//             return redirect()->route('student.exams.index')
//                 ->with('error', 'Exam session not found.');
//         }
        
//         if ($studentExam->submitted_at || in_array($studentExam->status, ['submitted', 'completed'])) {
//             return redirect()
//                 ->route('student.exams.index')
//                 ->with('error', 'Exam already submitted.');
//         }

//         // Validate exam access
//         $access = $this->examEngine->validateExamAccess($studentExam);
//         if (! $access['allowed']) {
//             return redirect()->route('student.exams.index')
//                 ->with('error', $access['reason']);
//         }

//         // Get questions in order
//         $questionOrder = $studentExam->question_order ??
//                         $studentExam->exam->questions()->orderBy('order')->pluck('questions.id')->toArray();

//         if (empty($questionOrder)) {
//             return redirect()->route('student.exams.index')
//                 ->with('error', 'No questions found for this exam.');
//         }

//         // ==========================================
//         // OPTIMIZED FOR DATABASE CACHE STORE
//         // ==========================================
//         $cacheKey = "student_exam_questions_{$studentExamId}";
        
//         // Store for 120 minutes
//         $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 120 * 60, function () use ($questionOrder) {
//             $rawQuestions = Question::whereIn('id', $questionOrder)
//                 ->with('options')
//                 ->get();
        
//             $optimizedArray = [];
        
//             foreach ($rawQuestions as $q) {
//                 $optimizedArray[$q->id] = [
//                     'id' => $q->id,
//                     'question_text' => $q->question_text,
//                     'type' => $q->type,
//                     'explanation' => $q->explanation,
//                     'marks' => $q->marks,
//                     'negative_marks' => $q->negative_marks,
//                     'options' => $q->options->map(function ($opt) {
//                         return [
//                             'id' => $opt->id,
//                             'option_text' => $opt->option_text,
//                             'order' => $opt->order,
//                         ];
//                     })->toArray()
//                 ];
//             }
        
//             return $optimizedArray;
//         });
        
//         // Pass to view (Optional: keep using the cached data directly if your view supports arrays)
//         $questions = $cachedData; 


//         // Get saved answers (keep database query clean, only fetch progress tracking)
//         $answers = $studentExam->answers()->get()->keyBy('question_id');
        
//         // Timer
//         $remainingSeconds = $studentExam->remaining_seconds;
        
//         $startQuestionId = $studentExam->current_question_id ?? $questionOrder[0];

//         return view('student.exams.take', compact('studentExam', 'questions', 'answers', 'questionOrder', 'startQuestionId', 'remainingSeconds'));

//     } catch (\Exception $e) {
//         Log::error('Take exam error: '.$e->getMessage(), [
//             'student_exam_id' => $studentExamId,
//             'trace' => $e->getTraceAsString(),
//         ]);

//         return redirect()->route('student.exams.index')
//             ->with('error', 'Error loading exam: '.$e->getMessage());
//     }
// }


//full load questions
/**
 * Take exam - Optimized with all questions preloaded
 */
/**
 * Take exam - Optimized with all questions preloaded--- working without image
 */
// public function take(Request $request, $studentExamId)
// {
//     try {
//         $studentExam = StudentExam::where('student_id', auth()->id())
//             ->with(['exam', 'exam.questions'])
//             ->find($studentExamId);

//         if (! $studentExam) {
//             return redirect()->route('student.exams.index')
//                 ->with('error', 'Exam session not found.');
//         }
        
//         if ($studentExam->submitted_at || in_array($studentExam->status, ['submitted', 'completed'])) {
//             return redirect()
//                 ->route('student.exams.index')
//                 ->with('error', 'Exam already submitted.');
//         }

//         // if (in_array($studentExam->status, ['started', 'in_progress'])) {
//         //     // If they try to access via back button, redirect them
//         //     // They should resume from dashboard
//         //     return redirect()
//         //         ->route('student.exams.index')
//         //         ->with('warning', 'Please resume your exam from the dashboard.');
//         // }
        
//             // Check if saved & exited (status is 'paused')
//         if ($studentExam->status === 'paused') {
//             return redirect()
//                 ->route('student.exams.index')
//                 ->with('warning', 'Your exam has been saved. Please click "Resume" to continue.');
//         }
        
//         // ✅ NEW: Check if exam is being accessed via back button or direct URL
//         // This detects if user navigated away without saving
//         $isBackNavigation = $this->isBackNavigation($request, $studentExam);
        
//         if ($isBackNavigation && $studentExam->status === 'in_progress') {
//             // User left without saving - don't allow direct access
//             // They must resume from dashboard
//             return redirect()
//                 ->route('student.exams.index')
//                 ->with('warning', 'You have an ongoing exam. Please click "Resume" from the dashboard to continue.');
//         }
        
//         // Validate exam access
//         $access = $this->examEngine->validateExamAccess($studentExam);
//         if (! $access['allowed']) {
//             return redirect()->route('student.exams.index')
//                 ->with('error', $access['reason']);
//         }

//         // Get questions in order
//         $questionOrder = $studentExam->question_order ??
//                         $studentExam->exam->questions()->orderBy('order')->pluck('questions.id')->toArray();

//         if (empty($questionOrder)) {
//             return redirect()->route('student.exams.index')
//                 ->with('error', 'No questions found for this exam.');
//         }

//         // ==========================================
//         // CACHE QUESTIONS
//         // ==========================================
//         $cacheKey = "student_exam_questions_{$studentExamId}";
        
//         $cachedQuestions = Cache::remember($cacheKey, 120 * 60, function () use ($questionOrder) {
//             $rawQuestions = Question::whereIn('id', $questionOrder)
//                 ->with('options')
//                 ->get();
        
//             $optimizedArray = [];
        
//             foreach ($rawQuestions as $q) {
//                 $optimizedArray[$q->id] = [
//                     'id' => $q->id,
//                     'question_text' => $q->question_text,
//                     'type' => $q->type,
//                     'explanation' => $q->explanation,
//                     'marks' => $q->marks,
//                     'negative_marks' => $q->negative_marks,
//                     'options' => $q->options->map(function ($opt) {
//                         return [
//                             'id' => $opt->id,
//                             'option_text' => $opt->option_text,
//                             'order' => $opt->order,
//                         ];
//                     })->toArray()
//                 ];
//             }
        
//             return $optimizedArray;
//         });

//         // Get saved answers
//         $answers = $studentExam->answers()->get()->keyBy('question_id');
        
//         // ==========================================
//         // BUILD COMPLETE QUESTIONS DATA FOR JAVASCRIPT
//         // ==========================================
//         $questionsData = [];
        
//         foreach ($questionOrder as $questionId) {
//             if (isset($cachedQuestions[$questionId])) {
//                 $questionData = $cachedQuestions[$questionId];
//                 $answer = $answers[$questionId] ?? null;
                
//                 // Apply option randomization if needed
//                 $options = collect($questionData['options']);
//                 if ($studentExam->exam->randomize_options) {
//                     $options = $options->shuffle();
//                 }
                
//                 // Build option data with selection state
//                 $optionsData = $options->map(function ($opt) use ($answer) {
//                     $isSelected = false;
//                     if ($answer && $answer->selected_options) {
//                         $isSelected = in_array($opt['id'], $answer->selected_options);
//                     }
                    
//                     return [
//                         'id' => $opt['id'],
//                         'text' => $opt['option_text'],
//                         'order' => $opt['order'],
//                         'selected' => $isSelected,
//                         'image' => null, // Add if you have option images
//                     ];
//                 })->toArray();
                
//                 // Build complete question data
//                 $questionsData[$questionId] = [
//                     'id' => $questionData['id'],
//                     'text' => $questionData['question_text'],
//                     'type' => $questionData['type'],
//                     'marks' => $questionData['marks'],
//                     'explanation' => $questionData['explanation'],
//                     'negative_marks' => $questionData['negative_marks'],
//                     'image' => null, // Add if you have question images
//                     'options' => $optionsData,
//                     'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
//                     'answer_text' => $answer ? $answer->answer_text : null,
//                     'selected_options' => $answer ? $answer->selected_options : [],
//                 ];
//             }
//         }
        
//         // ✅ Mark as accessed via dashboard (proper channel)
//         session(['exam_access_' . $studentExamId => true]);
//         session(['exam_access_time_' . $studentExamId => now()->timestamp]);
        
//         // Build answers data for JavaScript
//         $answersData = [];
//         foreach ($questionOrder as $questionId) {
//             $answer = $answers[$questionId] ?? null;
//             $answersData[$questionId] = [
//                 'is_visited' => $answer ? $answer->is_visited : false,
//                 'selected_options' => $answer ? $answer->selected_options : [],
//                 'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
//                 'answer_text' => $answer ? $answer->answer_text : null,
//             ];
//         }

//         // Timer
//         $remainingSeconds = $studentExam->remaining_seconds;
//         $startQuestionId = $studentExam->current_question_id ?? $questionOrder[0];

//         // FIX: Use $cachedQuestions as $questions for backward compatibility
//         $questions = $cachedQuestions;

//         return response()
//         ->view('student.exams.take', compact(
//             'studentExam', 
//             'questions',        // This is the $cachedQuestions data
//             'answers', 
//             'questionOrder', 
//             'startQuestionId', 
//             'remainingSeconds',
//             'questionsData',    // Complete questions data for JavaScript
//             'answersData'       // Answers data for JavaScript
//         ))
//         ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
//         ->header('Pragma', 'no-cache')
//         ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
//         // ->header('Clear-Site-Data', '"cache"') 
//         ->header('Vary', '*');

//     } catch (\Exception $e) {
//         Log::error('Take exam error: '.$e->getMessage(), [
//             'student_exam_id' => $studentExamId,
//             'trace' => $e->getTraceAsString(),
//         ]);

//         return redirect()->route('student.exams.index')
//             ->with('error', 'Error loading exam: '.$e->getMessage());
//     }
// }


/**
 * Take exam - Optimized with all questions preloaded
 */
public function take(Request $request, $studentExamId)
{
    try {
        $studentExam = StudentExam::where('student_id', auth()->id())
            ->with(['exam', 'exam.questions'])
            ->find($studentExamId);

        if (! $studentExam) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Exam session not found.');
        }
        
        if ($studentExam->submitted_at || in_array($studentExam->status, ['submitted', 'completed'])) {
            return redirect()
                ->route('student.exams.index')
                ->with('error', 'Exam already submitted.');
        }

        if ($studentExam->status === 'paused') {
            return redirect()
                ->route('student.exams.index')
                ->with('warning', 'Your exam has been saved. Please click "Resume" to continue.');
        }
        
        $isBackNavigation = $this->isBackNavigation($request, $studentExam);
        
        if ($isBackNavigation && $studentExam->status === 'in_progress') {
            return redirect()
                ->route('student.exams.index')
                ->with('warning', 'You have an ongoing exam. Please click "Resume" from the dashboard to continue.');
        }
        
        $access = $this->examEngine->validateExamAccess($studentExam);
        if (! $access['allowed']) {
            return redirect()->route('student.exams.index')
                ->with('error', $access['reason']);
        }

        $questionOrder = $studentExam->question_order ??
                        $studentExam->exam->questions()->orderBy('order')->pluck('questions.id')->toArray();

        if (empty($questionOrder)) {
            return redirect()->route('student.exams.index')
                ->with('error', 'No questions found for this exam.');
        }

        // ==========================================
        // CACHE QUESTIONS WITH IMAGES - WITH NULL CHECKS
        // ==========================================
        $cacheKey = "student_exam_questions_{$studentExamId}";
        
        $cachedQuestions = Cache::remember($cacheKey, 120 * 60, function () use ($questionOrder) {
            $rawQuestions = Question::whereIn('id', $questionOrder)
                ->with('options')
                ->get();
        
            $optimizedArray = [];
        
            foreach ($rawQuestions as $q) {
                $optimizedArray[$q->id] = [
                    'id' => $q->id,
                    'question_text' => $q->question_text ?? '',
                    'question_image' => $q->question_image ?? null,          // ✅ NULL CHECK
                    'question_image_alt' => $q->question_image_alt ?? null,  // ✅ NULL CHECK
                    'type' => $q->type ?? 'single_choice',
                    'explanation' => $q->explanation ?? '',
                    'marks' => $q->marks ?? 1,
                    'negative_marks' => $q->negative_marks ?? 0,
                    'options' => $q->options->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'option_text' => $opt->option_text ?? '',
                            'option_image' => $opt->option_image ?? null,        // ✅ NULL CHECK
                            'option_image_alt' => $opt->option_image_alt ?? null, // ✅ NULL CHECK
                            'order' => $opt->order ?? 0,
                        ];
                    })->toArray()
                ];
            }
        
            return $optimizedArray;
        });

        $answers = $studentExam->answers()->get()->keyBy('question_id');
        
        // ==========================================
        // BUILD COMPLETE QUESTIONS DATA FOR JAVASCRIPT
        // ==========================================
        $questionsData = [];
        
        foreach ($questionOrder as $questionId) {
            if (isset($cachedQuestions[$questionId])) {
                $questionData = $cachedQuestions[$questionId];
                $answer = $answers[$questionId] ?? null;
                
                $options = collect($questionData['options']);
                if ($studentExam->exam->randomize_options) {
                    $options = $options->shuffle();
                }
                
                $optionsData = $options->map(function ($opt) use ($answer) {
                    $isSelected = false;
                    if ($answer && $answer->selected_options) {
                        $isSelected = in_array($opt['id'], $answer->selected_options);
                    }
                    
                    // ✅ NULL CHECK for option_image
                    $optionImage = null;
                    if (!empty($opt['option_image'])) {
                        $optionImage = asset('storage/' . $opt['option_image']);
                    }
                    
                    return [
                        'id' => $opt['id'],
                        'text' => $opt['option_text'] ?? '',
                        'image' => $optionImage,
                        'image_alt' => $opt['option_image_alt'] ?? '',
                        'order' => $opt['order'] ?? 0,
                        'selected' => $isSelected,
                    ];
                })->toArray();
                
                // ✅ NULL CHECK for question_image
                $questionImage = null;
                if (!empty($questionData['question_image'])) {
                    $questionImage = asset('storage/' . $questionData['question_image']);
                }
                
                $questionsData[$questionId] = [
                    'id' => $questionData['id'],
                    'text' => $questionData['question_text'] ?? '',
                    'image' => $questionImage,
                    'image_alt' => $questionData['question_image_alt'] ?? '',
                    'type' => $questionData['type'] ?? 'single_choice',
                    'marks' => $questionData['marks'] ?? 1,
                    'explanation' => $questionData['explanation'] ?? '',
                    'negative_marks' => $questionData['negative_marks'] ?? 0,
                    'options' => $optionsData,
                    'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
                    'answer_text' => $answer ? $answer->answer_text : null,
                    'selected_options' => $answer ? $answer->selected_options : [],
                ];
            }
        }
        
        session(['exam_access_' . $studentExamId => true]);
        session(['exam_access_time_' . $studentExamId => now()->timestamp]);
        
        $answersData = [];
        foreach ($questionOrder as $questionId) {
            $answer = $answers[$questionId] ?? null;
            $answersData[$questionId] = [
                'is_visited' => $answer ? $answer->is_visited : false,
                'selected_options' => $answer ? $answer->selected_options : [],
                'is_marked_for_review' => $answer ? $answer->is_marked_for_review : false,
                'answer_text' => $answer ? $answer->answer_text : null,
            ];
        }

        $remainingSeconds = $studentExam->remaining_seconds;
        $startQuestionId = $studentExam->current_question_id ?? $questionOrder[0];
        $questions = $cachedQuestions;
        
        // dd($questions);

        return response()
        ->view('student.exams.take', compact(
            'studentExam', 
            'questions', 
            'answers', 
            'questionOrder', 
            'startQuestionId', 
            'remainingSeconds',
            'questionsData',
            'answersData'
        ))
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
        ->header('Vary', '*');

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
 * ✅ NEW: Detect if this is a back button navigation or direct URL access
 */
private function isBackNavigation($request, $studentExam)
{
    // Check if user accessed via dashboard (proper channel)
    $accessedViaDashboard = session('exam_access_' . $studentExam->id);
    $accessTime = session('exam_access_time_' . $studentExam->id);
    
    // If no session marker, it's likely back button or direct URL
    if (!$accessedViaDashboard) {
        return true;
    }
    
    // Check if session has expired (30 minutes since last access)
    if ($accessTime && (now()->timestamp - $accessTime) > 1800) {
        return true;
    }
    
    // Check referrer - if coming from outside, it's suspicious
    $referrer = $request->header('referer');
    if ($referrer) {
        $allowedReferrers = [
            route('student.dashboard'),
            route('student.exams.index'),
            route('student.exams.start', $studentExam->exam_id),
        ];
        
        $isAllowedReferrer = false;
        foreach ($allowedReferrers as $allowed) {
            if (strpos($referrer, $allowed) !== false) {
                $isAllowedReferrer = true;
                break;
            }
        }
        
        if (!$isAllowedReferrer) {
            return true;
        }
    }
    
    return false;
}


    /**
     * Get Question via AJAX with cache
     * */

     
     /**
 * Get question via AJAX (Database Cache Optimized)
 */
// public function getQuestion(Request $request, $studentExam, $question)
// {
//     try {
//         $studentExamModel = StudentExam::where('student_id', auth()->id())
//             ->findOrFail($studentExam);

//         if ($studentExamModel->submitted_at) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Exam already submitted.'
//             ], 403);
//         }

//         // 1. Fetch saved user progress from the DB
//         $answer = $studentExamModel->answers()
//             ->where('question_id', $question)
//             ->first();

//         if (! $answer) {
//             return $this->errorResponse('Question not found', 404);
//         }

//         // 2. Fetch lightweight structure from the Database Cache
//         $cacheKey = "student_exam_questions_{$studentExam}";
//         $cachedQuestions = \Illuminate\Support\Facades\Cache::get($cacheKey);

//         // Fallback safety layer: if cache was cleared, rebuild array format
//         if (! $cachedQuestions || ! isset($cachedQuestions[$question])) {
//             $questionOrder = $studentExamModel->question_order ?? [$question];
            
//             // Rebuild logic matching the array structure
//             $rawQuestions = Question::whereIn('id', $questionOrder)->with('options')->get();
//             $cachedQuestions = [];
//             foreach ($rawQuestions as $q) {
//                 $cachedQuestions[$q->id] = [
//                     'id' => $q->id,
//                     'question_text' => $q->question_text,
//                     'type' => $q->type,
//                     'explanation' => $q->explanation,
//                     'marks' => $q->marks,
//                     'negative_marks' => $q->negative_marks,
//                     'options' => $q->options->map(fn($opt) => [
//                         'id' => $opt->id, 'option_text' => $opt->option_text, 'order' => $opt->order
//                     ])->toArray()
//                 ];
//             }
//             \Illuminate\Support\Facades\Cache::put($cacheKey, $cachedQuestions, 120 * 60);
//         }

//         // Isolate specific question array from cache
//         $questionData = $cachedQuestions[$question];

//         // 3. Handle option shuffling via Laravel Collections directly on the array
//         $optionsCollection = collect($questionData['options']);
//         if ($studentExamModel->exam->randomize_options) {
//             $optionsCollection = $optionsCollection->shuffle();
//         }

//         return $this->successResponse([
//             'id' => $questionData['id'],
//             'text' => $questionData['question_text'],
//             'type' => $questionData['type'],
//             'options' => $optionsCollection->map(function ($opt) use ($answer) {
//                 return [
//                     'id' => $opt['id'],
//                     'text' => $opt['option_text'],
//                     'order' => $opt['order'],
//                     'selected' => $answer->selected_options &&
//                         in_array($opt['id'], $answer->selected_options),
//                 ];
//             }),
//             'answer' => $answer->answer_text,
//             'is_marked_for_review' => $answer->is_marked_for_review,
//             'explanation' => $questionData['explanation'],
//             'marks' => $questionData['marks'],
//             'negative_marks' => $questionData['negative_marks'],
//         ], 'Question loaded');

//     } catch (\Exception $e) {
//         return $this->errorResponse($e->getMessage(), 500);
//     }
// }

/**
 * Get question via AJAX (Database Cache Optimized)
 */
public function getQuestion(Request $request, $studentExam, $question)
{
    try {
        $studentExamModel = StudentExam::where('student_id', auth()->id())
            ->findOrFail($studentExam);

        if ($studentExamModel->submitted_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exam already submitted.'
            ], 403);
        }

        $answer = $studentExamModel->answers()
            ->where('question_id', $question)
            ->first();

        if (! $answer) {
            return $this->errorResponse('Question not found', 404);
        }

        $cacheKey = "student_exam_questions_{$studentExam}";
        $cachedQuestions = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (! $cachedQuestions || ! isset($cachedQuestions[$question])) {
            $questionOrder = $studentExamModel->question_order ?? [$question];
            
            $rawQuestions = Question::whereIn('id', $questionOrder)->with('options')->get();
            $cachedQuestions = [];
            foreach ($rawQuestions as $q) {
                $cachedQuestions[$q->id] = [
                    'id' => $q->id,
                    'question_text' => $q->question_text ?? '',
                    'question_image' => $q->question_image ?? null,
                    'question_image_alt' => $q->question_image_alt ?? null,
                    'type' => $q->type ?? 'single_choice',
                    'explanation' => $q->explanation ?? '',
                    'marks' => $q->marks ?? 1,
                    'negative_marks' => $q->negative_marks ?? 0,
                    'options' => $q->options->map(fn($opt) => [
                        'id' => $opt->id, 
                        'option_text' => $opt->option_text ?? '',
                        'option_image' => $opt->option_image ?? null,
                        'option_image_alt' => $opt->option_image_alt ?? null,
                        'order' => $opt->order ?? 0
                    ])->toArray()
                ];
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, $cachedQuestions, 120 * 60);
        }

        $questionData = $cachedQuestions[$question];

        $optionsCollection = collect($questionData['options']);
        if ($studentExamModel->exam->randomize_options) {
            $optionsCollection = $optionsCollection->shuffle();
        }

        // ✅ NULL CHECK for question_image
        $questionImage = null;
        if (!empty($questionData['question_image'])) {
            $questionImage = asset('storage/' . $questionData['question_image']);
        }

        return $this->successResponse([
            'id' => $questionData['id'],
            'text' => $questionData['question_text'],
            'image' => $questionImage,
            'image_alt' => $questionData['question_image_alt'] ?? '',
            'type' => $questionData['type'],
            'options' => $optionsCollection->map(function ($opt) use ($answer) {
                // ✅ NULL CHECK for option_image
                $optionImage = null;
                if (!empty($opt['option_image'])) {
                    $optionImage = asset('storage/' . $opt['option_image']);
                }
                
                return [
                    'id' => $opt['id'],
                    'text' => $opt['option_text'],
                    'image' => $optionImage,
                    'image_alt' => $opt['option_image_alt'] ?? '',
                    'order' => $opt['order'],
                    'selected' => $answer->selected_options &&
                        in_array($opt['id'], $answer->selected_options),
                ];
            }),
            'answer' => $answer->answer_text,
            'is_marked_for_review' => $answer->is_marked_for_review,
            'explanation' => $questionData['explanation'],
            'marks' => $questionData['marks'],
            'negative_marks' => $questionData['negative_marks'],
        ], 'Question loaded');

    } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}


    /**
     * Get question via AJAX without cache
     */
    // public function getQuestion(Request $request, $studentExam, $question)
    // {
    //     try {
    //         $studentExam = StudentExam::where('student_id', auth()->id())
    //             ->findOrFail($studentExam);
    //         if ($studentExam->submitted_at) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Exam already submitted.'
    //             ], 403);
    //         }

    //         $answer = $studentExam->answers()
    //             ->where('question_id', $question)
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
     * Save answer via AJAX
     */
    // public function saveAnswer(Request $request, $studentExamId)
    // {
    //     try {
    //         $request->validate([
    //             'question_id' => 'required|exists:questions,id',
    //             'answer' => 'nullable',
    //             'is_marked_for_review' => 'boolean',
    //         ]);

    //         $studentExam = StudentExam::where('student_id', auth()->id())
    //             ->whereIn('status', ['started', 'in_progress'])
    //             ->findOrFail($studentExamId);
    //         if ($studentExam->submitted_at) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Exam already submitted.'
    //             ], 403);
    //         }


    //         $answer = $this->examEngine->saveAnswer(
    //             $studentExam,
    //             $request->question_id,
    //             $request->answer,
    //             $request->boolean('is_marked_for_review')
    //         );

    //         return $this->successResponse([
    //             'answered_count' => $studentExam->fresh()->answered_questions,
    //             'marked_count' => $studentExam->fresh()->marked_for_review_count,
    //             'remaining_seconds' => $studentExam->fresh()->remaining_seconds,
    //         ], 'Answer saved successfully');
    //     } catch (\Exception $e) {
    //         return $this->errorResponse($e->getMessage(), 500);
    //     }
    // }
    
    /**
 * Save answer via AJAX
 */
public function saveAnswer(Request $request, $studentExamId)
{
    Log::info('SAVE Answer Controller');
    Log::info($request->all());
    Log::info($studentExamId);
    try {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable',
            'is_marked_for_review' => 'boolean',
            'answer_text' => 'nullable|string', 
        ]);

        $studentExam = StudentExam::where('student_id', auth()->id())
            ->whereIn('status', ['started', 'in_progress'])
            ->findOrFail($studentExamId);
            
        if ($studentExam->submitted_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exam already submitted.'
            ], 403);
        }

        // ✅ Pass answer_text to the service
        $answer = $this->examEngine->saveAnswer(
            $studentExam,
            $request->question_id,
            $request->answer,
            $request->boolean('is_marked_for_review'),
            $request->answer_text 
        );

        return $this->successResponse([
            'answered_count' => $studentExam->fresh()->answered_questions,
            'marked_count' => $studentExam->fresh()->marked_for_review_count,
            'remaining_seconds' => $studentExam->fresh()->remaining_seconds,
        ], 'Answer saved successfully');
    } catch (\Exception $e) {
        Log::error('Save answer error: ' . $e->getMessage(), [
            'student_exam_id' => $studentExamId,
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
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
                
            if ($studentExam->submitted_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Exam already submitted.'
                ], 403);
            }


            $this->examEngine->markForReview($studentExam, $request->question_id);

            return $this->successResponse([
                'marked_count' => $studentExam->fresh()->marked_for_review_count,
            ], 'Question '.($request->mark ? 'marked' : 'unmarked').' for review');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    
    public function syncTimer( Request $request,StudentExam $studentExam) {
        if ($studentExam->submitted_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Exam already submitted.'
                ], 403);
            }

    
        if ($studentExam->student_id !== auth()->id()) {
    
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }
    
        $request->validate([
            'remaining_seconds' => 'required|integer|min:0'
        ]);
    
        $studentExam->update([
            'remaining_seconds' => $request->remaining_seconds
        ]);
    
        return response()->json([
            'status' => 'success'
        ]);
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
            
            \Illuminate\Support\Facades\Cache::forget("student_exam_questions_{$studentExamId}");

            // ✅ Clear the session access marker
            session()->forget('exam_access_' . $studentExamId);
            session()->forget('exam_access_time_' . $studentExamId);
            
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
            if ($studentExam->submitted_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Exam already submitted.'
                ], 403);
            }

            return $this->successResponse([
                'remaining_seconds' => $studentExam->remaining_seconds,
                'formatted_time' => $studentExam->formatted_time_remaining,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    
    public function saveCurrentQuestion(Request $request, StudentExam $studentExam) {
    
        $request->validate([
            'question_id' => 'required|integer'
        ]);
    
        $studentExam->update([
            'current_question_id' => $request->question_id
        ]);
    
        return response()->json([
            'status' => 'success'
        ]);
    }
    
    /**
     * Save and exit exam
     */
    public function saveExit(Request $request, $studentExamId)
    {
        try {
            $studentExam = StudentExam::where('student_id', auth()->id())
                ->findOrFail($studentExamId);
    
            if ($studentExam->submitted_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Exam already submitted.'
                ], 403);
            }
    
            // ✅ Keep status as 'in_progress' but update timestamps
            // Don't change to 'paused' - that might cause issues
            $studentExam->update([

                'status' => 'paused',  
                'is_saved_exit'       => true,
                'saved_at'            => now(),
                'remaining_seconds' => $request->remaining_seconds ?? $studentExam->remaining_seconds,
                'current_question_id' => $request->current_question_id ?? $studentExam->current_question_id,
            ]);
            
            // ✅ Clear the session access marker
            session()->forget('exam_access_' . $studentExamId);
            session()->forget('exam_access_time_' . $studentExamId);
    
            return response()->json([
                'status' => 'success',
                'message' => 'Progress saved successfully. You can resume from dashboard.'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Save exit error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving progress.'
            ], 500);
        }
    }

    
    // public function saveExit(Request $request,StudentExam $studentExam) {

    //     // dd($request->all());
    //     $studentExam->update([
    //         'status' => 'paused',  
    //         'is_saved_exit'       => true,
    //         'saved_at'            => now(),
    //         'current_question_id' => $request->current_question_id,
    //         'remaining_seconds'   => $request->remaining_seconds,
    //     ]);
    
    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Exam progress saved'
    //     ]);
    // }
}
