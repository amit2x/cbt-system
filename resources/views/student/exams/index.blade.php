@extends('layouts.student')

@section('title', 'My Exams')

@push('styles')
<style>
    .exam-card {
        transition: transform 0.2s;
        height: 100%;
    }
    .exam-card:hover {
        transform: translateY(-5px);
    }
    .nav-tabs .nav-link {
        color: #4a5568;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #1a202c;
        font-weight: 600;
    }
    .tab-content {
        min-height: 300px;
    }
    .resume-btn {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    .exam-status-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        z-index: 1;
    }
    .progress-indicator {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
    }
    .progress-indicator .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #48bb78, #38a169);
        transition: width 0.3s;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">My Exams</h1>
            <p class="text-muted small mt-1">Manage your exams and track your progress</p>
        </div>
        @if($ongoingExams->count() > 0)
        <div class="d-flex align-items-center">
            <span class="badge bg-warning text-dark me-2">
                <i class="bi bi-clock-history"></i> {{ $ongoingExams->count() }} In Progress
            </span>
        </div>
        @endif
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="examTabs" role="tablist">
        
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $ongoingExams->count() <= 0 ? 'active' : '' }}" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab" aria-controls="available" aria-selected="false">
                <i class="bi bi-book text-primary"></i> Available
                @php
                $availableCount = $exams->filter(function($exam) use ($attemptedExams) {
                    $attempt = $attemptedExams[$exam->id] ?? null;
                    return $exam->is_available && (!$attempt || ($attempt->status == 'completed' && $exam->attempts_allowed > 1));
                })->count();
                @endphp
                @if($availableCount > 0)
                <span class="badge bg-primary text-white ms-1">{{ $availableCount }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $ongoingExams->count() > 0 ? 'active' : '' }} " id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button" role="tab" aria-controls="ongoing" aria-selected="true">
                <i class="bi bi-play-circle-fill text-warning"></i> Ongoing
                @if($ongoingExams->count() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $ongoingExams->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab" aria-controls="completed" aria-selected="false">
                <i class="bi bi-check-circle-fill text-success"></i> Completed
                @if($completedExams->count() > 0)
                <span class="badge bg-success text-white ms-1">{{ $completedExams->count() }}</span>
                @endif
            </button>
        </li>
        
        <!-- ✅ NEW: Quizzes Tab Button -->
        @if($hasQuizzes)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="quizzes-tab" data-bs-toggle="tab" data-bs-target="#quizzes" type="button" role="tab" aria-controls="quizzes" aria-selected="false">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Quizzes
                @php
                    $availableQuizCount = $quizzes->filter(function($quiz) use ($quizAttempts) {
                        $attempt = $quizAttempts[$quiz->id] ?? null;
                        return !$attempt || ($attempt->status === 'paused' && !$attempt->submitted_at);
                    })->count();
                @endphp
                @if($availableQuizCount > 0 || $ongoingQuizzes->count() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $availableQuizCount + $ongoingQuizzes->count() }}</span>
                @endif
            </button>
        </li>
        @endif
        
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">
                <i class="bi bi-grid-3x3-gap-fill"></i> All Exams
                <span class="badge bg-secondary text-white ms-1">{{ $exams->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="examTabsContent">
        
        <!-- Ongoing Tab -->
        <div class="tab-pane fade {{ $ongoingExams->count() > 0 ? 'show active' : '' }}" id="ongoing" role="tabpanel" aria-labelledby="ongoing-tab">
            <div class="row">
                @if($ongoingExams->count() > 0)
                    @foreach($ongoingExams as $studentExam)
                    @php
                        $exam = $studentExam->exam;
                        $progress = 0;
                        
                        if(!empty($studentExam->answers_state) && is_array($studentExam->answers_state)){
                            $answers_state = $studentExam->answers_state;
                            // 1. Calculate total questions by summing the unique statuses
                            $totalQuestions = $answers_state['answered'] 
                                            + $answers_state['marked_for_review'] 
                                            + $answers_state['not_visited'] 
                                            + $answers_state['not_answered'];
                            
                            // 2. Calculate progress percentage (handles division by zero safety)
                            $progress = $totalQuestions > 0 
                                ? round(($answers_state['answered'] / $totalQuestions) * 100) 
                                : 0;

                        }    

                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card exam-card border-warning">
                            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                                <span class="fw-bold">In Progress</span>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-hourglass-split"></i> 
                                    {{ $studentExam->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $exam->title }}</h5>
                                <p class="card-text small text-muted">
                                    {{ Str::limit($exam->description ?? 'No description', 80) }}
                                </p>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Progress</span>
                                        <span class="fw-bold">{{ $progress }}%</span>
                                    </div>
                                    <div class="progress-indicator" style="height:10px;">
                                        <div class="progress-bar" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="bi bi-clock"></i> Duration:</span>
                                    <span class="fw-bold">{{ $exam->formatted_duration }}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span><i class="bi bi-clock"></i> Remaining Time:</span>
                                    <span class="fw-bold">
                                        {{ \Carbon\CarbonInterval::seconds($studentExam->remaining_seconds)->cascade()->forHumans(['short' => true]) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between small mb-3">
                                    <span><i class="bi bi-question-circle"></i> Questions:</span>
                                    <span class="fw-bold">{{ $exam->question_count }}</span>
                                </div>
                            
                                @if(!empty($studentExam->answers_state) && is_array($studentExam->answers_state))
                                    <div class="border-top pt-2 mt-2">
                                        <div class="row g-2 text-center small">
                                            <div class="col-4">
                                                <div class="bg-success text-white p-1 rounded">
                                                    <div>Answered</div>
                                                    <div class="fw-bold">{{ $studentExam->answers_state['answered'] ?? 0 }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-warning text-dark p-1 rounded">
                                                    <div>Review</div>
                                                    <div class="fw-bold">{{ $studentExam->answers_state['marked_for_review'] ?? 0 }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-primary text-white p-1 rounded">
                                                    <div>Visited</div>
                                                    <div class="fw-bold">{{ $studentExam->answers_state['visited'] ?? 0 }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="bg-secondary text-white p-1 rounded">
                                                    <div>Not Answered</div>
                                                    <div class="fw-bold">{{ $studentExam->answers_state['not_answered'] ?? 0 }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="bg-dark text-white p-1 rounded">
                                                    <div>Not Visited</div>
                                                    <div class="fw-bold">{{ $studentExam->answers_state['not_visited'] ?? 0 }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            
                            </div>

                            <div class="card-footer bg-transparent">
                                <a href="{{ route('student.exam.resume', $studentExam) }}" class="btn btn-warning w-100 resume-btn">
                                    <i class="bi bi-arrow-right-circle"></i> Resume Exam
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history fs-1 text-warning"></i>
                            <h5 class="mt-3 text-muted">No Ongoing Exams</h5>
                            <p class="text-muted small">You don't have any exams in progress right now.</p>
                            <a href="#available-tab" class="btn btn-primary btn-sm" data-bs-toggle="tab" data-bs-target="#available">
                                <i class="bi bi-plus-circle"></i> Start a New Exam
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Available Tab -->
        <div class="tab-pane fade {{ $ongoingExams->count() <= 0 ? 'show active' : '' }} " id="available" role="tabpanel" aria-labelledby="available-tab">
            <div class="row">
                @php
                    $availableExams = $exams->filter(function($exam) use ($attemptedExams) {
                        $attempt = $attemptedExams[$exam->id] ?? null;
                        return $exam->is_available && (!$attempt || ($attempt->status == 'completed' && $exam->attempts_allowed > 1));
                    });
                @endphp
                
                @if($availableExams->count() > 0)
                    @foreach($availableExams as $exam)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card exam-card border-success">
                            <div class="card-header bg-success text-white">
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
                                    @if($exam->attempts_allowed > 1)
                                    <div class="d-flex justify-content-between small mt-1">
                                        <span><i class="bi bi-arrow-repeat"></i> Attempts:</span>
                                        <span class="fw-bold">
                                            @php
                                                //$attemptCount = $attemptedExams[$exam->id] ?   $attemptedExams[$exam->id]->attempt_number : 0;
                                                    
                                                    $attempt = $attemptedExams->get($exam->id);

                                                    $attemptCount = $attempt
                                                        ? $attempt->attempt_number
                                                        : 0;
                                            @endphp
                                            {{ $attemptCount + 1 }} / {{ $exam->attempts_allowed }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('student.exams.instructions', $exam) }}" class="btn btn-primary w-100">
                                    <i class="bi bi-play-circle"></i> Start Exam
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x fs-1 text-primary"></i>
                            <h5 class="mt-3 text-muted">No Available Exams</h5>
                            <p class="text-muted small">All exams have been completed or are not available yet.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Completed Tab -->
        <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
            <div class="row">
                @if($completedExams->count() > 0)
                    @foreach($completedExams as $studentExam)
                    @php
                        $exam = $studentExam->exam;
                        $result = $studentExam->result;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card exam-card {{ $result && $result->status == 'passed' ? 'border-success' : 'border-danger' }}">
                            <div class="card-header {{ $result && $result->status == 'passed' ? 'bg-success' : 'bg-danger' }} text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">{{ $exam->type_name }}</span>
                                    <span class="badge bg-light text-dark">
                                        {{ $result && $result->status == 'passed' ? 'Passed' : 'Failed' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $exam->title }}</h5>
                                <p class="card-text small text-muted">
                                    {{ Str::limit($exam->description ?? 'No description', 80) }}
                                </p>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span><i class="bi bi-calendar-check"></i> Completed:</span>
                                        <span class="fw-bold">{{ $studentExam->updated_at->format('d M Y h:i A') }}</span>
                                    </div>
                                    @if($result)
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span><i class="bi bi-percent"></i> Score:</span>
                                        <span class="fw-bold {{ $result->status == 'passed' ? 'text-success' : 'text-danger' }}">
                                            {{ $studentExam->percentage ?? $result->percentage }}%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span><i class="bi bi-check-circle text-success"></i> Correct:</span>
                                        <span class="fw-bold">{{ $result->correct_answers ?? 0 }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span><i class="bi bi-x-circle text-danger"></i> Wrong:</span>
                                        <span class="fw-bold">{{ $result->incorrect_answers ?? 0 }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span><i class="bi bi-trophy-fill text-warning"></i> Rank:</span>
                                        <span class="fw-bold">{{ $result->rank ?? 0 }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('student.results.show', $result) }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> View Result
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                            <h5 class="mt-3 text-muted">No Completed Exams</h5>
                            <p class="text-muted small">You haven't completed any exams yet.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- ✅ NEW: Quizzes Tab Content -->
        @if($hasQuizzes)
        <div class="tab-pane fade" id="quizzes" role="tabpanel" aria-labelledby="quizzes-tab">
            
            <!-- Ongoing Quizzes -->
            @if($ongoingQuizzes->count() > 0)
            <h6 class="text-warning mb-3">
                <i class="bi bi-play-circle-fill"></i> In Progress Quizzes
            </h6>
            <div class="row mb-4">
                @foreach($ongoingQuizzes as $studentExam)
                @php $quiz = $studentExam->exam; @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card quiz-card border-warning h-100">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold small">QUIZ</span>
                            <span class="badge bg-dark">In Progress</span>
                        </div>
                        <div class="card-body py-3">
                            <h6 class="card-title mb-2">{{ $quiz->title }}</h6>
                            <div class="d-flex gap-3 small text-muted mb-2">
                                <span><i class="bi bi-question-circle"></i> {{ $quiz->question_count ?? '?' }} Q</span>
                                <span><i class="bi bi-clock"></i> {{ $quiz->duration_minutes }} min</span>
                                <span><i class="bi bi-trophy"></i> {{ $quiz->total_marks }} M</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 50%"></div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent py-2">
                            <a href="{{ route('student.exam.resume', $studentExam) }}" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-play-fill"></i> Resume Quiz
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        
            <!-- Available Quizzes -->
            @php
                $availableQuizzes = $quizzes->filter(function($quiz) use ($quizAttempts) {
                    $attempt = $quizAttempts[$quiz->id] ?? null;
                    return !$attempt || ($attempt->status === 'paused' && !$attempt->submitted_at);
                });
            @endphp
            
            @if($availableQuizzes->count() > 0)
            <h6 class="text-success mb-3">
                <i class="bi bi-lightning-charge"></i> Available Quizzes
            </h6>
            <div class="row mb-4">
                @foreach($availableQuizzes as $quiz)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card quiz-card border-success h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold small">QUICK QUIZ</span>
                            <span class="badge bg-light text-success">New</span>
                        </div>
                        <div class="card-body py-3">
                            <h6 class="card-title mb-2">{{ $quiz->title }}</h6>
                            <p class="small text-muted mb-2">
                                {{ $quiz->batch->course->title ?? '' }} | {{ $quiz->batch->name ?? '' }}
                            </p>
                            <div class="d-flex gap-3 small text-muted mb-2">
                                <span><i class="bi bi-question-circle"></i> {{ $quiz->questions_count ?? '?' }} Q</span>
                                <span><i class="bi bi-clock"></i> {{ $quiz->duration_minutes }} min</span>
                                <span><i class="bi bi-trophy"></i> {{ $quiz->total_marks }} M</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent py-2">
                            <a href="{{ route('student.exams.instructions', $quiz) }}" class="btn btn-success btn-sm w-100">
                                <i class="bi bi-play-fill"></i> Start Quiz
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        
            <!-- Completed Quizzes -->
            @if($completedQuizzes->count() > 0)
            <h6 class="text-primary mb-3">
                <i class="bi bi-check-circle-fill"></i> Completed Quizzes
            </h6>
            <div class="row">
                @foreach($completedQuizzes as $studentExam)
                @php 
                    $quiz = $studentExam->exam;
                    $result = $studentExam->result;
                    $passed = $result && $result->status === 'passed';
                @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card quiz-card {{ $passed ? 'border-success' : 'border-danger' }} h-100">
                        <div class="card-header {{ $passed ? 'bg-success' : 'bg-danger' }} text-white d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold small">QUIZ</span>
                            <span class="badge bg-light {{ $passed ? 'text-success' : 'text-danger' }}">
                                {{ $passed ? 'Passed' : 'Failed' }}
                            </span>
                        </div>
                        <div class="card-body py-3">
                            <h6 class="card-title mb-2">{{ $quiz->title }}</h6>
                            @if($result)
                            <div class="d-flex justify-content-between small mb-2">
                                <span>Score:</span>
                                <strong class="{{ $passed ? 'text-success' : 'text-danger' }}">
                                    {{ $result->percentage ?? 0 }}%
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Marks:</span>
                                <strong>{{ $result->obtained_marks ?? 0 }}/{{ $result->total_marks ?? 0 }}</strong>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent py-2">
                            <a href="{{ route('student.results.show', $result) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-eye"></i> View Result
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        
            <!-- No Quizzes -->
            @if($availableQuizzes->count() === 0 && $ongoingQuizzes->count() === 0 && $completedQuizzes->count() === 0)
            <div class="text-center py-5">
                <i class="bi bi-lightning-charge fs-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No Quizzes Available</h5>
                <p class="text-muted small">No quizzes have been assigned to you yet.</p>
            </div>
            @endif
        </div>
        @endif

        <!-- All Exams Tab -->
        <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
            <div class="row">
                @if($exams->count() > 0)
                    @foreach($exams as $exam)
                    @php
                        $attempt = $attemptedExams[$exam->id] ?? null;
                        $canTake = !$attempt || ($attempt->status == 'completed' && $exam->attempts_allowed > 1);
                        $isOngoing = $attempt && in_array($attempt->status, ['started', 'in_progress']);
                        $isCompleted = $attempt && $attempt->status == 'completed';
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card exam-card 
                            @if($isOngoing) border-warning
                            @elseif($isCompleted && $attempt->result && $attempt->result->status == 'passed') border-success
                            @elseif($isCompleted) border-danger
                            @elseif($exam->is_available) border-primary
                            @else border-secondary @endif
                        ">
                            <div class="card-header 
                                @if($isOngoing) bg-warning
                                @elseif($isCompleted && $attempt->result && $attempt->result->status == 'passed') bg-success
                                @elseif($isCompleted) bg-danger
                                @elseif($exam->is_available) bg-primary
                                @else bg-secondary @endif 
                                text-white
                            ">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">{{ $exam->type_name }}</span>
                                    @if($attempt)
                                        @if($isOngoing)
                                            <span class="badge bg-light text-warning">In Progress</span>
                                        @elseif($isCompleted)
                                            <span class="badge bg-light text-{{ $attempt->result && $attempt->result->status == 'passed' ? 'success' : 'danger' }}">
                                                {{ $attempt->result && $attempt->result->status == 'passed' ? 'Passed' : 'Failed' }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-primary">Not Attempted</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $exam->title }}</h5>
                                <p class="card-text small text-muted">
                                    {{ Str::limit($exam->description ?? 'No description', 80) }}
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
                                    <div class="d-flex justify-content-between small">
                                        <span><i class="bi bi-trophy"></i> Passing:</span>
                                        <span class="fw-bold">{{ $exam->passing_percentage }}%</span>
                                    </div>
                                    @if($attempt && $isCompleted && $attempt->result)
                                    <div class="d-flex justify-content-between small mt-2 pt-2 border-top">
                                        <span><i class="bi bi-percent"></i> Score:</span>
                                        <span class="fw-bold {{ $attempt->result->status == 'passed' ? 'text-success' : 'text-danger' }}">
                                            {{ $attempt->percentage }}%
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                @if(!$exam->is_available)
                                <div class="alert alert-secondary p-2 small mb-0">
                                    <i class="bi bi-clock-history"></i>
                                    @if($exam->start_time > now())
                                        Starts {{ $exam->start_time->format('d M Y h:i A') }}
                                    @else
                                        Expired
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                @if($isOngoing)
                                    <a href="{{ route('student.exam.resume', $exam) }}" class="btn btn-warning w-100">
                                        <i class="bi bi-arrow-right-circle"></i> Resume
                                    </a>
                                @elseif($isCompleted)
                                    <a href="{{ route('student.results.show', $result) }}" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-eye"></i> View Result
                                    </a>
                                @elseif($exam->is_available && $canTake)
                                    <a href="{{ route('student.exams.instructions', $exam) }}" class="btn btn-primary w-100">
                                        <i class="bi bi-play-circle"></i> Start Exam
                                    </a>
                                @elseif(!$exam->is_available)
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-clock-history"></i> Not Available
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
                @else
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x fs-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No Exams Found</h5>
                            <p class="text-muted small">No exams are available for you at the moment.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Preserve tab state
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's a hash in the URL
        const hash = window.location.hash;
        if (hash) {
            const tabTrigger = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tabTrigger) {
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            }
        }

        // Update URL hash when tab changes
        const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                history.pushState(null, null, target);
            });
        });
    });
</script>
@endpush