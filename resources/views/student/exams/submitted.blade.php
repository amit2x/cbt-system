{{-- resources/views/student/exams/submitted.blade.php --}}

@extends('layouts.student')

@section('title', 'Exam Submitted')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h2 class="mb-3">Exam Submitted Successfully!</h2>
                    <p class="lead text-muted mb-4">
                        Your responses have been recorded.
                    </p>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Result Pending:</strong> Your result will be published by the examiner/administrator
                        soon.
                        @if($studentExam->result && $studentExam->result->result_publish_date)
                        <br>
                        <small>
                            Scheduled publish date:
                            <strong>{{ $studentExam->result->result_publish_date->format('d M Y, h:i A') }}</strong>
                        </small>
                        @endif
                    </div>

                    <div class="row mt-4 text-start">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Exam</small>
                                    <h6 class="mb-0">{{ $studentExam->exam->title }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Submitted At</small>
                                    <h6 class="mb-0">{{ $studentExam->submitted_at->format('d M Y, h:i A') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Total Questions</small>
                                    <h6 class="mb-0">{{ $studentExam->total_questions }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Attempted</small>
                                    <h6 class="mb-0">{{ $studentExam->answered_questions }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('student.exams.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-journal"></i> My Exams
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection