<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Batch;
use App\Models\Result;
use Illuminate\Http\Request;
use App\Services\Exam\ExamEngineService;
use App\Traits\ApiResponse;

class QuizController extends Controller
{
    use ApiResponse;

    protected $examEngine;

    public function __construct(ExamEngineService $examEngine)
    {
        $this->examEngine = $examEngine;
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display list of quizzes
     */
    public function index(Request $request)
    {
        $instructor = auth()->user();

        $query = Exam::where('type', 'quiz')
            ->where('created_by', $instructor->id)
            ->with(['batch.course'])
            ->withCount(['questions', 'studentExams']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quizzes = $query->latest()->paginate(15);

        // Add average score to each quiz
        foreach ($quizzes as $quiz) {
            $quiz->average_score = Result::where('exam_id', $quiz->id)->avg('percentage');
        }

        $batches = Batch::where('instructor_id', $instructor->id)->get();

        return view('instructor.quizzes.index', compact('quizzes', 'batches'));
    }

    /**
     * Show quiz creation form
     */
    public function create()
    {
        $instructor = auth()->user();

        // Get batches where instructor is assigned
        $batches = Batch::where('instructor_id', $instructor->id)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->with('course')
            ->get();

        // Get recent questions for quick selection
        $recentQuestions = Question::where('created_by', $instructor->id)
            ->where('status', 'active')
            ->latest()
            ->take(50)
            ->get();

        return view('instructor.quizzes.create', compact('batches', 'recentQuestions'));
    }

    /**
     * Launch a quick quiz
     */
    public function launch(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'batch_id' => 'required|exists:batches,id',
            'duration_minutes' => 'required|integer|min:1|max:60',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
            'randomize' => 'nullable|boolean'
        ]);

        try {
            $instructor = auth()->user();
            $batch = Batch::findOrFail($request->batch_id);

            // Verify instructor owns this batch
            if ($batch->instructor_id !== $instructor->id) {
                return back()->with('error', 'You are not authorized to create quizzes for this batch.');
            }

            $questions = Question::whereIn('id', $request->question_ids)->get();

            if ($questions->isEmpty()) {
                return back()->with('error', 'No valid questions selected.');
            }

            // Create quiz
            $exam = Exam::create([
                'title' => $request->title,
                'type' => 'quiz',
                'category' => 'custom',
                'batch_id' => $request->batch_id,
                'duration_minutes' => $request->duration_minutes,
                'total_marks' => $questions->sum('marks'),
                'passing_percentage' => 40,
                'negative_marking' => 0,
                'randomize_questions' => $request->boolean('randomize', true),
                'randomize_options' => true,
                'show_result_immediately' => true,
                'allow_review' => true,
                'attempts_allowed' => 1,
                'status' => 'published',
                'instructions' => 'Quick quiz created by instructor.',
                'created_by' => $instructor->id
            ]);

            // Attach questions
            foreach ($questions as $index => $question) {
                $exam->questions()->attach($question->id, [
                    'order' => $index,
                    'marks' => $question->marks,
                    'negative_marks' => 0
                ]);
            }

            // Update total marks
            $exam->updateTotalMarks();

            return redirect()->route('instructor.quizzes.show', $exam)
                ->with('success', 'Quiz launched successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error launching quiz: ' . $e->getMessage());
        }
    }

    /**
     * Launch instant quiz during class
     */
    public function launchInstant(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
            'duration_minutes' => 'required|integer|min:1|max:30'
        ]);

        try {
            $instructor = auth()->user();
            $batch = Batch::findOrFail($request->batch_id);

            // Verify instructor owns this batch
            if ($batch->instructor_id !== $instructor->id) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $questions = Question::whereIn('id', $request->question_ids)->get();

            // Create instant quiz
            $exam = Exam::create([
                'title' => 'Instant Quiz - ' . now()->format('d M Y H:i'),
                'type' => 'quiz',
                'category' => 'custom',
                'batch_id' => $request->batch_id,
                'duration_minutes' => $request->duration_minutes,
                'total_marks' => $questions->sum('marks'),
                'passing_percentage' => 40,
                'negative_marking' => 0,
                'randomize_questions' => true,
                'randomize_options' => true,
                'show_result_immediately' => true,
                'allow_review' => true,
                'attempts_allowed' => 1,
                'status' => 'published',
                'instructions' => 'Instant quiz during class.',
                'created_by' => $instructor->id
            ]);

            // Attach questions
            foreach ($questions as $index => $question) {
                $exam->questions()->attach($question->id, [
                    'order' => $index,
                    'marks' => $question->marks,
                    'negative_marks' => 0
                ]);
            }

            $exam->updateTotalMarks();

            // Broadcast to students (would use WebSockets)
            // event(new QuizLaunched($exam));

            return $this->successResponse([
                'exam_id' => $exam->id,
                'message' => 'Instant quiz launched!'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error launching quiz: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show quiz details
     */
    public function show(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id() || $quiz->type !== 'quiz') {
            abort(403);
        }

        $quiz->load(['batch.course', 'questions']);

        $stats = [
            'total_attempts' => $quiz->studentExams()->where('status', 'completed')->count(),
            'passed' => Result::where('exam_id', $quiz->id)->where('status', 'passed')->count(),
            'failed' => Result::where('exam_id', $quiz->id)->where('status', 'failed')->count(),
            'average_score' => Result::where('exam_id', $quiz->id)->avg('percentage'),
            'highest_score' => Result::where('exam_id', $quiz->id)->max('percentage'),
            'lowest_score' => Result::where('exam_id', $quiz->id)->min('percentage'),
            'avg_time' => Result::where('exam_id', $quiz->id)->avg('time_taken_seconds')
        ];

        return view('instructor.quizzes.show', compact('quiz', 'stats'));
    }

    /**
     * Show edit form
     */
    public function edit(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id() || $quiz->type !== 'quiz') {
            abort(403);
        }

        $instructor = auth()->user();
        $batches = Batch::where('instructor_id', $instructor->id)->get();
        $quiz->load('questions');

        return view('instructor.quizzes.edit', compact('quiz', 'batches'));
    }

    /**
     * Update quiz
     */
    public function update(Request $request, Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id() || $quiz->type !== 'quiz') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'batch_id' => 'required|exists:batches,id',
            'duration_minutes' => 'required|integer|min:1|max:60',
            'attempts_allowed' => 'required|integer|min:1|max:5',
            'status' => 'required|in:draft,published',
            'instructions' => 'nullable|string',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_result_immediately' => 'boolean'
        ]);

        $quiz->update($request->all());

        return redirect()->route('instructor.quizzes.show', $quiz)
            ->with('success', 'Quiz updated successfully');
    }

    /**
     * Delete quiz
     */
    public function destroy(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id() || $quiz->type !== 'quiz') {
            abort(403);
        }

        // Check if anyone has taken the quiz
        if ($quiz->studentExams()->count() > 0) {
            return back()->with('error', 'Cannot delete quiz that has been attempted by students');
        }

        $quiz->delete();

        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Quiz deleted successfully');
    }

    /**
     * Show results
     */
    public function results(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id() || $quiz->type !== 'quiz') {
            abort(403);
        }

        $results = Result::where('exam_id', $quiz->id)
            ->with('student')
            ->orderBy('percentage', 'desc')
            ->paginate(20);

        $stats = [
            'total_attempts' => $results->total(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'average_score' => $results->avg('percentage'),
            'highest_score' => $results->max('percentage'),
            'lowest_score' => $results->min('percentage')
        ];

        // Distribution data for chart
        $distribution = [
            $results->where('percentage', '<=', 20)->count(),
            $results->where('percentage', '>', 20)->where('percentage', '<=', 40)->count(),
            $results->where('percentage', '>', 40)->where('percentage', '<=', 60)->count(),
            $results->where('percentage', '>', 60)->where('percentage', '<=', 80)->count(),
            $results->where('percentage', '>', 80)->count()
        ];

        // Question analysis
        $questionLabels = [];
        $correctData = [];
        $incorrectData = [];

        foreach ($quiz->questions as $question) {
            $questionLabels[] = 'Q' . $question->id;
            $correctCount = 0;
            $incorrectCount = 0;

            foreach ($results as $result) {
                $answer = $result->studentExam->answers()
                    ->where('question_id', $question->id)
                    ->first();

                if ($answer) {
                    if ($answer->is_correct) {
                        $correctCount++;
                    } elseif ($answer->selected_options || $answer->answer_text) {
                        $incorrectCount++;
                    }
                }
            }

            $correctData[] = $correctCount;
            $incorrectData[] = $incorrectCount;
        }

        return view('instructor.quizzes.results', compact('quiz', 'results', 'stats', 'distribution', 'questionLabels', 'correctData', 'incorrectData'));
    }

    /**
     * Publish quiz
     */
    public function publish(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id() || $quiz->type !== 'quiz') {
            return $this->errorResponse('Unauthorized', 403);
        }

        $quiz->update(['status' => 'published']);

        return $this->successResponse(null, 'Quiz published successfully');
    }

    /**
     * Get available questions for adding
     */
    public function getAvailableQuestions(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $existingIds = $quiz->questions()->pluck('question_id')->toArray();

        $questions = Question::where('status', 'active')
            ->whereNotIn('id', $existingIds)
            ->latest()
            ->take(50)
            ->get()
            ->map(function($q) {
                return [
                    'id' => $q->id,
                    'text' => strip_tags($q->question_text),
                    'type' => $q->type_name,
                    'marks' => $q->marks
                ];
            });

        return $this->successResponse(['questions' => $questions]);
    }

    /**
     * Add questions to quiz
     */
    public function addQuestions(Request $request, Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        $currentOrder = $quiz->questions()->max('order') ?? 0;

        foreach ($request->question_ids as $index => $questionId) {
            $question = Question::find($questionId);
            $quiz->questions()->attach($questionId, [
                'order' => $currentOrder + $index + 1,
                'marks' => $question->marks,
                'negative_marks' => 0
            ]);
        }

        $quiz->updateTotalMarks();

        return $this->successResponse(null, 'Questions added successfully');
    }

    /**
     * Remove question from quiz
     */
    public function removeQuestion(Request $request, Exam $quiz, $questionId)
    {
        if ($quiz->created_by !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $quiz->questions()->detach($questionId);
        $quiz->updateTotalMarks();

        return $this->successResponse(null, 'Question removed successfully');
    }

    /**
     * Get quick quiz template
     */
    public function getTemplate()
    {
        $topics = [
            'algebra', 'geometry', 'calculus', 'trigonometry',
            'physics', 'chemistry', 'biology', 'literature',
            'history', 'geography', 'economics', 'computer_science'
        ];

        return $this->successResponse([
            'topics' => $topics,
            'difficulties' => ['easy', 'medium', 'hard']
        ]);
    }

    /**
     * Export results
     */
    public function exportResults(Exam $quiz)
    {
        if ($quiz->created_by !== auth()->id()) {
            abort(403);
        }

        // Implementation for CSV/Excel export
        // You can use Maatwebsite Excel package here

        return redirect()->back()->with('info', 'Export feature coming soon');
    }
}
