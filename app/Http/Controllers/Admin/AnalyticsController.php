<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsReport;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionCategory;
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
    // public function dashboard(Request $request)
    // {
    //     $dateFrom = $request->get('date_from', now()->subMonths(1)->format('Y-m-d'));
    //     $dateTo = $request->get('date_to', now()->format('Y-m-d'));

    //     $overallAnalytics = $this->analyticsService->generateOverallAnalytics($dateFrom, $dateTo);

    //     // Get recent reports
    //     $recentReports = AnalyticsReport::with('generator')
    //         ->latest()
    //         ->take(10)
    //         ->get();

    //     // Get popular metrics
    //     $popularExams = Exam::withCount('studentExams')
    //         ->having('student_exams_count', '>', 0)
    //         ->orderBy('student_exams_count', 'desc')
    //         ->take(5)
    //         ->get();

    //     return view('admin.analytics.dashboard', compact(
    //         'overallAnalytics',
    //         'recentReports',
    //         'popularExams',
    //         'dateFrom',
    //         'dateTo'
    //     ));
    // }
    
    /**
 * Analytics Dashboard
 */
public function dashboard(Request $request)
{
    $dateFrom = $request->get('date_from', now()->subMonths(1)->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));

    // Get overall analytics
    $overallAnalytics = $this->analyticsService->generateOverallAnalytics($dateFrom, $dateTo);
    
    // Add missing keys for the dashboard
    $overallAnalytics['overview']['total_questions'] = Question::count();
    $overallAnalytics['overview']['active_questions'] = Question::where('status', 'active')->count();
    $overallAnalytics['overview']['top_performers_count'] = Result::orderBy('percentage', 'desc')
        ->distinct('student_id')
        ->take(10)
        ->count();
    $overallAnalytics['overview']['active_batches'] = Batch::where('status', 'ongoing')->count();
    $overallAnalytics['overview']['saved_reports'] = AnalyticsReport::count();
    $overallAnalytics['overview']['total_batches'] = Batch::count();
    $overallAnalytics['overview']['total_students'] = User::role('student')->count();
    $overallAnalytics['overview']['pass_rate'] = Result::count() > 0 
        ? round((Result::where('status', 'passed')->count() / Result::count()) * 100, 2)
        : 0;
    
    // Get popular exams
    $popularExams = Exam::withCount('studentExams')
        ->having('student_exams_count', '>', 0)
        ->orderBy('student_exams_count', 'desc')
        ->take(5)
        ->get();

    // Get recent reports
    $recentReports = AnalyticsReport::with('generator')
        ->latest()
        ->take(10)
        ->get();
    
    // Get performance by course
    $performanceByCourse = Course::withCount(['exams', 'batches'])
        ->with(['exams.results'])
        ->take(6)
        ->get()
        ->map(function($course) {
            $allResults = $course->exams->flatMap(function($exam) {
                return $exam->results;
            });
            
            return [
                'title' => $course->title,
                'avg_score' => $allResults->avg('percentage') ?? 0,
                'attempts' => $allResults->count()
            ];
        });
    
    $overallAnalytics['performance_by_course'] = $performanceByCourse;

    return view('admin.analytics.dashboard', compact(
        'overallAnalytics',
        'recentReports',
        'popularExams',
        'dateFrom',
        'dateTo'
    ));
}

/**
 * Generate overall system analytics
 */
public function generateOverallAnalytics($dateFrom = null, $dateTo = null)
{
    $query = Result::query();
    
    if ($dateFrom) {
        $query->whereDate('created_at', '>=', $dateFrom);
    }
    if ($dateTo) {
        $query->whereDate('created_at', '<=', $dateTo);
    }

    $results = $query->get();
    $totalExams = Exam::count();
    $totalStudents = User::role('student')->count();
    $activeStudents = User::role('student')
        ->whereHas('results', function($q) use ($dateFrom, $dateTo) {
            if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
        })->count();

    return [
        'overview' => [
            'total_exams' => $totalExams,
            'total_results' => $results->count(),
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'engagement_rate' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0,
            'average_score' => round($results->avg('percentage'), 2),
            'pass_rate' => $results->count() > 0 
                ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                : 0,
            // Add these for the dashboard
            'total_questions' => Question::count(),
            'active_questions' => Question::where('status', 'active')->count(),
            'top_performers_count' => Result::orderBy('percentage', 'desc')
                ->distinct('student_id')
                ->take(10)
                ->count(),
            'active_batches' => Batch::where('status', 'ongoing')->count(),
            'saved_reports' => AnalyticsReport::count(),
            'total_batches' => Batch::count(),
        ],
        'daily_activity' => $this->getDailyActivity($dateFrom, $dateTo),
        'popular_exams' => $this->getPopularExams(),
        'performance_by_course' => $this->getPerformanceByCourse(),
        'student_growth' => $this->getStudentGrowth(),
        'difficulty_distribution' => $this->getDifficultyDistribution()
    ];
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
    // public function studentAnalytics(Request $request)
    // {
    //     $query = User::role('student')->withCount('results');

    //     if ($request->filled('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('name', 'like', "%{$request->search}%")
    //               ->orWhere('email', 'like', "%{$request->search}%");
    //         });
    //     }

    //     if ($request->filled('batch_id')) {
    //         $query->whereHas('batches', function($q) use ($request) {
    //             $q->where('batch_id', $request->batch_id);
    //         });
    //     }

    //     $students = $query->paginate(15);
    //     $batches = Batch::all();

    //     return view('admin.analytics.students', compact('students', 'batches'));
    // }
    /**
 * Student Analytics
 */
public function studentAnalytics(Request $request)
{
    $query = User::role('student')->with(['batches', 'results']);
    
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
    
    // Calculate stats
    $totalStudents = User::role('student')->count();
    $activeStudents = User::role('student')->where('is_active', true)->count();
    $averageScore = Result::avg('percentage') ?? 0;
    
    // Top performer
    $topResult = Result::with('student')
        ->orderBy('percentage', 'desc')
        ->first();
    
    $topPerformer = $topResult ? [
        'name' => $topResult->student->name,
        'score' => $topResult->percentage
    ] : null;
    
    // Distribution
    $distribution = [
        'excellent' => Result::where('percentage', '>=', 80)->count(),
        'good' => Result::where('percentage', '>=', 60)->where('percentage', '<', 80)->count(),
        'average' => Result::where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
        'below' => Result::where('percentage', '<', 40)->count()
    ];
    
    // Subject performance
    $subjectPerformance = [];
    $batches = Batch::all();
    
    return view('admin.analytics.students', compact(
        'students', 'totalStudents', 'activeStudents', 'averageScore',
        'topPerformer', 'distribution', 'subjectPerformance', 'batches'
    ));
}

/**
 * Batch Analytics
 */
public function batchAnalytics()
{
    $batches = Batch::with(['course', 'students', 'exams.results'])
        ->withCount(['students', 'exams'])
        ->paginate(15);
    
    // Calculate stats
    $totalBatches = Batch::count();
    $ongoingBatches = Batch::where('status', 'ongoing')->count();
    $totalStudents = Batch::withCount('students')->get()->sum('students_count');
    $averageScore = round(Result::avg('percentage') ?? 0, 2); 
    // Chart data
    $batchLabels = [];
    $batchScores = [];
    $batchPassRates = [];
    
    foreach ($batches as $batch) {
        $batchLabels[] = $batch->name;
        $batchScores[] = round($batch->exams->flatMap->results->avg('percentage') ?? 0, 2);
        $batchPassRates[] = round($batch->exams->flatMap->results->where('status', 'passed')->count() / 
            max($batch->exams->flatMap->results->count(), 1) * 100, 2);
    }
    
    $statusCounts = [
        'ongoing' => Batch::where('status', 'ongoing')->count(),
        'upcoming' => Batch::where('status', 'upcoming')->count(),
        'completed' => Batch::where('status', 'completed')->count()
    ];
    
    return view('admin.analytics.batches', compact(
        'batches', 'totalBatches', 'ongoingBatches', 'totalStudents',
        'averageScore', 'batchLabels', 'batchScores', 'batchPassRates', 'statusCounts'
    ));
}

/**
 * Question Analytics
 */
/**
 * Question Analytics
 */
public function questionAnalytics(Request $request)
{
    $query = Question::with(['subject', 'categories', 'exams'])
        ->withCount('exams');
    
    if ($request->filled('search')) {
        $query->where('question_text', 'like', "%{$request->search}%");
    }
    
    $questions = $query->paginate(15);
    
    // Stats
    $totalQuestions = Question::count();
    $activeQuestions = Question::where('status', 'active')->count();
    $totalCategories = QuestionCategory::count();
    
    // Distribution by type
    $typeDistribution = [
        'single_choice' => Question::where('type', 'single_choice')->count(),
        'multiple_choice' => Question::where('type', 'multiple_choice')->count(),
        'numerical' => Question::where('type', 'numerical')->count()
    ];
    
    // Distribution by difficulty
    $difficultyDistribution = [
        'easy' => Question::where('difficulty', 'easy')->count(),
        'medium' => Question::where('difficulty', 'medium')->count(),
        'hard' => Question::where('difficulty', 'hard')->count()
    ];
    
    return view('admin.analytics.questions', compact(
        'questions', 'totalQuestions', 'activeQuestions', 'totalCategories',
        'typeDistribution', 'difficultyDistribution'
    ));
}

    /**
     * Show detailed student analytics
     */
    // public function showStudentAnalytics(User $student, Request $request)
    // {
    //     if (!$student->hasRole('student')) {
    //         abort(404);
    //     }

    //     $batchId = $request->get('batch_id');
    //     $profile = $this->analyticsService->generateStudentProfile($student->id, $batchId);

    //     // Get recent results
    //     $recentResults = $student->results()
    //         ->with('exam')
    //         ->orderBy('created_at', 'desc')
    //         ->take(10)
    //         ->get();

    //     // Get available batches for filtering
    //     $batches = $student->batches()->get();

    //     return view('admin.analytics.student-detail', compact('student', 'profile', 'recentResults', 'batches'));
    // }
    
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

    // FIX: Ensure all array fields are properly decoded
    if ($profile) {
        // Decode strengths if it's a JSON string
        if (isset($profile['strengths'])) {
            if (is_string($profile['strengths'])) {
                $profile['strengths'] = json_decode($profile['strengths'], true) ?? [];
            }
            // Ensure it's an array
            if (!is_array($profile['strengths'])) {
                $profile['strengths'] = [];
            }
        }

        // Decode weaknesses if it's a JSON string
        if (isset($profile['weaknesses'])) {
            if (is_string($profile['weaknesses'])) {
                $profile['weaknesses'] = json_decode($profile['weaknesses'], true) ?? [];
            }
            if (!is_array($profile['weaknesses'])) {
                $profile['weaknesses'] = [];
            }
        }

        // Decode topic_analysis if it's a JSON string
        if (isset($profile['topic_analysis'])) {
            if (is_string($profile['topic_analysis'])) {
                $profile['topic_analysis'] = json_decode($profile['topic_analysis'], true) ?? [];
            }
            if (!is_array($profile['topic_analysis'])) {
                $profile['topic_analysis'] = [];
            }
            
            // Normalize topic data
            $profile['topic_analysis'] = collect($profile['topic_analysis'])->map(function($topic) {
                // If the topic has 'name' instead of 'topic', rename it
                if (isset($topic['name']) && !isset($topic['topic'])) {
                    $topic['topic'] = $topic['name'];
                }
                return [
                    'topic' => $topic['topic'] ?? 'Uncategorized',
                    'total' => (int) ($topic['total'] ?? 0),
                    'correct' => (int) ($topic['correct'] ?? 0),
                    'incorrect' => (int) ($topic['incorrect'] ?? 0),
                    'accuracy' => (float) ($topic['accuracy'] ?? 0)
                ];
            })->toArray();
        }

        // Decode trends if it's a JSON string
        if (isset($profile['trends']) && is_string($profile['trends'])) {
            $profile['trends'] = json_decode($profile['trends'], true) ?? [];
        }
    }

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
    // public function batchAnalytics(Request $request)
    // {
    //     $batches = Batch::with(['course', 'instructor'])
    //         ->withCount('students')
    //         ->paginate(15);

    //     if ($request->ajax()) {
    //         $batchId = $request->get('batch_id');
    //         $dateFrom = $request->get('date_from');
    //         $dateTo = $request->get('date_to');

    //         $batch = Batch::findOrFail($batchId);
    //         $analytics = $this->analyticsService->generateBatchReport($batch, $dateFrom, $dateTo);

    //         return $this->successResponse($analytics);
    //     }

    //     return view('admin.analytics.batches', compact('batches'));
    // }

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
    // public function questionAnalytics(Request $request)
    // {
    //     $query = Question::withCount(['exams', 'options'])
    //         ->with(['subject', 'creator']);

    //     if ($request->filled('search')) {
    //         $query->where('question_text', 'like', "%{$request->search}%");
    //     }

    //     if ($request->filled('difficulty')) {
    //         $query->where('difficulty', $request->difficulty);
    //     }

    //     if ($request->filled('type')) {
    //         $query->where('type', $request->type);
    //     }

    //     $questions = $query->paginate(15);

    //     if ($request->ajax()) {
    //         $questionId = $request->get('question_id');
    //         $examId = $request->get('exam_id');

    //         $analysis = $this->analyticsService->generateQuestionAnalysis($questionId, $examId);

    //         return $this->successResponse($analysis);
    //     }

    //     return view('admin.analytics.questions', compact('questions'));
    // }

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
    
    //exports method
    /**
 * Export exam analytics
 */
public function exportExamAnalytics(Exam $exam)
{
    $analytics = $this->analyticsService->generateExamAnalytics($exam);
    
    if (!$analytics) {
        return back()->with('error', 'No data available for export.');
    }

    // Create CSV export
    $filename = "exam_analytics_{$exam->id}_" . now()->format('Y-m-d') . ".csv";
    $handle = fopen('php://output', 'w');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Add headers
    fputcsv($handle, ['Metric', 'Value']);

    // Add data
    $this->addToCsv($handle, 'Exam Title', $exam->title);
    $this->addToCsv($handle, 'Total Participants', $analytics['overview']['total_participants'] ?? 0);
    $this->addToCsv($handle, 'Participation Rate', ($analytics['overview']['participation_rate'] ?? 0) . '%');
    $this->addToCsv($handle, 'Average Score', ($analytics['overview']['average_score'] ?? 0) . '%');
    $this->addToCsv($handle, 'Highest Score', ($analytics['overview']['highest_score'] ?? 0) . '%');
    $this->addToCsv($handle, 'Lowest Score', ($analytics['overview']['lowest_score'] ?? 0) . '%');
    $this->addToCsv($handle, 'Passed', $analytics['performance_metrics']['passed'] ?? 0);
    $this->addToCsv($handle, 'Failed', $analytics['performance_metrics']['failed'] ?? 0);
    $this->addToCsv($handle, 'Pass Rate', ($analytics['performance_metrics']['pass_rate'] ?? 0) . '%');
    $this->addToCsv($handle, 'Average Accuracy', ($analytics['performance_metrics']['average_accuracy'] ?? 0) . '%');
    $this->addToCsv($handle, 'Average Speed', ($analytics['performance_metrics']['average_speed'] ?? 0) . ' Q/min');

    fclose($handle);
    exit;
}

/**
 * Export student analytics
 */
public function exportStudentAnalytics(User $student, Request $request)
{
    $batchId = $request->get('batch_id');
    $profile = $this->analyticsService->generateStudentProfile($student->id, $batchId);
    
    if (!$profile) {
        return back()->with('error', 'No data available for export.');
    }

    $filename = "student_analytics_{$student->id}_" . now()->format('Y-m-d') . ".csv";
    $handle = fopen('php://output', 'w');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    fputcsv($handle, ['Metric', 'Value']);
    $this->addToCsv($handle, 'Student Name', $student->name);
    $this->addToCsv($handle, 'Student Email', $student->email);
    $this->addToCsv($handle, 'Total Exams', $profile['student_info']['total_exams']);
    $this->addToCsv($handle, 'Exams Passed', $profile['student_info']['exams_passed']);
    $this->addToCsv($handle, 'Pass Rate', $profile['student_info']['pass_rate'] . '%');
    $this->addToCsv($handle, 'Average Score', ($profile['performance']['average_score'] ?? 0) . '%');
    $this->addToCsv($handle, 'Average Accuracy', ($profile['performance']['average_accuracy'] ?? 0) . '%');
    $this->addToCsv($handle, 'Consistency Score', ($profile['performance']['consistency_score'] ?? 0) . '%');
    $this->addToCsv($handle, 'Improvement Rate', ($profile['performance']['improvement_rate'] ?? 0) . '%');

    fclose($handle);
    exit;
}

/**
 * Export batch analytics
 */
public function exportBatchAnalytics(Batch $batch, Request $request)
{
    $dateFrom = $request->get('date_from');
    $dateTo = $request->get('date_to');
    
    $report = $this->analyticsService->generateBatchReport($batch, $dateFrom, $dateTo);
    
    if (!$report) {
        return back()->with('error', 'No data available for export.');
    }

    $filename = "batch_analytics_{$batch->id}_" . now()->format('Y-m-d') . ".csv";
    $handle = fopen('php://output', 'w');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    fputcsv($handle, ['Metric', 'Value']);
    $this->addToCsv($handle, 'Batch Name', $batch->name);
    $this->addToCsv($handle, 'Course', $batch->course->title ?? 'N/A');
    $this->addToCsv($handle, 'Total Students', $report['batch_info']['total_students']);
    $this->addToCsv($handle, 'Active Students', $report['batch_info']['active_students']);
    $this->addToCsv($handle, 'Total Exams', $report['overall_performance']['total_exams'] ?? 0);
    $this->addToCsv($handle, 'Average Score', ($report['overall_performance']['average_score'] ?? 0) . '%');
    $this->addToCsv($handle, 'Pass Rate', ($report['overall_performance']['pass_rate'] ?? 0) . '%');

    fclose($handle);
    exit;
}

/**
 * Helper: Add data to CSV
 */
private function addToCsv($handle, $key, $value)
{
    fputcsv($handle, [$key, $value]);
}



/**
 * Analyze options for MCQ - FIXED with UTF-8 sanitization
 */
protected function analyzeOptions($questionId, $answers)
{
    $options = Question::find($questionId)->options;
    $analysis = [];

    foreach ($options as $option) {
        $count = $answers->filter(function($answer) use ($option) {
            $selected = json_decode($answer->selected_options ?? '[]', true);
            return in_array($option->id, $selected);
        })->count();

        // ✅ Sanitize option text to remove invalid UTF-8 characters
        $optionText = $option->option_text ?? '';
        $optionText = $this->sanitizeUtf8($optionText);
        $optionText = substr($optionText, 0, 50);

        $analysis['option_' . $option->id] = [
            'option_text' => $optionText,
            'count' => $count,
            'is_correct' => (bool) $option->is_correct
        ];
    }

    return $analysis;
}

/**
 * Sanitize UTF-8 string - removes invalid characters
 */
protected function sanitizeUtf8($string)
{
    if (is_null($string)) {
        return '';
    }
    
    // Remove invalid UTF-8 characters
    $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    
    // Remove control characters (except newlines, tabs, etc.)
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);
    
    // Remove any remaining invalid characters
    $string = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\x80-\xFF]/u', '', $string);
    
    return $string;
}

