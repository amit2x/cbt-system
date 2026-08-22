{{-- resources/views/admin/reports/students.blade.php --}}

@extends('layouts.admin')

@section('title', 'Student Performance Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Student Performance Reports</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Students</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.students') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="batch_id" class="form-control">
                        <option value="">All Batches</option>
                        @foreach($batches ?? [] as $batch)
                            <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.reports.export-students') }}" class="btn btn-success w-100">
                        <i class="bi bi-download"></i> Export
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
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
                            <th>Batch</th>
                            <th>Exams Taken</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Accuracy</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            @php
                                $stats = $student->getExamStatistics();
                            @endphp
                            <tr>
                                <td>{{ $students->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $student) }}">
                                        <strong>{{ $student->name }}</strong>
                                    </a>
                                </td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->batches->first()->name ?? 'N/A' }}</td>
                                <td>{{ $stats['total_exams'] ?? 0 }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $stats['average_score'] ?? 0 }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ ($stats['pass_rate'] ?? 0) >= 60 ? 'success' : 'warning' }}">
                                        {{ $stats['pass_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $stats['accuracy'] ?? 0 }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $student->is_active ? 'success' : 'secondary' }}">
                                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $student) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.reports.export-student', $student) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection