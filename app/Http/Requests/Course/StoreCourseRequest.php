<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('create courses');
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255|unique:courses,title',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Course title is required',
            'title.unique' => 'This course title already exists',
            'thumbnail.image' => 'Thumbnail must be an image',
            'thumbnail.max' => 'Thumbnail size must not exceed 2MB'
        ];
    }
}
