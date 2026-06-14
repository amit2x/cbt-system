@extends('layouts.admin')

@section('title', $course->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $course->title }}</h1>
        <div>
            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Course
            </a>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Course Information -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p class="text-muted">{{ $course->description ?? 'No description provided.' }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Status</h5>
                            <p>
                                <span class="badge bg-{{ $course->status == 'active' ? 'success' : 'secondary' }} fs-6">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Created By</h5>
                            <p>{{ $course->creator->name }} ({{ $course->created_at->format('d M Y') }})</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Modules</h6>
                </div>
                <div class="card-body">
                    @if($course->modules->count() > 0)
                        <div class="accordion" id="modulesAccordion">
                            @foreach($course->modules as $index => $module)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#module{{ $module->id }}">
                                            <strong>{{ $module->title }}</strong>
                                        </button>
                                    </h2>
                                    <div id="module{{ $module->id }}"
                                         class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}">
                                        <div class="accordion-body">
                                            {{ $module->description ?? 'No description' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted my-3">No modules added yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Thumbnail -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thumbnail</h6>
                </div>
                <div class="card-body text-center">
                    @if($course->thumbnail)
                        <img src="{{ asset('storage/' . $course->thumbnail) }}"
                             alt="{{ $course->title }}"
                             class="img-fluid rounded" style="max-height: 200px;">
                    @else
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded"
                             style="height: 150px;">
                            <i class="bi bi-image fs-1"></i>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Total Modules</span>
                            <span class="badge bg-primary">{{ $course->modules->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Total Batches</span>
                            <span class="badge bg-success">{{ $course->batches->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Active Batches</span>
                            <span class="badge bg-info">{{ $course->active_batches_count }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total Students</span>
                            <span class="badge bg-warning">{{ $course->total_students }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructors -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assigned Instructors</h6>
                </div>
                <div class="card-body">
                    @if($course->instructors->count() > 0)
                        @foreach($course->instructors as $instructor)
                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $instructor->avatar_url }}"
                                     alt="{{ $instructor->name }}"
                                     class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <h6 class="mb-0">{{ $instructor->name }}</h6>
                                    <small class="text-muted">{{ $instructor->email }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No instructors assigned</p>
                    @endif
                </div>
            </div>

            <!-- Batches -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Batches</h6>
                </div>
                <div class="card-body">
                    @if($course->batches->count() > 0)
                        @foreach($course->batches->take(3) as $batch)
                            <div class="mb-3 pb-2 border-bottom">
                                <h6>{{ $batch->name }}</h6>
                                <div class="small">
                                    <div>Instructor: {{ $batch->instructor->name }}</div>
                                    <div>Students: {{ $batch->students_count }}</div>
                                    <div>Status:
                                        <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($batch->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($course->batches->count() > 3)
                            <a href="#" class="btn btn-link btn-sm">View All Batches</a>
                        @endif
                    @else
                        <p class="text-muted mb-0">No batches created yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
