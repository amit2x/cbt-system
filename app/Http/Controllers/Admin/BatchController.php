<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BatchStudentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Batch\StoreBatchRequest;
use App\Imports\StudentsImport;
use App\Models\Batch;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of batches.
     */
    public function index()
    {
        $batches = Batch::with(['course', 'instructor'])
                        ->withCount('students')
                        ->latest()
                        ->paginate(10);

        return view('admin.batches.index', compact('batches'));
    }

    /**
     * Show form for creating new batch.
     */
    public function create()
    {
        $courses = Course::where('status', 'active')->get();
        $instructors = User::role('instructor')->where('is_active', true)->get();

        return view('admin.batches.create', compact('courses', 'instructors'));
    }

    /**
     * Store a newly created batch.
     */
    public function store(StoreBatchRequest $request)
    {
        $batch = Batch::create($request->validated());

        return redirect()->route('admin.batches.index')
                        ->with('success', 'Batch created successfully.');
    }

    /**
     * Display the specified batch.
     */
    public function show(Batch $batch)
    {
        $batch->load(['course', 'instructor', 'students' => function($q) {
            $q->withPivot('status', 'enrolled_at');
        }]);

        return view('admin.batches.show', compact('batch'));
    }

    /**
     * Show form for editing batch.
     */
    public function edit(Batch $batch)
    {
        $courses = Course::where('status', 'active')->get();
        $instructors = User::role('instructor')->where('is_active', true)->get();

        return view('admin.batches.edit', compact('batch', 'courses', 'instructors'));
    }

    /**
     * Update the specified batch.
     */
    public function update(StoreBatchRequest $request, Batch $batch)
    {
        $batch->update($request->validated());

        return redirect()->route('admin.batches.index')
                        ->with('success', 'Batch updated successfully.');
    }

    /**
     * Remove the specified batch.
     */
    public function destroy(Batch $batch)
    {
        // Check if batch has students
        if ($batch->students()->count() > 0) {
            return back()->with('error', 'Cannot delete batch with enrolled students.');
        }

        $batch->delete();

        return redirect()->route('admin.batches.index')
                        ->with('success', 'Batch deleted successfully.');
    }

    /**
     * Show add students form.
     */
    public function addStudents(Batch $batch)
    {
        $students = User::role('student')
                        ->where('is_active', true)
                        ->whereDoesntHave('batches', function($q) use ($batch) {
                            $q->where('batch_id', $batch->id);
                        })
                        ->get();

        return view('admin.batches.add-students', compact('batch', 'students'));
    }

    /**
     * Store students in batch.
     */
    public function storeStudents(Request $request, Batch $batch)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        foreach ($request->student_ids as $studentId) {
            $batch->students()->attach($studentId, [
                'enrolled_at' => now(),
                'status' => 'active'
            ]);
        }

        return redirect()->route('admin.batches.show', $batch)
                        ->with('success', 'Students added successfully.');
    }

    /**
     * Remove student from batch.
     */
    public function removeStudent(Batch $batch, User $student)
    {
        $batch->students()->detach($student->id);

        return back()->with('success', 'Student removed from batch.');
    }

    /**
     * Update student status in batch.
     */
    public function updateStudentStatus(Request $request, Batch $batch, User $student)
    {
        $request->validate([
            'status' => 'required|in:active,completed,dropped'
        ]);

        $batch->students()->updateExistingPivot($student->id, [
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student status updated'
        ]);
    }

    /**
     * Import students via Excel.
     */
     /**
     * Import students via Excel.
     */
    public function importStudents(Request $request, Batch $batch)
    {
        $validator = validator($request->all(), [
        'file' => [
            'required',
            'file',
            function ($attribute, $value, $fail) {
                $extension = $value->getClientOriginalExtension();
                $mime = $value->getMimeType();

                // Allowed extensions
                $allowedExtensions = ['xlsx', 'xls', 'csv'];

                // Allowed MIME types (including common variations)
                $allowedMimes = [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv',
                    'text/plain',
                    'text/x-csv',
                    'application/csv',
                    'application/x-csv'
                ];

                if (!in_array(strtolower($extension), $allowedExtensions)) {
                    $fail('The file must be of type: xlsx, xls, or csv.');
                }

                if ($extension === 'csv' && !in_array($mime, $allowedMimes)) {
                    // For CSV files, be more permissive
                    return true;
                } elseif (!in_array($mime, $allowedMimes)) {
                    $fail('Invalid file format. Please upload a valid Excel or CSV file.');
                }
            },
        ],
        'file' => 'max:10240' // 10MB max
    ], [
        'file.required' => 'Please select a file to upload.',
        'file.max' => 'The file size must not exceed 10MB.'
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

        try {
            Log::info('Starting student import', [
                'batch_id' => $batch->id,
                'file' => $request->file('file')->getClientOriginalName()
            ]);

            // Create import instance
            $import = new StudentsImport($batch);

            // Perform import
            Excel::import($import, $request->file('file'));

            // Get import statistics
            $stats = $import->getStats();

            // Prepare success message with details
            $message = "Import completed: ";
            $message .= "{$stats['created']} new students created, ";
            $message .= "{$stats['enrolled']} enrolled in batch";

            if ($stats['existing'] > 0) {
                $message .= ", {$stats['existing']} already existed";
            }
            if ($stats['skipped'] > 0) {
                $message .= ", {$stats['skipped']} already enrolled in this batch";
            }
            if (!empty($stats['errors'])) {
                $message .= ", but encountered {$stats['errors']} errors";
                Log::warning('Import had errors', ['errors' => $stats['errors']]);
            }

            Log::info('Import completed', $stats);

            return redirect()->route('admin.batches.show', $batch)
                            ->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            Log::error('Import validation failed', ['errors' => $errorMessages]);

            return back()->with('error', 'Validation failed: ' . implode('; ', $errorMessages));

        } catch (\Exception $e) {
            Log::error('Import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error importing students: ' . $e->getMessage());
        }
    }


    /**
     * Export batch students to Excel.
     */
    public function exportStudents(Batch $batch)
    {
        return Excel::download(new BatchStudentsExport($batch), 'batch-' . $batch->id . '-students.xlsx');
    }

    /**
     * Get batch statistics.
     */
    public function statistics(Batch $batch)
    {
        $stats = [
            'total_students' => $batch->students()->count(),
            'active_students' => $batch->students()->wherePivot('status', 'active')->count(),
            'completed_students' => $batch->students()->wherePivot('status', 'completed')->count(),
            'dropped_students' => $batch->students()->wherePivot('status', 'dropped')->count(),
            'available_seats' => $batch->available_seats,
            'progress' => $batch->progress,
            'exams_conducted' => $batch->exams()->count(),
            'avg_score' => $batch->exams()
                                ->join('results', 'exams.id', '=', 'results.exam_id')
                                ->avg('results.percentage') ?? 0
        ];

        return response()->json($stats);
    }

    public function downloadImportTemplate()
{
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
    ];

    $columns = ['name', 'email', 'phone'];

    $callback = function() use ($columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
        fputcsv($file, ['Amit Kumar Singh', 'amitk2@cbt.com', '1234567890']);
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
