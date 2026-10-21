<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\StudyMaterial;
use App\Models\StudyMaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudyMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of study materials.
     */
    public function index(Request $request)
    {
        // $query = StudyMaterial::with(['course', 'module', 'category', 'creator'])
        //     ->withCount('views');

        //     $query = StudyMaterial::with(['course', 'module', 'category', 'creator'])
        // ->withCount('viewLogs');
        $query = StudyMaterial::with(['course', 'module', 'category', 'creator'])
            ->select('*', 'views as views_count');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('description', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('material_type')) {
            $query->where('material_type', $request->material_type);
        }

        $materials = $query->latest()->paginate(15);
        $courses = Course::where('status', 'active')->get();
        $categories = StudyMaterialCategory::active()->get();
        $materialTypes = ['pdf', 'document', 'video', 'link', 'content'];

        return view('admin.study-materials.index', compact('materials', 'courses', 'categories', 'materialTypes'));
    }

    /**
     * Show form for creating new study material.
     */
    public function create()
    {
        $courses = Course::where('status', 'active')->get();
        $categories = StudyMaterialCategory::active()->get();
        $materialTypes = ['pdf', 'document', 'video', 'link', 'content'];

        return view('admin.study-materials.create', compact('courses', 'categories', 'materialTypes'));
    }

    /**
     * Store a newly created study material.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'nullable|exists:modules,id',
            'category_id' => 'nullable|exists:study_material_categories,id',
            'material_type' => 'required|in:pdf,document,video,link,content',
            'external_url' => 'required_if:material_type,link|nullable|url',
            'file' => 'required_if:material_type,pdf,document,video|nullable|file|max:20480',
            'status' => 'required|in:draft,published,archived',
        ]);

        $data = $request->except(['file', 'metadata']);
        $data['created_by'] = auth()->id();

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('study-materials', 'public');

            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        // Generate slug
        $data['slug'] = Str::slug($request->title);

        $material = StudyMaterial::create($data);

        return redirect()->route('admin.study-materials.index')
            ->with('success', 'Study material created successfully.');
    }

    /**
     * Display the specified study material.
     */
    public function show(StudyMaterial $studyMaterial)
    {
        $studyMaterial->load(['course', 'module', 'category', 'creator']);

        return view('admin.study-materials.show', compact('studyMaterial'));
    }

    /**
     * Show form for editing study material.
     */
    public function edit(StudyMaterial $studyMaterial)
    {
        $courses = Course::where('status', 'active')->get();
        $categories = StudyMaterialCategory::active()->get();
        $materialTypes = ['pdf', 'document', 'video', 'link', 'content'];
        $modules = Module::where('course_id', $studyMaterial->course_id)->get();

        return view('admin.study-materials.edit', compact('studyMaterial', 'courses', 'categories', 'materialTypes', 'modules'));
    }

    /**
     * Update the specified study material.
     */
    public function update(Request $request, StudyMaterial $studyMaterial)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'nullable|exists:modules,id',
            'category_id' => 'nullable|exists:study_material_categories,id',
            'material_type' => 'required|in:pdf,document,video,link,content',
            'external_url' => 'required_if:material_type,link|nullable|url',
            'file' => 'nullable|file|max:20480',
            'status' => 'required|in:draft,published,archived',
        ]);

        $data = $request->except(['file', 'metadata']);

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($studyMaterial->file_path) {
                Storage::disk('public')->delete($studyMaterial->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('study-materials', 'public');

            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        $studyMaterial->update($data);

        return redirect()->route('admin.study-materials.index')
            ->with('success', 'Study material updated successfully.');
    }

    /**
     * Remove the specified study material.
     */
    public function destroy(StudyMaterial $studyMaterial)
    {
        // Delete file if exists
        if ($studyMaterial->file_path) {
            Storage::disk('public')->delete($studyMaterial->file_path);
        }

        $studyMaterial->delete();

        return redirect()->route('admin.study-materials.index')
            ->with('success', 'Study material deleted successfully.');
    }

    /**
     * Download study material file.
     */
    public function download(StudyMaterial $studyMaterial)
    {
        if (! $studyMaterial->file_path || ! Storage::disk('public')->exists($studyMaterial->file_path)) {
            return back()->with('error', 'File not found.');
        }

        $studyMaterial->incrementDownloads();

        return Storage::disk('public')->download(
            $studyMaterial->file_path,
            $studyMaterial->file_name ?? 'download'
        );
    }

    /**
     * Toggle material status.
     */
    public function toggleStatus(StudyMaterial $studyMaterial)
    {
        $status = $studyMaterial->status === 'published' ? 'draft' : 'published';
        $studyMaterial->update(['status' => $status]);

        return redirect()->route('admin.study-materials.index')
            ->with('success', 'Material status updated successfully.');
    }

    /**
     * Get modules by course (AJAX).
     */
    public function getModules(Request $request)
    {
        $modules = Module::where('course_id', $request->course_id)
            ->where('status', 'active')
            ->orderBy('order')
            ->get();

        return response()->json($modules);
    }
}
