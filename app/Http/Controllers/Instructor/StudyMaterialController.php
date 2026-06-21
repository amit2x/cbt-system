<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Models\StudyMaterialCategory;
use Illuminate\Http\Request;

class StudyMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display a listing of study materials.
     */
    public function index(Request $request)
    {
        $instructor = auth()->user();
        
        // Get materials for instructor's courses
        $query = StudyMaterial::with(['course', 'module', 'category'])
            ->whereHas('course.instructors', function($q) use ($instructor) {
                $q->where('user_id', $instructor->id);
            })
            ->orWhereHas('course.batches', function($q) use ($instructor) {
                $q->where('instructor_id', $instructor->id);
            });

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->where('material_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $materials = $query->latest()->paginate(15);
        
        // Get categories and courses for filters
        $categories = StudyMaterialCategory::active()->get();
        $courses = $instructor->teachingCourses()->where('status', 'active')->get();

        return view('instructor.study-materials.index', compact('materials', 'categories', 'courses'));
    }

    /**
     * Display the specified study material.
     */
    public function show(StudyMaterial $studyMaterial)
    {
        // Check if instructor has access to this material
        $instructor = auth()->user();
        $hasAccess = $studyMaterial->course->instructors()->where('user_id', $instructor->id)->exists()
            || $studyMaterial->course->batches()->where('instructor_id', $instructor->id)->exists();

        if (!$hasAccess) {
            abort(403, 'You do not have access to this material.');
        }

        $studyMaterial->incrementViews();

        return view('instructor.study-materials.show', compact('studyMaterial'));
    }

    /**
     * Download study material file.
     */
    public function download(StudyMaterial $studyMaterial)
    {
        // Check access
        $instructor = auth()->user();
        $hasAccess = $studyMaterial->course->instructors()->where('user_id', $instructor->id)->exists()
            || $studyMaterial->course->batches()->where('instructor_id', $instructor->id)->exists();

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
    
    //  /**
    //  * View study material file (for PDF preview).
    //  */
    // public function view(StudyMaterial $studyMaterial)
    // {
    //     // Check access
    //     $student = auth()->user();
    //     $hasAccess = $student->batches()
    //         ->where('batches.course_id', $studyMaterial->course_id)
    //         ->wherePivot('status', 'active')
    //         ->exists();
    
    //     if (!$hasAccess) {
    //         abort(403);
    //     }
    
    //     if (!$studyMaterial->file_path || !\Storage::disk('public')->exists($studyMaterial->file_path)) {
    //         abort(404, 'File not found.');
    //     }
    
    //     // Get the file content
    //     $filePath = storage_path('app/public/' . $studyMaterial->file_path);
    //     $fileContent = file_get_contents($filePath);
    //     $mimeType = $studyMaterial->file_type ?? 'application/pdf';
    
    //     // Return the file with inline disposition
    //     return response($fileContent)
    //         ->header('Content-Type', $mimeType)
    //         ->header('Content-Disposition', 'inline; filename="' . $studyMaterial->file_name . '"')
    //         ->header('Cache-Control', 'public, max-age=3600');
    // }
    
    /**
 * View study material file (for PDF preview).
 */
    public function view(StudyMaterial $studyMaterial)
    {
        // Check access - Student
        $student = auth()->user();
        
        // Check if student has access via enrolled batches
        $hasStudentAccess = $student->batches()
            ->where('batches.course_id', $studyMaterial->course_id)
            ->wherePivot('status', 'active')
            ->exists();
    
        // Check if user is instructor and has access via teaching courses
        $hasInstructorAccess = false;
        if ($student->hasRole('instructor')) {
            $hasInstructorAccess = $studyMaterial->course->instructors()
                ->where('user_id', $student->id)
                ->exists();
        }
    
        // Check if user is admin (full access)
        $hasAdminAccess = $student->hasRole('admin');
    
        if (!$hasStudentAccess && !$hasInstructorAccess && !$hasAdminAccess) {
            abort(403, 'You do not have access to this material.');
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