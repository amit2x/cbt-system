<?php
// app/Services/Proctoring/ProctoringService.php

namespace App\Services\Proctoring;

use App\Models\LiveMonitoringSession;
use App\Models\ProctoringImage;
use App\Models\StudentActivityLog;
use App\Models\StudentExam;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProctoringService
{
    /**
     * Start a monitoring session
     */
    public function startSession($examId, $instructorId, $batchId = null, $settings = [])
    {
        $session = LiveMonitoringSession::create([
            'exam_id' => $examId,
            'instructor_id' => $instructorId,
            'batch_id' => $batchId,
            'session_name' => $settings['session_name'] ?? 'Monitoring Session ' . now()->format('Y-m-d H:i'),
            'started_at' => now(),
            'status' => 'active',
            'track_tab_switches' => $settings['track_tab_switches'] ?? true,
            'track_mouse_events' => $settings['track_mouse_events'] ?? false,
            'enable_proctoring' => $settings['enable_proctoring'] ?? true,
            'screenshot_interval' => $settings['screenshot_interval'] ?? 60,
            'alert_settings' => $settings['alert_settings'] ?? [
                'max_tab_switches' => 3,
                'max_warnings' => 5,
                'auto_terminate_after' => 10
            ],
            'total_students' => $settings['total_students'] ?? 0,
            'active_students' => $settings['active_students'] ?? 0
        ]);

        Log::info('Proctoring session started', [
            'session_id' => $session->id,
            'exam_id' => $examId,
            'instructor_id' => $instructorId
        ]);

        return $session;
    }

    /**
     * End a monitoring session
     */
    public function endSession($sessionId)
    {
        $session = LiveMonitoringSession::findOrFail($sessionId);
        $session->end();

        Log::info('Proctoring session ended', [
            'session_id' => $sessionId,
            'duration' => $session->duration
        ]);

        return $session;
    }

    /**
     * Capture a screenshot during monitoring
     */
    public function captureScreenshot($studentExamId, $monitoringSessionId, $imageData, $metadata = [])
    {
        try {
            $studentExam = StudentExam::findOrFail($studentExamId);
            $session = LiveMonitoringSession::findOrFail($monitoringSessionId);

            if (!$session->shouldCaptureScreenshot()) {
                return null;
            }

            // Decode image data
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $imageData = base64_decode($imageData);
            }

            // Generate paths
            $filename = Str::random(40) . '.jpg';
            $path = 'proctoring/' . $studentExamId . '/' . date('Y-m-d') . '/' . $filename;
            $thumbnailPath = 'proctoring/' . $studentExamId . '/' . date('Y-m-d') . '/thumb_' . $filename;

            // Save image
            Storage::disk('public')->put($path, $imageData);

            // Create thumbnail
            $thumbnail = $this->createThumbnail($imageData);
            Storage::disk('public')->put($thumbnailPath, $thumbnail);

            // Calculate image size
            $imageSize = strlen($imageData);

            // Check for suspicious activity
            $isSuspicious = $this->detectSuspiciousActivity($metadata, $studentExam);

            // Create proctoring image record
            $proctoringImage = ProctoringImage::create([
                'airport_id' => $studentExam->airport_id,
                'student_exam_id' => $studentExam->id,
                'student_id' => $studentExam->student_id,
                'exam_id' => $studentExam->exam_id,
                'monitoring_session_id' => $monitoringSessionId,
                'image_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'image_size' => $imageSize,
                'metadata' => array_merge($metadata, [
                    'timestamp' => now()->toDateTimeString(),
                    'question_id' => $studentExam->current_question_id
                ]),
                'capture_type' => 'automatic',
                'is_suspicious' => $isSuspicious,
                'suspicious_reason' => $isSuspicious ? 'Suspicious activity detected' : null,
                'reviewed' => false
            ]);

            // Update session stats
            if ($isSuspicious) {
                $session->increment('warning_count');
            }

            Log::info('Screenshot captured', [
                'image_id' => $proctoringImage->id,
                'student_exam_id' => $studentExamId,
                'is_suspicious' => $isSuspicious
            ]);

            return $proctoringImage;

        } catch (\Exception $e) {
            Log::error('Failed to capture screenshot: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a thumbnail from image data
     */
    protected function createThumbnail($imageData, $maxWidth = 200, $maxHeight = 200)
    {
        $image = imagecreatefromstring($imageData);
        if (!$image) {
            return $imageData;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($thumbnail, null, 80);
        $thumbnailData = ob_get_clean();

        imagedestroy($image);
        imagedestroy($thumbnail);

        return $thumbnailData;
    }

    /**
     * Detect suspicious activity
     */
    protected function detectSuspiciousActivity($metadata, $studentExam)
    {
        // Check for multiple tab switches
        if (isset($metadata['tab_switch_count']) && $metadata['tab_switch_count'] > 3) {
            return true;
        }

        // Check for excessive mouse leaves
        if (isset($metadata['mouse_leave_count']) && $metadata['mouse_leave_count'] > 5) {
            return true;
        }

        // Check for copy/paste attempts
        if (isset($metadata['copy_attempt']) && $metadata['copy_attempt'] === true) {
            return true;
        }

        // Check for dev tools open
        if (isset($metadata['devtools_open']) && $metadata['devtools_open'] === true) {
            return true;
        }

        return false;
    }

    /**
     * Log student activity
     */
    public function logActivity($studentExamId, $monitoringSessionId, $activityType, $data = [])
    {
        $studentExam = StudentExam::findOrFail($studentExamId);

        return StudentActivityLog::create([
            'student_exam_id' => $studentExamId,
            'student_id' => $studentExam->student_id,
            'exam_id' => $studentExam->exam_id,
            'monitoring_session_id' => $monitoringSessionId,
            'activity_type' => $activityType,
            'activity_data' => $data,
            'description' => $data['description'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'occurred_at' => now()
        ]);
    }

    /**
     * Get monitoring session status
     */
    public function getSessionStatus($sessionId)
    {
        $session = LiveMonitoringSession::with(['exam', 'batch'])
            ->findOrFail($sessionId);

        $recentImages = ProctoringImage::where('monitoring_session_id', $sessionId)
            ->latest()
            ->limit(10)
            ->get();

        $studentStats = StudentExam::where('exam_id', $session->exam_id)
            ->whereIn('status', ['started', 'in_progress'])
            ->count();

        return [
            'session' => $session,
            'student_stats' => [
                'total' => $session->total_students,
                'active' => $session->active_students,
                'warnings' => $session->warning_count,
                'terminated' => $session->terminated_count
            ],
            'recent_images' => $recentImages,
            'is_active' => $session->isActive()
        ];
    }

    /**
     * Review proctoring images
     */
    public function reviewImages($imageIds, $status, $reason = null)
    {
        $images = ProctoringImage::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            if ($status === 'suspicious') {
                $image->markAsSuspicious($reason);
            } else {
                $image->update(['is_suspicious' => false]);
            }
            $image->markAsReviewed();
        }

        return $images;
    }

    /**
     * Get student proctoring summary
     */
    public function getStudentProctoringSummary($studentExamId)
    {
        $images = ProctoringImage::where('student_exam_id', $studentExamId)->get();
        $activities = StudentActivityLog::where('student_exam_id', $studentExamId)->get();

        return [
            'total_images' => $images->count(),
            'suspicious_images' => $images->where('is_suspicious', true)->count(),
            'reviewed_images' => $images->where('reviewed', true)->count(),
            'tab_switches' => $activities->where('activity_type', 'tab_switch')->count(),
            'warnings' => $activities->where('severity', 'warning')->count(),
            'critical_events' => $activities->where('severity', 'critical')->count()
        ];
    }
}