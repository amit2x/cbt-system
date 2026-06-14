<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\RankList;
use App\Models\Result;
use App\Models\User;
use App\Services\Exam\ResultAnalysisService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $resultAnalysis;

    public function __construct(ResultAnalysisService $resultAnalysis)
    {
        $this->resultAnalysis = $resultAnalysis;
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
    public function batchDetail(Batch $batch)
    {
        $performance = $this->resultAnalysis->getBatchPerformance($batch->id);

        // Get exam-wise performance
        $exams = Exam::where('batch_id', $batch->id)
            ->orWhereHas('batches', function($q) use ($batch) {
                $q->where('batch_id', $batch->id);
            })
            ->with(['results' => function($q) use ($batch) {
                $q->where('batch_id', $batch->id);
            }])
            ->get();

        $examPerformance = [];
        foreach ($exams as $exam) {
            $examResults = $exam->results->where('batch_id', $batch->id);
            $examPerformance[] = [
                'exam' => $exam,
                'attempted' => $examResults->count(),
                'average' => round($examResults->avg('percentage'), 2),
                'highest' => $examResults->max('percentage'),
                'passed' => $examResults->where('status', 'passed')->count()
            ];
        }

        // Student list with performance
        $students = $batch->students()
            ->with(['results' => function($q) use ($batch) {
                $q->where('batch_id', $batch->id);
            }])
            ->get()
            ->map(function($student) {
                $results = $student->results;
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'exams_taken' => $results->count(),
                    'average_score' => round($results->avg('percentage'), 2),
                    'best_score' => $results->max('percentage'),
                    'status' => $student->pivot->status
                ];
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
