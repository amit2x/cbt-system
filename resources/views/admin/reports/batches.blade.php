{{-- resources/views/admin/reports/batches.blade.php --}}

@extends('layouts.admin')

@section('title', 'Batch Performance Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Batch Performance Reports</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Batches</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.batches') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="course_id" class="form-control">
                        <option value="">All Courses</option>
                        @foreach($courses ?? [] as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Cards -->
    <div class="row">
        @forelse($batches as $batch)
            @php
                $stats = $batch->getPerformanceStats();
            @endphp
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $batch->name }}</h6>
                        <span class="badge bg-{{ $batch->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($batch->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="text-muted">Course</small>
                                <p class="mb-0 fw-bold">{{ $batch->course->title ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Instructor</small>
                                <p class="mb-0 fw-bold">{{ $batch->instructor->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Students</small>
                                <p class="mb-0 fw-bold">{{ $stats['total_students'] ?? 0 }}</p>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Active Students</small>
                                <p class="mb-0 fw-bold">{{ $stats['active_students'] ?? 0 }}</p>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Avg Score</small>
                                <p class="mb-0">
                                    <span class="badge bg-primary">{{ $stats['average_score'] ?? 0 }}%</span>
                                </p>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">Pass Rate</small>
                                <p class="mb-0">
                                    <span class="badge bg-{{ ($stats['pass_rate'] ?? 0) >= 60 ? 'success' : 'warning' }}">
                                        {{ $stats['pass_rate'] ?? 0 }}%
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <small>Participation Rate</small>
                                <small>{{ $stats['participation_rate'] ?? 0 }}%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $stats['participation_rate'] ?? 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('admin.reports.batch-detail', $batch) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.reports.export-batch', $batch) }}" 
                           class="btn btn-sm btn-success">
                            <i class="bi bi-download"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-4">
                    <i class="bi bi-grid-3x3-gap-fill fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No batches found.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{ $batches->links('pagination::bootstrap-5') }}
</div>
@endsection