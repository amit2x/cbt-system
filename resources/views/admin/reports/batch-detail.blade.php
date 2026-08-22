{{-- resources/views/admin/reports/batch-detail.blade.php --}}

@extends('layouts.admin')

@section('title', 'Batch Performance Detail - ' . $batch->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Batch Performance: {{ $batch->name }}</h1>
        <div>
            <a href="{{ route('admin.reports.export-batch', $batch) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Report
            </a>
            <a href="{{ route('admin.reports.batches') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Batches Report
            </a>
        </div>
    </div>

    <!-- Batch Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Name:</strong></td>
                            <td>{{ $batch->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Course:</strong></td>
                            <td>{{ $batch->course->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Instructor:</strong></td>
                            <td>{{ $batch->instructor->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Duration:</strong></td>
                            <td>{{ $batch->start_date->format('d M Y') }} - {{ $batch->end_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $batch->status == 'active' ? 'success' : ($batch->status == 'completed' ? 'info' : 'warning') }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total Students:</strong></td>
                            <td>{{ $performance['total_students'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $performance['average_score'] ?? 0 }}%</h3>
                                    <p class="mb-0">Average Score</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $performance['pass_percentage'] ?? 0 }}%</h3>
                                    <p class="mb-0">Pass Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $performance['average_accuracy'] ?? 0 }}%</h3>
                                    <p class="mb-0">Avg Accuracy</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $performance['average_speed'] ?? 0 }}</h3>
                                    <p class="mb-0">Avg Speed (Q/min)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Performance -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Exam-wise Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Participants</th>
                            <th>Passed</th>
                            <th>Failed</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($examPerformance ?? [] as $exam)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.reports.exam-detail', $exam['exam_id']) }}">
                                        <strong>{{ $exam['exam_title'] }}</strong>
                                    </a>
                                </td>
                                <td>{{ $exam['total_students'] }}</td>
                                <td class="text-success">{{ $exam['passed'] }}</td>
                                <td class="text-danger">{{ $exam['failed'] }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $exam['average_score'] }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $exam['pass_rate'] >= 60 ? 'success' : 'warning' }}">
                                        {{ $exam['pass_rate'] }}%
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reports.exam-detail', $exam['exam_id']) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No exams found for this batch.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Performance -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Student Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Exams Taken</th>
                            <th>Avg Score</th>
                            <th>Best Score</th>
                            <th>Pass Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students ?? [] as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $student['student_id']) }}">
                                        <strong>{{ $student['student_name'] }}</strong>
                                    </a>
                                </td>
                                <td>{{ $student['email'] ?? 'N/A' }}</td>
                                <td>{{ $student['exams_taken'] ?? 0 }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $student['average_score'] ?? 0 }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $student['best_score'] ?? 0 }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ ($student['pass_rate'] ?? 0) >= 60 ? 'success' : 'warning' }}">
                                        {{ $student['pass_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ ($student['status'] ?? 'active') == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($student['status'] ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $student['student_id']) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No students found in this batch.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection