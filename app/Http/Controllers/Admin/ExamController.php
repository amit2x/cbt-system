<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExamReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\AddQuestionsRequest;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
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
            'practice' => 'Practice',
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
            'practice' => 'Practice',
        ];
        $categories = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'custom' => 'Custom',
        ];

        return view('admin.exams.create', compact('courses', 'batches', 'types', 'categories'));
    }

    /**
     * Store a newly created exam.
     */
     public function store(StoreExamRequest $request)
    {
        Log::info('Exam store request data:', $request->all());
    
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
    
            // ✅ EXPLICITLY SET BOOLEANS
            // This ensures that if the checkbox is unchecked (and missing from request), it saves as false (0).
            $data['randomize_questions'] = $request->boolean('randomize_questions');
            $data['randomize_options'] = $request->boolean('randomize_options');
            $data['show_result_immediately'] = $request->boolean('show_result_immediately');
            $data['allow_review'] = $request->boolean('allow_review');
            $data['is_scheduled'] = $request->boolean('is_scheduled');
    
            // Handle scheduled exams
            if (! $data['is_scheduled']) {
                $data['start_time'] = null;
                $data['end_time'] = null;
            }
    
            // Validate module selection
            if (empty($data['module_id'])) {
                Log::warning('Module ID is empty');
                return back()->with('error', 'Please select a module for this exam.')->withInput();
            }
    
            $exam = Exam::create($data);
    
            // Attach modules if provided (for backward compatibility)
            if ($request->has('module_ids')) {
                $exam->modules()->attach($request->module_ids);
            }
    
            // Attach primary batch
            if ($request->has('batch_id') && !empty($request->batch_id)) {
                $exam->batches()->attach($request->batch_id);
            }
    
            // Attach additional batches
            if ($request->has('batch_ids') && !empty($request->batch_ids)) {
                $exam->batches()->attach($request->batch_ids);
            }
    
            DB::commit();
    
            return redirect()->route('admin.exams.show', $exam)
                ->with('success', 'Exam created successfully. Now add questions to the exam.');
                
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exam creation error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error creating exam: '.$e->getMessage())->withInput();
        }
    }
    // public function store(StoreExamRequest $request)
    // {
    //     Log::info('Exam store request data:', $request->all());

    //     DB::beginTransaction();
    //     try {
    //         $data = $request->validated();
    //         $data['created_by'] = auth()->id();

    //         // Handle scheduled exams
    //         if (! $request->is_scheduled) {
    //             $data['start_time'] = null;
    //             $data['end_time'] = null;
    //         }

    //         // Validate module selection
    //         if (empty($data['module_id'])) {
    //             Log::warning('Module ID is empty');

    //             return back()->with('error', 'Please select a module for this exam.')
    //                 ->withInput();
    //         }

    //         $exam = Exam::create($data);

    //         // Attach modules if provided (for backward compatibility)
    //         if ($request->has('module_ids')) {
    //             $exam->modules()->attach($request->module_ids);
    //         }

    //         // Attach primary batch
    //         if ($request->has('batch_id') && ! empty($request->batch_id)) {
    //             $exam->batches()->attach($request->batch_id);
    //         }

    //         // Attach additional batches
    //         if ($request->has('batch_ids') && ! empty($request->batch_ids)) {
    //             $exam->batches()->attach($request->batch_ids);
    //         }

    //         DB::commit();

    //         return redirect()->route('admin.exams.show', $exam)
    //             ->with('success', 'Exam created successfully. Now add questions to the exam.');
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         Log::error('Validation error:', $e->errors());

    //         return back()->withErrors($e->errors())->withInput();

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Exam creation error: '.$e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return back()->with('error', 'Error creating exam: '.$e->getMessage())
    //             ->withInput();
    //     }
    // }

    //     public function store(StoreExamRequest $request)
    // {
    //     // Debug: Log the incoming request data
    //     Log::info('Exam store request data:', $request->all());

    //     DB::beginTransaction();
    //     try {
    //         $data = $request->validated();
    //              Log::info('Validated data:', $data);

    //         $data['created_by'] = auth()->id();

    //         // Handle scheduled exams
    //         if (!$request->is_scheduled) {
    //             $data['start_time'] = null;
    //             $data['end_time'] = null;
    //         }

    //         // Validate module selection
    //         if (empty($data['module_id'])) {
    //             Log::warning('Module ID is empty');
    //             return back()->with('error', 'Please select a module for this exam.')
    //                         ->withInput();
    //         }

    //         Log::info('Creating exam with data:', $data);
    //         $exam = Exam::create($data);
    //         Log::info('Exam created with ID: ' . $exam->id);

    //         // Attach modules if provided (for backward compatibility)
    //         if ($request->has('module_ids')) {
    //             $exam->modules()->attach($request->module_ids);
    //         }

    //         // Attach multiple batches if provided
    //         if ($request->has('batch_ids')) {
    //             $exam->batches()->sync($request->batch_ids);
    //         }

    //         DB::commit();

    //         return redirect()->route('admin.exams.show', $exam)
    //                         ->with('success', 'Exam created successfully. Now add questions to the exam.');

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         DB::rollBack();
    //         Log::error('Validation error:', $e->errors());
    //         return back()->withErrors($e->errors())->withInput();

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Exam creation error: ' . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return back()->with('error', 'Error creating exam: ' . $e->getMessage())
    //                      ->withInput();
    //     }
    // }

    /**
     * Display the specified exam.
     */
    // public function show(Exam $exam)
    // {
    //     $exam->load(['course', 'modules', 'batch', 'batches', 'creator', 'sections' => function($q) {
    //         $q->withCount('questions');
    //     }]);

    //     $exam->loadCount(['questions', 'studentExams']);

    //     // Get questions grouped by section
    //     $questions = $exam->questions()
    //                      ->with(['subject', 'options'])
    //                      ->orderBy('pivot_order')
    //                      ->get()
    //                      ->groupBy('pivot.section_id');

    //     $stats = [
    //         'total_students' => $exam->total_students,
    //         'attempted' => $exam->attempted_count,
    //         'average_score' => round($exam->average_score, 2),
    //         'highest_score' => round($exam->highest_score, 2),
    //         'passing_count' => $exam->passing_count,
    //         'failing_count' => $exam->failing_count
    //     ];

    //     return view('admin.exams.show', compact('exam', 'questions', 'stats'));
    // }

    /**
     * Display the specified exam.
     */
    // public function show(Exam $exam)
    // {
    //     $exam->load(['course', 'batch', 'batches', 'creator', 'module', 'modules', 'sections' => function ($q) {
    //         $q->withCount('questions');
    //     }]);

    //     $exam->loadCount(['questions', 'studentExams']);

    //     // Get questions grouped by section
    //     $questions = $exam->questions()
    //         ->with(['subject', 'options'])
    //         ->orderBy('pivot_order')
    //         ->get()
    //         ->groupBy('pivot.section_id');

    //     $stats = [
    //         'total_students' => $exam->total_students,
    //         'attempted' => $exam->attempted_count,
    //         'average_score' => round($exam->average_score, 2),
    //         'highest_score' => round($exam->highest_score, 2),
    //         'passing_count' => $exam->passing_count,
    //         'failing_count' => $exam->failing_count,
    //     ];

    //     return view('admin.exams.show', compact('exam', 'questions', 'stats'));
    // }
    
    public function show(Exam $exam)
    {
        $exam->load([
            'course', 
            'batch', 
            'batches', 
            'creator', 
            'module', 
            'modules', 
            'sections' => function ($q) {
                $q->withCount('questions');
            }
        ]);
    
        $exam->loadCount(['questions', 'studentExams']);
    
        // Get questions grouped by section
        $questions = $exam->questions()
            ->with(['subject', 'options'])
            ->orderBy('pivot_order')
            ->get()
            ->groupBy('pivot.section_id');
    
        // ✅ 1. Check if the exam is actually completed
        $isExamCompleted = $exam->status === 'completed';
    
        // ✅ 2. Check if results are allowed to be shown (either immediately or manually published)
        $hasPublishedResults = $exam->results()->where('is_published', true)->exists();
        $resultsAllowed = $exam->show_result_immediately || $hasPublishedResults;
    
        // ✅ 3. Final decision: Only show stats if exam is completed AND results are allowed
        $canShowStats = $isExamCompleted && $resultsAllowed;
    
        if ($canShowStats) {
            $stats = [
                'total_students' => $exam->total_students ?? 0,
                'attempted' => $exam->attempted_count ?? 0,
                'average_score' => round($exam->average_score ?? 0, 2),
                'highest_score' => round($exam->highest_score ?? 0, 2),
                'passing_count' => $exam->passing_count ?? 0,
                'failing_count' => $exam->failing_count ?? 0,
                'is_pending' => false, 
            ];
        } else {
            $stats = [
                'total_students' => $exam->total_students ?? 0,
                'attempted' => $exam->attempted_count ?? 0,
                'average_score' => 0,
                'highest_score' => 0,
                'passing_count' => 0,
                'failing_count' => 0,
                'is_pending' => true, 
            ];
        }
    
        return view('admin.exams.show', compact('exam', 'questions', 'stats'));
    }

    /**
     * Show form for editing exam.
     */
    // public function edit(Exam $exam)
    // {
    //     $courses = Course::where('status', 'active')->get();
    //     $batches = Batch::whereIn('status', ['upcoming', 'ongoing'])->get();
    //     $types = [
    //         'mock_test' => 'Mock Test',
    //         'full_exam' => 'Full Exam',
    //         'quiz' => 'Quiz',
    //         'practice' => 'Practice'
    //     ];
    //     $categories = [
    //         'daily' => 'Daily',
    //         'weekly' => 'Weekly',
    //         'monthly' => 'Monthly',
    //         'custom' => 'Custom'
    //     ];

    //     $exam->load('batches');

    //     return view('admin.exams.edit', compact('exam', 'courses', 'batches', 'types', 'categories'));
    // }

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
            'practice' => 'Practice',
        ];
        $categories = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'custom' => 'Custom',
        ];

        $exam->load('batches');

        // Get modules for the course (will be loaded via AJAX)
        $modules = [];
        if ($exam->course_id) {
            $modules = Module::where('course_id', $exam->course_id)
                ->where('status', 'active')
                ->orderBy('order')
                ->get();
        }

        return view('admin.exams.edit', compact('exam', 'courses', 'batches', 'types', 'categories', 'modules'));
    }

    /**
     * Update the specified exam.
     */
    // public function update(UpdateExamRequest $request, Exam $exam)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $data = $request->validated();
    //         $data['updated_by'] = auth()->id();

    //         // Handle scheduled exams
    //         if (!$request->is_scheduled) {
    //             $data['start_time'] = null;
    //             $data['end_time'] = null;
    //         }

    //         $exam->update($data);

    //         // Update multiple batches
    //         if ($request->has('batch_ids')) {
    //             $exam->batches()->sync($request->batch_ids);
    //         }

    //         DB::commit();

    //         return redirect()->route('admin.exams.show', $exam)
    //                         ->with('success', 'Exam updated successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Error updating exam: ' . $e->getMessage())
    //                      ->withInput();
    //     }
    // }

    /**
     * Update the specified exam.
     */
    // public function update(UpdateExamRequest $request, Exam $exam)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $data = $request->validated();
    //         $data['updated_by'] = auth()->id();

    //         // Handle scheduled exams
    //         if (! $request->is_scheduled) {
    //             $data['start_time'] = null;
    //             $data['end_time'] = null;
    //         }

    //         // Update the exam
    //         $exam->update($data);

    //         // Sync modules if provided
    //         if ($request->has('module_ids')) {
    //             $exam->modules()->sync($request->module_ids);
    //         }

    //         // Sync primary batch
    //         $batchIds = [];
    //         if ($request->has('batch_id') && ! empty($request->batch_id)) {
    //             $batchIds[] = $request->batch_id;
    //         }

    //         // Sync additional batches
    //         if ($request->has('batch_ids') && ! empty($request->batch_ids)) {
    //             $batchIds = array_merge($batchIds, $request->batch_ids);
    //         }

    //         // Sync all batches
    //         $exam->batches()->sync($batchIds);

    //         DB::commit();

    //         return redirect()->route('admin.exams.show', $exam)
    //             ->with('success', 'Exam updated successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Exam update error: '.$e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return back()->with('error', 'Error updating exam: '.$e->getMessage())
    //             ->withInput();
    //     }
    // }
    
    public function update(UpdateExamRequest $request, Exam $exam)
    {
        Log::info('Exam update request data:', $request->all());
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['updated_by'] = auth()->id();
    
            // ✅ EXPLICITLY SET BOOLEANS
            $data['randomize_questions'] = $request->boolean('randomize_questions');
            $data['randomize_options'] = $request->boolean('randomize_options');
            $data['show_result_immediately'] = $request->boolean('show_result_immediately');
            $data['allow_review'] = $request->boolean('allow_review');
            $data['is_scheduled'] = $request->boolean('is_scheduled');
    
            // Handle scheduled exams
            if (! $data['is_scheduled']) {
                $data['start_time'] = null;
                $data['end_time'] = null;
            }
    
            // Update the exam
            $exam->update($data);
    
            // Sync modules if provided
            if ($request->has('module_ids')) {
                $exam->modules()->sync($request->module_ids);
            }
    
            // Sync primary batch
            $batchIds = [];
            if ($request->has('batch_id') && !empty($request->batch_id)) {
                $batchIds[] = $request->batch_id;
            }
    
            // Sync additional batches
            if ($request->has('batch_ids') && !empty($request->batch_ids)) {
                $batchIds = array_merge($batchIds, $request->batch_ids);
            }
    
            // Sync all batches
            $exam->batches()->sync($batchIds);
    
            DB::commit();
    
            return redirect()->route('admin.exams.show', $exam)
                ->with('success', 'Exam updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exam update error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error updating exam: '.$e->getMessage())->withInput();
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
            return back()->with('error', 'Error deleting exam: '.$e->getMessage());
        }
    }

    /**
     * Show add questions form.
     */
    // public function addQuestions(Exam $exam)
    // {
    //     $exam->load('questions');

    //     // Get available questions
    //     $questions = Question::with(['subject', 'options'])
    //                         ->where('status', 'active')
    //                         ->latest()
    //                         ->paginate(20);

    //     $sections = $exam->sections;

    //     return view('admin.exams.add-questions', compact('exam', 'questions', 'sections'));
    // }

    /**
     * Show add questions form.
     */
    public function addQuestions(Exam $exam)
    {
        // show only exam related modules, questions.
        // step-1 exams table contain course_id, module_id, batch_ids
        // i will use module_id to find questions  question->where(subjec_id, $exam->module_id)

        $exam->load(['course']);

        // Get questions based on exam's course and modules
        $query = Question::with(['subject', 'options'])
            // ->where('status', 'active');
            ->where('status', 'active')
            ->where('subject_id', $exam->module_id);

        // Filter by course if exam has one
        // if ($exam->course_id) {
        //     // Get modules for this course
        //     $moduleIds = Module::where('course_id', $exam->course_id)->pluck('id')->toArray();

        //     if (!empty($moduleIds)) {
        //         // Filter questions that belong to this course's modules
        //         $query->whereIn('subject_id', $moduleIds);
        //     }
        // }

        // If exam has specific modules (via pivot table)
        // if (method_exists($exam, 'modules') && $exam->modules()->count() > 0) {
        //     $moduleIds = $exam->modules()->pluck('id')->toArray();
        //     if (!empty($moduleIds)) {
        //         $query->whereIn('subject_id', $moduleIds);
        //     }
        // }

        // Exclude questions already added to this exam
        $existingQuestionIds = $exam->questions()->pluck('question_id')->toArray();
        if (! empty($existingQuestionIds)) {
            $query->whereNotIn('id', $existingQuestionIds);
        }

        $questions = $query->latest()->paginate(40);

        // Get sections for this exam
        $sections = $exam->sections;

        return view('admin.exams.add-questions', compact('exam', 'questions', 'sections'));
    }

    /**
     * Store questions for exam.
     */
    // public function storeQuestions(AddQuestionsRequest $request, Exam $exam)
    // {
    //     // dd($request->all());
    //     DB::beginTransaction();
    //     try {
    //         // $exam->questions()->detach();
    //         foreach ($request->questions as $index => $questionData) {

    //         if (!$exam->questions()->where('question_id', $questionData['question_id'])->exists()) {

    //             $exam->questions()->attach(
    //                 $questionData['question_id'],
    //                 [
    //                     'section_id' => $questionData['section_id'] ?? null,
    //                     'order' => $index,
    //                     'marks' => $questionData['marks'] ?? 1,
    //                     'negative_marks' => $questionData['negative_marks'] ?? 0,
    //                     'time_limit_seconds' => $questionData['time_limit_seconds'] ?? null,
    //                 ]
    //             );
    //         }
    //     }

    //         foreach ($request->questions as $index => $questionData) {
    //             $exam->questions()->attach($questionData['question_id'], [
    //                 'section_id' => $questionData['section_id'] ?? null,
    //                 'order' => $index,
    //                 'marks' => $questionData['marks'] ?? null,
    //                 'negative_marks' => $questionData['negative_marks'] ?? null,
    //                 'time_limit_seconds' => $questionData['time_limit_seconds'] ?? null
    //             ]);
    //         }

    //         // Update exam total marks
    //         $exam->updateTotalMarks();
    //         $exam->load('questions');
    //         DB::commit();

    //         return redirect()->route('admin.exams.show', $exam)
    //                         ->with('success', 'Questions added to exam successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Error adding questions: ' . $e->getMessage());
    //     }
    // }

    public function storeQuestions(AddQuestionsRequest $request, Exam $exam)
    {
        DB::beginTransaction();

        try {

            $syncData = [];

            foreach ($request->questions as $index => $questionData) {

                if (empty($questionData['question_id'])) {
                    continue;
                }

                $syncData[$questionData['question_id']] = [
                    'section_id' => $questionData['section_id'] ?? null,
                    'order' => $index,
                    'marks' => $questionData['marks'] ?? 1,
                    'negative_marks' => $questionData['negative_marks'] ?? 0,
                    'time_limit_seconds' => $questionData['time_limit_seconds'] ?? null,
                ];
            }

            // Add only new questions, don't remove existing ones
            $exam->questions()->syncWithoutDetaching($syncData);

            // Update total marks
            $exam->updateTotalMarks();

            DB::commit();

            return redirect()
                ->route('admin.exams.show', $exam)
                ->with('success', 'Questions added successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Error adding questions: '.$e->getMessage()
            );
        }
    }

    public function addRandomQuestions(Request $request, Exam $exam)
    {
        try {

            $request->validate([
                'count' => 'required|integer|min:1',
            ]);

            $count = (int) $request->count;

            $availableQuestionQuery = Question::whereNotIn(
                'id',
                $exam->questions()->pluck('questions.id')
            )
                ->where('subject_id', $exam->module_id);

            $availableCount = $availableQuestionQuery->count();

            if ($availableCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No questions available to add.',
                ], 422);
            }

            if ($count > $availableCount) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$availableCount} questions are available.",
                ], 422);
            }

            $availableQuestionIds = $availableQuestionQuery
                ->inRandomOrder()
                ->limit($count)
                ->pluck('id');

            $currentOrder = (int) $exam->questions()->max('exam_questions.order');

            $syncData = [];

            foreach ($availableQuestionIds as $index => $id) {

                $question = Question::find($id);

                $syncData[$id] = [
                    'marks' => $question->marks ?? 1,
                    'negative_marks' => 0,
                    'order' => $currentOrder + $index + 1,
                    'section_id' => null,
                    'time_limit_seconds' => null,
                ];
            }

            $exam->questions()->syncWithoutDetaching($syncData);

            $exam->updateTotalMarks();

            return response()->json([
                'success' => true,
                'message' => "{$count} questions added successfully.",
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Exception $e) {

            Log::error('Random Question Add Error', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add random questions.',
            ], 500);
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
                'message' => 'Question removed from exam',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing question',
            ], 500);
        }
    }

    public function bulkRemoveQuestions(Request $request, Exam $exam)
    {
        $request->validate([
            'question_ids' => 'required|array|min:1',
        ]);

        $exam->questions()->detach($request->question_ids);

        $exam->updateTotalMarks();

        return response()->json([
            'success' => true,
            'message' => count($request->question_ids)
                .' question(s) removed successfully.',
        ]);
    }

    /**
     * Update questions order.
     */
    public function updateQuestionsOrder(Request $request, Exam $exam)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:exam_questions,id',
            'questions.*.order' => 'required|integer',
        ]);

        try {
            foreach ($request->questions as $questionData) {
                DB::table('exam_questions')
                    ->where('id', $questionData['id'])
                    ->update(['order' => $questionData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Questions order updated',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order',
            ], 500);
        }
    }

    /**
     * Publish exam.
     */
    public function publish(Exam $exam)
    {
        try {
            if (! $exam->canBePublished()) {
                return back()->with('error', 'Cannot publish exam without questions.');
            }

            $exam->update(['status' => 'published']);

            return redirect()->route('admin.exams.show', $exam)
                ->with('success', 'Exam published successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error publishing exam: '.$e->getMessage());
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
            $newExam->title = $exam->title.' (Copy)';
            $newExam->slug = $exam->slug.'-copy-'.uniqid();
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
                    'time_limit_seconds' => $question->pivot->time_limit_seconds,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.exams.edit', $newExam)
                ->with('success', 'Exam duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error duplicating exam: '.$e->getMessage());
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
                'attempts_allowed' => $exam->attempts_allowed,
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
                    : 0,
            ],
            'question_analysis' => $this->getQuestionAnalysis($exam),
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
                ->whereIn('student_exam_id', function ($q) use ($exam) {
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
                'success_rate' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
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
            ->where('is_published', true)
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
            'total_marks' => $exam->total_marks,
        ];

        // Score distribution for chart
        $distribution = [
            '90-100' => $results->where('percentage', '>=', 90)->count(),
            '75-89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
            '60-74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
            '40-59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
            'below-40' => $results->where('percentage', '<', 40)->count(),
        ];

        return view('admin.exams.results', compact('exam', 'results', 'stats', 'distribution'));
    }

    /**
     * Export exam results.
     */
    public function exportResults(Exam $exam)
    {
        try {
            $fileName = 'exam_report_'.$exam->id.'_'.date('Y-m-d').'.xlsx';

            return Excel::download(new ExamReportExport($exam), $fileName);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export exam report: '.$e->getMessage());
        }
    }
}
