<?php

namespace App\Http\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateBatchRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('edit batches');
    }

    public function rules()
    {
        $batchId = $this->route('batch')->id;
        
        return [
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'capacity' => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string|max:1000',
            'status' => [
                'required',
                'in:upcoming,ongoing,completed',
                // Custom validation rule to ensure status matches dates
                function ($attribute, $value, $fail) {
                    $startDate = Carbon::parse($this->start_date);
                    $endDate = Carbon::parse($this->end_date);
                    $today = Carbon::today();

                    if ($value === 'upcoming' && $startDate <= $today) {
                        $fail('An upcoming batch must have a future start date.');
                    }
                    
                    if ($value === 'ongoing' && ($startDate > $today || $endDate < $today)) {
                        $fail('An ongoing batch must have started and not yet ended.');
                    }
                    
                    if ($value === 'completed' && $endDate > $today) {
                        $fail('A completed batch must have an end date in the past.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Batch name is required.',
            'name.max' => 'Batch name cannot exceed 255 characters.',
            'course_id.required' => 'Please select a course.',
            'course_id.exists' => 'The selected course is invalid.',
            'instructor_id.required' => 'Please select an instructor.',
            'instructor_id.exists' => 'The selected instructor is invalid.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after' => 'End date must be after the start date.',
            'capacity.required' => 'Batch capacity is required.',
            'capacity.integer' => 'Capacity must be a number.',
            'capacity.min' => 'Capacity must be at least 1.',
            'capacity.max' => 'Capacity cannot exceed 1000.',
            'status.required' => 'Batch status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure capacity is an integer
        if ($this->has('capacity')) {
            $this->merge([
                'capacity' => (int) $this->capacity
            ]);
        }
    }
}