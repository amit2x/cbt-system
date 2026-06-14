<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('create questions');
    }

    public function rules()
    {
        $rules = [
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

        // Add validation based on question type
        if (in_array($this->type, ['single_choice', 'multiple_choice'])) {
            $rules['options'] = 'required|array|min:2|max:10';
            $rules['options.*.option_text'] = 'required|string';
            $rules['options.*.is_correct'] = 'required|boolean';
            $rules['options.*.order'] = 'integer';

            // For single choice, exactly one correct answer
            if ($this->type === 'single_choice') {
                $rules['options'] = $rules['options'] . '|' . $this->validateSingleChoice();
            }

            // For multiple choice, at least one correct answer
            if ($this->type === 'multiple_choice') {
                $rules['options'] = $rules['options'] . '|' . $this->validateMultipleChoice();
            }
        }

        if ($this->type === 'numerical') {
            $rules['correct_numerical'] = 'required|numeric';
            $rules['tolerance'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    private function validateSingleChoice()
    {
        return function ($attribute, $value, $fail) {
            $correctCount = collect($value)->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                $fail('Single choice questions must have exactly one correct answer.');
            }
        };
    }

    private function validateMultipleChoice()
    {
        return function ($attribute, $value, $fail) {
            $correctCount = collect($value)->where('is_correct', true)->count();
            if ($correctCount < 1) {
                $fail('Multiple choice questions must have at least one correct answer.');
            }
        };
    }

    public function messages()
    {
        return [
            'question_text.required' => 'Question text is required',
            'type.required' => 'Question type is required',
            'type.in' => 'Invalid question type selected',
            'difficulty.required' => 'Difficulty level is required',
            'marks.required' => 'Marks are required',
            'marks.min' => 'Marks must be at least 1',
            'marks.max' => 'Marks cannot exceed 100',
            'negative_marks.max' => 'Negative marks cannot exceed marks',
            'options.required' => 'Options are required for choice questions',
            'options.min' => 'At least 2 options are required',
            'options.max' => 'Maximum 10 options allowed',
            'options.*.option_text.required' => 'Option text is required',
            'options.*.is_correct.required' => 'Please specify if option is correct'
        ];
    }
}
