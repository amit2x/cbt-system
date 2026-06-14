<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Models\LiveMonitoringSession;
use App\Services\Exam\LiveMonitoringService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class LiveMonitoringController extends Controller
{
    use ApiResponse;

    protected $monitoringService;

    public function __construct(LiveMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Show live monitoring dashboard
     */
    public function index()
    {
        $instructor = auth()->user();

        // Get active exams for instructor's batches
        $activeExams = Exam::whereHas('batches', function($q) use ($instructor) {
                $q->whereIn('batches.id', $instructor->teachingBatches->pluck('id'));
            })
            ->where('status', 'published')
            ->where(function($q) {
                $q->whereNull('end_time')
                  ->orWhere('end_time', '>', now());
            })
            ->with(['course', 'batch'])
            ->get();

        // Get active monitoring sessions
        $activeSessions = LiveMonitoringSession::where('instructor_id', $instructor->id)
            ->where('status', 'active')
            ->with(['exam', 'batch'])
            ->get();

        return view('instructor.live-monitoring.index', compact('activeExams', 'activeSessions'));
    }

    /**
     * Start monitoring session
     */
    public function startSession(Request $request, Exam $exam)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'settings' => 'nullable|array'
        ]);

        try {
            $session = $this->monitoringService->createSession(
                $exam,
                auth()->id(),
                $request->batch_id,
                $request->settings ?? []
            );

            return redirect()->route('instructor.live-monitoring.show', $session)
                ->with('success', 'Monitoring session started');
        } catch (\Exception $e) {
            return back()->with('error', 'Error starting session: ' . $e->getMessage());
        }
    }

    /**
     * Show monitoring session
     */
    public function show(LiveMonitoringSession $session)
    {
        // Ensure instructor owns this session
        if ($session->instructor_id !== auth()->id()) {
            abort(403);
        }

        $data = $this->monitoringService->getDashboardData($session);

        return view('instructor.live-monitoring.show', compact('data'));
    }

    /**
     * Get live updates via AJAX
     */
    public function getUpdates(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $data = $this->monitoringService->getDashboardData($session);

        return $this->successResponse($data);
    }

    /**
     * Get student details
     */
    public function getStudentDetails(LiveMonitoringSession $session, $studentId)
    {
        if ($session->instructor_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $studentExam = StudentExam::where('exam_id', $session->exam_id)
            ->where('student_id', $studentId)
            ->with(['student', 'answers.question'])
            ->first();

        if (!$studentExam) {
            return $this->errorResponse('Student not found', 404);
        }

        $timeline = $this->monitoringService->getStudentTimeline($studentExam);

        $data = [
            'student' => [
                'name' => $studentExam->student->name,
                'email' => $studentExam->student->email,
                'avatar' => $studentExam->student->avatar_url
            ],
            'exam_progress' => [
                'started_at' => $studentExam->started_at->format('H:i:s'),
                'time_elapsed' => now()->diffInMinutes($studentExam->started_at) . ' minutes',
                'progress' => $studentExam->progress_percentage,
                'answered' => $studentExam->answered_questions,
                'marked' => $studentExam->marked_for_review_count,
                'tab_switches' => $studentExam->tab_switch_count
            ],
            'timeline' => $timeline,
            'answers' => $studentExam->answers->map(function($answer) {
                return [
                    'question_id' => $answer->question_id,
                    'question' => substr(strip_tags($answer->question->question_text), 0, 100),
                    'has_answer' => !is_null($answer->selected_options) || !is_null($answer->answer_text),
                    'saved_at' => $answer->last_saved_at?->format('H:i:s')
                ];
            })
        ];

        return $this->successResponse($data);
    }

    /**
     * Send warning to student
     */
    public function sendWarning(Request $request, LiveMonitoringSession $session, $studentId)
    {
        $request->validate([
            'message' => 'required|string|max:255'
        ]);

        if ($session->instructor_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $studentExam = StudentExam::where('exam_id', $session->exam_id)
            ->where('student_id', $studentId)
            ->first();

        if (!$studentExam) {
            return $this->errorResponse('Student not found', 404);
        }

        // Log warning
        $this->monitoringService->logActivity(
            $studentExam->id,
            'warning_issued',
            'warning',
            ['message' => $request->message],
            $request->message
        );

        // Send real-time notification (would integrate with WebSockets)
        // For now, just log it
        \Log::info("Warning sent to student {$studentId}: {$request->message}");

        return $this->successResponse(null, 'Warning sent successfully');
    }

    /**
     * Terminate student exam
     */
    public function terminateStudent(Request $request, LiveMonitoringSession $session, $studentId)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        if ($session->instructor_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $studentExam = StudentExam::where('exam_id', $session->exam_id)
            ->where('student_id', $studentId)
            ->whereIn('status', ['started', 'in_progress'])
            ->first();

        if (!$studentExam) {
            return $this->errorResponse('Student not found or exam already completed', 404);
        }

        $this->monitoringService->terminateExam($studentExam, $request->reason);

        return $this->successResponse(null, 'Student exam terminated');
    }

    /**
     * End monitoring session
     */
    public function endSession(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $session->end();

        return redirect()->route('instructor.live-monitoring.index')
            ->with('success', 'Monitoring session ended');
    }

    /**
     * Get suspicious images
     */
    public function getSuspiciousImages(LiveMonitoringSession $session = null)
    {
        if ($session && $session->instructor_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $images = $this->monitoringService->getSuspiciousImages($session?->id);

        return view('instructor.live-monitoring.suspicious-images', compact('images', 'session'));
    }

    /**
     * Review suspicious image
     */
    public function reviewImage(Request $request, ProctoringImage $image)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string'
        ]);

        $image->markAsReviewed(auth()->id());

        if ($request->action === 'reject') {
            $image->update(['is_suspicious' => false]);
        }

        return $this->successResponse(null, 'Image reviewed successfully');
    }

    /**
     * Export monitoring report
     */
    public function exportReport(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id()) {
            abort(403);
        }

        $data = $this->monitoringService->getDashboardData($session);

        // Generate PDF/Excel report
        // Implementation depends on chosen package

        return redirect()->back()->with('info', 'Report export feature coming soon');
    }
}
