<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Batch;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = User::currentAirport()->role('student')
            ->with(['batches.course'])
            ->withCount(['results', 'exams']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%")
                    ->orWhere('roll_no', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('batch_id')) {
            $query->whereHas('batches', function ($q) use ($request) {
                $q->where('batch_id', $request->batch_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $students = $query->latest()->paginate(15);

        // Calculate additional stats for each student
        foreach ($students as $student) {
            $student->average_score = round($student->results()->avg('percentage') ?? 0, 2);
            $student->exams_taken = $student->exams()->where('status', 'completed')->count();
        }

        $batches = Batch::where('status', 'ongoing')->get();

        return view('admin.students.index', compact('students', 'batches'));
    }

    /**
     * Show form for creating new student.
     */
    public function create()
    {
        $batches = Batch::where('status', 'ongoing')->get();

        return view('admin.students.create', compact('batches'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'roll_no' => 'nullable|string|max:50',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'roll_no' => $request->roll_no,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $student = User::create($data);
        $student->assignRole('student');

        if ($request->has('batch_ids')) {
            foreach ($request->batch_ids as $batchId) {
                $student->batches()->attach($batchId, [
                    'enrolled_at' => now(),
                    'status' => 'active',
                ]);
            }
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(User $student)
    {
        if (! $student->hasRole('student')) {
            abort(404);
        }

        $student->load(['batches.course', 'results.exam']);

        // Statistics
        $stats = [
            'total_exams' => $student->exams()->count(),
            'completed_exams' => $student->exams()->where('status', 'completed')->count(),
            'average_score' => round($student->results()->avg('percentage') ?? 0, 2),
            'highest_score' => round($student->results()->max('percentage') ?? 0, 2),
            'total_correct' => $student->results()->sum('correct_answers'),
            'total_incorrect' => $student->results()->sum('incorrect_answers'),
            'total_time_spent' => round($student->results()->sum('time_taken_seconds') / 3600, 2),
        ];

        // Subject-wise performance
        $subjectPerformance = [];
        $results = $student->results()->with('exam.course')->get();
        $subjectScores = [];

        foreach ($results as $result) {
            $courseName = $result->exam->course->title ?? 'General';
            if (! isset($subjectScores[$courseName])) {
                $subjectScores[$courseName] = ['total' => 0, 'count' => 0];
            }
            $subjectScores[$courseName]['total'] += $result->percentage;
            $subjectScores[$courseName]['count']++;
        }

        foreach ($subjectScores as $name => $data) {
            $subjectPerformance[] = [
                'name' => $name,
                'score' => round($data['total'] / $data['count'], 2),
            ];
        }

        // Performance trend
        $trendData = $student->results()
            ->with('exam')
            ->orderBy('created_at')
            ->take(10)
            ->get();

        $trendLabels = $trendData->map(function ($item) {
            return $item->exam->title.' ('.$item->created_at->format('d M').')';
        })->toArray();

        $trendScores = $trendData->pluck('percentage')->toArray();

        return view('admin.students.show', compact('student', 'stats', 'subjectPerformance', 'trendLabels', 'trendScores'));
    }

    /**
     * Show form for editing student.
     */
    public function edit(User $student)
    {
        if (! $student->hasRole('student')) {
            abort(404);
        }

        $batches = Batch::where('status', 'ongoing')->get();
        $studentBatches = $student->batches()->pluck('batches.id')->toArray();

        return view('admin.students.edit', compact('student', 'batches', 'studentBatches'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, User $student)
    {
        if (! $student->hasRole('student')) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'roll_no' => 'nullable|string|max:50',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'roll_no' => $request->roll_no,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('avatar')) {
            if ($student->avatar) {
                Storage::disk('public')->delete($student->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $student->update($data);

        // Update batch assignments
        $batchData = [];
        if ($request->has('batch_ids')) {
            foreach ($request->batch_ids as $batchId) {
                $batchData[$batchId] = ['enrolled_at' => now(), 'status' => 'active'];
            }
        }
        $student->batches()->sync($batchData);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(User $student)
    {
        if (! $student->hasRole('student')) {
            return back()->with('error', 'Invalid student.');
        }

        // Check if student has exam attempts
        if ($student->exams()->count() > 0) {
            return back()->with('error', 'Cannot delete student with exam attempts.');
        }

        if ($student->avatar) {
            Storage::disk('public')->delete($student->avatar);
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Bulk delete students.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $students = User::whereIn('id', $request->student_ids)
            ->role('student')
            ->doesntHave('exams')
            ->get();

        foreach ($students as $student) {
            if ($student->avatar) {
                Storage::disk('public')->delete($student->avatar);
            }
            $student->delete();
        }

        return $this->successResponse(null, 'Selected students deleted successfully.');
    }

    /**
     * Bulk activate/deactivate students.
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'status' => 'required|in:active,inactive',
        ]);

        User::whereIn('id', $request->student_ids)
            ->role('student')
            ->update(['is_active' => $request->status === 'active']);

        return $this->successResponse(null, 'Student status updated successfully.');
    }

    /**
     * Import students from Excel.
     */
    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    //         'batch_id' => 'required|exists:batches,id',
    //     ]);

    //     try {
    //         Excel::import(new StudentsImport($request->batch_id), $request->file('file'));

    //         return redirect()->route('admin.students.index')
    //             ->with('success', 'Students imported successfully.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Error importing students: '.$e->getMessage());
    //     }
    // }
    /**
     * Show import form.
     */
    public function import()
    {
        $batches = Batch::where('status', 'ongoing')->get();

        return view('admin.students.import', compact('batches'));
    }

    /**
     * Import students from Excel.
     */
    // public function importStudents(Request $request)
    // {
    //     // dd($request->all());
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    //         'batch_id' => 'required|exists:batches,id',
    //     ]);

    //     try {
    //         Excel::import(new StudentsImport($request->batch_id), $request->file('file'));

    //         return redirect()->route('admin.students.index')
    //             ->with('success', 'Students imported successfully.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Error importing students: '.$e->getMessage());
    //     }
    // }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'batch_id' => 'required|exists:batches,id',
        ]);

        try {

            $batch = Batch::findOrFail($request->batch_id);

            Excel::import(
                new StudentsImport($batch),
                $request->file('file')
            );

            return redirect()->route('admin.students.index')
                ->with('success', 'Students imported successfully.');

        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Error importing students: '.$e->getMessage()
            );
        }
    }

    /**
     * Export students to Excel.
     */
    // public function export(Request $request)
    // {
    //     return Excel::download(new StudentsExport($request->filters ?? []), 'students_'.date('Y-m-d').'.xlsx');
    // }

    /**
     * Export students to Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['batch_id', 'status']);

        return Excel::download(new StudentsExport($filters), 'students_'.date('Y-m-d').'.xlsx');
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        $columns = ['name', 'email', 'phone', 'organization_name', 'address'];
        $filename = 'student_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Amit Kumar', 'amitk2@aai.aero', '7633947548', 'Airports Authority of India', 'NSCBI Airport Kolkata']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Toggle student status.
     */
    public function toggleStatus(Request $request, User $student)
    {
        if (! $student->hasRole('student')) {
            return $this->errorResponse('Invalid student.', 404);
        }

        $student->update(['is_active' => ! $student->is_active]);

        return $this->successResponse([
            'is_active' => $student->is_active,
        ], 'Student status updated successfully.');
    }

    /**
     * Reset student password.
     */
    public function resetPassword(Request $request, User $student)
    {
        if (! $student->hasRole('student')) {
            return $this->errorResponse('Invalid student.', 404);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student->update(['password' => Hash::make($request->password)]);

        return $this->successResponse(null, 'Password reset successfully.');
    }
}
