<?php
// app/Helpers/ActivityHelper.php

namespace App\Helpers;

use App\Services\ActivityLogger;
use Illuminate\Support\Facades\App;

class ActivityHelper
{
    /**
     * Log an activity.
     */
    public static function log(string $description, $subject = null, array $properties = [], string $logName = 'default')
    {
        $logger = App::make(ActivityLogger::class);
        $logger->log($description, $subject, $properties, $logName);
    }

    /**
     * Log a security event.
     */
    public static function security(string $event, array $details = [])
    {
        $logger = App::make(ActivityLogger::class);
        $logger->logSecurity($event, $details);
    }

    /**
     * Log an exam event.
     */
    public static function exam(string $event, $exam, $student = null, array $details = [])
    {
        $logger = App::make(ActivityLogger::class);
        $logger->logExam($event, $exam, $student, $details);
    }

    /**
     * Log a data change.
     */
    public static function dataChange(string $action, $subject, array $changes = [])
    {
        $logger = App::make(ActivityLogger::class);
        $logger->logDataChange($action, $subject, $changes);
    }
    
    
    
    /***
     * 
     * HELPER EXAMPLE
     */
     
     // Log custom activity
      //  ActivityHelper::log('A new exam was created', $exam, [
        //    'exam_type' => $exam->type,
          //  'duration' => $exam->duration_minutes
        //]);
}