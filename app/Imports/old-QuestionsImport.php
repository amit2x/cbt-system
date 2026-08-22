<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Validator;

class QuestionsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $type;

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function model(array $row)
    {
        $question = Question::create([
            'question_text' => $row['question_text'],
            'type' => $this->type ?? $row['type'] ?? 'single_choice',
            'difficulty' => $row['difficulty'] ?? 'medium',
            'marks' => $row['marks'] ?? 1,
            'negative_marks' => $row['negative_marks'] ?? 0,
            'explanation' => $row['explanation'] ?? null,
            'subject_id' => $row['subject_id'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'active'
        ]);

        // Create options for choice questions
        if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($row["option_{$i}"])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $row["option_{$i}"],
                        'is_correct' => ($row["option_{$i}_correct"] ?? 0) == 1,
                        'order' => $i - 1
                    ]);
                }
            }
        }

        return $question;
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string',
            'type' => 'nullable|in:single_choice,multiple_choice,numerical',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'marks' => 'nullable|numeric|min:0',
            'negative_marks' => 'nullable|numeric|min:0',
        ];
    }
}
