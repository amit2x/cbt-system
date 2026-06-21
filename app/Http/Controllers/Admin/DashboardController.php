<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Result;
use App\Models\StudentExam;
use App\Models\Feedback;
use App\Models\StudyMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        // Get current date ranges
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // ==================== STATISTICS ====================
        $stats = [
            'total_students' => User::role('student')->count(),
            'total_instructors' => User::role('instructor')->count(),
            'total_courses' => Course::count(),
            'active_courses' => Course::where('status', 'active')->count(),
            'total_batches' => Batch::count(),
            'ongoing_batches' => Batch::where('status', 'ongoing')->count(),
            'total_exams' => Exam::count(),
            'published_exams' => Exam::where('status', 'published')->count(),
            'total_questions' => Question::count(),
            'active_questions' => Question::where('status', 'active')->count(),
            'total_results' => Result::count(),
            'total_attempts' => StudentExam::where('status', 'completed')->count(),
            'study_materials' => StudyMaterial::where('status', 'published')->count(),
            'feedback_count' => Feedback::count(),
        ];

        // ==================== RECENT ACTIVITY ====================
        $recentStudents = User::role('student')
            ->latest()
            ->take(5)
            ->get();

        $recentExams = Exam::with(['course', 'batch'])
            ->latest()
            ->take(5)
            ->get();

        $recentResults = Result::with(['student', 'exam'])
            ->latest()
            ->take(5)
            ->get();

        $recentFeedbacks = Feedback::with(['student', 'batch'])
            ->latest()
            ->take(5)
            ->get();

        // ==================== PERFORMANCE METRICS ====================
        // Average score across all exams
        $averageScore = Result::avg('percentage') ?? 0;
        
        // Pass rate
        $totalResults = Result::count();
        $passedResults = Result::where('status', 'passed')->count();
        $passRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 2) : 0;

        // Student engagement (students who have taken at least one exam)
        $totalStudents = User::role('student')->count();
        $activeStudents = User::role('student')
            ->whereHas('exams', function($q) {
                $q->where('status', 'completed');
            })
            ->count();
        $engagementRate = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0;

        // ==================== CHART DATA ====================
        // Daily activity for last 30 days
        $dailyActivity = $this->getDailyActivity();
        
        // Monthly trend for last 6 months
        $monthlyTrend = $this->getMonthlyTrend();
        
        // Course-wise exam performance
        $coursePerformance = $this->getCoursePerformance();

        // ==================== TOP PERFORMERS ====================
        $topPerformers = Result::with(['student', 'exam'])
            ->orderBy('percentage', 'desc')
            ->take(10)
            ->get();

        // ==================== BATCH PERFORMANCE ====================
        $batchPerformance = Batch::withCount(['students', 'exams'])
            ->with(['course'])
            ->take(5)
            ->get()
            ->map(function($batch) {
                $batch->average_score = Result::where('batch_id', $batch->id)->avg('percentage') ?? 0;
                $batch->pass_rate = Result::where('batch_id', $batch->id)
                    ->where('status', 'passed')
                    ->count() > 0 
                    ? round((Result::where('batch_id', $batch->id)->where('status', 'passed')->count() / 
                            max(Result::where('batch_id', $batch->id)->count(), 1)) * 100, 2)
                    : 0;
                return $batch;
            });

        // ==================== SYSTEM HEALTH ====================
        $systemHealth = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_views' => StudyMaterial::sum('views') ?? 0,
            'total_downloads' => StudyMaterial::sum('downloads') ?? 0,
            'server_time' => now()->format('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentStudents',
            'recentExams',
            'recentResults',
            'recentFeedbacks',
            'averageScore',
            'passRate',
            'engagementRate',
            'dailyActivity',
            'monthlyTrend',
            'coursePerformance',
            'topPerformers',
            'batchPerformance',
            'systemHealth'
        ));
    }

    /**
     * Get daily activity for the last 30 days
     */
    private function getDailyActivity()
    {
        $data = DB::table('results')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as attempts'),
                DB::raw('AVG(percentage) as avg_score')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d M');
            })->toArray(),
            'attempts' => $data->pluck('attempts')->toArray(),
            'avg_score' => $data->pluck('avg_score')->map(function($score) {
                return round($score, 2);
            })->toArray()
        ];
    }

    /**
     * Get monthly trend for the last 6 months
     */
    private function getMonthlyTrend()
    {
        $data = DB::table('results')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as attempts'),
                DB::raw('AVG(percentage) as avg_score')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $data->pluck('month')->map(function($month) {
                return \Carbon\Carbon::parse($month . '-01')->format('M Y');
            })->toArray(),
            'attempts' => $data->pluck('attempts')->toArray(),
            'avg_score' => $data->pluck('avg_score')->map(function($score) {
                return round($score, 2);
            })->toArray()
        ];
    }

    /**
     * Get course-wise performance
     */
    private function getCoursePerformance()
    {
        return Course::withCount(['exams', 'batches'])
            ->with(['exams.results'])
            ->take(6)
            ->get()
            ->map(function($course) {
                $allResults = $course->exams->flatMap(function($exam) {
                    return $exam->results;
                });
                
                return [
                    'name' => $course->title,
                    'exams' => $course->exams_count,
                    'batches' => $course->batches_count,
                    'avg_score' => $allResults->avg('percentage') ?? 0,
                    'pass_rate' => $allResults->count() > 0 
                        ? round(($allResults->where('status', 'passed')->count() / $allResults->count()) * 100, 2)
                        : 0
                ];
            });
    }
}