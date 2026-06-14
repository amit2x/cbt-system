<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsReport;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Result;
use App\Models\User;
use App\Services\Analytics\AdvancedAnalyticsService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use ApiResponse;

    protected $analyticsService;

    public function __construct(AdvancedAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Analytics Dashboard
     */
    public function dashboard(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $overallAnalytics = $this->analyticsService->generateOverallAnalytics($dateFrom, $dateTo);

        // Get recent reports
        $recentReports = AnalyticsReport::with('generator')
            ->latest()
            ->take(10)
            ->get();

        // Get popular metrics
        $popularExams = Exam::withCount('studentExams')
            ->having('student_exams_count', '>', 0)
            ->orderBy('student_exams_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.analytics.dashboard', compact(
            'overallAnalytics',
            'recentReports',
            'popularExams',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Exam Analytics
     */
    public function examAnalytics(Request $request)
    {
        $exams = Exam::withCount(['results', 'questions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($request->ajax()) {
            $examId = $request->input('exam_id');
            $exam = Exam::findOrFail($examId);
            $analytics = $this->analyticsService->generateExamAnalytics($exam->DeltaCMS);

            return $this->successResponse($analytics);
        }

        return view('admin.analytics.exams', compact('exams'));
    }

    /**
     * Show detailed exam analytics
     */
    public function showExamAnalytics(Exam $exam)
    {
        $analytics = $this->analyticsService->generateExamAnalytics($exam);

        // Get question performance
        $questionPerformance = [];
        foreach ($exam->questions as $question) {
            $questionPerformance[] = $this->analyticsService->generateQuestionAnalysis($question->id, $exam->id);
        }

        // Get student list with scores
        $students = $exam->results()
            ->with('student')
            ->orderBy('percentage', 'desc')
            ->paginate(20);

        return view('admin.analytics.exam-detail', compact('exam', 'analytics', 'questionPerformance', 'students'));
    }

    /**
     * Student Analytics
     */
    public function studentAnalytics(Request $request)
    {
        $query = User::role('student')->withCount('results');

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

        return view('admin.analytics.students', compact('students', 'batches'));
    }

    /**
     * Show detailed student analytics
     */
    public function showStudentAnalytics(User $student, Request $request)
    {
        if (!$student->hasRole('student')) {
            abort(404);
        }

        $batchId = $request->get('batch_id');
        $profile = $this->analyticsService->generateStudentProfile($student->id, $batchId);

        // Get recent results
        $recentResults = $student->results()
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get available batches for filtering
        $batches = $student->batches()->get();

        return view('admin.analytics.student-detail', compact('student', 'profile', 'recentResults', 'batches'));
    }

    /**
     * Batch Analytics
     */
    public function batchAnalytics(Request $request)
    {
        $batches = Batch::with(['course', 'instructor'])
            ->withCount('students')
            ->paginate(15);

        if ($request->ajax()) {
            $batchId = $request->get('batch_id');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $batch = Batch::findOrFail($batchId);
            $analytics = $this->analyticsService->generateBatchReport($batch, $dateFrom, $dateTo);

            return $this->successResponse($analytics);
        }

        return view('admin.analytics.batches', compact('batches'));
    }

    /**
     * Show detailed batch analytics
     */
    public function showBatchAnalytics(Batch $batch, Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $report = $this->analyticsService->generateBatchReport($batch, $dateFrom, $dateTo);

        // Get student list with performance
        $students = $batch->students()
            ->with(['results' => function($q) use ($batch, $dateFrom, $dateTo) {
                $q->where('batch_id', $batch->id);
                if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
            }])
            ->paginate(20);

        return view('admin.analytics.batch-detail', compact('batch', 'report', 'students', 'dateFrom', 'dateTo'));
    }

    /**
     * Question Analytics
     */
    public function questionAnalytics(Request $request)
    {
        $query = Question::withCount(['exams', 'options'])
            ->with(['subject', 'creator']);

        if ($request->filled('search')) {
            $query->where('question_text', 'like', "%{$request->search}%");
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $questions = $query->paginate(15);

        if ($request->ajax()) {
            $questionId = $request->get('question_id');
            $examId = $request->get('exam_id');

            $analysis = $this->analyticsService->generateQuestionAnalysis($questionId, $examId);

            return $this->successResponse($analysis);
        }

        return view('admin.analytics.questions', compact('questions'));
    }

    /**
     * Show detailed question analytics
     */
    public function showQuestionAnalytics(Question $question, Request $request)
    {
        $examId = $request->get('exam_id');
        $analysis = $this->analyticsService->generateQuestionAnalysis($question->id, $examId);

        // Get exams where this question was used
        $exams = $question->exams()
            ->withCount('studentExams')
            ->get();

        return view('admin.analytics.question-detail', compact('question', 'analysis', 'exams'));
    }

    /**
     * Generate custom report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:exam,student,batch,question,overall',
            'title' => 'required|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'exam_id' => 'required_if:report_type,exam|nullable|exists:exams,id',
            'batch_id' => 'required_if:report_type,batch|nullable|exists:batches,id',
            'student_id' => 'required_if:report_type,student|nullable|exists:users,id',
            'question_id' => 'required_if:report_type,question|nullable|exists:questions,id',
            'format' => 'nullable|in:json,pdf,excel,csv'
        ]);

        $filters = $request->only(['date_from', 'date_to', 'exam_id', 'batch_id', 'student_id', 'question_id']);

        // Generate data based on report type
        $data = null;
        switch ($request->report_type) {
            case 'exam':
                $exam = Exam::find($request->exam_id);
                $data = $this->analyticsService->generateExamAnalytics($exam);
                break;
            case 'student':
                $data = $this->analyticsService->generateStudentProfile($request->student_id, $request->batch_id);
                break;
            case 'batch':
                $batch = Batch::find($request->batch_id);
                $data = $this->analyticsService->generateBatchReport($batch, $request->date_from, $request->date_to);
                break;
            case 'question':
                $data = $this->analyticsService->generateQuestionAnalysis($request->question_id, $request->exam_id);
                break;
            case 'overall':
                $data = $this->analyticsService->generateOverallAnalytics($request->date_from, $request->date_to);
                break;
        }

        if (!$data) {
            return back()->with('error', 'No data available for the selected criteria');
        }

        // Save report
        $report = $this->analyticsService->saveReport(
            $request->report_type,
            $request->title,
            $data,
            $filters,
            auth()->id()
        );

        // Handle export format
        if ($request->filled('format') && $request->format !== 'json') {
            return $this->exportReport($report, $request->format);
        }

        return redirect()->route('admin.analytics.show-report', $report)
            ->with('success', 'Report generated successfully');
    }

    /**
     * Show saved report
     */
    public function showReport(AnalyticsReport $report)
    {
        return view('admin.analytics.show-report', compact('report'));
    }

    /**
     * List saved reports
     */
    public function listReports(Request $request)
    {
        $query = AnalyticsReport::with('generator')
            ->orderBy('created_at', 'desc');

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reports = $query->paginate(20);
        $reportTypes = [
            'exam' => 'Exam Reports',
            'student' => 'Student Reports',
            'batch' => 'Batch Reports',
            'question' => 'Question Analysis',
            'overall' => 'Overall Analytics'
        ];

        return view('admin.analytics.reports', compact('reports', 'reportTypes'));
    }

    /**
     * Export report
     */
    protected function exportReport(AnalyticsReport $report, $format)
    {
        switch ($format) {
            case 'pdf':
                // PDF export logic
                break;
            case 'excel':
                // Excel export logic
                break;
            case 'csv':
                // CSV export logic
                break;
        }

        return redirect()->back()->with('info', 'Export feature coming soon');
    }

    /**
     * Delete report
     */
    public function deleteReport(AnalyticsReport $report)
    {
        $report->delete();

        return redirect()->route('admin.analytics.reports')
            ->with('success', 'Report deleted successfully');
    }

    /**
     * Get real-time analytics data
     */
    public function getRealTimeData(Request $request)
    {
        $data = [
            'active_exams' => Exam::where('status', 'published')
                ->where(function($q) {
                    $q->whereNull('end_time')
                      ->orWhere('end_time', '>', now());
                })->count(),
            'active_students' => User::role('student')
                ->whereHas('exams', function($q) {
                    $q->where('status', 'started');
                })->count(),
            'completed_today' => Result::whereDate('created_at', today())->count(),
            'avg_score_today' => round(Result::whereDate('created_at', today())->avg('percentage'), 2)
        ];

        return $this->successResponse($data);
    }

    /**
     * Get comparison data
     */
    public function getComparisonData(Request $request)
    {
        $request->validate([
            'exam_ids' => 'required|array',
            'exam_ids.*' => 'exists:exams,id'
        ]);

        $comparison = [];
        foreach ($request->exam_ids as $examId) {
            $exam = Exam::find($examId);
            $analytics = $this->analyticsService->generateExamAnalytics($exam);

            $comparison[] = [
                'exam_id' => $examId,
                'exam_title' => $exam->title,
                'average_score' => $analytics['overview']['average_score'] ?? 0,
                'pass_rate' => $analytics['performance_metrics']['pass_rate'] ?? 0,
                'participation_rate' => $analytics['overview']['participation_rate'] ?? 0
            ];
        }

        return $this->successResponse($comparison);
    }
}
