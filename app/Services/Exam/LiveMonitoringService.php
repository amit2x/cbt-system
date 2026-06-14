<?php

namespace App\Services\Exam;

use App\Models\Exam;
use App\Models\LiveMonitoringSession;
use App\Models\ProctoringImage;
use App\Models\StudentActivityLog;
use App\Models\StudentExam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LiveMonitoringService
{
    /**
     * Create monitoring session
     */
    public function createSession(Exam $exam, $instructorId, $batchId = null, $settings = [])
    {
        return LiveMonitoringSession::create([
            'exam_id' => $exam->id,
            'instructor_id' => $instructorId,
            'batch_id' => $batchId,
            'session_name' => $exam->title . ' - ' . now()->format('d M Y H:i'),
            'started_at' => now(),
            'status' => 'active',
            'track_tab_switches' => $settings['track_tab_switches'] ?? true,
            'track_mouse_events' => $settings['track_mouse_events'] ?? false,
            'enable_proctoring' => $settings['enable_proctoring'] ?? false,
            'screenshot_interval' => $settings['screenshot_interval'] ?? 30,
            'alert_settings' => $settings['alert_settings'] ?? [
                'max_tab_switches' => 3,
                'max_window_blurs' => 2,
                'warning_threshold' => 2,
                'auto_terminate' => true
            ],
            'total_students' => $this->getTotalStudents($exam, $batchId)
        ]);
    }

    /**
     * Get total students for exam/batch
     */
    protected function getTotalStudents(Exam $exam, $batchId = null)
    {
        if ($batchId) {
            return DB::table('batch_student')
                ->where('batch_id', $batchId)
                ->where('status', 'active')
                ->count();
        }

        if ($exam->batch_id) {
            return DB::table('batch_student')
                ->where('batch_id', $exam->batch_id)
                ->where('status', 'active')
                ->count();
        }

        return $exam->batches()
            ->withCount('students')
            ->get()
            ->sum('students_count');
    }

    /**
     * Get live dashboard data
     */
    public function getDashboardData(LiveMonitoringSession $session)
    {
        $activeExams = StudentExam::where('exam_id', $session->exam_id)
            ->whereIn('status', ['started', 'in_progress']);

        if ($session->batch_id) {
            $activeExams->where('batch_id', $session->batch_id);
        }

        $students = $activeExams->with('student')->get();

        $data = [
            'session' => [
                'id' => $session->id,
                'name' => $session->session_name,
                'duration' => $session->duration,
                'status' => $session->status
            ],
            'overview' => [
                'total_students' => $session->total_students,
                'active_students' => $students->count(),
                'completed' => StudentExam::where('exam_id', $session->exam_id)
                    ->where('status', 'completed')
                    ->count(),
                'warnings' => $session->warning_count,
                'terminated' => $session->terminated_count
            ],
            'students' => [],
            'recent_alerts' => [],
            'activity_chart' => $this->getActivityChart($session)
        ];

        // Student details with activity
        foreach ($students as $studentExam) {
            $activity = $this->getStudentActivity($studentExam);
            $warnings = $studentExam->tab_switch_count ?? 0;

            $data['students'][] = [
                'id' => $studentExam->student->id,
                'name' => $studentExam->student->name,
                'email' => $studentExam->student->email,
                'avatar' => $studentExam->student->avatar_url,
                'started_at' => $studentExam->started_at->format('H:i:s'),
                'progress' => $studentExam->progress_percentage,
                'answered' => $studentExam->answered_questions,
                'marked' => $studentExam->marked_for_review_count,
                'warnings' => $warnings,
                'last_activity' => $activity['last_activity'],
                'status' => $this->getStudentStatus($warnings),
                'has_suspicious_images' => $this->hasSuspiciousImages($studentExam)
            ];
        }

        // Recent alerts
        $data['recent_alerts'] = StudentActivityLog::where('monitoring_session_id', $session->id)
            ->whereIn('severity', ['warning', 'critical'])
            ->with('student')
            ->orderBy('occurred_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'student' => $log->student->name,
                    'type' => $log->activity_type,
                    'severity' => $log->severity,
                    'description' => $log->formatted_data,
                    'time' => $log->occurred_at->diffForHumans()
                ];
            });

        return $data;
    }

    /**
     * Get student activity summary
     */
    protected function getStudentActivity(StudentExam $studentExam)
    {
        $lastLog = $studentExam->logs()
            ->orderBy('occurred_at', 'desc')
            ->first();

        return [
            'last_activity' => $lastLog ? $lastLog->occurred_at->diffForHumans() : 'No activity',
            'tab_switches' => $studentExam->tab_switch_count,
            'answers_saved' => $studentExam->answers()->count(),
            'time_spent' => now()->diffInMinutes($studentExam->started_at)
        ];
    }

    /**
     * Get student status based on warnings
     */
    protected function getStudentStatus($warnings)
    {
        if ($warnings >= 5) return 'critical';
        if ($warnings >= 3) return 'warning';
        if ($warnings >= 1) return 'attention';
        return 'good';
    }

    /**
     * Check if student has suspicious images
     */
    protected function hasSuspiciousImages(StudentExam $studentExam)
    {
        return $studentExam->proctoringImages()
            ->where('is_suspicious', true)
            ->exists();
    }

    /**
     * Get activity chart data
     */
    protected function getActivityChart(LiveMonitoringSession $session)
    {
        $minutes = 30; // Last 30 minutes
        $data = [];
        $labels = [];

        for ($i = $minutes; $i >= 0; $i--) {
            $time = now()->subMinutes($i);
            $labels[] = $time->format('H:i');

            $count = StudentActivityLog::where('monitoring_session_id', $session->id)
                ->whereBetween('occurred_at', [$time, $time->copy()->addMinute()])
                ->count();

            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Log student activity
     */
    public function logActivity($studentExamId, $activityType, $severity = 'info', $data = null, $description = null)
    {
        $studentExam = StudentExam::findOrFail($studentExamId);

        return StudentActivityLog::create([
            'student_exam_id' => $studentExamId,
            'student_id' => $studentExam->student_id,
            'exam_id' => $studentExam->exam_id,
            'activity_type' => $activityType,
            'activity_data' => $data,
            'description' => $description,
            'severity' => $severity,
            'occurred_at' => now()
        ]);
    }

    /**
     * Process warning and check thresholds
     */
    public function processWarning(StudentExam $studentExam, $activityType, $settings = null)
    {
        $session = LiveMonitoringSession::where('exam_id', $studentExam->exam_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$session) return null;

        $alertSettings = $session->alert_settings;
        $warningCount = $this->getWarningCount($studentExam, $activityType);

        // Log the activity
        $log = $this->logActivity(
            $studentExam->id,
            $activityType,
            $warningCount >= ($alertSettings['warning_threshold'] ?? 2) ? 'warning' : 'info',
            ['count' => $warningCount],
            "Warning count: $warningCount"
        );

        // Attach to monitoring session
        if ($session) {
            $log->update(['monitoring_session_id' => $session->id]);
            $session->increment('warning_count');
        }

        // Check if should auto-terminate
        if ($alertSettings['auto_terminate'] ?? true) {
            if ($warningCount >= ($alertSettings['max_tab_switches'] ?? 3)) {
                $this->terminateExam($studentExam, "Exceeded maximum warnings");
            }
        }

        return $log;
    }

    /**
     * Get warning count for student
     */
    protected function getWarningCount(StudentExam $studentExam, $activityType)
    {
        return StudentActivityLog::where('student_exam_id', $studentExam->id)
            ->where('activity_type', $activityType)
            ->where('severity', 'warning')
            ->count();
    }

    /**
     * Terminate exam
     */
    public function terminateExam(StudentExam $studentExam, $reason)
    {
        $studentExam->update([
            'status' => 'terminated'
        ]);

        // Log termination
        $this->logActivity(
            $studentExam->id,
            'exam_terminated',
            'critical',
            ['reason' => $reason],
            "Exam terminated: $reason"
        );

        // Update session count
        $session = LiveMonitoringSession::where('exam_id', $studentExam->exam_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($session) {
            $session->increment('terminated_count');
        }

        return $studentExam;
    }

    /**
     * Capture proctoring image
     */
    public function captureImage(StudentExam $studentExam, $imageData)
    {
        // Generate unique filename
        $filename = 'proctoring/' . $studentExam->id . '/' . now()->timestamp . '.jpg';

        // Store image
        $path = Storage::disk('public')->put($filename, base64_decode($imageData));

        // Create thumbnail (you'd need an image processing library for this)
        $thumbnailPath = null;

        $image = ProctoringImage::create([
            'student_exam_id' => $studentExam->id,
            'student_id' => $studentExam->student_id,
            'exam_id' => $studentExam->exam_id,
            'image_path' => $filename,
            'thumbnail_path' => $thumbnailPath,
            'image_size' => strlen($imageData),
            'metadata' => [
                'timestamp' => now()->toDateTimeString(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip()
            ],
            'capture_type' => 'automatic'
        ]);

        // Check for suspicious activity (you'd implement AI detection here)
        $this->checkSuspiciousActivity($image);

        return $image;
    }

    /**
     * Check for suspicious activity in image (placeholder for AI integration)
     */
    protected function checkSuspiciousActivity(ProctoringImage $image)
    {
        // TODO: Integrate with AI service for face detection, multiple person detection, etc.
        // For now, just mark some as suspicious randomly for demo
        if (rand(1, 100) <= 5) { // 5% chance
            $image->markAsSuspicious('Multiple faces detected');
        }
    }

    /**
     * Send alert to instructor
     */
    public function sendAlert($sessionId, $type, $message, $studentId = null)
    {
        // This would integrate with WebSockets for real-time notifications
        // For now, we'll just log it
        Log::info("Live Monitoring Alert: $message");
    }

    /**
     * Get suspicious images for review
     */
    public function getSuspiciousImages($sessionId = null)
    {
        $query = ProctoringImage::suspicious()
            ->unreviewed()
            ->with(['student', 'exam']);

        if ($sessionId) {
            $query->where('monitoring_session_id', $sessionId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get student timeline
     */
    public function getStudentTimeline(StudentExam $studentExam)
    {
        $logs = $studentExam->logs()
            ->orderBy('occurred_at', 'desc')
            ->get();

        $answers = $studentExam->answers()
            ->with('question')
            ->orderBy('last_saved_at', 'desc')
            ->get();

        $timeline = [];

        // Add exam start
        $timeline[] = [
            'time' => $studentExam->started_at,
            'type' => 'exam_start',
            'description' => 'Exam started',
            'icon' => 'play-circle',
            'color' => 'success'
        ];

        // Add answers
        foreach ($answers as $answer) {
            if ($answer->last_saved_at) {
                $timeline[] = [
                    'time' => $answer->last_saved_at,
                    'type' => 'answer_saved',
                    'description' => 'Answered question: ' . substr(strip_tags($answer->question->question_text), 0, 50) . '...',
                    'icon' => 'check-circle',
                    'color' => 'info'
                ];
            }
        }

        // Add activity logs
        foreach ($logs as $log) {
            $timeline[] = [
                'time' => $log->occurred_at,
                'type' => $log->activity_type,
                'description' => $log->formatted_data,
                'icon' => $this->getActivityIcon($log->activity_type),
                'color' => $log->severity_color
            ];
        }

        // Add exam end if completed
        if ($studentExam->submitted_at) {
            $timeline[] = [
                'time' => $studentExam->submitted_at,
                'type' => 'exam_end',
                'description' => 'Exam ' . ($studentExam->status == 'completed' ? 'completed' : 'terminated'),
                'icon' => $studentExam->status == 'completed' ? 'check-circle-fill' : 'exclamation-triangle-fill',
                'color' => $studentExam->status == 'completed' ? 'success' : 'danger'
            ];
        }

        // Sort by time
        usort($timeline, function($a, $b) {
            return $b['time']->timestamp - $a['time']->timestamp;
        });

        return $timeline;
    }

    /**
     * Get icon for activity type
     */
    protected function getActivityIcon($type)
    {
        return match($type) {
            'tab_switch' => 'arrow-left-right',
            'window_blur' => 'window',
            'copy_attempt' => 'files',
            'paste_attempt' => 'clipboard',
            'right_click' => 'mouse',
            'devtools_open' => 'tools',
            'warning_issued' => 'exclamation-triangle',
            default => 'record'
        };
    }
}
