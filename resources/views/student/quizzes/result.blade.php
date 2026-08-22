@extends('layouts.student')

@section('title', 'Quiz Result - ' . ($studentExamModel->exam->title ?? 'Quiz'))

@push('styles')
<style>
.result-card {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
}
.result-header {
    padding: 40px;
    text-align: center;
    color: white;
}
.result-header.passed {
    background: linear-gradient(135deg, #28a745, #20c997);
}
.result-header.failed {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
}
.score-circle {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    border: 4px solid rgba(255,255,255,0.4);
}
.score-circle .percentage {
    font-size: 2.5rem;
    font-weight: 800;
}
.score-circle .percent-sign {
    font-size: 1.2rem;
    font-weight: 600;
}
.result-body {
    padding: 30px 35px;
}
.stat-box {
    text-align: center;
    padding: 15px;
    border-radius: 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}
.stat-box .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 2px;
}
.stat-box .stat-label {
    font-size: 0.8rem;
    color: #666;
}
.stat-box.correct .stat-value { color: #28a745; }
.stat-box.incorrect .stat-value { color: #dc3545; }
.stat-box.skipped .stat-value { color: #6c757d; }
.stat-box.marks .stat-value { color: #4e73df; }
.stat-box.time .stat-value { color: #f6c23e; }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <!-- Back Button -->
            <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Back to Quizzes
            </a>

            <div class="card result-card">
                
                @php
                    $percentage = $result->percentage ?? 0;
                    $passed = ($result->status ?? 'failed') === 'passed';
                @endphp

                <!-- Result Header -->
                <div class="result-header {{ $passed ? 'passed' : 'failed' }}">
                    <h5 class="mb-3">{{ $studentExamModel->exam->title ?? 'Quiz' }}</h5>
                    
                    <div class="score-circle">
                        <div>
                            <span class="percentage">{{ $percentage }}</span>
                            <span class="percent-sign">%</span>
                        </div>
                    </div>
                    
                    <h4 class="mb-1">
                        @if($passed)
                            🎉 Congratulations! You Passed!
                        @else
                            😔 Better Luck Next Time!
                        @endif
                    </h4>
                    <p class="mb-0 opacity-75 small">
                        {{ $studentExamModel->batch->course->title ?? '' }} | {{ $studentExamModel->batch->name ?? '' }}
                    </p>
                </div>

                <!-- Result Stats -->
                <div class="result-body">
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="stat-box correct">
                                <div class="stat-value">{{ $result->correct_count ?? 0 }}</div>
                                <div class="stat-label">Correct</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box incorrect">
                                <div class="stat-value">{{ $result->incorrect_count ?? 0 }}</div>
                                <div class="stat-label">Incorrect</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box skipped">
                                <div class="stat-value">{{ $result->skipped_count ?? 0 }}</div>
                                <div class="stat-label">Skipped</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-box marks">
                                <div class="stat-value">{{ $result->obtained_marks ?? 0 }}/{{ $result->total_marks ?? 0 }}</div>
                                <div class="stat-label">Marks Obtained</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box time">
                                @php
                                    $seconds = $result->time_taken_seconds ?? 0;
                                    $minutes = floor($seconds / 60);
                                    $secs = $seconds % 60;
                                @endphp
                                <div class="stat-value">{{ $minutes }}:{{ str_pad($secs, 2, '0', STR_PAD_LEFT) }}</div>
                                <div class="stat-label">Time Taken</div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Score</span>
                            <span class="fw-bold {{ $passed ? 'text-success' : 'text-danger' }}">{{ $percentage }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar {{ $passed ? 'bg-success' : 'bg-danger' }}" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small mt-1">
                            <span>0%</span>
                            <span class="text-muted">Pass: {{ $studentExamModel->exam->passing_percentage ?? 40 }}%</span>
                            <span>100%</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary">
                            <i class="bi bi-list-check"></i> All Quizzes
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('student.results.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-bar-chart"></i> All Results
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection