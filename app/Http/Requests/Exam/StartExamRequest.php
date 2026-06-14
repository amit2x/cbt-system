<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StartExamRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('student');
    }

    public function rules()
    {
        return [
            'exam_id' => 'required|exists:exams,id'
        ];
    }
}
