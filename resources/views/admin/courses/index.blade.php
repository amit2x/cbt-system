@extends('layouts.admin')

@section('title', 'Courses Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Courses</h1>
        <div>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Course
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <!--<div class="row mb-4">-->
    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-primary shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">-->
    <!--                            Total Courses</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $courses->total() }}</div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-book fs-2 text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-success shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">-->
    <!--                            Active Courses</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $courses->where('status', 'active')->count() }}-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-check-circle fs-2 text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-info shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">-->
    <!--                            Total Modules</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $courses->sum('modules_count') }}-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-grid-3x3-gap-fill fs-2 text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-warning shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">-->
    <!--                            Total Batches</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $courses->sum('batches_count') }}-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-people fs-2 text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
    <div class="row mb-4">
    <!-- Total Courses -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Courses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $courses->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-book fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Courses -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Active Courses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $courses->where('status', 'active')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Modules -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Modules</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $courses->sum('modules_count') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-grid-3x3-gap-fill fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Batches -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Batches</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $courses->sum('batches_count') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search courses..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Modules</th>
                            <th>Batches</th>
                            <th>Instructors</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>
                                @if($course->thumbnail)
                                    <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                         alt="{{ $course->title }}"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded"
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.courses.show', $course) }}" class="text-decoration-none fw-bold">
                                    {{ $course->title }}
                                </a>
                                <br>
                                <small class="text-muted">{{ Str::limit($course->description, 50) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $course->modules_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $course->batches_count }}</span>
                            </td>
                            <td>
                                @foreach($course->instructors as $instructor)
                                    <span class="badge bg-secondary">{{ $instructor->name }}</span>
                                @endforeach
                                @if($course->instructors->count() == 0)
                                    <span class="text-muted small">No instructors</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $progress = 0;
                                    if($course->modules_count > 0) {
                                        $completedModules = $course->modules->where('status', 'active')->count();
                                        $progress = round(($completedModules / $course->modules_count) * 100);
                                    }
                                    $progressColor = $progress >= 80 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                                @endphp
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $progressColor }}" 
                                             role="progressbar" 
                                             style="width: {{ $progress }}%"
                                             aria-valuenow="{{ $progress }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="ms-2 small fw-bold">{{ $progress }}%</span>
                                </div>
                                <small class="text-muted">{{ $course->modules->where('status', 'active')->count() }} active modules</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $course->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.courses.show', $course) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.courses.edit', $course) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('admin.modules.index', $course) }}"
                                       class="btn btn-sm btn-success" title="Manage Modules">
                                        <i class="bi bi-grid-3x3-gap-fill"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $course) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this course?')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-book fs-1 text-muted d-block mb-2"></i>
                                No courses found.
                                <br>
                                <a href="{{ route('admin.courses.create') }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle"></i> Create Your First Course
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Showing {{ $courses->firstItem() ?? 0 }} to {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} courses
                    </small>
                </div>
                <div>
                    {{ $courses->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar {
        transition: width 0.6s ease;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .stats-card {
        transition: all 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Animate progress bars on load
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(function(bar, index) {
            setTimeout(function() {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(function() {
                    bar.style.width = width;
                }, 100);
            }, index * 100);
        });

        // Auto-hide alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                setTimeout(function() {
                    bsAlert.close();
                }, 5000);
            });
        }, 1000);
    });
</script>
@endpush