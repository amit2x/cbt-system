<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Batch;
use App\Models\StudentExam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display analytics dashboard
     */
    public function index()
    {
        $instructor = auth()->user();
        
        // Get batches taught by instructor
        $batchIds = Batch::where('instructor_id', $instructor->id)->pluck('id');
        
        // Get exams for instructor's batches
        $examIds = Exam::whereIn('batch_id', $batchIds)
            ->orWhereHas('batches', function($q) use ($batchIds) {
                $q->whereIn('batches.id', $batchIds);
            })
            ->pluck('id');

        // Overall statistics
        $stats = [
            'total_exams' => Exam::whereIn('id', $examIds)->count(),
            'total_students' => User::role('student')
                ->whereHas('batches', function($q) use ($batchIds) {
                    $q->whereIn('batches.id', $batchIds);
                })
                ->count(),
            'total_attempts' => StudentExam::whereIn('exam_id', $examIds)
                ->where('status', 'completed')
                ->count(),
            'average_score' => round(Result::whereIn('exam_id', $examIds)->avg('percentage') ?? 0, 2),
            'pass_rate' => round(Result::whereIn('exam_id', $examIds)
                ->where('status', 'passed')
                ->count() / max(Result::whereIn('exam_id', $examIds)->count(), 1) * 100, 2),
            'total_questions_attempted' => Result::whereIn('exam_id', $examIds)->sum('correct_answers') 
                + Result::whereIn('exam_id', $examIds)->sum('incorrect_answers'),
            'total_correct' => Result::whereIn('exam_id', $examIds)->sum('correct_answers'),
            'total_incorrect' => Result::whereIn('exam_id', $examIds)->sum('incorrect_answers'),
        ];

        // Exam performance
        $exams = Exam::whereIn('id', $examIds)
            ->withCount(['questions', 'studentExams'])
            ->with(['batch.course'])
            ->get()
            ->map(function($exam) {
                $results = Result::where('exam_id', $exam->id);
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'type' => $exam->type_name,
                    'questions' => $exam->questions_count,
                    'attempts' => $exam->student_exams_count,
                    'avg_score' => round($results->avg('percentage') ?? 0, 2),
                    'pass_rate' => round($results->where('status', 'passed')->count() / max($results->count(), 1) * 100, 2),
                    'batch' => $exam->batch->name ?? 'N/A',
                    'course' => $exam->course->title ?? 'N/A',
                    'created_at' => $exam->created_at,
                ];
            })
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        // Batch performance
        $batchPerformance = Batch::whereIn('id', $batchIds)
            ->with(['course', 'students'])
            ->withCount('students')
            ->get()
            ->map(function($batch) {
                $examIds = $batch->exams()->pluck('id');
                $results = Result::whereIn('exam_id', $examIds);
                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'course' => $batch->course->title ?? 'N/A',
                    'students' => $batch->students_count,
                    'exams' => $batch->exams()->count(),
                    'avg_score' => round($results->avg('percentage') ?? 0, 2),
                    'pass_rate' => round($results->where('status', 'passed')->count() / max($results->count(), 1) * 100, 2),
                ];
            })
            ->sortByDesc('avg_score')
            ->values();

        // Performance trend (last 30 days)
        $trendData = Result::whereIn('exam_id', $examIds)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as attempts'),
                DB::raw('AVG(percentage) as avg_score')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trend = [
            'labels' => $trendData->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d M');
            })->toArray(),
            'attempts' => $trendData->pluck('attempts')->toArray(),
            'avg_score' => $trendData->pluck('avg_score')->map(function($score) {
                return round($score, 2);
            })->toArray(),
        ];

        // Top performing students
        $topStudents = User::role('student')
            ->whereHas('batches', function($q) use ($batchIds) {
                $q->whereIn('batches.id', $batchIds);
            })
            ->with(['results' => function($q) use ($examIds) {
                $q->whereIn('exam_id', $examIds);
            }])
            ->get()
            ->map(function($student) {
                $results = $student->results;
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'exams' => $results->count(),
                    'avg_score' => round($results->avg('percentage') ?? 0, 2),
                    'best_score' => round($results->max('percentage') ?? 0, 2),
                ];
            })
            ->sortByDesc('avg_score')
            ->take(10)
            ->values();

        return view('instructor.analytics.index', compact(
            'stats',
            'exams',
            'batchPerformance',
            'trend',
            'topStudents'
        ));
    }

    /**
     * Exam analytics
     */
    public function examAnalytics(Request $request)
    {
        $instructor = auth()->user();
        $batchIds = Batch::where('instructor_id', $instructor->id)->pluck('id');
        
        $query = Exam::whereIn('batch_id', $batchIds)
            ->orWhereHas('batches', function($q) use ($batchIds) {
                $q->whereIn('batches.id', $batchIds);
            })
            ->withCount(['questions', 'studentExams']);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $exams = $query->latest()->paginate(15);

        return view('instructor.analytics.exams', compact('exams'));
    }

    /**
     * Student analytics
     */
    public function studentAnalytics(Request $request)
    {
        $instructor = auth()->user();
        $batchIds = Batch::where('instructor_id', $instructor->id)->pluck('id');

        $query = User::role('student')
            ->whereHas('batches', function($q) use ($batchIds) {
                $q->whereIn('batches.id', $batchIds);
            })
            ->with(['results']);

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

        $batches = Batch::whereIn('id', $batchIds)->get();

        return view('instructor.analytics.students', compact('students', 'batches'));
    }

    /**
     * Batch analytics
     */
    public function batchAnalytics()
    {
        $instructor = auth()->user();
        
        $batches = Batch::where('instructor_id', $instructor->id)
            ->with(['course', 'students'])
            ->withCount('students')
            ->get()
            ->map(function($batch) {
                $examIds = $batch->exams()->pluck('id');
                $results = Result::whereIn('exam_id', $examIds);
                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'course' => $batch->course->title ?? 'N/A',
                    'students' => $batch->students_count,
                    'exams' => $batch->exams()->count(),
                    'avg_score' => round($results->avg('percentage') ?? 0, 2),
                    'pass_rate' => round($results->where('status', 'passed')->count() / max($results->count(), 1) * 100, 2),
                    'created_at' => $batch->created_at,
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        return view('instructor.analytics.batches', compact('batches'));
    }

}
