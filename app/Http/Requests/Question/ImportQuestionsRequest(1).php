<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class ImportQuestionsRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('import questions');
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'type' => 'nullable|in:single_choice,multiple_choice,numerical'
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Please select a file to import',
            'file.mimes' => 'File must be of type: xlsx, xls, csv',
            'file.max' => 'File size must not exceed 10MB'
        ];
    }
}
