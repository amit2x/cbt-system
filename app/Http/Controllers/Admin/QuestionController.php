<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Module;
use App\Models\QuestionCategory;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Requests\Question\ImportQuestionsRequest;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\QuestionsImport;
use App\Exports\QuestionsExport;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    protected $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $query = Question::with(['creator', 'subject', 'categories']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('difficulty')) {
            $query->difficulty($request->difficulty);
        }

        if ($request->filled('subject_id')) {
            $query->forSubject($request->subject_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('question_categories.id', $request->category_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $questions = $query->latest()->paginate(15);

        // Get filter data
        $subjects = Module::where('status', 'active')->get();
        $categories = QuestionCategory::active()->get();
        $types = ['single_choice' => 'Single Choice', 'multiple_choice' => 'Multiple Choice', 'numerical' => 'Numerical'];
        $difficulties = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];

        return view('admin.questions.index', compact('questions', 'subjects', 'categories', 'types', 'difficulties'));
    }

    /**
     * Show form for creating new question.
     */
    public function create()
    {
        $subjects = Module::where('status', 'active')->get();
        $categories = QuestionCategory::active()->get();
        $types = [
            'single_choice' => 'Single Choice',
            'multiple_choice' => 'Multiple Choice',
            'numerical' => 'Numerical'
        ];
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard'
        ];

        return view('admin.questions.create', compact('subjects', 'categories', 'types', 'difficulties'));
    }

    /**
     * Store a newly created question.
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            $questionData = $request->except('options');
            $options = $request->input('options', []);

            $question = $this->questionRepository->createWithOptions($questionData, $options);

            return redirect()->route('admin.questions.index')
                            ->with('success', 'Question created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating question: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        $question->load(['creator', 'subject', 'categories', 'options']);

        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show form for editing question.
     */
    public function edit(Question $question)
    {
        $subjects = Module::where('status', 'active')->get();
        $categories = QuestionCategory::active()->get();
        $types = [
            'single_choice' => 'Single Choice',
            'multiple_choice' => 'Multiple Choice',
            'numerical' => 'Numerical'
        ];
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard'
        ];

        $question->load(['categories', 'options']);

        return view('admin.questions.edit', compact('question', 'subjects', 'categories', 'types', 'difficulties'));
    }

    /**
     * Update the specified question.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        try {
            $questionData = $request->except('options');
            $options = $request->input('options', []);

            $this->questionRepository->updateWithOptions($question->id, $questionData, $options);

            return redirect()->route('admin.questions.index')
                            ->with('success', 'Question updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating question: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question)
    {
        try {
            // Check if question is used in any exam
            if ($question->exams()->count() > 0) {
                return back()->with('error', 'Cannot delete question that is used in exams.');
            }

            $question->delete();

            return redirect()->route('admin.questions.index')
                            ->with('success', 'Question deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting question: ' . $e->getMessage());
        }
    }

    /**
     * Show import form.
     */
    public function showImportForm()
    {
        return view('admin.questions.import');
    }

    /**
     * Import questions from file.
     */
    public function import(ImportQuestionsRequest $request)
    {
        try {
            Excel::import(new QuestionsImport($request->type), $request->file('file'));

            return redirect()->route('admin.questions.index')
                            ->with('success', 'Questions imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing questions: ' . $e->getMessage());
        }
    }

    /**
     * Export questions to file.
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['type', 'difficulty', 'subject_id', 'category_id']);

            return Excel::download(new QuestionsExport($filters), 'questions_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting questions: ' . $e->getMessage());
        }
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'question_text', 'type', 'difficulty', 'marks', 'negative_marks',
            'explanation', 'subject_id', 'option_1', 'option_1_correct',
            'option_2', 'option_2_correct', 'option_3', 'option_3_correct',
            'option_4', 'option_4_correct'
        ];

        $filename = 'question_import_template.xlsx';

        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $headers;

            public function __construct($headers)
            {
                $this->headers = $headers;
            }

            public function array(): array
            {
                return [
                    $this->headers,
                    [
                        'Sample question?', 'single_choice', 'medium', '1', '0',
                        'This is a sample explanation', '1',
                        'Option A', '1', 'Option B', '0', 'Option C', '0', 'Option D', '0'
                    ]
                ];
            }
        }, $filename);
    }

    /**
     * Bulk delete questions.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        try {
            Question::whereIn('id', $request->question_ids)
                    ->whereDoesntHave('exams')
                    ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Selected questions deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting questions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update status.
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            Question::whereIn('id', $request->question_ids)
                    ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Question status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }
}
