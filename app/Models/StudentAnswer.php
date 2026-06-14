<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
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

    public function saveAnswer($answer, $isMarkedForReview = false)
    {
        $this->update([
            'selected_options' => is_array($answer) ? $answer : null,
            'answer_text' => ! is_array($answer) ? $answer : null,
            'is_marked_for_review' => $isMarkedForReview,
            'is_visited' => true,
            'visit_count' => $this->visit_count + 1,
            'save_count' => $this->save_count + 1,
            'last_saved_at' => now(),
        ]);

        $this->studentExam->updateState();
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
    public function calculateMarks()
    {
        if (! $this->selected_options && ! $this->answer_text) {
            $this->update([
                'is_correct' => null,
                'marks_obtained' => 0,
                'negative_marks_applied' => 0,
            ]);

            return 0;
        }

        $question = $this->question;
        $examQuestion = $question->exams()
            ->where('exam_id', $this->studentExam->exam_id)
            ->first();

        $marks = $examQuestion ? ($examQuestion->pivot->marks ?? $question->marks) : $question->marks;
        $negativeMarks = $examQuestion ? ($examQuestion->pivot->negative_marks ?? $question->negative_marks) : $question->negative_marks;

        $isCorrect = false;

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

        $obtainedMarks = $isCorrect ? $marks : -$negativeMarks;
        if ($obtainedMarks < 0) {
            $obtainedMarks = 0;
        }

        $this->update([
            'is_correct' => $isCorrect,
            'marks_obtained' => $obtainedMarks,
            'negative_marks_applied' => ! $isCorrect ? $negativeMarks : 0,
        ]);

        return $obtainedMarks;
    }
}
