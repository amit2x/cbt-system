<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    /**
     * Display analytics dashboard
     */
    public function index()
    {
        $student = auth()->user();

        // Get all results
        $results = Result::where('student_id', $student->id)->where('is_published', true)
            ->with(['exam.course'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Overall statistics
        $stats = [
            'total_exams' => $results->count(),
            'passed' => $results->where('status', 'passed')->count(),
            'failed' => $results->where('status', 'failed')->count(),
            'pass_rate' => $results->count() > 0
                ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                : 0,
            'average_score' => round($results->avg('percentage') ?? 0, 2),
            'highest_score' => round($results->max('percentage') ?? 0, 2),
            'lowest_score' => round($results->min('percentage') ?? 0, 2),
            'average_accuracy' => round($results->avg('accuracy_percentage') ?? 0, 2),
            'average_speed' => round($results->avg('speed_per_minute') ?? 0, 2),
            'total_time_spent' => round($results->sum('time_taken_seconds') / 3600, 2),
            'total_questions_attempted' => $results->sum('correct_answers') + $results->sum('incorrect_answers'),
            'total_correct' => $results->sum('correct_answers'),
            'total_incorrect' => $results->sum('incorrect_answers'),
        ];

        // Performance trend data
        $trendData = $results->take(10)->reverse()->values();
        $performanceTrend = [
            'labels' => $trendData->map(function ($result) {
                return $result->exam->title.' ('.$result->created_at->format('d M').')';
            })->toArray(),
            'scores' => $trendData->pluck('percentage')->toArray(),
            'accuracy' => $trendData->pluck('accuracy_percentage')->toArray(),
            'speed' => $trendData->pluck('speed_per_minute')->toArray(),
        ];

        // Subject-wise performance
        $subjectPerformance = $this->getSubjectPerformance($student->id);

        // Topic-wise performance (from tags)
        $topicPerformance = $this->getTopicPerformance($student->id);

        // Difficulty-wise performance
        $difficultyPerformance = $this->getDifficultyPerformance($student->id);

        // Question type performance
        $typePerformance = $this->getTypePerformance($student->id);

        // Recent exams
        $recentExams = $results->take(5);

        // Monthly performance
        $monthlyData = $this->getMonthlyPerformance($student->id);

        return view('student.analytics.index', compact(
            'stats',
            'performanceTrend',
            'subjectPerformance',
            'topicPerformance',
            'difficultyPerformance',
            'typePerformance',
            'recentExams',
            'monthlyData'
        ));
    }

    /**
     * Get subject-wise performance
     */
    protected function getSubjectPerformance($studentId)
    {
        $results = Result::where('student_id', $studentId)->where('is_published', true)
            ->with('exam.course')
            ->get();

        $subjects = [];
        foreach ($results as $result) {
            $courseName = $result->exam->course->title ?? 'General';
            if (! isset($subjects[$courseName])) {
                $subjects[$courseName] = [
                    'total' => 0,
                    'correct' => 0,
                    'incorrect' => 0,
                    'score' => 0,
                ];
            }
            $subjects[$courseName]['total']++;
            $subjects[$courseName]['correct'] += $result->correct_answers;
            $subjects[$courseName]['incorrect'] += $result->incorrect_answers;
            $subjects[$courseName]['score'] += $result->percentage;
        }

        $result = [];
        foreach ($subjects as $name => $data) {
            $totalAttempted = $data['correct'] + $data['incorrect'];
            $result[] = [
                'name' => $name,
                'total' => $data['total'],
                'correct' => $data['correct'],
                'incorrect' => $data['incorrect'],
                'accuracy' => $totalAttempted > 0
                    ? round(($data['correct'] / $totalAttempted) * 100, 2)
                    : 0,
                'avg_score' => $data['total'] > 0 ? round($data['score'] / $data['total'], 2) : 0,
            ];
        }

        usort($result, function ($a, $b) {
            return $b['avg_score'] <=> $a['avg_score'];
        });

        return $result;
    }

    /**
     * Get topic-wise performance
     */
    protected function getTopicPerformance($studentId)
    {
        // This would require storing topic data in results
        // For now, return sample data or empty array
        return [];
    }

    /**
     * Get difficulty-wise performance - FIXED to only include published results
     */
    protected function getDifficultyPerformance($studentId)
    {
        // Get only student_exam_ids that have a published result
        $publishedStudentExamIds = Result::where('student_id', $studentId)
            ->where('is_published', true)
            ->pluck('student_exam_id')
            ->toArray();

        if (empty($publishedStudentExamIds)) {
            return $this->emptyDifficultyResult();
        }

        $answers = DB::table('student_answers')
            ->join('questions', 'student_answers.question_id', '=', 'questions.id')
            ->whereIn('student_answers.student_exam_id', $publishedStudentExamIds)
            ->select(
                'questions.difficulty',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN student_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            )
            ->groupBy('questions.difficulty')
            ->get();

        $difficulties = ['easy', 'medium', 'hard'];
        $result = [];

        foreach ($difficulties as $difficulty) {
            $data = $answers->firstWhere('difficulty', $difficulty);
            $total = $data->total ?? 0;
            $correct = $data->correct ?? 0;

            $result[] = [
                'difficulty' => ucfirst($difficulty),
                'total' => $total,
                'correct' => $correct,
                'incorrect' => $total - $correct,
                'accuracy' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
            ];
        }

        return $result;
    }

    /**
     * Get question type performance - FIXED to only include published results
     */
    protected function getTypePerformance($studentId)
    {
        // Get only student_exam_ids that have a published result
        $publishedStudentExamIds = Result::where('student_id', $studentId)
            ->where('is_published', true)
            ->pluck('student_exam_id')
            ->toArray();

        if (empty($publishedStudentExamIds)) {
            return $this->emptyTypeResult();
        }

        $answers = DB::table('student_answers')
            ->join('questions', 'student_answers.question_id', '=', 'questions.id')
            ->whereIn('student_answers.student_exam_id', $publishedStudentExamIds)
            ->select(
                'questions.type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN student_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            )
            ->groupBy('questions.type')
            ->get();

        $types = ['single_choice', 'multiple_choice', 'numerical'];
        $result = [];

        foreach ($types as $type) {
            $data = $answers->firstWhere('type', $type);
            $total = $data->total ?? 0;
            $correct = $data->correct ?? 0;

            $result[] = [
                'type' => ucfirst(str_replace('_', ' ', $type)),
                'total' => $total,
                'correct' => $correct,
                'incorrect' => $total - $correct,
                'accuracy' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
            ];
        }

        return $result;
    }

    /**
     * Get monthly performance data
     */
    protected function getMonthlyPerformance($studentId)
    {
        $results = Result::where('student_id', $studentId)->where('is_published', true)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as attempts'),
                DB::raw('AVG(percentage) as avg_score')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->take(6)
            ->get();

        return [
            'labels' => $results->pluck('month')->map(function ($month) {
                return Carbon::parse($month.'-01')->format('M Y');
            })->toArray(),
            'attempts' => $results->pluck('attempts')->toArray(),
            'avg_score' => $results->pluck('avg_score')->map(function ($score) {
                return round($score, 2);
            })->toArray(),
        ];
    }

    public function performance()
    {
        return view('student.analytics.performance');
    }

    public function topics()
    {
        return view('student.analytics.topics');
    }

    /**
     * Helper to return empty difficulty data structure
     */
    protected function emptyDifficultyResult()
    {
        return [
            ['difficulty' => 'Easy', 'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0],
            ['difficulty' => 'Medium', 'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0],
            ['difficulty' => 'Hard', 'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0],
        ];
    }

    /**
     * Helper to return empty type data structure
     */
    protected function emptyTypeResult()
    {
        return [
            ['type' => 'Single choice', 'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0],
            ['type' => 'Multiple choice', 'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0],
            ['type' => 'Numerical', 'total' => 0, 'correct' => 0, 'incorrect' => 0, 'accuracy' => 0],
        ];
    }
}
