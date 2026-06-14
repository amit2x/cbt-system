<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\Result;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display a listing of instructor's batches
     */
    public function index()
    {
        $instructor = auth()->user();

        $batches = Batch::where('instructor_id', $instructor->id)
            ->with(['course', 'instructor'])
            ->withCount('students')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('instructor.batches.index', compact('batches'));
    }

    /**
     * Display the specified batch details
     */
    public function show(Batch $batch)
    {
        // Verify instructor owns this batch
        if ($batch->instructor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this batch.');
        }

        $batch->load(['course', 'instructor', 'students' => function ($q) {
            $q->withPivot('status', 'enrolled_at');
        }]);

        // Get exam statistics for this batch
        $exams = Exam::where('batch_id', $batch->id)
            ->orWhereHas('batches', function ($q) use ($batch) {
                $q->where('batch_id', $batch->id);
            })
            ->withCount(['questions', 'studentExams'])
            ->get();

        $examStats = [];
        foreach ($exams as $exam) {
            $results = Result::where('exam_id', $exam->id)
                ->where('batch_id', $batch->id)
                ->get();

            $examStats[] = [
                'exam' => $exam,
                'attempts' => $results->count(),
                'average_score' => round($results->avg('percentage'), 2),
                'passed' => $results->where('status', 'passed')->count(),
                'failed' => $results->where('status', 'failed')->count(),
            ];
        }

        // Get student performance summary
        $studentPerformance = [];
        foreach ($batch->students as $student) {
            $results = Result::where('student_id', $student->id)
                ->where('batch_id', $batch->id)
                ->get();

            $studentPerformance[] = [
                'student' => $student,
                'exams_taken' => $results->count(),
                'average_score' => round($results->avg('percentage'), 2),
                'passed' => $results->where('status', 'passed')->count(),
                'failed' => $results->where('status', 'failed')->count(),
            ];
        }

        // Sort by average score
        usort($studentPerformance, function ($a, $b) {
            return $b['average_score'] <=> $a['average_score'];
        });

        $stats = [
            'total_students' => $batch->students->count(),
            'active_students' => $batch->students->where('pivot.status', 'active')->count(),
            'total_exams' => $exams->count(),
            'average_score' => round(collect($studentPerformance)->avg('average_score'), 2),
            'top_student' => $studentPerformance[0]['student'] ?? null,
            'top_score' => $studentPerformance[0]['average_score'] ?? 0,
        ];

        return view('instructor.batches.show', compact('batch', 'examStats', 'studentPerformance', 'stats'));
    }

    /**
     * Get students list for the batch
     */
    public function students(Batch $batch)
    {
        if ($batch->instructor_id !== auth()->id()) {
            abort(403);
        }

        $students = $batch->students()
            ->withPivot('status', 'enrolled_at')
            ->paginate(20);

        return view('instructor.batches.students', compact('batch', 'students'));
    }

    /**
     * Get exams list for the batch
     */
    public function exams(Batch $batch)
    {
        if ($batch->instructor_id !== auth()->id()) {
            abort(403);
        }

        $exams = Exam::where('batch_id', $batch->id)
            ->orWhereHas('batches', function ($q) use ($batch) {
                $q->where('batch_id', $batch->id);
            })
            ->withCount(['questions', 'studentExams'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('instructor.batches.exams', compact('batch', 'exams'));
    }

    /**
     * Get performance analytics for the batch
     */
    public function performance(Batch $batch)
    {
        if ($batch->instructor_id !== auth()->id()) {
            abort(403);
        }

        // Get all results for this batch
        $results = Result::where('batch_id', $batch->id)
            ->with(['student', 'exam'])
            ->get();

        // Calculate daily performance trend
        $dailyTrend = $results->groupBy(function ($result) {
            return $result->created_at->format('Y-m-d');
        })->map(function ($dayResults) {
            return [
                'date' => $dayResults->first()->created_at->format('d M'),
                'average' => round($dayResults->avg('percentage'), 2),
                'attempts' => $dayResults->count(),
            ];
        })->values();

        // Subject-wise performance (by course)
        $subjectPerformance = $results->groupBy(function ($result) {
            return $result->exam->course->title ?? 'General';
        })->map(function ($subjectResults, $subject) {
            return [
                'subject' => $subject,
                'average' => round($subjectResults->avg('percentage'), 2),
                'attempts' => $subjectResults->count(),
            ];
        })->values();

        // Performance distribution
        $distribution = [
            '90-100' => $results->where('percentage', '>=', 90)->count(),
            '75-89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
            '60-74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
            '40-59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
            'below-40' => $results->where('percentage', '<', 40)->count(),
        ];

        return view('instructor.batches.performance', compact('batch', 'dailyTrend', 'subjectPerformance', 'distribution'));
    }
}
