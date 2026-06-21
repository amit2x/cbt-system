<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Models\StudyMaterialCategory;
use App\Models\Course;
use Illuminate\Http\Request;

class StudyMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    /**
     * Display a listing of study materials for the student.
     */
    public function index(Request $request)
    {
        $student = auth()->user();

        // Get materials for student's enrolled courses
        $query = StudyMaterial::with(['course', 'module', 'category'])
            ->published()
            ->forStudent($student->id);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('type')) {
            $query->where('material_type', $request->type);
        }

        $materials = $query->latest()->paginate(20);
        
        // Get categories for filter
        $categories = StudyMaterialCategory::active()->get();
        
        // Get enrolled courses
        $batchIds = $student->batches()
            ->wherePivot('status', 'active')
            ->pluck('batches.id');
            
        $courses = Course::whereIn('id', function($q) use ($batchIds) {
            $q->select('course_id')
              ->from('batches')
              ->whereIn('id', $batchIds);
        })->get();

        return view('student.study-materials.index', compact('materials', 'categories', 'courses'));
    }

    /**
     * Display the specified study material.
     */
    public function show(StudyMaterial $studyMaterial)
    {
        // Check if student has access to this material
        $student = auth()->user();
        $hasAccess = $student->batches()
            ->where('batches.course_id', $studyMaterial->course_id)
            ->wherePivot('status', 'active')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'You do not have access to this material.');
        }

        // Increment views
        $studyMaterial->incrementViews();

        // Get related materials (same course)
        $relatedMaterials = StudyMaterial::published()
            ->where('course_id', $studyMaterial->course_id)
            ->where('id', '!=', $studyMaterial->id)
            ->forStudent($student->id)
            ->take(3)
            ->get();

        return view('student.study-materials.show', compact('studyMaterial', 'relatedMaterials'));
    }

    /**
     * Download study material file.
     */
    public function download(StudyMaterial $studyMaterial)
    {
        // Check access
        $student = auth()->user();
        $hasAccess = $student->batches()
            ->where('batches.course_id', $studyMaterial->course_id)
            ->wherePivot('status', 'active')
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        if (!$studyMaterial->file_path || !\Storage::disk('public')->exists($studyMaterial->file_path)) {
            return back()->with('error', 'File not found.');
        }

        $studyMaterial->incrementDownloads();

        return \Storage::disk('public')->download(
            $studyMaterial->file_path,
            $studyMaterial->file_name ?? 'download'
        );
    }

    /**
     * Get materials by category (AJAX).
     */
    public function getByCategory($categoryId)
    {
        $student = auth()->user();
        
        $materials = StudyMaterial::with(['course', 'module'])
            ->published()
            ->where('category_id', $categoryId)
            ->forStudent($student->id)
            ->get();

        return response()->json($materials);
    }

    /**
     * Get materials by course (AJAX).
     */
    public function getByCourse($courseId)
    {
        $student = auth()->user();
        
        $materials = StudyMaterial::with(['course', 'module', 'category'])
            ->published()
            ->where('course_id', $courseId)
            ->forStudent($student->id)
            ->get();

        return response()->json($materials);
    }
    
    /**
     * View study material file (for PDF preview).
     */
    public function view(StudyMaterial $studyMaterial)
    {
        // Check access
        $student = auth()->user();
        $hasAccess = $student->batches()
            ->where('batches.course_id', $studyMaterial->course_id)
            ->wherePivot('status', 'active')
            ->exists();
    
        if (!$hasAccess) {
            abort(403);
        }
    
        if (!$studyMaterial->file_path || !\Storage::disk('public')->exists($studyMaterial->file_path)) {
            abort(404, 'File not found.');
        }
    
        // Get the file content
        $filePath = storage_path('app/public/' . $studyMaterial->file_path);
        $fileContent = file_get_contents($filePath);
        $mimeType = $studyMaterial->file_type ?? 'application/pdf';
    
        // Return the file with inline disposition
        return response($fileContent)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $studyMaterial->file_name . '"')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}