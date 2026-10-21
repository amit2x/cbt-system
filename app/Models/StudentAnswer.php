<?php

namespace App\Models;

use App\Traits\HasAirport;
use Carbon\Carbon;

class StudentAnswer extends BaseModel
{
    use HasAirport;

    protected $fillable = [
        'student_exam_id', 'question_id', 'question_option_id',
        'answer_text', 'selected_options', 'is_correct',
        'marks_obtained', 'negative_marks_applied',
        'time_taken_seconds', 'is_marked_for_review', 'is_visited',
        'visit_count', 'last_saved_at', 'save_count',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'is_correct' => 'boolean',
        'is_marked_for_review' => 'boolean',
        'is_visited' => 'boolean',
        'last_saved_at' => 'datetime',
        'marks_obtained' => 'float',
        'negative_marks_applied' => 'float',
        'time_taken_seconds' => 'integer',
        'visit_count' => 'integer',
        'save_count' => 'integer',
    ];

    protected $appends = [
        'formatted_answer',
        'time_taken_formatted',
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }

    public function getTimeTakenFormattedAttribute()
    {
        if (! $this->time_taken_seconds) {
            return '0s';
        }

        $seconds = $this->time_taken_seconds;
        if ($seconds < 60) {
            return $seconds.'s';
        }
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;

        return $minutes.'m '.$secs.'s';
    }

    public function getFormattedAnswerAttribute()
    {
        if ($this->selected_options) {
            $options = QuestionOption::whereIn('id', $this->selected_options)
                ->orderBy('order')
                ->get();

            return $options->map(function ($opt) {
                return $opt->letter.'. '.$opt->option_text;
            })->implode('<br>');
        }

        return $this->answer_text;
    }

    public function getOptionLettersAttribute()
    {
        if (! $this->selected_options) {
            return [];
        }

        $options = QuestionOption::whereIn('id', $this->selected_options)
            ->orderBy('order')
            ->get();

        return $options->pluck('letter')->toArray();
    }

    // public function saveAnswer($answer, $isMarkedForReview = false)
    // {
    //     $this->update([
    //         'selected_options' => is_array($answer) ? $answer : null,
    //         'answer_text' => ! is_array($answer) ? $answer : null,
    //         'is_marked_for_review' => $isMarkedForReview,
    //         'is_visited' => true,
    //         'visit_count' => $this->visit_count + 1,
    //         'save_count' => $this->save_count + 1,
    //         'last_saved_at' => now(),
    //     ]);

    //     $this->studentExam->updateState();
    // }

    //  public function saveAnswer($answer, $isMarkedForReview = false)
    // {
    //     // Calculate time taken since last save
    //     $timeTaken = $this->last_saved_at
    //         ? now()->diffInSeconds($this->last_saved_at)
    //         : 0;

    //     $this->update([
    //         'selected_options' => is_array($answer) ? $answer : null,
    //         'answer_text' => !is_array($answer) ? $answer : null,
    //         'is_marked_for_review' => $isMarkedForReview,
    //         'is_visited' => true,
    //         'visit_count' => $this->visit_count + 1,
    //         'save_count' => $this->save_count + 1,
    //         'time_taken_seconds' => ($this->time_taken_seconds ?? 0) + $timeTaken,
    //         'last_saved_at' => now()
    //     ]);

    //     $this->studentExam->updateState();
    // }

    public function saveAnswer($answer, $isMarkedForReview = false)
    {
        $currentTime = now();
        $timeTaken = 0;

        // Calculate time taken using absolute flag (true) to prevent negative timestamps
        if ($this->last_saved_at) {
            $lastSavedAt = Carbon::parse($this->last_saved_at);
            $timeTaken = (int) $lastSavedAt->diffInSeconds($currentTime, true);
        }

        $oldTotalTime = $this->time_taken_seconds ?? 0;
        $newTotalTime = $oldTotalTime + $timeTaken;

        // Debug logs to inspect the math before database insertion
        \Log::info('Debug Model Time Calculation:', [
            'student_answer_id' => $this->id,
            'current_time' => $currentTime->toDateTimeString(),
            'previous_last_saved_at' => $this->last_saved_at ? Carbon::parse($this->last_saved_at)->toDateTimeString() : null,
            'calculated_diff_seconds' => $timeTaken,
            'old_total_seconds' => $oldTotalTime,
            'new_total_seconds' => $newTotalTime,
        ]);

        $this->update([
            'selected_options' => is_array($answer) ? $answer : null,
            'answer_text' => ! is_array($answer) ? $answer : null,
            'is_marked_for_review' => $isMarkedForReview,
            'is_visited' => true,
            'visit_count' => ($this->visit_count ?? 0) + 1,
            'save_count' => ($this->save_count ?? 0) + 1,
            'time_taken_seconds' => $newTotalTime,
            'last_saved_at' => $currentTime,
        ]);

        if ($this->studentExam) {
            $this->studentExam->updateState();
        }
    }

    public function markForReview()
    {
        $this->update([
            'is_marked_for_review' => ! $this->is_marked_for_review,
            'last_saved_at' => now(),
        ]);

        $this->studentExam->updateState();
    }

    // public function calculateMarks()
    // {
    //     if (!$this->selected_options && !$this->answer_text) {
    //         $this->update([
    //             'is_correct' => null,
    //             'marks_obtained' => 0,
    //             'negative_marks_applied' => 0
    //         ]);
    //         return 0;
    //     }

    //     $question = $this->question;
    //     $examQuestion = $question->exams()
    //         ->where('exam_id', $this->studentExam->exam_id)
    //         ->first();

    //     $marks = $examQuestion ? $examQuestion->pivot->marks : $question->marks;
    //     $negativeMarks = $examQuestion ? $examQuestion->pivot->negative_marks : $question->negative_marks;

    //     $isCorrect = false;

    //     switch ($question->type) {
    //         case 'single_choice':
    //             $selectedOption = is_array($this->selected_options)
    //                 ? ($this->selected_options[0] ?? null)
    //                 : $this->selected_options;
    //             $isCorrect = $question->isCorrectSingle($selectedOption);
    //             break;

    //         case 'multiple_choice':
    //             $selectedOptions = is_array($this->selected_options)
    //                 ? $this->selected_options
    //                 : [];
    //             $isCorrect = $question->isCorrectMultiple($selectedOptions);
    //             break;

    //         case 'numerical':
    //             $isCorrect = $question->isCorrectNumerical($this->answer_text);
    //             break;
    //     }

    //     $obtainedMarks = $isCorrect ? $marks : -$negativeMarks;
    //     if ($obtainedMarks < 0) $obtainedMarks = 0;

    //     $this->update([
    //         'is_correct' => $isCorrect,
    //         'marks_obtained' => $obtainedMarks,
    //         'negative_marks_applied' => !$isCorrect ? $negativeMarks : 0
    //     ]);

    //     return $obtainedMarks;
    // }
    /**
     * Calculate marks for this answer
     */
    // public function calculateMarks()
    // {
    //     if (! $this->selected_options && ! $this->answer_text) {
    //         $this->update([
    //             'is_correct' => null,
    //             'marks_obtained' => 0,
    //             'negative_marks_applied' => 0,
    //         ]);

    //         return 0;
    //     }

    //     $question = $this->question;
    //     $examQuestion = $question->exams()
    //         ->where('exam_id', $this->studentExam->exam_id)
    //         ->first();

    //     $marks = $examQuestion ? ($examQuestion->pivot->marks ?? $question->marks) : $question->marks;
    //     $negativeMarks = $examQuestion ? ($examQuestion->pivot->negative_marks ?? $question->negative_marks) : $question->negative_marks;

    //     $isCorrect = false;

    //     switch ($question->type) {
    //         case 'single_choice':
    //             $selectedOption = is_array($this->selected_options)
    //                 ? ($this->selected_options[0] ?? null)
    //                 : $this->selected_options;
    //             $isCorrect = $question->isCorrectSingle($selectedOption);
    //             break;

    //         case 'multiple_choice':
    //             $selectedOptions = is_array($this->selected_options)
    //                 ? $this->selected_options
    //                 : [];
    //             $isCorrect = $question->isCorrectMultiple($selectedOptions);
    //             break;

    //         case 'numerical':
    //             $isCorrect = $question->isCorrectNumerical($this->answer_text);
    //             break;
    //     }

    //     $obtainedMarks = $isCorrect ? $marks : -$negativeMarks;
    //     if ($obtainedMarks < 0) {
    //         $obtainedMarks = 0;
    //     }

    //     $this->update([
    //         'is_correct' => $isCorrect,
    //         'marks_obtained' => $obtainedMarks,
    //         'negative_marks_applied' => ! $isCorrect ? $negativeMarks : 0,
    //     ]);

    //     return $obtainedMarks;
    // }

    /**
     * Calculate marks for this answer
     */
    // public function calculateMarks()
    // {
    //     // If no answer provided, return 0
    //     if (!$this->selected_options && !$this->answer_text) {
    //         $this->update([
    //             'is_correct' => null,
    //             'marks_obtained' => 0,
    //             'negative_marks_applied' => 0
    //         ]);
    //         return 0;
    //     }

    //     $question = $this->question;

    //     // Get the exam question pivot to get marks and negative marks
    //     $examQuestion = $question->exams()
    //         ->where('exam_id', $this->studentExam->exam_id)
    //         ->first();

    //     // Get marks from pivot or use question defaults
    //     $marks = $examQuestion ? ($examQuestion->pivot->marks ?? $question->marks) : $question->marks;
    //     $negativeMarks = $examQuestion ? ($examQuestion->pivot->negative_marks ?? $question->negative_marks) : $question->negative_marks;

    //     $isCorrect = false;

    //     // Check answer based on question type
    //     switch ($question->type) {
    //         case 'single_choice':
    //             $selectedOption = is_array($this->selected_options)
    //                 ? ($this->selected_options[0] ?? null)
    //                 : $this->selected_options;
    //             $isCorrect = $question->isCorrectSingle($selectedOption);
    //             break;

    //         case 'multiple_choice':
    //             $selectedOptions = is_array($this->selected_options)
    //                 ? $this->selected_options
    //                 : [];
    //             $isCorrect = $question->isCorrectMultiple($selectedOptions);
    //             break;

    //         case 'numerical':
    //             $isCorrect = $question->isCorrectNumerical($this->answer_text);
    //             break;
    //     }

    //     // Calculate marks
    //     $obtainedMarks = $isCorrect ? $marks : -$negativeMarks;
    //     // Ensure marks don't go below 0
    //     if ($obtainedMarks < 0) $obtainedMarks = 0;

    //     // Update the answer record
    //     $this->update([
    //         'is_correct' => $isCorrect,
    //         'marks_obtained' => $obtainedMarks,
    //         'negative_marks_applied' => !$isCorrect ? $negativeMarks : 0
    //     ]);

    //     \Log::info('Answer calculated', [
    //         'answer_id' => $this->id,
    //         'question_id' => $this->question_id,
    //         'is_correct' => $isCorrect,
    //         'marks_obtained' => $obtainedMarks,
    //         'marks' => $marks,
    //         'negative_marks' => $negativeMarks
    //     ]);

    //     return $obtainedMarks;
    // }

    /**
     * Calculate marks for this answer
     */
    public function calculateMarks()
    {
        // If no answer provided, return 0
        if (! $this->selected_options && ! $this->answer_text) {
            $this->update([
                'is_correct' => null,
                'marks_obtained' => 0,
                'negative_marks_applied' => 0,
            ]);

            return 0;
        }

        $question = $this->question;

        if (! $question) {
            return 0;
        }

        // Get the exam question pivot to get marks and negative marks
        $examQuestion = $question->exams()
            ->where('exam_id', $this->studentExam->exam_id)
            ->first();

        // need to update the marks and negative marks based on the exam question pivot if it exists, otherwise use the question defaults

        // Get marks from pivot or use question defaults
        $marks = $examQuestion ? ($examQuestion->pivot->marks ?? $question->marks) : $question->marks;
        $negativeMarks = $examQuestion ? ($examQuestion->pivot->negative_marks ?? $question->negative_marks) : $question->negative_marks;

        $isCorrect = false;

        // Check answer based on question type
        switch ($question->type) {
            case 'single_choice':
                $selectedOption = is_array($this->selected_options)
                    ? ($this->selected_options[0] ?? null)
                    : $this->selected_options;
                $isCorrect = $question->isCorrectSingle($selectedOption);
                break;

            case 'multiple_choice':
                $selectedOptions = is_array($this->selected_options)
                    ? $this->selected_options
                    : [];
                $isCorrect = $question->isCorrectMultiple($selectedOptions);
                break;

            case 'numerical':
                $isCorrect = $question->isCorrectNumerical($this->answer_text);
                break;
        }

        // Calculate marks
        $obtainedMarks = $isCorrect ? $marks : -$negativeMarks;

        // Ensure marks don't go below 0
        if ($obtainedMarks < 0) {
            $obtainedMarks = 0;
        }

        // Update the answer record
        $this->update([
            'is_correct' => $isCorrect,
            'marks_obtained' => $obtainedMarks,
            'negative_marks_applied' => ! $isCorrect ? $negativeMarks : 0,
        ]);

        return $obtainedMarks;
    }
}
