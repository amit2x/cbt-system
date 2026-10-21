<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Module;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of modules for a course.
     */
    public function index(Course $course)
    {
        $modules = $course->modules()
            ->orderBy('order')
            ->get();

        return view('admin.modules.index', compact('course', 'modules'));
    }

    /**
     * Show form for creating a new module.
     */
    public function create(Course $course)
    {
        return view('admin.modules.create', compact('course'));
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $module = $course->modules()->create([
            'title' => $request->title,
            'description' => $request->description,
            'order' => $course->modules()->max('order') + 1,
            'status' => $request->status ?? 'active',
        ]);

        if ($request->ajax()) {
            return $this->successResponse([
                'module' => $module,
            ], 'Module created successfully.');
        }

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Module created successfully.');
    }

    /**
     * Display the specified module.
     */
    public function show(Course $course, Module $module)
    {
        $module->load(['course']);
        $exams = Exam::whereIn('id', function ($query) use ($module) {
            $query->select('exam_questions.exam_id')
                ->from('exam_questions')
                ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
                ->where('questions.subject_id', $module->id);
        })
            ->withCount('examQuestions')
            ->get();

        return view('admin.modules.show', compact('course', 'module', 'exams'));
    }

    /**
     * Show form for editing the module.
     */
    public function edit(Course $course, Module $module)
    {
        return view('admin.modules.edit', compact('course', 'module'));
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, Course $course, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $module->update($request->only(['title', 'description', 'status']));

        if ($request->ajax()) {
            return $this->successResponse([
                'module' => $module,
            ], 'Module updated successfully.');
        }

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Course $course, Module $module)
    {
        // Check if module has any exams or questions
        if ($module->exams()->count() > 0) {
            if (request()->ajax()) {
                return $this->errorResponse('Cannot delete module with associated exams.', 422);
            }

            return back()->with('error', 'Cannot delete module with associated exams.');
        }

        $module->delete();

        if (request()->ajax()) {
            return $this->successResponse(null, 'Module deleted successfully.');
        }

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Module deleted successfully.');
    }

    /**
     * Update module order.
     */
    public function updateOrder(Request $request, Course $course)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer',
        ]);

        foreach ($request->modules as $moduleData) {
            Module::where('id', $moduleData['id'])
                ->where('course_id', $course->id)
                ->update(['order' => $moduleData['order']]);
        }

        if ($request->ajax()) {
            return $this->successResponse(null, 'Module order updated.');
        }

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Module order updated.');
    }

    /**
     * Toggle module status.
     */
    public function toggleStatus(Course $course, Module $module)
    {
        $module->update([
            'status' => $module->status === 'active' ? 'inactive' : 'active',
        ]);

        if (request()->ajax()) {
            return $this->successResponse([
                'status' => $module->status,
            ], 'Module status updated.');
        }

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'Module status updated.');
    }

    /**
     * Bulk update module status.
     */
    public function bulkStatus(Request $request, Course $course)
    {
        $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
            'status' => 'required|in:active,inactive',
        ]);

        $count = Module::whereIn('id', $request->module_ids)
            ->where('course_id', $course->id)
            ->update(['status' => $request->status]);

        return $this->successResponse([
            'updated' => $count,
        ], "{$count} modules updated successfully.");
    }

    /**
     * Bulk delete modules.
     */
    public function bulkDelete(Request $request, Course $course)
    {
        $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        $count = Module::whereIn('id', $request->module_ids)
            ->where('course_id', $course->id)
            ->whereDoesntHave('exams')
            ->delete();

        return $this->successResponse([
            'deleted' => $count,
        ], "{$count} modules deleted successfully.");
    }

    /**
     * Get modules for a course (API endpoint).
     */
    public function getModules(Course $course)
    {
        $modules = $course->modules()
            ->where('status', 'active')
            ->orderBy('order')
            ->get()
            ->map(function ($module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'description' => $module->description,
                    'order' => $module->order,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $modules,
        ]);
    }
}
