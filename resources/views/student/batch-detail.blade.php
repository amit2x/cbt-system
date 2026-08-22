{{-- resources/views/student/batch-detail.blade.php --}}

@extends('layouts.student')

@section('title', $batch->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-collection me-2"></i>{{ $batch->name }}
            </h1>
            <p class="text-muted small mb-0">
                <i class="bi bi-book me-1"></i>
                {{ $batch->course->title }} | 
                <i class="bi bi-person me-1"></i>
                {{ $batch->instructor->name ?? 'N/A' }}
            </p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <!-- Batch Info -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Course:</strong></td>
                            <td>{{ $batch->course->title }}</td>
                        </tr>
                        <tr>
                            <td><strong>Instructor:</strong></td>
                            <td>{{ $batch->instructor->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Duration:</strong></td>
                            <td>
                                {{ $batch->start_date->format('d M Y') }} - 
                                {{ $batch->end_date->format('d M Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'secondary' : 'primary') }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Students:</strong></td>
                            <td>{{ $batch->students_count }}/{{ $batch->capacity }}</td>
                        </tr>
                        <tr>
                            <td><strong>Progress:</strong></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" 
                                         role="progressbar" 
                                         style="width: {{ $batch->progress }}%">
                                        {{ round($batch->progress) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Exams in this Batch -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-journal me-2"></i>Exams
                        <span class="badge bg-primary ms-2">{{ $exams->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($exams->count() > 0)
                        <div class="list-group">
                            @foreach($exams as $exam)
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
                                            @php
                                                $attempt = $exam->studentExams()->where('student_id', auth()->id())->first();
                                            @endphp
                                            @if($attempt && $attempt->status == 'completed')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Completed
                                                </span>
                                            @elseif($attempt && in_array($attempt->status, ['started', 'in_progress']))
                                                <a href="{{ route('student.exams.take', $attempt) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-play-circle"></i> Resume
                                                </a>
                                            @else
                                                <a href="{{ route('student.exams.instructions', $exam) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-play-circle"></i> Start
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No exams available in this batch.</p>
                    @endif
                </div>
            </div>

            <!-- Batch Members (Optional) -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-people me-2"></i>Batch Members
                        <span class="badge bg-info ms-2">{{ $batch->students_count }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($batch->students->count() > 0)
                        <div class="row">
                            @foreach($batch->students as $student)
                                <div class="col-md-4 col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $student->avatar_url }}" 
                                             alt="{{ $student->name }}" 
                                             class="rounded-circle me-2" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                        <span class="small">{{ $student->name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No students in this batch.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection