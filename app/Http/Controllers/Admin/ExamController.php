<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Question;
use App\Models\ExamSection;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Http\Requests\Exam\AddQuestionsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $query = Exam::with(['course', 'batch', 'creator'])
                     ->withCount(['questions', 'studentExams']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $exams = $query->latest()->paginate(15);

        $courses = Course::where('status', 'active')->get();
        $types = [
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice'
        ];

        return view('admin.exams.index', compact('exams', 'courses', 'types'));
    }

    /**
     * Show form for creating new exam.
     */
    public function create()
    {
        $courses = Course::where('status', 'active')->get();
        $batches = Batch::whereIn('status', ['upcoming', 'ongoing'])->get();
        $types = [
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice'
        ];
        $categories = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'custom' => 'Custom'
        ];

        return view('admin.exams.create', compact('courses', 'batches', 'types', 'categories'));
    }

    /**
     * Store a newly created exam.
     */
    public function store(StoreExamRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            // Handle scheduled exams
            if (!$request->is_scheduled) {
                $data['start_time'] = null;
                $data['end_time'] = null;
            }

            $exam = Exam::create($data);

            // Attach multiple batches if provided
            if ($request->has('batch_ids')) {
                $exam->batches()->sync($request->batch_ids);
            }

            DB::commit();

            return redirect()->route('admin.exams.show', $exam)
                            ->with('success', 'Exam created successfully. Now add questions to the exam.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating exam: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        $exam->load(['course', 'batch', 'batches', 'creator', 'sections' => function($q) {
            $q->withCount('questions');
        }]);

        $exam->loadCount(['questions', 'studentExams']);

        // Get questions grouped by section
        $questions = $exam->questions()
                         ->with(['subject', 'options'])
                         ->orderBy('pivot_order')
                         ->get()
                         ->groupBy('pivot.section_id');

        $stats = [
            'total_students' => $exam->total_students,
            'attempted' => $exam->attempted_count,
            'average_score' => round($exam->average_score, 2),
            'highest_score' => round($exam->highest_score, 2),
            'passing_count' => $exam->passing_count,
            'failing_count' => $exam->failing_count
        ];

        return view('admin.exams.show', compact('exam', 'questions', 'stats'));
    }

    /**
     * Show form for editing exam.
     */
    public function edit(Exam $exam)
    {
        $courses = Course::where('status', 'active')->get();
        $batches = Batch::whereIn('status', ['upcoming', 'ongoing'])->get();
        $types = [
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice'
        ];
        $categories = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'custom' => 'Custom'
        ];

        $exam->load('batches');

        return view('admin.exams.edit', compact('exam', 'courses', 'batches', 'types', 'categories'));
    }

    /**
     * Update the specified exam.
     */
    public function update(UpdateExamRequest $request, Exam $exam)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['updated_by'] = auth()->id();

            // Handle scheduled exams
            if (!$request->is_scheduled) {
                $data['start_time'] = null;
                $data['end_time'] = null;
            }

            $exam->update($data);

            // Update multiple batches
            if ($request->has('batch_ids')) {
                $exam->batches()->sync($request->batch_ids);
            }

            DB::commit();

            return redirect()->route('admin.exams.show', $exam)
                            ->with('success', 'Exam updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating exam: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(Exam $exam)
    {
        try {
            // Check if exam has attempts
            if ($exam->studentExams()->count() > 0) {
                return back()->with('error', 'Cannot delete exam that has been attempted by students.');
            }

            $exam->delete();

            return redirect()->route('admin.exams.index')
                            ->with('success', 'Exam deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting exam: ' . $e->getMessage());
        }
    }

    /**
     * Show add questions form.
     */
    public function addQuestions(Exam $exam)
    {
        $exam->load('questions');

        // Get available questions
        $questions = Question::with(['subject', 'options'])
                            ->where('status', 'active')
                            ->latest()
                            ->paginate(20);

        $sections = $exam->sections;

        return view('admin.exams.add-questions', compact('exam', 'questions', 'sections'));
    }

    /**
     * Store questions for exam.
     */
    public function storeQuestions(AddQuestionsRequest $request, Exam $exam)
    {
        DB::beginTransaction();
        try {
            $exam->questions()->detach();

            foreach ($request->questions as $index => $questionData) {
                $exam->questions()->attach($questionData['question_id'], [
                    'section_id' => $questionData['section_id'] ?? null,
                    'order' => $index,
                    'marks' => $questionData['marks'] ?? null,
                    'negative_marks' => $questionData['negative_marks'] ?? null,
                    'time_limit_seconds' => $questionData['time_limit_seconds'] ?? null
                ]);
            }

            // Update exam total marks
            $exam->updateTotalMarks();

            DB::commit();

            return redirect()->route('admin.exams.show', $exam)
                            ->with('success', 'Questions added to exam successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adding questions: ' . $e->getMessage());
        }
    }

    /**
     * Remove question from exam.
     */
    public function removeQuestion(Exam $exam, $questionId)
    {
        try {
            $exam->questions()->detach($questionId);
            $exam->updateTotalMarks();

            return response()->json([
                'success' => true,
                'message' => 'Question removed from exam'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing question'
            ], 500);
        }
    }

    /**
     * Update questions order.
     */
    public function updateQuestionsOrder(Request $request, Exam $exam)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:exam_questions,id',
            'questions.*.order' => 'required|integer'
        ]);

        try {
            foreach ($request->questions as $questionData) {
                DB::table('exam_questions')
                  ->where('id', $questionData['id'])
                  ->update(['order' => $questionData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Questions order updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order'
            ], 500);
        }
    }

    /**
     * Publish exam.
     */
    public function publish(Exam $exam)
    {
        try {
            if (!$exam->canBePublished()) {
                return back()->with('error', 'Cannot publish exam without questions.');
            }

            $exam->update(['status' => 'published']);

            return redirect()->route('admin.exams.show', $exam)
                            ->with('success', 'Exam published successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error publishing exam: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate exam.
     */
    public function duplicate(Exam $exam)
    {
        DB::beginTransaction();
        try {
            $newExam = $exam->replicate();
            $newExam->title = $exam->title . ' (Copy)';
            $newExam->slug = $exam->slug . '-copy-' . uniqid();
            $newExam->status = 'draft';
            $newExam->created_by = auth()->id();
            $newExam->save();

            // Duplicate sections
            foreach ($exam->sections as $section) {
                $newSection = $section->replicate();
                $newSection->exam_id = $newExam->id;
                $newSection->save();
            }

            // Duplicate questions
            foreach ($exam->questions as $question) {
                $newExam->questions()->attach($question->id, [
                    'section_id' => $question->pivot->section_id,
                    'order' => $question->pivot->order,
                    'marks' => $question->pivot->marks,
                    'negative_marks' => $question->pivot->negative_marks,
                    'time_limit_seconds' => $question->pivot->time_limit_seconds
                ]);
            }

            DB::commit();

            return redirect()->route('admin.exams.edit', $newExam)
                            ->with('success', 'Exam duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error duplicating exam: ' . $e->getMessage());
        }
    }

    /**
     * Get exam statistics.
     */
    public function statistics(Exam $exam)
    {
        $stats = [
            'overview' => [
                'total_questions' => $exam->question_count,
                'total_marks' => $exam->total_marks,
                'duration' => $exam->formatted_duration,
                'attempts_allowed' => $exam->attempts_allowed
            ],
            'performance' => [
                'total_students' => $exam->total_students,
                'attempted' => $exam->attempted_count,
                'average_score' => round($exam->average_score, 2),
                'highest_score' => round($exam->highest_score, 2),
                'lowest_score' => round($exam->lowest_score, 2),
                'passing_count' => $exam->passing_count,
                'failing_count' => $exam->failing_count,
                'pass_percentage' => $exam->attempted_count > 0
                    ? round(($exam->passing_count / $exam->attempted_count) * 100, 2)
                    : 0
            ],
            'question_analysis' => $this->getQuestionAnalysis($exam)
        ];

        return response()->json($stats);
    }

    /**
     * Get question-wise analysis.
     */
    protected function getQuestionAnalysis(Exam $exam)
    {
        $analysis = [];

        foreach ($exam->questions as $question) {
            $attempts = DB::table('student_answers')
                         ->where('question_id', $question->id)
                         ->whereIn('student_exam_id', function($q) use ($exam) {
                             $q->select('id')
                               ->from('student_exams')
                               ->where('exam_id', $exam->id);
                         })
                         ->get();

            $correct = $attempts->where('is_correct', true)->count();
            $total = $attempts->count();

            $analysis[] = [
                'question_id' => $question->id,
                'question_text' => substr(strip_tags($question->question_text), 0, 100),
                'attempts' => $total,
                'correct' => $correct,
                'wrong' => $total - $correct,
                'success_rate' => $total > 0 ? round(($correct / $total) * 100, 2) : 0
            ];
        }

        return $analysis;
    }

    /**
     * Display exam results
     */
    public function results(Exam $exam)
    {
        $exam->load(['course', 'batch']);

        // Get all results for this exam with student details
        $results = $exam->results()
            ->with(['student', 'student.batches'])
            ->orderBy('percentage', 'desc')
            ->paginate(20);

        // Calculate statistics
        $stats = [
            'total_attempts' => $results->total(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'average_score' => round($results->avg('percentage'), 2),
            'highest_score' => $results->max('percentage'),
            'lowest_score' => $results->min('percentage'),
            'total_marks' => $exam->total_marks
        ];

        // Score distribution for chart
        $distribution = [
            '90-100' => $results->where('percentage', '>=', 90)->count(),
            '75-89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
            '60-74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
            '40-59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
            'below-40' => $results->where('percentage', '<', 40)->count()
        ];

        return view('admin.exams.results', compact('exam', 'results', 'stats', 'distribution'));
    }

    /**
     * Export exam results.
     */
    public function exportResults(Exam $exam)
    {
        // Implementation for exporting results to Excel
        // Will be implemented when we reach reporting phase
    }
}
