@extends('layouts.student')

@section('title', 'My Exams')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Exams</h1>
    </div>

    <!-- Available Exams -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Available Exams</h6>
        </div>
        <div class="card-body">
            @if($exams->count() > 0)
            <div class="row">
                @foreach($exams as $exam)
                @php
                $attempt = $attemptedExams[$exam->id] ?? null;
                $canTake = !$attempt || ($attempt->status == 'completed' && $exam->attempts_allowed > 1);
                // $canTake = true;

                @endphp

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 {{ $exam->is_available ? 'border-success' : 'border-warning' }}">
                        <div class="card-header bg-{{ $exam->type_badge }} text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ $exam->type_name }}</span>
                                @if($exam->category)
                                <span class="badge bg-light text-dark">{{ ucfirst($exam->category) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $exam->title }}</h5>
                            <p class="card-text small text-muted">
                                {{ Str::limit($exam->description ?? 'No description', 100) }}
                            </p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="bi bi-clock"></i> Duration:</span>
                                    <span class="fw-bold">{{ $exam->formatted_duration }}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="bi bi-question-circle"></i> Questions:</span>
                                    <span class="fw-bold">{{ $exam->question_count }}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="bi bi-award"></i> Total Marks:</span>
                                    <span class="fw-bold">{{ $exam->total_marks }}</span>
                                </div>
                                @if($exam->negative_marking > 0)
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="bi bi-dash-circle"></i> Negative:</span>
                                    <span class="fw-bold text-danger">{{ $exam->negative_marking }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between small">
                                    <span><i class="bi bi-trophy"></i> Passing:</span>
                                    <span class="fw-bold">{{ $exam->passing_percentage }}%</span>
                                </div>
                            </div>

                            @if($attempt)
                            <div class="alert alert-info p-2 small mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Attempted:</span>
                                    <span>{{ $attempt->created_at->format('d M Y') }}</span>
                                </div>
                                @if($attempt->status == 'completed' && $attempt->result)
                                <div class="d-flex justify-content-between">
                                    <span>Score:</span>
                                    <span
                                        class="fw-bold {{ $attempt->result->status == 'passed' ? 'text-success' : 'text-danger' }}">
                                        {{ $attempt->percentage }}%
                                    </span>
                                </div>
                                @elseif($attempt->status == 'started')
                                <div class="text-warning">In Progress</div>
                                @endif
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent">
                            @if($exam->is_available && $canTake)
                            <a href="{{ route('student.exams.instructions', $exam) }}" class="btn btn-primary w-100">
                                <i class="bi bi-play-circle"></i> Start Exam
                            </a>
                            @elseif(!$exam->is_available)
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-clock-history"></i>
                                @if($exam->start_time > now())
                                Starts {{ $exam->start_time->format('d M Y h:i A') }}
                                @else
                                Expired
                                @endif
                            </button>
                            @else
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-x-circle"></i> Attempts Exhausted
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4">
                <i class="bi bi-journal-x fs-1 text-muted"></i>
                <p class="mt-2">No exams available at the moment.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection