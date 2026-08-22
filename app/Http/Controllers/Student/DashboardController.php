<?php
// app/Http/Controllers/Student/DashboardController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Feedback;
use App\Models\Result;
use App\Models\StudentExam;
use App\Models\StudyMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    public function index()
    {
        $student = auth()->user();

        // Get student's batch IDs
        $batchIds = $student->batches()
            ->wherePivot('status', 'active')
            ->pluck('batches.id')
            ->toArray();

        // Get all courses from student's batches
        $courses = Course::whereHas('batches', function($q) use ($batchIds) {
            $q->whereIn('batches.id', $batchIds);
        })->with(['batches', 'exams', 'modules'])->get();

        // Get all batches
        $batches = $student->batches()->with(['course', 'instructor'])->get();

        // Basic statistics
        $enrolledCourses = $courses->count();
        $examsTaken = StudentExam::where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        $pendingExams = Exam::whereIn('batch_id', $batchIds)
            ->where('status', 'published')
            ->whereDoesntHave('studentExams', function($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->where('status', 'completed');
            })
            ->count();

        // Performance metrics
        $results = Result::where('student_id', $student->id)->get();
        $averageScore = round($results->avg('percentage') ?? 0, 2);

        $totalCorrect = $results->sum('correct_answers');
        $totalQuestions = $results->sum('total_questions');
        $overallAccuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 2) : 0;

        // Class rank calculation
        $classRank = null;
        $totalStudents = null;
        if (!empty($batchIds)) {
            $rankData = DB::table('results')
                ->select('student_id', DB::raw('AVG(percentage) as avg_score'))
                ->whereIn('batch_id', $batchIds)
                ->groupBy('student_id')
                ->orderBy('avg_score', 'desc')
                ->get();

            $totalStudents = $rankData->count();
            foreach ($rankData as $index => $data) {
                if ($data->student_id == $student->id) {
                    $classRank = $index + 1;
                    break;
                }
            }
        }

        // Subject-wise performance
        $subjectPerformance = [];
        $results->load('exam.course');
        $subjectScores = [];

        foreach ($results as $result) {
            $courseName = $result->exam->course->title ?? 'General';
            if (!isset($subjectScores[$courseName])) {
                $subjectScores[$courseName] = ['total' => 0, 'count' => 0];
            }
            $subjectScores[$courseName]['total'] += $result->percentage;
            $subjectScores[$courseName]['count']++;
        }

        foreach ($subjectScores as $name => $data) {
            $subjectPerformance[] = [
                'name' => $name,
                'score' => round($data['total'] / $data['count'], 2)
            ];
        }

        // Performance trend data
        $recentResults = Result::where('student_id', $student->id)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $performanceLabels = $recentResults->map(function($item) {
            return $item->exam->title . ' (' . $item->created_at->format('d M') . ')';
        })->toArray();

        $performanceScores = $recentResults->pluck('percentage')->toArray();

        // Upcoming exams
        $upcomingExams = Exam::whereIn('batch_id', $batchIds)
            ->whereHas('batch', function ($q) {
                $today = now()->toDateString();
                $q->whereDate('start_date', '<=', $today)
                  ->whereDate('end_date', '>=', $today);
            })
            ->where('status', 'published')
            ->whereDoesntHave('studentExams', function($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->where('status', 'completed');
            })
            ->where(function($q) {
                $q->whereNull('start_time')
                  ->orWhere('start_time', '>', now());
            })
            ->with('course')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Pending feedback count
        $pendingFeedbackCount = Feedback::whereIn('batch_id', $batchIds)
            ->where('status', 'active')
            ->whereDoesntHave('responses', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->count();

        // Study streak calculation
        $streakDays = $this->calculateStreak($student->id);
        $totalStudyTime = round($results->sum('time_taken_seconds') / 3600, 1);
        $weeklyGoal = 10;
        $weeklyProgress = min(100, round(($totalStudyTime / $weeklyGoal) * 100, 2));

        $streakMessage = $streakDays >= 7 ? 'Amazing streak! 🔥' : ($streakDays >= 3 ? 'Good progress! 💪' : 'Keep going! 📚');

        // Achievements
        $achievements = $this->getAchievements($student->id, $results);
        $nextAchievement = $this->getNextAchievement($student->id, $results);
        $nextAchievementProgress = $this->getNextAchievementProgress($student->id, $results);

        // Study materials
        $studyMaterials = $this->getRecommendedMaterials($student->id, $batchIds);

        // Stats array for consistency
        $stats = [
            'total_exams' => $examsTaken + $pendingExams,
            'completed_exams' => $examsTaken,
            'pending_exams' => $pendingExams,
            'avg_score' => $averageScore,
        ];

        // Recent activity
        $recentActivities = $this->getRecentActivities($student);

        return view('student.dashboard', compact(
            'courses',
            'batches',
            'enrolledCourses',
            'examsTaken',
            'pendingExams',
            'averageScore',
            'overallAccuracy',
            'classRank',
            'totalStudents',
            'subjectPerformance',
            'performanceLabels',
            'performanceScores',
            'upcomingExams',
            'recentResults',
            'pendingFeedbackCount',
            'streakDays',
            'totalStudyTime',
            'weeklyGoal',
            'weeklyProgress',
            'streakMessage',
            'achievements',
            'nextAchievement',
            'nextAchievementProgress',
            'studyMaterials',
            'stats',
            'recentActivities'
        ));
    }

    /**
     * Calculate study streak
     */
    private function calculateStreak($studentId)
    {
        $exams = StudentExam::where('student_id', $studentId)
            ->where('status', 'completed')
            ->orderBy('submitted_at', 'desc')
            ->get();

        if ($exams->isEmpty()) {
            return 0;
        }

        $streak = 1;
        $lastDate = $exams->first()->submitted_at->startOfDay();

        foreach ($exams->skip(1) as $exam) {
            $examDate = $exam->submitted_at->startOfDay();
            $diffInDays = $lastDate->diffInDays($examDate);

            if ($diffInDays == 1) {
                $streak++;
                $lastDate = $examDate;
            } elseif ($diffInDays > 1) {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get achievements
     */
    private function getAchievements($studentId, $results)
    {
        $achievements = [];
        $totalExams = $results->count();
        $perfectScores = $results->where('percentage', 100)->count();
        $averageScore = $results->avg('percentage');
        $highestScore = $results->max('percentage');

        if ($totalExams >= 1) {
            $achievements[] = [
                'title' => 'First Exam',
                'description' => 'Completed your first exam',
                'icon' => 'trophy',
                'color' => 'success',
                'earned' => true
            ];
        }

        if ($totalExams >= 5) {
            $achievements[] = [
                'title' => 'Getting Started',
                'description' => 'Completed 5 exams',
                'icon' => 'award',
                'color' => 'info',
                'earned' => true
            ];
        }

        if ($perfectScores >= 1) {
            $achievements[] = [
                'title' => 'Perfect Score',
                'description' => 'Got 100% on an exam',
                'icon' => 'star',
                'color' => 'warning',
                'earned' => true
            ];
        }

        if ($averageScore >= 80) {
            $achievements[] = [
                'title' => 'Top Performer',
                'description' => 'Maintain 80%+ average',
                'icon' => 'graph-up',
                'color' => 'primary',
                'earned' => true
            ];
        }

        return array_slice($achievements, 0, 4);
    }

    /**
     * Get next achievement
     */
    private function getNextAchievement($studentId, $results)
    {
        $totalExams = $results->count();

        if ($totalExams < 5) {
            return 'Complete 5 Exams';
        } elseif ($totalExams < 10) {
            return 'Complete 10 Exams';
        } elseif ($results->where('percentage', 100)->count() < 3) {
            return 'Get 3 Perfect Scores';
        } else {
            return 'Achieve 90%+ Average';
        }
    }

    /**
     * Get next achievement progress
     */
    private function getNextAchievementProgress($studentId, $results)
    {
        $totalExams = $results->count();

        if ($totalExams < 5) {
            return ($totalExams / 5) * 100;
        } elseif ($totalExams < 10) {
            return (($totalExams - 5) / 5) * 100;
        } elseif ($results->where('percentage', 100)->count() < 3) {
            return ($results->where('percentage', 100)->count() / 3) * 100;
        } else {
            $avgScore = $results->avg('percentage');
            return min(100, ($avgScore / 90) * 100);
        }
    }

    /**
     * Get recommended study materials
     */
    private function getRecommendedMaterials($studentId, $batchIds)
    {
        // Get the student's enrolled courses from their batches
        $courseIds = DB::table('batches')
            ->whereIn('id', $batchIds)
            ->pluck('course_id')
            ->toArray();

        if (empty($courseIds)) {
            return collect([]);
        }

        // Get published study materials for the student's courses
        $materials = StudyMaterial::with(['course', 'module', 'category'])
            ->whereIn('course_id', $courseIds)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($materials->isEmpty()) {
            return collect([]);
        }

        return $materials->map(function($material) {
            return [
                'id' => $material->id,
                'title' => $material->title,
                'subject' => $material->course->title ?? 'General',
                'type' => $material->material_type,
                'category_name' => $material->category->name ?? 'Uncategorized',
                'created_at' => $material->created_at->diffForHumans(),
                'download_url' => $material->file_path ? route('student.study-materials.download', $material) : null,
            ];
        });
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($student)
    {
        $activities = [];

        // Recent exams taken
        $recentExams = StudentExam::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('exam')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentExams as $exam) {
            $activities[] = [
                'title' => 'Completed Exam: ' . ($exam->exam->title ?? 'N/A'),
                'description' => 'Score: ' . ($exam->percentage ?? 0) . '%',
                'color' => 'success',
                'time' => $exam->updated_at->diffForHumans()
            ];
        }

        // Recent batch enrollments
        $recentBatches = $student->batches()
            ->orderBy('batch_student.created_at', 'desc')
            ->take(3)
            ->get();

        foreach ($recentBatches as $batch) {
            $activities[] = [
                'title' => 'Enrolled in Batch: ' . $batch->name,
                'description' => $batch->course->title ?? '',
                'color' => 'info',
                'time' => $batch->pivot->created_at->diffForHumans()
            ];
        }

        // Sort by time descending
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 10);
    }

    // ==================== ADDITIONAL VIEW METHODS ====================

    /**
     * Show course detail
     */
    public function courseDetail(Course $course)
    {
        $course->load(['batches.instructor', 'modules', 'exams']);
        return view('student.course-detail', compact('course'));
    }

    /**
     * Show batch detail
     */
    public function batchDetail(Batch $batch)
    {
        $batch->load(['course', 'instructor', 'students']);
        $exams = $batch->exams()->where('status', 'published')->get();
        return view('student.batch-detail', compact('batch', 'exams'));
    }

    /**
     * Show batch exams
     */
    public function batchExams(Batch $batch)
    {
        $exams = $batch->exams()->where('status', 'published')->get();
        return view('student.batch-exams', compact('batch', 'exams'));
    }
}