{{-- resources/views/student/batch-exams.blade.php --}}

@extends('layouts.student')

@section('title', 'Exams - ' . $batch->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-journal me-2"></i>{{ $batch->name }} - Exams
            </h1>
            <p class="text-muted small mb-0">
                <i class="bi bi-book me-1"></i>
                {{ $batch->course->title ?? 'N/A' }} | 
                <i class="bi bi-person me-1"></i>
                {{ $batch->instructor->name ?? 'N/A' }}
            </p>
        </div>
        <a href="{{ route('student.batch.detail', $batch) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Batch
        </a>
    </div>

    <!-- Batch Info Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Status</small>
                    <br>
                    <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'secondary' : 'primary') }}">
                        {{ ucfirst($batch->status) }}
                    </span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Duration</small>
                    <br>
                    <span>{{ $batch->start_date->format('d M Y') }} - {{ $batch->end_date->format('d M Y') }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Students</small>
                    <br>
                    <span>{{ $batch->students_count }}/{{ $batch->capacity }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Progress</small>
                    <br>
                    <div class="progress" style="height: 20px; width: 80%;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $batch->progress }}%">
                            {{ round($batch->progress) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-journal me-2"></i>Available Exams
                <span class="badge bg-primary ms-2">{{ $exams->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($exams->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Title</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Total Marks</th>
                                <th>Questions</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exams as $exam)
                                @php
                                    $attempt = $exam->studentExams()->where('student_id', auth()->id())->first();
                                    $isCompleted = $attempt && $attempt->status == 'completed';
                                    $isInProgress = $attempt && in_array($attempt->status, ['started', 'in_progress']);
                                    $isAvailable = $exam->status == 'published';
                                    $isUpcoming = $exam->start_time && $exam->start_time->isFuture();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $exam->title }}</strong>
                                        @if($exam->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($exam->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($exam->type) }}</span>
                                    </td>
                                    <td>{{ $exam->duration_minutes }} min</td>
                                    <td>{{ $exam->total_marks }}</td>
                                    <td>{{ $exam->questions_count ?? $exam->questions()->count() }}</td>
                                    <td>
                                        @if($isCompleted)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Completed
                                            </span>
                                        @elseif($isInProgress)
                                            <span class="badge bg-warning">
                                                <i class="bi bi-play-circle me-1"></i>In Progress
                                            </span>
                                        @elseif($isUpcoming)
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-clock me-1"></i>Upcoming
                                            </span>
                                        @elseif($isAvailable)
                                            <span class="badge bg-primary">
                                                <i class="bi bi-check-circle me-1"></i>Available
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($exam->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isCompleted)
                                            <a href="{{ route('student.exam.result', $attempt) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="bi bi-bar-chart"></i> View Result
                                            </a>
                                        @elseif($isInProgress)
                                            <a href="{{ route('student.exam.take', $attempt) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-play-circle"></i> Resume
                                            </a>
                                        @elseif($isAvailable && !$isUpcoming)
                                            <a href="{{ route('student.exams.instructions', $exam) }}" 
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-play-circle"></i> Start
                                            </a>
                                        @elseif($isUpcoming)
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-clock"></i> 
                                                {{ $exam->start_time->format('d M, h:i A') }}
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                Not Available
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-journal fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No exams available in this batch.</p>
                    <p class="text-muted small">Check back later for new exams.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h3 class="text-primary">{{ $exams->count() }}</h3>
                    <p class="text-muted">Total Exams</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    @php
                        $completedCount = $exams->filter(function($exam) {
                            $attempt = $exam->studentExams()->where('student_id', auth()->id())->first();
                            return $attempt && $attempt->status == 'completed';
                        })->count();
                    @endphp
                    <h3 class="text-success">{{ $completedCount }}</h3>
                    <p class="text-muted">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    @php
                        $pendingCount = $exams->count() - $completedCount;
                    @endphp
                    <h3 class="text-warning">{{ $pendingCount }}</h3>
                    <p class="text-muted">Pending</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection