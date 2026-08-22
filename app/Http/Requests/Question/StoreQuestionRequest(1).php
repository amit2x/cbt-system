<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        'type' => ['required', Rule::in(['single_choice', 'multiple_choice', 'numerical'])],
        'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
        'marks' => 'required|integer|min:1|max:100',
        'negative_marks' => 'required|numeric|min:0|max:100',
        'subject_id' => 'nullable|exists:modules,id',
        'category_ids' => 'nullable|array',
        'category_ids.*' => 'exists:question_categories,id',
        'tags' => 'nullable|array',
        'status' => ['required', Rule::in(['active', 'inactive'])]
    ];

    // Only validate options for choice questions
    if (in_array($this->type, ['single_choice', 'multiple_choice'])) {
        $rules['options'] = 'required|array|min:2|max:10';
        $rules['options.*.option_text'] = 'required|string';
        // Make is_correct optional to avoid validation errors
        $rules['options.*.is_correct'] = 'nullable|boolean';
        $rules['options.*.order'] = 'nullable|integer';
    }

    if ($this->type === 'numerical') {
        $rules['correct_numerical'] = 'required|numeric';
        $rules['tolerance'] = 'nullable|numeric|min:0';
    }

    return $rules;
}
    /**
     * Get custom validation messages
     */
    public function messages()
    {
        return [
            'question_text.required' => 'Question text is required',
            'type.required' => 'Question type is required',
            'type.in' => 'Invalid question type selected',
            'difficulty.required' => 'Difficulty level is required',
            'difficulty.in' => 'Invalid difficulty level selected',
            'marks.required' => 'Marks are required',
            'marks.min' => 'Marks must be at least 1',
            'marks.max' => 'Marks cannot exceed 100',
            'negative_marks.required' => 'Negative marks are required',
            'negative_marks.min' => 'Negative marks cannot be negative',
            'negative_marks.max' => 'Negative marks cannot exceed marks',
            'options.required' => 'Options are required for choice questions',
            'options.min' => 'At least 2 options are required',
            'options.max' => 'Maximum 10 options allowed',
            'options.*.option_text.required' => 'Option text is required',
            'options.*.is_correct.required' => 'Please specify if option is correct',
            'correct_numerical.required' => 'Correct numerical answer is required',
            'status.in' => 'Invalid status selected'
        ];
    }

    /**
     * Get custom validation attributes
     */
    public function attributes()
    {
        return [
            'question_text' => 'question text',
            'subject_id' => 'subject/module',
            'category_ids' => 'categories',
            'correct_numerical' => 'correct numerical answer',
            'options.*.option_text' => 'option text',
            'options.*.is_correct' => 'correct answer flag'
        ];
    }

    /**
     * Prepare the data for validation
     */

    protected function prepareForValidation()
{
    // Convert tags string to array
    if ($this->has('tags') && is_string($this->tags)) {
        $this->merge([
            'tags' => array_filter(
                array_map('trim', explode(',', $this->tags))
            )
        ]);
    }

    // Convert JSON options if required
    if ($this->has('options') && is_string($this->options)) {
        $this->merge([
            'options' => json_decode($this->options, true)
        ]);
    }

    // Fix single choice correct answer
    if (
        $this->type === 'single_choice' &&
        $this->has('correct_option') &&
        is_array($this->options)
    ) {

        $options = $this->options;
        $correctIndex = (int) $this->correct_option;

        foreach ($options as $index => &$option) {
            $option['is_correct'] = ($index === $correctIndex) ? 1 : 0;
        }

        $this->merge([
            'options' => $options
        ]);
    }
}

    /**
     * Configure the validator instance.
     */
    
    public function withValidator($validator)
{
    $validator->after(function ($validator) {

        if ($this->type === 'single_choice') {

            if (!$this->filled('correct_option')) {
                $validator->errors()->add(
                    'correct_option',
                    'Please select one correct answer.'
                );
            }

            $correctCount = collect($this->options)
                ->where('is_correct', 1)
                ->count();

            if ($correctCount !== 1) {
                $validator->errors()->add(
                    'options',
                    'Single choice questions must have exactly one correct answer.'
                );
            }
        }

        if ($this->type === 'multiple_choice') {

            $correctCount = collect($this->options)
                ->filter(function ($option) {
                    return !empty($option['is_correct']);
                })
                ->count();

            if ($correctCount < 1) {
                $validator->errors()->add(
                    'options',
                    'Multiple choice questions must have at least one correct answer.'
                );
            }
        }

        if (
            $this->type === 'numerical' &&
            !is_numeric($this->correct_numerical)
        ) {
            $validator->errors()->add(
                'correct_numerical',
                'The correct numerical answer must be a number.'
            );
        }
    });
}

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log validation errors
        \Log::error('Question validation failed:', [
            'errors' => $validator->errors()->toArray(),
            'data' => $this->all()
        ]);
        
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     * This is a fallback for debugging.
     */
    public function getRules()
    {
        return $this->rules();
    }
}