<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    
    
    /**
     * Display a listing of courses.
     */
    public function index(Request $request)
    {
        $query = Course::with(['creator', 'instructors'])
            ->withCount(['modules', 'batches']);
    
        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%");
            });
        }
    
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $courses = $query->latest()->paginate(15);
    
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show form for creating new course.
     */
    public function create()
    {
        $instructors = User::role('instructor')->where('is_active', true)->get();
        return view('admin.courses.create', compact('instructors'));
    }

    /**
     * Store a newly created course.
     */
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('courses', 'public');
            $data['thumbnail'] = $path;
        }

        $data['created_by'] = auth()->id();

        $course = Course::create($data);

        // Assign instructors if any
        if ($request->has('instructors')) {
            $course->instructors()->sync($request->instructors);
        }

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $course->load(['modules', 'batches' => function($q) {
            $q->withCount('students');
        }, 'instructors']);

        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show form for editing course.
     */
    public function edit(Course $course)
    {
        $instructors = User::role('instructor')->where('is_active', true)->get();
        return view('admin.courses.edit', compact('course', 'instructors'));
    }

    /**
     * Update the specified course.
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $data = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $path = $request->file('thumbnail')->store('courses', 'public');
            $data['thumbnail'] = $path;
        }

        $course->update($data);

        // Update instructors
        if ($request->has('instructors')) {
            $course->instructors()->sync($request->instructors);
        }

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course)
    {
        // Check if course has batches
        if ($course->batches()->count() > 0) {
            return back()->with('error', 'Cannot delete course with existing batches.');
        }

        // Delete thumbnail
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course deleted successfully.');
    }

    /**
     * Store module for course.
     */
    public function storeModule(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $module = $course->modules()->create([
            'title' => $request->title,
            'description' => $request->description,
            'order' => $course->modules()->max('order') + 1
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Module added successfully',
                'module' => $module
            ]);
        }

        return redirect()->back()->with('success', 'Module added successfully.');
    }

    /**
     * Update module.
     */
    public function updateModule(Request $request, Course $course, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $module->update($request->only(['title', 'description']));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Module updated successfully.');
    }

    /**
     * Delete module.
     */
    public function deleteModule(Course $course, Module $module)
    {
        $module->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Module deleted successfully.');
    }

    /**
     * Update module order.
     */
    public function updateModuleOrder(Request $request, Course $course)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer'
        ]);

        foreach ($request->modules as $moduleData) {
            Module::where('id', $moduleData['id'])
                  ->where('course_id', $course->id)
                  ->update(['order' => $moduleData['order']]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Module order updated'
            ]);
        }

        return redirect()->back()->with('success', 'Module order updated.');
    }
    
    /**
     * Get batches for a course (API endpoint).
     */
    public function getBatchesApi(Course $course)
    {
        $batches = $course->batches()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($batch) {
                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'status' => $batch->status,
                    'students_count' => $batch->students()->count(),
                    'course_id' => $batch->course_id,
                ];
            });
    
        return response()->json([
            'status' => 'success',
            'data' => $batches
        ]);
    }
}
