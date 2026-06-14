<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Models\Question;
use App\Models\Result;
use App\Models\StudentExam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    public function index()
    {
        $instructor = auth()->user();

        // Get instructor's teaching data
        $teachingBatches = Batch::where('instructor_id', $instructor->id)->pluck('id');
        $batchIds = $teachingBatches->toArray();

        // Statistics
        $totalCourses = Course::whereHas('instructors', function($q) use ($instructor) {
            $q->where('user_id', $instructor->id);
        })->count();

        $activeBatches = Batch::where('instructor_id', $instructor->id)
            ->where('status', 'ongoing')
            ->count();

        $totalStudents = DB::table('batch_student')
            ->whereIn('batch_id', $batchIds)
            ->where('status', 'active')
            ->count();

        $totalExams = Exam::where(function($q) use ($batchIds) {
            $q->whereIn('batch_id', $batchIds)
              ->orWhereHas('batches', function($q2) use ($batchIds) {
                  $q2->whereIn('batches.id', $batchIds);
              });
        })->count();

        $totalQuestions = Question::where('created_by', $instructor->id)->count();

        $totalQuizzes = Exam::where('type', 'quiz')
            ->where('created_by', $instructor->id)
            ->count();

        // Performance metrics
        $results = Result::whereIn('batch_id', $batchIds)->get();
        $averageScore = $results->avg('percentage') ?? 0;

        $totalAttempts = $results->count();
        $passedCount = $results->where('status', 'passed')->count();
        $passRate = $totalAttempts > 0 ? round(($passedCount / $totalAttempts) * 100, 2) : 0;

        $completedExams = StudentExam::whereIn('batch_id', $batchIds)
            ->where('status', 'completed')
            ->count();
        $totalAssigned = StudentExam::whereIn('batch_id', $batchIds)->count();
        $completionRate = $totalAssigned > 0 ? round(($completedExams / $totalAssigned) * 100, 2) : 0;

        $activeStudents = StudentExam::whereIn('batch_id', $batchIds)
            ->where('status', 'started')
            ->count();
        $engagementRate = $totalAssigned > 0 ? round(($activeStudents / $totalAssigned) * 100, 2) : 0;

        $feedbackResponses = FeedbackResponse::whereHas('feedback', function($q) use ($instructor) {
            $q->where('instructor_id', $instructor->id);
        })->count();

        // Recent exams
        $recentExams = Exam::where(function($q) use ($batchIds) {
                $q->whereIn('batch_id', $batchIds)
                  ->orWhereHas('batches', function($q2) use ($batchIds) {
                      $q2->whereIn('batches.id', $batchIds);
                  });
            })
            ->withCount('studentExams as attempts_count')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Active students currently taking exams
        $activeStudentsList = StudentExam::whereIn('batch_id', $batchIds)
            ->where('status', 'started')
            ->with(['student', 'exam'])
            ->take(5)
            ->get()
            ->map(function($studentExam) {
                return (object)[
                    'name' => $studentExam->student->name,
                    'avatar_url' => $studentExam->student->avatar_url,
                    'exam_title' => $studentExam->exam->title,
                    'time_elapsed' => $studentExam->started_at->diffForHumans()
                ];
            });

        // Recent feedback
        $recentFeedback = FeedbackResponse::whereHas('feedback', function($q) use ($instructor) {
                $q->where('instructor_id', $instructor->id);
            })
            ->with(['feedback', 'student'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($response) {
                return (object)[
                    'student_name' => $response->student->name,
                    'rating' => $response->rating,
                    'comment' => $response->comment,
                    'created_at' => $response->created_at
                ];
            });

        // Upcoming tasks (exams starting soon)
        $upcomingTasks = Exam::where(function($q) use ($batchIds) {
                $q->whereIn('batch_id', $batchIds)
                  ->orWhereHas('batches', function($q2) use ($batchIds) {
                      $q2->whereIn('batches.id', $batchIds);
                  });
            })
            ->where('is_scheduled', true)
            ->where('start_time', '>', now())
            ->where('start_time', '<', now()->addDays(7))
            ->orderBy('start_time')
            ->take(5)
            ->get()
            ->map(function($exam) {
                $daysUntil = now()->diffInDays($exam->start_time);
                $priority = $daysUntil <= 1 ? 'high' : ($daysUntil <= 3 ? 'medium' : 'low');
                $color = $daysUntil <= 1 ? 'danger' : ($daysUntil <= 3 ? 'warning' : 'info');

                return [
                    'title' => $exam->title . ' starts',
                    'time' => $exam->start_time->format('M d, h:i A') . ' (' . $exam->start_time->diffForHumans() . ')',
                    'priority' => $priority,
                    'color' => $color,
                    'icon' => 'calendar-check'
                ];
            });

        $latestFeedback = Feedback::where('instructor_id', $instructor->id)
            ->latest()
            ->first();

        return view('instructor.dashboard', compact(
            'totalCourses',
            'activeBatches',
            'totalStudents',
            'totalExams',
            'totalQuestions',
            'totalQuizzes',
            'averageScore',
            'passRate',
            'completionRate',
            'engagementRate',
            'feedbackResponses',
            'recentExams',
            'activeStudentsList',
            'recentFeedback',
            'upcomingTasks',
            'latestFeedback'
        ));
    }
}
