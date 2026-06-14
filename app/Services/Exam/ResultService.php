<?php

namespace App\Services\Exam;

use App\Models\StudentExam;
use App\Models\Result;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\DB;

class ResultService
{
    /**
     * Calculate exam result
     */
    public function calculateResult(StudentExam $studentExam)
    {
        $answers = StudentAnswer::where('student_exam_id', $studentExam->id)->get();

        $totalMarks = 0;
        $obtainedMarks = 0;
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;

        // Get exam questions with marks
        $examQuestions = $studentExam->exam->questions()->withPivot('marks')->get();
        $totalQuestions = $examQuestions->count();

        foreach ($examQuestions as $question) {
            $answer = $answers->where('question_id', $question->id)->first();

            // Get marks from exam question pivot or default question marks
            $questionMarks = $question->pivot->marks ?? $question->marks;
            $totalMarks += $questionMarks;

            if (!$answer) {
                $unansweredCount++;
                continue;
            }

            if ($answer->is_correct) {
                $obtainedMarks += $questionMarks;
                $correctCount++;
            } else {
                $obtainedMarks -= $question->negative_marks;
                $incorrectCount++;
            }
        }

        // Calculate percentage
        $percentage = $totalMarks > 0 ? ($obtainedMarks / $totalMarks) * 100 : 0;

        // Create or update result
        $result = Result::updateOrCreate(
            [
                'student_exam_id' => $studentExam->id,
                'student_id' => $studentExam->student_id,
                'exam_id' => $studentExam->exam_id
            ],
            [
                'total_marks' => $totalMarks,
                'obtained_marks' => max(0, $obtainedMarks),
                'percentage' => round($percentage, 2),
                'correct_answers' => $correctCount,
                'incorrect_answers' => $incorrectCount,
                'unanswered' => $unansweredCount
            ]
        );

        // Calculate rank if needed
        $this->calculateRank($result);

        return $result;
    }

    /**
     * Calculate rank for the result
     */
    protected function calculateRank(Result $result)
    {
        $betterResults = Result::where('exam_id', $result->exam_id)
            ->where('obtained_marks', '>', $result->obtained_marks)
            ->count();

        $rank = $betterResults + 1;

        $result->update(['rank' => $rank]);

        return $rank;
    }

    /**
     * Get performance analysis
     */
    public function getPerformanceAnalysis($studentId, $examId = null)
    {
        $query = Result::where('student_id', $studentId);

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        $results = $query->get();

        return [
            'total_exams' => $results->count(),
            'average_percentage' => $results->avg('percentage'),
            'best_performance' => $results->max('percentage'),
            'total_correct' => $results->sum('correct_answers'),
            'total_incorrect' => $results->sum('incorrect_answers'),
            'total_unanswered' => $results->sum('unanswered')
        ];
    }
}
