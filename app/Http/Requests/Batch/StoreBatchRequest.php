<?php

namespace App\Http\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('create batches');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:upcoming,ongoing,completed'
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'End date must be after or same to start date',
            'end_date.after' => 'End date must be after or same to start date',
            'capacity.min' => 'Capacity must be at least 1',
            'capacity.max' => 'Capacity cannot exceed 1000'
        ];
    }
}
