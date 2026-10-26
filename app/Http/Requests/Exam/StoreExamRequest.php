<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('create exams');
    }

    // public function rules()
    // {
    //     return [
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'type' => 'required|in:mock_test,full_exam,quiz,practice',
    //         'category' => 'required|in:daily,weekly,monthly,custom',
    //         'course_id' => 'required|exists:courses,id',
    //         'module_id' => 'required|exists:modules,id',
    //         'batch_id' => 'required|exists:batches,id',
    //         'duration_minutes' => 'required|integer|min:1|max:1440',
    //         'passing_percentage' => 'required|numeric|min:0|max:100',
    //         'negative_marking' => 'required|numeric|min:0',
    //         'randomize_questions' => 'boolean',
    //         'randomize_options' => 'boolean',
    //         'show_result_immediately' => 'boolean',
    //         'allow_review' => 'boolean',
    //         'attempts_allowed' => 'required|integer|min:1|max:10',
    //         'is_scheduled' => 'boolean',
    //         'start_time' => 'required_if:is_scheduled,true|nullable|date|after:now',
    //         'end_time' => 'required_if:is_scheduled,true|nullable|date|after:start_time',
    //         'status' => 'required|in:draft,published',
    //         'instructions' => 'nullable|string',
    //         'batch_ids' => 'nullable|array',
    //         'batch_ids.*' => 'exists:batches,id'
    //     ];
    // }
    
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'type' => 'required|in:mock_test,full_exam,quiz,practice',
            'category' => 'required|in:daily,weekly,monthly,custom',
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'batch_id' => 'required|exists:batches,id',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'passing_percentage' => 'required|numeric|min:0|max:100',
            'negative_marking' => 'required|numeric|min:0',
            'attempts_allowed' => 'required|integer|min:1|max:10',
            
            // ✅ Make sure these are boolean
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_result_immediately' => 'boolean',
            'allow_review' => 'boolean',
            'is_scheduled' => 'boolean',
            
            'start_time' => 'required_if:is_scheduled,true|nullable|date|after:now',
            'end_time' => 'required_if:is_scheduled,true|nullable|date|after:start_time',
            'status' => 'required|in:draft,published',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Exam title is required',
            'course_id.required' => 'Please select a course',
            'course_id.exists' => 'Selected course does not exist',
            'module_id.required' => 'Please select a module',
            'module_id.exists' => 'Selected module does not exist',
            'duration_minutes.required' => 'Exam duration is required',
            'duration_minutes.min' => 'Duration must be at least 1 minute',
            'duration_minutes.max' => 'Duration cannot exceed 24 hours',
            'passing_percentage.required' => 'Passing percentage is required',
            'attempts_allowed.required' => 'Number of allowed attempts is required',
            'start_time.required_if' => 'Start time is required for scheduled exams',
            'end_time.required_if' => 'End time is required for scheduled exams',
            'end_time.after' => 'End time must be after start time'
        ];
    }
}