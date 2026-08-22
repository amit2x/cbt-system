<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\StudentExam;
use App\Services\Exam\ResultAnalysisService;
use App\Services\Exam\ExamEngineService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class ResultController extends Controller
{
    use ApiResponse;

    protected $resultAnalysis;
    protected $examEngine;


    public function __construct(ResultAnalysisService $resultAnalysis , ExamEngineService $examEngine)
    {
        $this->resultAnalysis = $resultAnalysis;
        $this->examEngine = $examEngine;
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
    // public function show(Result $result)
    // {
    //     // Ensure student owns this result
    //     if ($result->student_id !== auth()->id()) {
    //         abort(403, 'Unauthorized access');
    //     }

    //     $result->load(['exam', 'batch', 'sections', 'studentExam.answers.question']);

    //     // Get detailed analysis
    //     $analysis = [
    //         'sectional' => $result->sectional_analysis,
    //         'topic' => $result->topic_analysis,
    //         'difficulty' => $result->difficulty_analysis,
    //         'accuracy_by_type' => $result->accuracy_by_type
    //     ];
        
        

    //     // Get answer review
    //     $answers = $result->studentExam->answers()->with('question.options')->get();

    //     return view('student.results.show', compact('result', 'analysis', 'answers'));
    // }
    
    
    public function show(Result $result)
{
    // Ensure student owns this result
    if ($result->student_id !== auth()->id()) {
        abort(403, 'Unauthorized access');
    }

    $result->load(['exam', 'batch', 'sections', 'studentExam.answers.question']);

    $analysis = [
        'sectional' => $result->sectional_analysis ?? [],
        'topic' => $result->topic_analysis ?? [],
        'difficulty' => $result->difficulty_analysis ?? [],
        'accuracy_by_type' => $result->accuracy_by_type ?? []
    ];

    $answers = $result->studentExam->answers()
        ->with(['question.options'])
        ->orderBy('id')
        ->get();

    return view('student.results.show', compact('result', 'analysis', 'answers'));
}

    /**
     * Get result analytics via AJAX
     */
    // public function analytics(Result $result)
    // {
    //     if ($result->student_id !== auth()->id()) {
    //         return $this->errorResponse('Unauthorized', 403);
    //     }

    //     $analysis = [
    //         'overall' => [
    //             'percentage' => $result->percentage,
    //             'rank' => $result->rank,
    //             'percentile' => $result->percentile,
    //             'accuracy' => $result->accuracy_percentage,
    //             'speed' => $result->speed_per_minute,
    //             'time_taken' => $result->formatted_time
    //         ],
    //         'sectional' => $result->sectional_analysis,
    //         'topic' => $result->topic_analysis,
    //         'difficulty' => $result->difficulty_analysis,
    //         'by_type' => $result->accuracy_by_type,
    //         'comparison' => [
    //             'average_score' => Result::where('exam_id', $result->exam_id)->avg('percentage'),
    //             'highest_score' => Result::where('exam_id', $result->exam_id)->max('percentage'),
    //             'total_participants' => $result->total_participants
    //         ]
    //     ];

    //     return $this->successResponse($analysis);
    // }




public function analytics(Result $result)
{
    // 1. Authorization guard clause
    if ($result->student_id !== auth()->id()) {
        return $this->errorResponse('Unauthorized', 403);
    }

    // 2. Fetch or cache the total participants and scores below this student
    $comparisonData = Cache::remember("exam_stats_{$result->exam_id}", 300, function () use ($result) {
        $stats = Result::where('exam_id', $result->exam_id)
            ->selectRaw('AVG(percentage) as avg_score, MAX(percentage) as max_score, COUNT(id) as total_users')
            ->first();

        return [
            'average_score'      => round($stats->avg_score ?? 0, 2),
            'highest_score'      => round($stats->max_score ?? 0, 2),
            'total_participants' => (int) ($stats->total_users ?? 0)
        ];
    });

    // 3. Dynamic Percentile Calculation Fallback (If column is null)
    $percentile = $result->percentile;
    if (is_null($percentile) && $comparisonData['total_participants'] > 1) {
        // Count how many students scored LESS than or EQUAL to this student
        $studentsScoredLessOrEqual = Result::where('exam_id', $result->exam_id)
            ->where('obtained_marks', '<=', $result->obtained_marks)
            ->count();
        
        // Percentile Formula: (Number of values below score / Total number of scores) * 100
        $percentile = round(($studentsScoredLessOrEqual / $comparisonData['total_participants']) * 100, 2);
    } elseif ($comparisonData['total_participants'] <= 1) {
        $percentile = 100.00; // Only participant is automatically 100th percentile
    }

    // 4. Construct structured payload with safe JSON parsing fallbacks
    $analysis = [
        'title'=> $result->exam->title,
        'overall' => [
            'percentage' => $result->percentage,
            'rank'       => $result->rank ?? 1, // Fallback to 1 if null
            'percentile' => $percentile,
            'accuracy'   => $result->accuracy_percentage ?? $this->calculateAccuracy($result),
            'speed'      => $result->speed_per_minute ?? 0,
            'time_taken' => $result->time_taken_seconds
        ],
        'sectional'  => $this->parseJsonField($result->sectional_analysis),
        'topic'      => $this->parseJsonField($result->topic_analysis),
        'difficulty' => $this->parseJsonField($result->difficulty_analysis),
        'by_type'    => $this->parseJsonField($result->accuracy_by_type),
        'comparison' => $comparisonData
    ];

    return $this->successResponse($analysis);
}

/**
 * Ensures text JSON columns from MySQL are cleanly parsed into associative arrays.
 */
private function parseJsonField($field)
{
    if (blank($field)) {
        return [];
    }
    if (is_array($field)) {
        return $field;
    }
    if (is_string($field)) {
        return json_decode($field, true) ?? [];
    }
    return [];
}

/**
 * Fallback accuracy calculator if accuracy_percentage is null in database
 */
private function calculateAccuracy($result)
{
    $totalAttempted = ($result->correct_answers ?? 0) + ($result->incorrect_answers ?? 0);
    if ($totalAttempted === 0) {
        return 0;
    }
    return round((($result->correct_answers ?? 0) / $totalAttempted) * 100, 2);
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
