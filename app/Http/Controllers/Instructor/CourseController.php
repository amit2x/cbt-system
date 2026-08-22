<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Batch;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display instructor's courses
     */
    public function index()
    {
        $instructor = auth()->user();

        $courses = $instructor->teachingCourses()
            ->with(['modules', 'batches' => function($q) use ($instructor) {
                $q->where('instructor_id', $instructor->id);
            }])
            ->withCount(['modules', 'batches'])
            ->paginate(10);

        return view('instructor.courses.index', compact('courses'));
    }

    /**
     * Show course details
     */
    public function show(Course $course)
    {
        // Check if instructor teaches this course
        if (!$course->instructors()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You are not authorized to view this course');
        }

        $course->load(['modules', 'batches' => function($q) {
            $q->where('instructor_id', auth()->id())
              ->withCount('students');
        }]);

        // Get recent exams from this course
        $recentExams = $course->exams()
            ->where('status', 'published')
            ->withCount('questions')
            ->latest()
            ->take(5)
            ->get();

        return view('instructor.courses.show', compact('course', 'recentExams'));
    }

    /**
     * Get course modules (AJAX)
     */
    public function getModules(Course $course)
    {
        // if (!$course->instructors()->where('user_id', auth()->id())->exists()) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $modules = $course->modules()->where('status', 'active')->get();

        return response()->json($modules);
    }
}
