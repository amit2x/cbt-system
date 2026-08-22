@extends('layouts.student')

@section('title', 'Quiz Instructions - ' . $quiz->title)

@push('styles')
<style>
.quiz-instruction-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
}
.quiz-instruction-header {
    background: linear-gradient(135deg, #f6c23e, #e0a800);
    color: #1a1a1a;
    padding: 30px 35px;
}
.quiz-instruction-header h3 {
    font-weight: 700;
    margin-bottom: 5px;
}
.quiz-instruction-body {
    padding: 30px 35px;
}
.quiz-stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 1px solid #e9ecef;
}
.quiz-stat-item .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.stat-icon.blue { background: #e3f2fd; color: #1976d2; }
.stat-icon.green { background: #e8f5e9; color: #2e7d32; }
.stat-icon.orange { background: #fff3e0; color: #f57c00; }
.stat-icon.purple { background: #f3e5f5; color: #7b1fa2; }
.stat-icon.red { background: #ffebee; color: #c62828; }
.rule-list li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}
.rule-list li:last-child {
    border-bottom: none;
}
.btn-start-quiz {
    background: linear-gradient(135deg, #f6c23e, #e0a800);
    color: #1a1a1a;
    border: none;
    padding: 14px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(246, 194, 62, 0.3);
}
.btn-start-quiz:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(246, 194, 62, 0.5);
    color: #1a1a1a;
}
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Back Button -->
            <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Back to Quizzes
            </a>

            <!-- Main Card -->
            <div class="card quiz-instruction-card">
                <div class="quiz-instruction-header">
                    <span class="badge bg-dark mb-2">QUIZ</span>
                    <h3>{{ $quiz->title }}</h3>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-book"></i> {{ $quiz->batch->course->title ?? 'Course' }} 
                        <span class="mx-2">|</span>
                        <i class="bi bi-people"></i> {{ $quiz->batch->name ?? 'Batch' }}
                    </p>
                </div>

                <div class="quiz-instruction-body">
                    
                    <!-- Quiz Stats -->
                    <h5 class="fw-bold mb-3">📋 Quiz Overview</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="quiz-stat-item">
                                <div class="stat-icon blue">
                                    <i class="bi bi-question-circle"></i>
                                </div>
                                <div>
                                    <strong>{{ $quiz->questions->count() }}</strong>
                                    <small class="text-muted d-block">Total Questions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quiz-stat-item">
                                <div class="stat-icon orange">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div>
                                    <strong>{{ $quiz->duration_minutes }} Minutes</strong>
                                    <small class="text-muted d-block">Duration</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quiz-stat-item">
                                <div class="stat-icon green">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div>
                                    <strong>{{ $quiz->total_marks }} Marks</strong>
                                    <small class="text-muted d-block">Maximum Score</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quiz-stat-item">
                                <div class="stat-icon red">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div>
                                    <strong>{{ $quiz->passing_percentage }}%</strong>
                                    <small class="text-muted d-block">Passing Criteria</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Rules -->
                    <h5 class="fw-bold mb-3">📝 Important Instructions</h5>
                    <ul class="rule-list mb-4">
                        <li><strong>Timer starts immediately</strong> after you click "Start Quiz".</li>
                        <li>You can <strong>navigate</strong> between questions using the question palette.</li>
                        <li>Click <strong>Save & Next</strong> to save your answer for each question.</li>
                        <li>Use <strong>Mark for Review</strong> to flag questions you want to revisit.</li>
                        <li>You can <strong>change answers</strong> anytime before final submission.</li>
                        <li>The quiz will <strong>auto-submit</strong> when time runs out.</li>
                        <li><strong>Results are shown instantly</strong> after submission.</li>
                        <li>Do not <strong>refresh the page</strong> or use the <strong>browser back button</strong>.</li>
                    </ul>

                    @if($quiz->instructions)
                        <div class="alert alert-info mb-4">
                            <strong><i class="bi bi-info-circle"></i> Instructor's Note:</strong><br>
                            {!! nl2br(e($quiz->instructions)) !!}
                        </div>
                    @endif

                    @if($quiz->negative_marking > 0)
                        <div class="alert alert-warning mb-4">
                            <strong><i class="bi bi-exclamation-triangle"></i> Negative Marking:</strong>
                            {{ $quiz->negative_marking }} marks will be deducted for each wrong answer.
                        </div>
                    @endif

                    <!-- Start Button -->
                    <div class="text-center mt-4">
                        <form action="{{ route('student.quizzes.start', $quiz) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-start-quiz">
                                <i class="bi bi-play-fill me-2"></i> Start Quiz Now
                            </button>
                        </form>
                        <p class="text-muted small mt-3">
                            <i class="bi bi-shield-check"></i> By starting, you agree to follow all the instructions above.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection