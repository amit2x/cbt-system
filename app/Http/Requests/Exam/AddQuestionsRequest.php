<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class AddQuestionsRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('edit exams');
    }

    public function rules()
    {
        return [
            'questions' => 'required|array',
            'questions.*.question_id' => 'required|exists:questions,id',
            'questions.*.section_id' => 'nullable|exists:exam_sections,id',
            'questions.*.marks' => 'nullable|integer|min:1',
            'questions.*.negative_marks' => 'nullable|numeric|min:0',
            'questions.*.time_limit_seconds' => 'nullable|integer|min:0'
        ];
    }
}
