@extends('layouts.student')

@section('title', 'My Quizzes')

@push('styles')
<style>
.quiz-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid #4e73df;
}
.quiz-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.quiz-card.completed {
    border-left-color: #28a745;
}
.quiz-card.active {
    border-left-color: #f6c23e;
}
.status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}
.quiz-info {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.quiz-info-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: #666;
}
.quiz-info-item i {
    color: #4e73df;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Quizzes</h1>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Active / Pending Quizzes -->
    <div class="mb-4">
        <h5 class="mb-3 text-warning">
            <i class="bi bi-pencil-square"></i> Active Quizzes
        </h5>
        
        @if($activeQuizzes->count() > 0)
            <div class="row">
                @foreach($activeQuizzes as $quiz)
                    <div class="col-md-6 mb-3">
                        <div class="card quiz-card active position-relative h-100">
                            <span class="badge bg-warning text-dark status-badge">
                                @if($quiz->student_attempt && $quiz->student_attempt->status === 'paused')
                                    In Progress
                                @else
                                    New
                                @endif
                            </span>
                            <div class="card-body">
                                <h6 class="card-title">{{ $quiz->title }}</h6>
                                <p class="text-muted small mb-2">
                                    {{ $quiz->batch->course->title ?? 'N/A' }} | {{ $quiz->batch->name ?? 'N/A' }}
                                </p>
                                
                                <div class="quiz-info mb-3">
                                    <span class="quiz-info-item">
                                        <i class="bi bi-question-circle"></i> {{ $quiz->question_count ?? $quiz->questions_count ?? '?' }} Q
                                    </span>
                                    <span class="quiz-info-item">
                                        <i class="bi bi-clock"></i> {{ $quiz->duration_minutes }} min
                                    </span>
                                    <span class="quiz-info-item">
                                        <i class="bi bi-trophy"></i> {{ $quiz->total_marks }} Marks
                                    </span>
                                </div>
                                
                                <a href="{{ route('student.quizzes.instructions', $quiz) }}" class="btn btn-warning btn-sm">
                                    @if($quiz->student_attempt && $quiz->student_attempt->status === 'paused')
                                        <i class="bi bi-play-fill"></i> Resume Quiz
                                    @else
                                        <i class="bi bi-play-fill"></i> Start Quiz
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body empty-state">
                    <i class="bi bi-clipboard-check"></i>
                    <h5>No Active Quizzes</h5>
                    <p class="text-muted">You don't have any pending quizzes right now.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Completed Quizzes -->
    <div class="mb-4">
        <h5 class="mb-3 text-success">
            <i class="bi bi-check-circle"></i> Completed Quizzes
        </h5>
        
        @if($completedQuizzes->count() > 0)
            <div class="row">
                @foreach($completedQuizzes as $quiz)
                    <div class="col-md-6 mb-3">
                        <div class="card quiz-card completed position-relative h-100">
                            <span class="badge bg-success status-badge">Completed</span>
                            <div class="card-body">
                                <h6 class="card-title">{{ $quiz->title }}</h6>
                                <p class="text-muted small mb-2">
                                    {{ $quiz->batch->course->title ?? 'N/A' }} | {{ $quiz->batch->name ?? 'N/A' }}
                                </p>
                                
                                <div class="quiz-info mb-3">
                                    <span class="quiz-info-item">
                                        <i class="bi bi-question-circle"></i> {{ $quiz->question_count ?? $quiz->questions_count ?? '?' }} Q
                                    </span>
                                    <span class="quiz-info-item">
                                        <i class="bi bi-trophy"></i> {{ $quiz->total_marks }} Marks
                                    </span>
                                    @if($quiz->student_attempt && $quiz->student_attempt->result)
                                        <span class="quiz-info-item">
                                            <i class="bi bi-bar-chart"></i> 
                                            {{ $quiz->student_attempt->result->percentage ?? 0 }}%
                                        </span>
                                    @endif
                                </div>
                                
                                @if($quiz->student_attempt)
                                    <a href="{{ route('student.quiz.result', $quiz->student_attempt->id) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-eye"></i> View Result
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body empty-state">
                    <i class="bi bi-journal-check"></i>
                    <h5>No Completed Quizzes</h5>
                    <p class="text-muted">You haven't completed any quizzes yet.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection