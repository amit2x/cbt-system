<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return auth()->user()->can('update questions');
    // }

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

        if (in_array($this->type, ['single_choice', 'multiple_choice'])) {
            $rules['options'] = 'required|array|min:2|max:10';
            $rules['options.*.id'] = 'nullable|exists:question_options,id';
            $rules['options.*.option_text'] = 'nullable|string';
            $rules['options.*.option_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            $rules['options.*.option_image_alt'] = 'nullable|string|max:255';
            $rules['options.*.is_correct'] = 'nullable|boolean';
            $rules['options.*.order'] = 'nullable|integer';
            $rules['options.*.remove_image'] = 'nullable|boolean';
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
            'options.required' => 'At least 2 options are required',
            'options.min' => 'At least 2 options are required',
            'options.*.option_text.required_without' => 'Option text or image is required for each option',
            'options.*.option_image.image' => 'Option image must be a valid image file',
            'options.*.option_image.mimes' => 'Option image must be a JPEG, PNG, JPG, GIF, or SVG file',
            'options.*.option_image.max' => 'Option image must not exceed 2MB',
            'correct_numerical.required' => 'Correct numerical answer is required',
        ];
    }

    public function attributes()
    {
        return [
            'question_text' => 'question text',
            'question_image' => 'question image',
            'options.*.option_text' => 'option text',
            'options.*.option_image' => 'option image',
            'options.*.option_image_alt' => 'option image alt text',
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

        // Handle options for choice questions
        if (in_array($this->type, ['single_choice', 'multiple_choice']) && $this->has('options')) {
            $options = $this->options;
            $type = $this->type;

            // For single choice - use correct_option
            if ($type === 'single_choice' && $this->has('correct_option')) {
                $correctIndex = (int) $this->correct_option;
                foreach ($options as $index => &$option) {
                    $option['is_correct'] = ($index === $correctIndex) ? 1 : 0;
                }
            }

            // For multiple choice - use correct_options array
            if ($type === 'multiple_choice' && $this->has('correct_options')) {
                $correctIndices = $this->correct_options;
                
                // Ensure correct_indices is an array
                if (!is_array($correctIndices)) {
                    $correctIndices = [];
                }

                // Reset all to 0
                foreach ($options as $index => &$option) {
                    $option['is_correct'] = 0;
                }

                // Set correct ones to 1
                foreach ($correctIndices as $index) {
                    if (isset($options[$index])) {
                        $options[$index]['is_correct'] = 1;
                    }
                }
            }

            $this->merge(['options' => $options]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check question has text or image
            if (empty($this->question_text) && empty($this->file('question_image')) && empty($this->question_image)) {
                $validator->errors()->add(
                    'question_text',
                    'Please provide either question text or an image.'
                );
            }

            // Validate options for choice questions
            if (in_array($this->type, ['single_choice', 'multiple_choice']) && is_array($this->options)) {
                
                // Check each option has either text or image
                foreach ($this->options as $index => $option) {
                    $hasText = !empty($option['option_text']);
                    $hasImage = isset($option['option_image']) && $option['option_image'] instanceof \Illuminate\Http\UploadedFile;
                    $hasExistingImage = !empty($option['option_image_path']) || !empty($option['existing_image']);
                    $hasRemoveImage = isset($option['remove_image']) && $option['remove_image'] == 1;
                    
                    // If removing image, check if has text
                    if ($hasRemoveImage && !$hasText) {
                        $validator->errors()->add(
                            "options.{$index}.option_text",
                            "Option " . ($index + 1) . " must have text if you remove the image."
                        );
                    }
                    
                    // If no text and no image (existing or new)
                    if (!$hasText && !$hasImage && !$hasExistingImage && !$hasRemoveImage) {
                        $validator->errors()->add(
                            "options.{$index}.option_text",
                            "Option " . ($index + 1) . " must have either text or an image."
                        );
                    }
                }

                // Count correct answers
                $correctCount = 0;
                foreach ($this->options as $option) {
                    if (!empty($option['is_correct']) && $option['is_correct'] == 1) {
                        $correctCount++;
                    }
                }

                // Validate based on type
                if ($this->type === 'single_choice') {
                    if ($correctCount !== 1) {
                        $validator->errors()->add(
                            'correct_option',
                            'Single choice questions must have exactly one correct answer.'
                        );
                    }
                } elseif ($this->type === 'multiple_choice') {
                    if ($correctCount < 1) {
                        $validator->errors()->add(
                            'correct_options',
                            'Multiple choice questions must have at least one correct answer.'
                        );
                    }
                }
            }

            // Validate numerical answer
            if ($this->type === 'numerical') {
                if (!is_numeric($this->correct_numerical)) {
                    $validator->errors()->add(
                        'correct_numerical',
                        'The correct numerical answer must be a number.'
                    );
                }
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function getRules()
    {
        return $this->rules();
    }
}