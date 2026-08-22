<?php
// app/Http/Requests/Question/ImportQuestionsRequest.php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportQuestionsRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('import questions');
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'type' => 'nullable|in:single_choice,multiple_choice,numerical',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'The file must be an Excel file (xlsx, xls) or CSV.',
            'file.max' => 'The file size must not exceed 5MB.',
            'type.in' => 'The type must be single_choice, multiple_choice, or numerical.',
            'images.*.mimes' => 'Images must be JPG, PNG, GIF, WebP, or SVG format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ];
    }
}