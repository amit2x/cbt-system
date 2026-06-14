<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('edit courses');
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255|unique:courses,title,' . $this->course->id,
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive'
        ];
    }
}
