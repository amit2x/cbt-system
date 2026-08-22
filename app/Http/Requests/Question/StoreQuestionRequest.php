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
            'question_text' => 'required_without:question_image|string|nullable',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'question_image_alt' => 'nullable|string|max:255',
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
            $rules['options.*.option_text'] = 'required_without:options.*.option_image|string|nullable';
            $rules['options.*.option_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            $rules['options.*.option_image_alt'] = 'nullable|string|max:255';
            $rules['options.*.is_correct'] = 'nullable|boolean';
            $rules['options.*.order'] = 'nullable|integer';
            
            // Add validation for correct_options
            if ($this->type === 'multiple_choice') {
                $rules['correct_options'] = 'required|array|min:1';
                $rules['correct_options.*'] = 'integer|min:0';
            }
            
            if ($this->type === 'single_choice') {
                $rules['correct_option'] = 'required|integer|min:0';
            }
        }

        if ($this->type === 'numerical') {
            $rules['correct_numerical'] = 'required|numeric';
            $rules['tolerance'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'question_text.required_without' => 'Question text or image is required',
            'question_image.image' => 'Question image must be a valid image file',
            'question_image.mimes' => 'Question image must be a JPEG, PNG, JPG, GIF, or SVG file',
            'question_image.max' => 'Question image must not exceed 2MB',
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
            'options.*.option_text.required_without' => 'Option text or image is required for each option',
            'options.*.option_image.image' => 'Option image must be a valid image file',
            'options.*.option_image.mimes' => 'Option image must be a JPEG, PNG, JPG, GIF, or SVG file',
            'options.*.option_image.max' => 'Option image must not exceed 2MB',
            'correct_option.required' => 'Please select the correct answer',
            'correct_options.required' => 'Please select at least one correct answer',
            'correct_options.min' => 'Please select at least one correct answer',
            'correct_numerical.required' => 'Correct numerical answer is required',
            'status.in' => 'Invalid status selected'
        ];
    }

    public function attributes()
    {
        return [
            'question_text' => 'question text',
            'question_image' => 'question image',
            'question_image_alt' => 'question image alt text',
            'subject_id' => 'subject/module',
            'category_ids' => 'categories',
            'correct_numerical' => 'correct numerical answer',
            'options.*.option_text' => 'option text',
            'options.*.option_image' => 'option image',
            'options.*.option_image_alt' => 'option image alt text',
            'options.*.is_correct' => 'correct answer flag'
        ];
    }

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

        // Handle single choice - set is_correct based on selected radio
        if ($this->type === 'single_choice' && $this->has('correct_option') && is_array($this->options)) {
            $options = $this->options;
            $correctIndex = (int) $this->correct_option;

            foreach ($options as $index => &$option) {
                $option['is_correct'] = ($index === $correctIndex) ? 1 : 0;
            }

            $this->merge([
                'options' => $options
            ]);
        }

        // Handle multiple choice - ensure is_correct is properly set from checkboxes
        if ($this->type === 'multiple_choice' && $this->has('correct_options') && is_array($this->options)) {
            $options = $this->options;
            $correctIndices = $this->correct_options; // Array of selected indices

            // Reset all is_correct to 0 first
            foreach ($options as $index => &$option) {
                $option['is_correct'] = 0;
            }

            // Set is_correct to 1 for selected options
            if (is_array($correctIndices)) {
                foreach ($correctIndices as $index) {
                    if (isset($options[$index])) {
                        $options[$index]['is_correct'] = 1;
                    }
                }
            }

            $this->merge([
                'options' => $options
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check that either question text or image is provided
            if (empty($this->question_text) && empty($this->file('question_image'))) {
                $validator->errors()->add(
                    'question_text',
                    'Please provide either question text or an image.'
                );
            }

            if ($this->type === 'single_choice') {
                if (!$this->filled('correct_option')) {
                    $validator->errors()->add(
                        'correct_option',
                        'Please select one correct answer.'
                    );
                }

                if (is_array($this->options)) {
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
            }

            if ($this->type === 'multiple_choice') {
                if (is_array($this->options)) {
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

            // Check each option has either text or image
            if (in_array($this->type, ['single_choice', 'multiple_choice']) && is_array($this->options)) {
                foreach ($this->options as $index => $option) {
                    $hasText = !empty($option['option_text']);
                    $hasImage = isset($option['option_image']) && $option['option_image'] instanceof \Illuminate\Http\UploadedFile;
                    
                    if (!$hasText && !$hasImage) {
                        $validator->errors()->add(
                            "options.{$index}.option_text",
                            "Option " . ($index + 1) . " must have either text or an image."
                        );
                    }
                }
            }
        });
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::error('Question validation failed:', [
            'errors' => $validator->errors()->toArray(),
            'data' => $this->all()
        ]);
        
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    public function getRules()
    {
        return $this->rules();
    }
}