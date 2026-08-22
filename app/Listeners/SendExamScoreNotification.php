<?php

namespace App\Listeners;

use App\Events\ExamScorePublished;
use App\Notifications\ExamScoreNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExamScoreNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ExamScorePublished $event)
    {
        $event->student->notify(
            new ExamScoreNotification($event->exam, $event->score)
        );
    }
}