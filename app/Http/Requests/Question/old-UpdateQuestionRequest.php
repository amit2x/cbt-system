<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('edit questions');
    }

    public function rules()
    {
        return [
            'question_text' => 'required|string',
            'explanation' => 'nullable|string',
            'type' => 'required|in:single_choice,multiple_choice,numerical',
            'difficulty' => 'required|in:easy,medium,hard',
            'marks' => 'required|integer|min:1|max:100',
            'negative_marks' => 'required|numeric|min:0|max:100',
            'subject_id' => 'nullable|exists:modules,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:question_categories,id',
            'tags' => 'nullable|array',
            'status' => 'required|in:active,inactive'
        ];
    }
}