/**
 * Generate question performance analysis - FIXED
 */
public function generateQuestionAnalysis($questionId, $examId = null)
{
    $query = DB::table('student_answers')
        ->join('student_exams', 'student_answers.student_exam_id', '=', 'student_exams.id')
        ->where('student_answers.question_id', $questionId);

    if ($examId) {
        $query->where('student_exams.exam_id', $examId);
    }

    $answers = $query->select(
        'student_answers.*',
        'student_exams.exam_id',
        'student_exams.student_id'
    )->get();

    $question = Question::find($questionId);

    if ($answers->isEmpty()) {
        return null;
    }

    $total = $answers->count();
    $correct = $answers->where('is_correct', true)->count();
    $incorrect = $answers->where('is_correct', false)
        ->filter(function($a) {
            return $a->selected_options || $a->answer_text;
        })->count();
    $unanswered = $total - $correct - $incorrect;

    // Calculate time metrics
    $times = $answers->pluck('time_taken_seconds')->filter();
    $avgTime = $times->avg() ?: 0;
    $minTime = $times->min() ?: 0;
    $maxTime = $times->max() ?: 0;

    // Calculate difficulty index (percentage who got it wrong)
    $difficultyIndex = $total > 0 ? round(($incorrect / $total) * 100, 2) : 0;

    // Option analysis for MCQs - with UTF-8 sanitization
    $optionAnalysis = null;
    if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
        $optionAnalysis = $this->analyzeOptions($questionId, $answers);
        
        // ✅ Ensure optionAnalysis is JSON encodable
        if (is_array($optionAnalysis)) {
            // Sanitize all values
            foreach ($optionAnalysis as $key => $value) {
                if (is_string($value)) {
                    $optionAnalysis[$key] = $this->sanitizeUtf8($value);
                }
            }
        }
    }

    // Calculate discrimination index
    $discriminationIndex = $this->calculateDiscriminationIndex($questionId, $answers);

    // Determine effectiveness
    $effectivenessScore = $total > 0 ? ($correct / $total) * 100 : 0;
    $effectivenessLevel = $this->determineEffectiveness($effectivenessScore, $difficultyIndex);

    // ✅ Convert option_analysis to JSON and ensure it's valid
    $optionAnalysisJson = null;
    if ($optionAnalysis) {
        $jsonString = json_encode($optionAnalysis);
        if ($jsonString === false) {
            // If encoding fails, try with sanitized data
            $optionAnalysis = $this->sanitizeArray($optionAnalysis);
            $jsonString = json_encode($optionAnalysis);
        }
        $optionAnalysisJson = $jsonString !== false ? $jsonString : '{}';
    }

    // Update or create question performance record
    QuestionPerformance::updateOrCreate(
        ['question_id' => $questionId, 'exam_id' => $examId],
        [
            'total_attempts' => $total,
            'correct_attempts' => $correct,
            'incorrect_attempts' => $incorrect,
            'accuracy_rate' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
            'avg_time_seconds' => round($avgTime, 2),
            'min_time_seconds' => round($minTime, 2),
            'max_time_seconds' => round($maxTime, 2),
            'difficulty_index' => $difficultyIndex,
            'discrimination_index' => $discriminationIndex,
            'option_analysis' => $optionAnalysisJson, // ✅ Store as JSON string
            'effectiveness_score' => round($effectivenessScore, 2),
            'effectiveness_level' => $effectivenessLevel
        ]
    );

    return [
        'question_id' => $questionId,
        'question_text' => substr($this->sanitizeUtf8(strip_tags($question->question_text)), 0, 200),
        'type' => $question->type_name,
        'difficulty' => $question->difficulty_name,
        'metrics' => [
            'total_attempts' => $total,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'unanswered' => $unanswered,
            'accuracy_rate' => round(($correct / max($total, 1)) * 100, 2),
            'difficulty_index' => $difficultyIndex,
            'discrimination_index' => $discriminationIndex,
            'avg_time' => gmdate('i:s', (int) $avgTime),
            'effectiveness_level' => $effectivenessLevel
        ],
        'option_analysis' => $optionAnalysis
    ];
}

/**
 * Sanitize all strings in an array recursively
 */
protected function sanitizeArray($array)
{
    if (!is_array($array)) {
        return $this->sanitizeUtf8((string) $array);
    }

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = $this->sanitizeArray($value);
        } elseif (is_string($value)) {
            $array[$key] = $this->sanitizeUtf8($value);
        }
    }

    return $array;
}


}
