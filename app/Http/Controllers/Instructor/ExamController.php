<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\Result;
use App\Models\StudentExam;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display a listing of instructor's exams.
     */
    public function index(Request $request)
    {
        $instructor = auth()->user();

        // Get batches where instructor is assigned
        $batches = Batch::where('instructor_id', $instructor->id)->get();
        $batchIds = $batches->pluck('id')->toArray();

        $query = Exam::where(function ($q) use ($instructor, $batchIds) {
            // Exams created by instructor
            $q->where('created_by', $instructor->id)
              // OR exams for instructor's batches
                ->orWhereIn('batch_id', $batchIds)
                ->orWhereHas('batches', function ($q2) use ($batchIds) {
                    $q2->whereIn('batches.id', $batchIds);
                });
        })
            ->with(['batch.course'])
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

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate average score for each exam
        foreach ($exams as $exam) {
            $exam->average_score = round(Result::where('exam_id', $exam->id)->avg('percentage') ?? 0, 1);
        }

        return view('instructor.exams.index', compact('exams', 'batches'));
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        $instructor = auth()->user();

        // Verify instructor has access to this exam
        if (! $this->instructorHasAccess($exam, $instructor)) {
            abort(403, 'Unauthorized access to this exam.');
        }

        $exam->load(['batch.course', 'questions' => function ($q) {
            $q->with(['subject', 'options']);
        }]);

        // Get statistics
        $stats = [
            'total_attempts' => StudentExam::where('exam_id', $exam->id)->where('status', 'completed')->count(),
            'passed' => Result::where('exam_id', $exam->id)->where('status', 'passed')->count(),
            'failed' => Result::where('exam_id', $exam->id)->where('status', 'failed')->count(),
            'average_score' => round(Result::where('exam_id', $exam->id)->avg('percentage') ?? 0, 2),
            'highest_score' => round(Result::where('exam_id', $exam->id)->max('percentage') ?? 0, 2),
            'lowest_score' => round(Result::where('exam_id', $exam->id)->min('percentage') ?? 0, 2),
            'avg_time' => round(Result::where('exam_id', $exam->id)->avg('time_taken_seconds') ?? 0, 2),
        ];

        // Get recent results
        $recentResults = Result::where('exam_id', $exam->id)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $exam->loadCount('questions');

        return view('instructor.exams.show', compact('exam', 'stats', 'recentResults'));
    }

    /**
     * Display exam results.
     */
    public function results(Exam $exam)
    {
        $instructor = auth()->user();

        // Verify instructor has access
        if (! $this->instructorHasAccess($exam, $instructor)) {
            abort(403);
        }

        // Get all results for this exam
        $results = Result::where('exam_id', $exam->id)
            ->with('student')
            ->orderBy('percentage', 'desc')
            ->paginate(20);

        // Calculate statistics
        $stats = [
            'total_attempts' => $results->total(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'average_score' => round($results->avg('percentage') ?? 0, 2),
            'highest_score' => round($results->max('percentage') ?? 0, 2),
            'lowest_score' => round($results->min('percentage') ?? 0, 2),
        ];

        // Score distribution for chart
        $distribution = [
            '90-100' => $results->where('percentage', '>=', 90)->count(),
            '75-89' => $results->where('percentage', '>=', 75)->where('percentage', '<', 90)->count(),
            '60-74' => $results->where('percentage', '>=', 60)->where('percentage', '<', 75)->count(),
            '40-59' => $results->where('percentage', '>=', 40)->where('percentage', '<', 60)->count(),
            'below-40' => $results->where('percentage', '<', 40)->count(),
        ];

        // Question analysis
        $questionLabels = [];
        $correctData = [];
        $incorrectData = [];

        foreach ($exam->questions as $question) {
            $questionLabels[] = 'Q'.($loop->index + 1);
            $correctCount = 0;
            $incorrectCount = 0;

            foreach ($results as $result) {
                $answer = $result->studentExam->answers()
                    ->where('question_id', $question->id)
                    ->first();

                if ($answer) {
                    if ($answer->is_correct) {
                        $correctCount++;
                    } elseif ($answer->selected_options || $answer->answer_text) {
                        $incorrectCount++;
                    }
                }
            }

            $correctData[] = $correctCount;
            $incorrectData[] = $incorrectCount;
        }

        return view('instructor.exams.results', compact('exam', 'results', 'stats', 'distribution', 'questionLabels', 'correctData', 'incorrectData'));
    }

    /**
     * Export exam results.
     */
    public function exportResults(Exam $exam)
    {
        $instructor = auth()->user();

        if (! $this->instructorHasAccess($exam, $instructor)) {
            abort(403);
        }

        $results = Result::where('exam_id', $exam->id)
            ->with('student')
            ->orderBy('percentage', 'desc')
            ->get();

        // Generate CSV
        $filename = "exam_results_{$exam->id}_".now()->format('Y-m-d').'.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        // Add headers
        fputcsv($handle, ['Student Name', 'Email', 'Score', 'Percentage', 'Correct', 'Incorrect', 'Unanswered', 'Time Taken', 'Status', 'Submitted At']);

        // Add data
        foreach ($results as $result) {
            fputcsv($handle, [
                $result->student->name,
                $result->student->email,
                $result->obtained_marks.'/'.$result->total_marks,
                $result->percentage.'%',
                $result->correct_answers,
                $result->incorrect_answers,
                $result->unanswered,
                gmdate('H:i:s', $result->time_taken_seconds),
                ucfirst($result->status),
                $result->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Publish exam.
     */
    public function publish(Exam $exam)
    {
        $instructor = auth()->user();

        if (! $this->instructorHasAccess($exam, $instructor)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($exam->questions()->count() == 0) {
            return $this->errorResponse('Cannot publish exam with no questions.', 422);
        }

        $exam->update(['status' => 'published']);

        return $this->successResponse(null, 'Exam published successfully');
    }

    /**
     * Check if instructor has access to exam.
     */
    protected function instructorHasAccess($exam, $instructor)
    {
        // Check if exam was created by instructor
        if ($exam->created_by == $instructor->id) {
            return true;
        }

        // Check if exam is for instructor's batch
        if ($exam->batch_id) {
            return Batch::where('id', $exam->batch_id)
                ->where('instructor_id', $instructor->id)
                ->exists();
        }

        // Check if exam is for any of instructor's batches through exam_batches
        return $exam->batches()
            ->where('instructor_id', $instructor->id)
            ->exists();
    }
}
