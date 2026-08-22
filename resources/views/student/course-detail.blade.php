{{-- resources/views/student/course-detail.blade.php --}}

@extends('layouts.student')

@section('title', $course->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-book me-2"></i>{{ $course->title }}
            </h1>
            <p class="text-muted small mb-0">
                <i class="bi bi-grid me-1"></i>
                {{ $course->modules->count() }} Modules | 
                {{ $course->batches->count() }} Batches | 
                {{ $course->exams->count() }} Exams
            </p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <!-- Course Info -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Information</h6>
                </div>
                <div class="card-body">
                    @if($course->thumbnail)
                        <img src="{{ asset('storage/' . $course->thumbnail) }}" 
                             alt="{{ $course->title }}" 
                             class="img-fluid rounded mb-3">
                    @endif
                    <p>{{ $course->description }}</p>
                    <hr>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $course->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $course->created_at->format('d M Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Batches -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-collection me-2"></i>Batches
                        <span class="badge bg-primary ms-2">{{ $course->batches->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($course->batches->count() > 0)
                        <div class="list-group">
                            @foreach($course->batches as $batch)
                                <a href="{{ route('student.batch.detail', $batch) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $batch->name }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>
                                                {{ $batch->instructor->name ?? 'N/A' }}
                                                |
                                                <i class="bi bi-people me-1"></i>
                                                {{ $batch->students_count }}/{{ $batch->capacity }}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'secondary' : 'primary') }}">
                                                {{ ucfirst($batch->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No batches available for this course.</p>
                    @endif
                </div>
            </div>

            <!-- Modules -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-grid me-2"></i>Modules
                        <span class="badge bg-info ms-2">{{ $course->modules->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($course->modules->count() > 0)
                        <div class="list-group">
                            @foreach($course->modules as $module)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $module->title }}</h6>
                                            <small class="text-muted">{{ $module->description }}</small>
                                        </div>
                                        <span class="badge bg-{{ $module->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($module->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No modules available for this course.</p>
                    @endif
                </div>
            </div>

            <!-- Exams -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-journal me-2"></i>Exams
                        <span class="badge bg-warning ms-2">{{ $course->exams->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($course->exams->count() > 0)
                        <div class="list-group">
                            @foreach($course->exams as $exam)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $exam->title }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $exam->duration_minutes }} min |
                                                <i class="bi bi-award me-1"></i>
                                                {{ $exam->total_marks }} marks
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $exam->status == 'published' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($exam->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No exams available for this course.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection