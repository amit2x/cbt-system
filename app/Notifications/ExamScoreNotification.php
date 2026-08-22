<?php

namespace App\Notifications;

use App\Models\Exam;
use App\Models\User;

class ExamScoreNotification extends BaseNotification
{
    protected $exam;
    protected $score;

    public function __construct(Exam $exam, $score)
    {
        parent::__construct([
            'title' => 'Exam Score Available',
            'message' => "Your score for exam '{$exam->title}' is: {$score}%",
            'exam_id' => $exam->id,
            'score' => $score,
            'url' => route('student.exams.show', $exam->id),
            'icon' => 'bi-file-text',
            'type' => 'exam'
        ]);
    }
}