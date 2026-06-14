<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\StudentExam;
use App\Services\Exam\ResultAnalysisService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ResultController extends Controller
{
    use ApiResponse;

    protected $resultAnalysis;

    public function __construct(ResultAnalysisService $resultAnalysis)
    {
        $this->resultAnalysis = $resultAnalysis;
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    /**
     * Display student's results
     */
    public function index(Request $request)
    {
        $studentId = auth()->id();

        $query = Result::with(['exam', 'batch'])
            ->where('student_id', $studentId);

        // Apply filters
        if ($request->filled('exam_type')) {
            $query->whereHas('exam', function($q) use ($request) {
                $q->where('type', $request->exam_type);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get performance trends
        $trends = $this->resultAnalysis->getStudentTrends($studentId);

        // Get statistics
        $stats = [
            'total_exams' => Result::where('student_id', $studentId)->count(),
            'passed' => Result::where('student_id', $studentId)->where('status', 'passed')->count(),
            'failed' => Result::where('student_id', $studentId)->where('status', 'failed')->count(),
            'average_score' => round(Result::where('student_id', $studentId)->avg('percentage'), 2),
            'best_score' => Result::where('student_id', $studentId)->max('percentage'),
            'total_correct' => Result::where('student_id', $studentId)->sum('correct_answers'),
            'total_incorrect' => Result::where('student_id', $studentId)->sum('incorrect_answers')
        ];

        $examTypes = [
            'mock_test' => 'Mock Test',
            'full_exam' => 'Full Exam',
            'quiz' => 'Quiz',
            'practice' => 'Practice'
        ];

        return view('student.results.index', compact('results', 'stats', 'trends', 'examTypes'));
    }

    /**
     * Display result details
     */
    public function show(Result $result)
    {
        // Ensure student owns this result
        if ($result->student_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $result->load(['exam', 'batch', 'sections', 'studentExam.answers.question']);

        // Get detailed analysis
        $analysis = [
            'sectional' => $result->sectional_analysis,
            'topic' => $result->topic_analysis,
            'difficulty' => $result->difficulty_analysis,
            'accuracy_by_type' => $result->accuracy_by_type
        ];

        // Get answer review
        $answers = $result->studentExam->answers()->with('question.options')->get();

        return view('student.results.show', compact('result', 'analysis', 'answers'));
    }

    /**
     * Get result analytics via AJAX
     */
    public function analytics(Result $result)
    {
        if ($result->student_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $analysis = [
            'overall' => [
                'percentage' => $result->percentage,
                'rank' => $result->rank,
                'percentile' => $result->percentile,
                'accuracy' => $result->accuracy_percentage,
                'speed' => $result->speed_per_minute,
                'time_taken' => $result->formatted_time
            ],
            'sectional' => $result->sectional_analysis,
            'topic' => $result->topic_analysis,
            'difficulty' => $result->difficulty_analysis,
            'by_type' => $result->accuracy_by_type,
            'comparison' => [
                'average_score' => Result::where('exam_id', $result->exam_id)->avg('percentage'),
                'highest_score' => Result::where('exam_id', $result->exam_id)->max('percentage'),
                'total_participants' => $result->total_participants
            ]
        ];

        return $this->successResponse($analysis);
    }

    /**
     * Download result as PDF
     */
    public function download(Result $result)
    {
        if ($result->student_id !== auth()->id()) {
            abort(403);
        }

        // PDF generation logic here
        // You can use a package like barryvdh/laravel-dompdf

        return redirect()->back()->with('info', 'PDF download feature coming soon');
    }

    /**
     * Share result
     */
    public function share(Result $result)
    {
        if ($result->student_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Generate shareable link or certificate
        $shareData = [
            'student_name' => $result->student->name,
            'exam_name' => $result->exam->title,
            'score' => $result->percentage . '%',
            'rank' => $result->rank,
            'date' => $result->created_at->format('d M Y')
        ];

        return $this->successResponse([
            'share_url' => route('student.results.public', $result->id) . '?token=' . encrypt($result->id),
            'message' => 'Result shared successfully'
        ]);
    }

    /**
     * Public result view (for sharing)
     */
    public function publicView($id, Request $request)
    {
        try {
            $token = $request->input('token');
            $decrypted = decrypt($token);

            if ($decrypted != $id) {
                abort(404);
            }

            $result = Result::with(['student', 'exam'])->findOrFail($id);

            return view('student.results.public', compact('result'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Compare results with peers
     */
    public function compare(Result $result)
    {
        if ($result->student_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Get batch average if available
        $batchAverage = null;
        if ($result->batch_id) {
            $batchAverage = Result::where('exam_id', $result->exam_id)
                ->where('batch_id', $result->batch_id)
                ->avg('percentage');
        }

        // Get overall average
        $overallAverage = Result::where('exam_id', $result->exam_id)->avg('percentage');

        // Get toppers
        $toppers = Result::where('exam_id', $result->exam_id)
            ->orderBy('percentage', 'desc')
            ->with('student')
            ->take(5)
            ->get()
            ->map(function($r) {
                return [
                    'name' => $r->student->name,
                    'score' => $r->percentage
                ];
            });

        return $this->successResponse([
            'your_score' => $result->percentage,
            'batch_average' => round($batchAverage, 2),
            'overall_average' => round($overallAverage, 2),
            'rank' => $result->rank,
            'total_participants' => $result->total_participants,
            'toppers' => $toppers,
            'percentile' => $result->percentile
        ]);
    }
}
