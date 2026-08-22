<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\User;
use App\Models\RankList;
use App\Exports\ResultsExport;
use App\Exports\ExamReportExport;
use App\Exports\StudentReportExport;
use App\Exports\BatchReportExport;
use App\Exports\AllStudentsReportExport;
use App\Models\QuestionPerformance;
use App\Models\Batch;
use App\Models\Result;
use App\Models\Question;
use App\Models\Course;
use App\Services\Analytics\ResultAnalysisService;
use App\Services\Analytics\AdvancedAnalyticsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    protected $resultAnalysis;
    protected $advancedAnalytics;

    public function __construct(
        ResultAnalysisService $resultAnalysis,
        AdvancedAnalyticsService $advancedAnalytics
    ) {
        $this->resultAnalysis = $resultAnalysis;
        $this->advancedAnalytics = $advancedAnalytics;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }
    

    /**
     * Reports dashboard
     */
    public function index()
    {
        $stats = [
            'total_exams' => Exam::count(),
            'total_results' => Result::count(),
            'average_score' => round(Result::avg('percentage'), 2),
            'pass_rate' => round((Result::where('status', 'passed')->count() / max(Result::count(), 1)) * 100, 2),
            'total_students' => User::role('student')->count(),
            'active_students' => User::role('student')->whereHas('results', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->count()
        ];

        // Recent activity
        $recentResults = Result::with(['student', 'exam'])
            ->latest()
            ->take(10)
            ->get();

        // Top performers
        $topPerformers = Result::with('student')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('percentage', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact('stats', 'recentResults', 'topPerformers'));
    }
    
    
     /**
     * Exam-wise Reports
     */
    public function exams(Request $request)
    {
        $query = Exam::withCount('questions');

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate(15);

        $examTypes = [
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice'
        ];

        return view('admin.reports.exams', compact('exams', 'examTypes'));
    }

    /**
     * Exam Detailed Report
     */
    // public function examDetail(Exam $exam)
    // {
    //     $analytics = $this->advancedAnalytics->generateExamAnalytics($exam);

    //     return view('admin.reports.exam-detail', compact('exam', 'analytics'));
    // }

    /**
     * Student Performance Reports
     */
    public function students(Request $request)
    {
        $query = User::role('student')->with(['batches']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('batch_id')) {
            $query->whereHas('batches', function($q) use ($request) {
                $q->where('batches.id', $request->batch_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $students = $query->orderBy('name')->paginate(15);

        $batches = Batch::where('status', 'active')->get();

        return view('admin.reports.students', compact('students', 'batches'));
    }

    /**
     * Batch Performance Reports
     */
    
    
    public function batches(Request $request)
    {
        $query = Batch::with(['course', 'instructor']);
    
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }
    
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $batches = $query->orderBy('created_at', 'desc')->paginate(12);
    
        // Add performance stats to each batch
        $batches->getCollection()->transform(function($batch) {
            $stats = $this->resultAnalysis->getBatchPerformance($batch->id);
            $batch->performance = $stats;
            return $batch;
        });
    
        $courses = Course::where('status', 'active')->get();
    
        return view('admin.reports.batches', compact('batches', 'courses'));
    }

    /**
     * Batch Detailed Report
     */
    // public function batchDetail(Batch $batch)
    // {
    //     $report = $this->advancedAnalytics->generateBatchReport($batch);

    //     return view('admin.reports.batch-detail', compact('batch', 'report'));
    // }

    /**
     * Question Analysis
     */
    // public function questionAnalysis(Request $request)
    // {
    //     $query = Question::with(['performance']);

    //     if ($request->filled('exam_id')) {
    //         $query->whereHas('exams', function($q) use ($request) {
    //             $q->where('exams.id', $request->exam_id);
    //         });
    //     }

    //     if ($request->filled('type')) {
    //         $query->where('type', $request->type);
    //     }

    //     if ($request->filled('difficulty')) {
    //         $query->where('difficulty', $request->difficulty);
    //     }

    //     if ($request->filled('effectiveness')) {
    //         $query->whereHas('performance', function($q) use ($request) {
    //             $q->where('effectiveness_level', $request->effectiveness);
    //         });
    //     }

    //     $questions = $query->orderBy('id')->paginate(15);

    //     // Statistics
    //     $stats = [
    //         'total_questions' => Question::count(),
    //         'avg_accuracy' => round(Question::whereHas('performance')->avg('accuracy_rate'), 2),
    //         'avg_difficulty' => round(Question::whereHas('performance')->avg('difficulty_index'), 2),
    //         'good_questions' => Question::whereHas('performance', function($q) {
    //             $q->where('effectiveness_level', 'good');
    //         })->count()
    //     ];

    //     $exams = Exam::where('status', 'published')->get();

    //     return view('admin.reports.question-analysis', compact('questions', 'stats', 'exams'));
    // }
    
    // app/Http/Controllers/Admin/ReportController.php - Update questionAnalysis method

/**
 * Question Analysis
 */
public function questionAnalysis(Request $request)
{
    $query = Question::with(['performance']);

    if ($request->filled('exam_id')) {
        $query->whereHas('exams', function($q) use ($request) {
            $q->where('exams.id', $request->exam_id);
        });
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('difficulty')) {
        $query->where('difficulty', $request->difficulty);
    }

    if ($request->filled('effectiveness')) {
        $query->whereHas('performance', function($q) use ($request) {
            $q->where('effectiveness_level', $request->effectiveness);
        });
    }

    $questions = $query->orderBy('id')->paginate(15);

    // Statistics with null safety
    $stats = [
        'total_questions' => Question::count(),
        'avg_accuracy' => round(QuestionPerformance::avg('accuracy_rate') ?? 0, 2),
        'avg_difficulty' => round(QuestionPerformance::avg('difficulty_index') ?? 0, 2),
        'good_questions' => QuestionPerformance::where('effectiveness_level', 'good')->count()
    ];

    $exams = Exam::where('status', 'published')->get();

    return view('admin.reports.question-analysis', compact('questions', 'stats', 'exams'));
}

    /**
     * Question Detail Report
     */
    public function questionDetail(Question $question)
    {
        $analysis = $this->advancedAnalytics->generateQuestionAnalysis($question->id);

        return view('admin.reports.question-detail', compact('question', 'analysis'));
    }
    
    
    
    /**
     * Export Exam Report
     */
    public function exportExam(Exam $exam)
    {
        try {
            $fileName = 'exam_report_' . $exam->id . '_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new ExamReportExport($exam), $fileName);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export exam report: ' . $e->getMessage());
        }
    }

    /**
     * Export Student Report
     */
    public function exportStudent(Request $request, User $student)
    {
        try {
            $batchId = $request->get('batch_id');
            $fileName = 'student_report_' . $student->id . '_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new StudentReportExport($student, $batchId), $fileName);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export student report: ' . $e->getMessage());
        }
    }

    /**
     * Export Batch Report
     */
    public function exportBatch(Batch $batch)
    {
        try {
            $fileName = 'batch_report_' . $batch->id . '_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new BatchReportExport($batch), $fileName);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export batch report: ' . $e->getMessage());
        }
    }

    /**
     * Export All Students Report
     */
    public function exportStudents(Request $request)
    {
        try {
            $filters = $request->only(['batch_id', 'status']);
            $fileName = 'all_students_report_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new AllStudentsReportExport($filters), $fileName);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export students report: ' . $e->getMessage());
        }
    }

    
    /**
     * Exam-wise reports
     */
    public function examReports(Request $request)
    {
        $query = Exam::withCount(['results', 'studentExams']);

        if ($request->filled('exam_id')) {
            $query->where('id', $request->exam_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $exams = $query->paginate(15);

        $examTypes = [
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice'
        ];

        return view('admin.reports.exams', compact('exams', 'examTypes'));
    }

    /**
     * Exam detailed report
     */
    public function examDetail(Exam $exam)
    {
        $analytics = $this->resultAnalysis->getExamAnalytics($exam->id);

        // Get batch-wise performance
        $batchPerformance = [];
        foreach ($exam->batches as $batch) {
            $batchPerformance[$batch->id] = $this->resultAnalysis->getBatchPerformance($batch->id, $exam->id);
        }

        // Get question analysis
        $questionAnalysis = $this->resultAnalysis->getQuestionAnalysis($exam->id);

        return view('admin.reports.exam-detail', compact('exam', 'analytics', 'batchPerformance', 'questionAnalysis'));
    }

    /**
     * Student performance report
     */
    public function studentReports(Request $request)
    {
        $query = User::role('student')
            ->withCount(['results', 'exams'])
            ->withSum('results', 'obtained_marks');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('batch_id')) {
            $query->whereHas('batches', function($q) use ($request) {
                $q->where('batch_id', $request->batch_id);
            });
        }

        $students = $query->paginate(15);

        $batches = Batch::all();

        return view('admin.reports.students', compact('students', 'batches'));
    }

    /**
     * Student detailed report
     */
    public function studentDetail(User $student)
    {
        if (!$student->hasRole('student')) {
            abort(404);
        }

        $results = Result::where('student_id', $student->id)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->get();

        $trends = $this->resultAnalysis->getStudentTrends($student->id);

        $stats = [
            'total_exams' => $results->count(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'average_score' => round($results->avg('percentage'), 2),
            'best_score' => $results->max('percentage'),
            'total_correct' => $results->sum('correct_answers'),
            'total_incorrect' => $results->sum('incorrect_answers'),
            'average_accuracy' => round($results->avg('accuracy_percentage'), 2),
            'average_speed' => round($results->avg('speed_per_minute'), 2)
        ];

        // Subject-wise performance
        $subjectPerformance = [];
        foreach ($results as $result) {
            if ($result->sectional_analysis) {
                foreach ($result->sectional_analysis as $section) {
                    $subjectName = $section['section_name'];
                    if (!isset($subjectPerformance[$subjectName])) {
                        $subjectPerformance[$subjectName] = [
                            'total' => 0,
                            'correct' => 0,
                            'marks' => 0
                        ];
                    }
                    $subjectPerformance[$subjectName]['total'] += $section['total_questions'];
                    $subjectPerformance[$subjectName]['correct'] += $section['correct'];
                    $subjectPerformance[$subjectName]['marks'] += $section['obtained_marks'];
                }
            }
        }

        return view('admin.reports.student-detail', compact('student', 'results', 'stats', 'trends', 'subjectPerformance'));
    }

    /**
     * Batch performance report
     */
    public function batchReports(Request $request)
    {
        $query = Batch::with(['course', 'instructor'])
            ->withCount('students');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->paginate(15);

        // Add performance stats
        foreach ($batches as $batch) {
            $batch->performance = $this->resultAnalysis->getBatchPerformance($batch->id);
        }

        return view('admin.reports.batches', compact('batches'));
    }

    /**
     * Batch detailed report
     */
    /**
 * Batch Detailed Report
 */
public function batchDetail(Batch $batch)
{
    // Get batch performance
    $performance = $this->resultAnalysis->getBatchPerformance($batch->id);

    // Get exam-wise performance for this batch
    $examResults = Result::where('batch_id', $batch->id)
        ->with('exam')
        ->get()
        ->groupBy('exam_id');

    $examPerformance = [];
    foreach ($examResults as $examId => $results) {
        $exam = $results->first()->exam;
        $examPerformance[] = [
            'exam_id' => $examId,
            'exam_title' => $exam->title ?? 'Unknown',
            'total_students' => $results->count(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'average_score' => round($results->avg('percentage'), 2),
            'pass_rate' => $results->count() > 0
                ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                : 0
        ];
    }

    // Get student performance in this batch
    $students = [];
    $batchStudents = $batch->students()->with('results')->get();
    
    foreach ($batchStudents as $student) {
        $studentResults = $student->results()->where('batch_id', $batch->id)->get();
        if ($studentResults->isEmpty()) {
            continue;
        }
        
        $students[] = [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'email' => $student->email,
            'exams_taken' => $studentResults->count(),
            'average_score' => round($studentResults->avg('percentage'), 2),
            'best_score' => $studentResults->max('percentage'),
            'pass_rate' => $studentResults->count() > 0
                ? round(($studentResults->where('status', 'passed')->count() / $studentResults->count()) * 100, 2)
                : 0,
            'status' => $student->is_active ? 'active' : 'inactive'
        ];
    }

    // Sort by average score descending
    usort($students, function($a, $b) {
        return $b['average_score'] <=> $a['average_score'];
    });

    return view('admin.reports.batch-detail', compact('batch', 'performance', 'examPerformance', 'students'));
}

    /**
     * Export results
     */
    public function export(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'format' => 'required|in:xlsx,csv'
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        return Excel::download(
            new ResultsExport($exam->id),
            "results-{$exam->slug}-" . now()->format('Y-m-d') . ".{$request->format}"
        );
    }

    /**
     * Generate rank list
     */
    public function generateRankList(Exam $exam, Request $request)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id'
        ]);

        $rankList = RankList::generateForExam($exam, $request->batch_id, auth()->id());

        if (!$rankList) {
            return back()->with('error', 'No results found to generate rank list');
        }

        return redirect()->route('admin.reports.rank-list', $rankList->id)
            ->with('success', 'Rank list generated successfully');
    }

    /**
     * View rank list
     */
    public function viewRankList(RankList $rankList)
    {
        return view('admin.reports.rank-list', compact('rankList'));
    }

    /**
     * Publish rank list
     */
    public function publishRankList(RankList $rankList)
    {
        $rankList->publish();

        return response()->json([
            'success' => true,
            'message' => 'Rank list published successfully'
        ]);
    }
}

