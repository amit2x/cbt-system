<?php

namespace App\Events;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamScorePublished
{
    use Dispatchable, SerializesModels;

    public $student;
    public $exam;
    public $score;

    public function __construct(User $student, Exam $exam, $score)
    {
        $this->student = $student;
        $this->exam = $exam;
        $this->score = $score;
    }
}