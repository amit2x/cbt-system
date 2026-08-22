<?php
// app/Http/Controllers/Instructor/MonitoringController.php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\LiveMonitoringSession;
use App\Models\StudentExam;
use App\Models\Exam;
use App\Models\ProctoringImage;
use App\Models\StudentActivityLog;
use App\Services\Proctoring\ProctoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    protected $proctoringService;

    public function __construct(ProctoringService $proctoringService)
    {
        $this->proctoringService = $proctoringService;
        $this->middleware('auth');
        $this->middleware('role:instructor|admin'); // ✅ Both instructor and admin can access
    }

    /**
     * Show monitoring dashboard - Only instructor's own sessions
     */
    public function index()
    {
        $user = Auth::user();
        
        // Instructors see only their own sessions
        $activeSessions = LiveMonitoringSession::active()
            ->where('instructor_id', $user->id)
            ->with(['exam', 'batch'])
            ->get();

        $endedSessions = LiveMonitoringSession::ended()
            ->where('instructor_id', $user->id)
            ->with(['exam', 'batch'])
            ->latest()
            ->limit(10)
            ->get();

        // Get exams that instructor can monitor
        $availableExams = Exam::whereHas('batches', function($q) use ($user) {
            $q->where('instructor_id', $user->id);
        })->where('status', 'published')->get();

        return view('instructor.monitoring.index', compact('activeSessions', 'endedSessions', 'availableExams'));
    }

    /**
     * Show monitoring dashboard for admin - All sessions
     */
    public function adminIndex()
    {
        // For admin to see all sessions
        $activeSessions = LiveMonitoringSession::active()
            ->with(['exam', 'batch', 'instructor'])
            ->get();

        $endedSessions = LiveMonitoringSession::ended()
            ->with(['exam', 'batch', 'instructor'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.monitoring.index', compact('activeSessions', 'endedSessions'));
    }

    /**
     * Start monitoring session
     */
    public function startSession(Request $request)
    {
        $request->validate([
            'exam_id'   => 'required|exists:exams,id',
            'batch_id'  => 'nullable|exists:batches,id',
            'settings'  => 'nullable|array',
        ]);
    
        // Fetch exam
        $exam = Exam::findOrFail($request->exam_id);
    
        // Use exam batch if none selected
        $batchId = $request->batch_id ?: $exam->batch_id;
    
        // Verify instructor owns this exam
        if (!$this->canMonitorExam($exam)) {
            return redirect()->back()->with(
                'error',
                'You are not authorized to monitor this exam.'
            );
        }
    
        $session = $this->proctoringService->startSession(
            $exam->id,
            auth()->id(),
            $batchId,
            $request->settings ?? []
        );
    
        return redirect()
            ->route('instructor.monitoring.show', $session->id)
            ->with('success', 'Monitoring session started successfully.');
    }

    /**
     * Show monitoring session
     */
    public function show(LiveMonitoringSession $session)
    {
        // Verify instructor owns this session
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to this monitoring session.');
        }

        $status = $this->proctoringService->getSessionStatus($session->id);
        
        // Get active students in this exam
        $students = StudentExam::where('exam_id', $session->exam_id)
            ->whereIn('status', ['started', 'in_progress'])
            ->with(['student'])
            ->get();

        // Get recent proctoring images
        $recentImages = ProctoringImage::where('monitoring_session_id', $session->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('instructor.monitoring.show', compact('session', 'status', 'students', 'recentImages'));
    }

    /**
     * End monitoring session
     */
    public function endSession(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->proctoringService->endSession($session->id);

        return redirect()->route('instructor.monitoring.index')
            ->with('success', 'Monitoring session ended.');
    }

    /**
     * Pause monitoring session
     */
    public function pauseSession(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $session->pause();

        return redirect()->back()
            ->with('success', 'Monitoring session paused.');
    }

    /**
     * Resume monitoring session
     */
    public function resumeSession(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $session->resume();

        return redirect()->back()
            ->with('success', 'Monitoring session resumed.');
    }

    /**
     * Get student proctoring details
     */
    public function studentProctoringDetails($studentExamId)
    {
        $studentExam = StudentExam::findOrFail($studentExamId);
        
        // Verify instructor has access to this student's exam
        if (!$this->canMonitorStudent($studentExam)) {
            abort(403);
        }

        $summary = $this->proctoringService->getStudentProctoringSummary($studentExamId);
        $images = ProctoringImage::where('student_exam_id', $studentExamId)
            ->latest()
            ->get();
        $activities = StudentActivityLog::where('student_exam_id', $studentExamId)
            ->latest()
            ->get();

        return view('instructor.monitoring.student-details', compact('studentExam', 'summary', 'images', 'activities'));
    }

    /**
     * Review proctoring images
     */
    public function reviewImages(Request $request)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:proctoring_images,id',
            'status' => 'required|in:normal,suspicious',
            'reason' => 'nullable|string'
        ]);

        // Verify instructor has access to these images
        $image = ProctoringImage::find($request->image_ids[0]);
        if ($image && !$this->canMonitorStudent($image->studentExam)) {
            abort(403);
        }

        $this->proctoringService->reviewImages(
            $request->image_ids,
            $request->status,
            $request->reason
        );

        return redirect()->back()->with('success', 'Images reviewed successfully.');
    }

    /**
     * Capture manual screenshot
     */
    public function captureManualScreenshot(Request $request, $sessionId)
    {
        $request->validate([
            'student_exam_id' => 'required|exists:student_exams,id',
            'image_data' => 'required|string'
        ]);

        $session = LiveMonitoringSession::findOrFail($sessionId);
        
        // Verify instructor owns this session
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $image = $this->proctoringService->captureScreenshot(
            $request->student_exam_id,
            $sessionId,
            $request->image_data,
            [
                'capture_type' => 'manual',
                'description' => 'Manual screenshot captured by instructor',
                'instructor_id' => auth()->id()
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Screenshot captured successfully.',
            'image' => $image
        ]);
    }

    /**
     * Log student activity
     */
    public function logActivity(Request $request, $sessionId)
    {
        $request->validate([
            'student_exam_id' => 'required|exists:student_exams,id',
            'activity_type' => 'required|in:tab_switch,window_blur,window_focus,mouse_leave,mouse_enter,copy_attempt,paste_attempt,right_click,devtools_open',
            'description' => 'nullable|string',
            'severity' => 'nullable|in:info,warning,critical'
        ]);

        $session = LiveMonitoringSession::findOrFail($sessionId);
        
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->proctoringService->logActivity(
            $request->student_exam_id,
            $sessionId,
            $request->activity_type,
            $request->only(['description', 'severity'])
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Activity logged successfully.'
        ]);
    }

    /**
     * Check if instructor can monitor an exam
     */
    protected function canMonitorExam($exam)
    {
        $user = auth()->user();
        
        // Admin can monitor all
        if ($user->isAdmin()) {
            return true;
        }

        // Check if instructor teaches this exam's batch
        return $exam->batches()->where('instructor_id', $user->id)->exists();
    }

    /**
     * Check if instructor can monitor a student
     */
    protected function canMonitorStudent($studentExam)
    {
        $user = auth()->user();
        
        // Admin can monitor all
        if ($user->isAdmin()) {
            return true;
        }

        // Check if instructor teaches this student's exam
        $exam = $studentExam->exam;
        return $exam->batches()->where('instructor_id', $user->id)->exists();
    }

    /**
     * Export monitoring report
     */
    public function exportReport(LiveMonitoringSession $session)
    {
        if ($session->instructor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $images = ProctoringImage::where('monitoring_session_id', $session->id)->get();
        $activities = StudentActivityLog::where('monitoring_session_id', $session->id)->get();

        // Generate CSV report
        $filename = 'monitoring-report-' . $session->id . '-' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Session ID', 'Exam', 'Student', 'Activity', 'Timestamp']);

        foreach ($activities as $activity) {
            fputcsv($handle, [
                $session->id,
                $session->exam->title,
                $activity->student->name ?? 'Unknown',
                $activity->activity_type,
                $activity->created_at
            ]);
        }

        fclose($handle);
        exit;
    }
}