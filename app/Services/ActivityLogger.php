<?php
// app/Services/ActivityLogger.php

namespace App\Services;

use Spatie\Activitylog\ActivityLogger as SpatieActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a custom activity.
     */
    public function log(string $description, $subject = null, array $properties = [], string $logName = 'default')
    {
        $logger = activity($logName);

        if ($subject) {
            $logger->performedOn($subject);
        }

        if (Auth::check()) {
            $logger->causedBy(Auth::user());
        }

        if (!empty($properties)) {
            $logger->withProperties($properties);
        }

        // Add IP address and user agent
        $logger->withProperties([
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
        ]);

        $logger->log($description);
    }

    /**
     * Log an authentication event.
     */
    public function logAuth(string $event, $user = null)
    {
        $user = $user ?? Auth::user();
        $userId = $user ? $user->id : 'unknown';
        $userName = $user ? $user->name : 'Unknown User';

        $this->log("{$userName} ({$userId}) {$event}", null, [
            'event' => 'auth',
            'auth_type' => $event,
            'user_id' => $userId
        ], 'auth');
    }

    /**
     * Log an exam event.
     */
    public function logExam(string $event, $exam, $student = null, array $details = [])
    {
        $student = $student ?? Auth::user();
        $logName = 'exam';

        $properties = array_merge([
            'exam_id' => $exam->id ?? null,
            'exam_title' => $exam->title ?? null,
            'student_id' => $student?->id,
            'student_name' => $student?->name,
            'event_type' => $event,
        ], $details);

        $this->log("{$event}: {$exam->title}", $exam, $properties, $logName);
    }

    /**
     * Log a security event.
     */
    public function logSecurity(string $event, array $details = [])
    {
        $properties = array_merge([
            'event' => 'security',
            'security_event' => $event,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Guest',
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ], $details);

        $this->log("Security event: {$event}", null, $properties, 'security');
    }

    /**
     * Log a data change.
     */
    public function logDataChange(string $action, $subject, array $changes = [])
    {
        $modelName = class_basename($subject);
        $modelId = $subject->id ?? 'new';
        $identifier = $subject->name ?? $subject->title ?? "{$modelName} #{$modelId}";

        $properties = array_merge([
            'model' => $modelName,
            'model_id' => $modelId,
            'identifier' => $identifier,
            'action' => $action,
        ], $changes);

        $this->log("{$action} {$modelName}: {$identifier}", $subject, $properties);
    }
}