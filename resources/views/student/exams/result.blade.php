@extends('layouts.student')

@section('title', 'Exam Result')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Result Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-{{ $studentExam->result->status == 'passed' ? 'success' : 'danger' }} text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-{{ $studentExam->result->status == 'passed' ? 'check-circle' : 'exclamation-circle' }}"></i>
                        Exam Result: {{ $studentExam->exam->title }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Score Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="score-circle mb-3">
                                <div class="display-1 fw-bold {{ $studentExam->result->status == 'passed' ? 'text-success' : 'text-danger' }}">
                                    {{ round($studentExam->percentage) }}%
                                </div>
                            </div>
                            <h5 class="mb-0">
                                <span class="badge bg-{{ $studentExam->result->status == 'passed' ? 'success' : 'danger' }} fs-6">
                                    {{ strtoupper($studentExam->result->status) }}
                                </span>
                            </h5>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">{{ $studentExam->correct_answers }}</h3>
                                            <p class="mb-0">Correct</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-danger">{{ $studentExam->incorrect_answers }}</h3>
                                            <p class="mb-0">Incorrect</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-secondary">{{ $studentExam->unanswered }}</h3>
                                            <p class="mb-0">Unanswered</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">Total Questions:</td>
                                    <td><strong>{{ $studentExam->total_questions }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Marks:</td>
                                    <td><strong>{{ $studentExam->obtained_marks }} / {{ $studentExam->exam->total_marks }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Time Taken:</td>
                                    <td><strong>
                                        @if($studentExam->total_seconds_taken < 0)
                                            Invalid Time Data
                                        @else
                                            {{ \Carbon\CarbonInterval::seconds($studentExam->total_seconds_taken)->cascade()->format('%H:%I:%S') }}
                                        @endif
                                    </strong></td>
                                </tr>
                                @if($studentExam->result->rank)
                                <tr>
                                    <td>Rank:</td>
                                    <td><strong class="text-primary">#{{ $studentExam->result->rank }}</strong></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Performance Charts -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Question Analysis</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="questionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Score Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="scoreChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Analysis -->
                    @if(!empty($summary['section_analysis']))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Section-wise Performance</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Section</th>
                                            <th>Total</th>
                                            <th>Correct</th>
                                            <th>Incorrect</th>
                                            <th>Unanswered</th>
                                            <th>Accuracy</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['section_analysis'] as $section)
                                            <tr>
                                                <td>{{ $section['name'] }}</td>
                                                <td>{{ $section['total'] }}</td>
                                                <td class="text-success">{{ $section['correct'] }}</td>
                                                <td class="text-danger">{{ $section['incorrect'] }}</td>
                                                <td class="text-secondary">{{ $section['unanswered'] }}</td>
                                                <td>
                                                    @if($section['total'] > 0)
                                                        {{ round(($section['correct'] / $section['total']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Topic Analysis -->
                    @if(!empty($summary['topic_analysis']))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Topic-wise Performance</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Topic</th>
                                            <th>Questions</th>
                                            <th>Correct</th>
                                            <th>Accuracy</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['topic_analysis'] as $topic)
                                            <tr>
                                                <td>{{ $topic['name'] }}</td>
                                                <td>{{ $topic['total'] }}</td>
                                                <td class="text-success">{{ $topic['correct'] }}</td>
                                                <td>
                                                    @if($topic['total'] > 0)
                                                        {{ round(($topic['correct'] / $topic['total']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Answer Review -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Answer Review</h6>
                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#answerReview">
                                Show/Hide Answers
                            </button>
                        </div>
                        <div class="collapse" id="answerReview">
                            <div class="card-body">
                                @foreach($studentExam->answers as $index => $answer)
                                    @php
                                        $question = $answer->question;
                                        $isCorrect = $answer->is_correct;
                                        $hasAnswer = $answer->selected_options || $answer->answer_text;
                                    @endphp
                                    <div class="card mb-3 {{ $answer->is_correct ? 'border-success' : ($answer->selected_options ? 'border-danger' : 'border-secondary') }}">
                                        <div class="card-header bg-light">
                                            <!-- Main Flex Header Wrapper -->
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                                                
                                                <!-- Left Side: Question Title & Verdict Status -->
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                                    <h6 class="mb-0">Question {{ $index + 1 }}</h6>
                                                    @if($isCorrect)
                                                        <span class="badge bg-success">Correct</span>
                                                    @elseif($hasAnswer)
                                                        <span class="badge bg-danger">Incorrect</span>
                                                    @else
                                                        <span class="badge bg-secondary">Unanswered</span>
                                                    @endif
                                                </div>
                                
                                                <!-- Right Side: Performance & Analytics Metrics Container -->
                                                <div class="d-flex flex-wrap align-items-center gap-2 ms-md-auto">
                                                    <span class="badge bg-info text-dark bg-opacity-10 border border-info">
                                                        <i class="bi bi-star-fill me-1 text-info"></i> Max: {{ $question->marks }} Marks
                                                    </span>
                                                    <span class="badge bg-success text-dark bg-opacity-10 border {{ $isCorrect ? 'border-success' : 'border-danger'}} ">
                                                        <i class="bi bi-award-fill me-1 {{ $isCorrect ? 'text-success' : 'text-danger'}}"></i> Obtained: {{ $answer->marks_obtained ?? 0 }}
                                                    </span>
                                                    <span class="badge bg-dark text-dark bg-opacity-10 border border-dark">
                                                        <i class="bi bi-clock me-1 text-dark"></i> {{ $answer->time_taken_seconds ?? 0 }} Sec
                                                    </span>
                                                    
                                                    @if($answer->is_marked_for_review)
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-bookmark-star-fill me-1"></i> Reviewed
                                                        </span>
                                                    @endif
                                
                                                    <span class="badge bg-secondary text-dark bg-opacity-10 border border-secondary">
                                                        <i class="bi bi-eye me-1 text-secondary"></i> 
                                                        {{ $answer->is_visited ? 'Visited' : 'Not Visited' }} 
                                                        @if($answer->visit_count > 0)
                                                            ({{ $answer->visit_count }}x)
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <!-- Question Text with Image -->
                                            <div class="question-content mb-3">
                                                @if($question->question_image)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $question->question_image) }}" 
                                                             alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                                                             class="img-fluid question_image" 
                                                             >
                                                    </div>
                                                @endif
                                                <p>{{ $question->question_text }}</p>
                                            </div>

                                            <!-- Options with Images -->
                                            @if($question->type == 'single_choice' || $question->type == 'multiple_choice')
                                                <div class="row">
                                                    @foreach($question->options as $option)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="p-2 rounded
                                                                {{ $option->is_correct ? 'bg-success text-white' : '' }}
                                                                {{ in_array($option->id, $answer->selected_options ?? []) && !$option->is_correct ? 'bg-danger text-white' : '' }}">
                                                                
                                                                <!-- Option Image -->
                                                                @if($option->option_image)
                                                                    <div class="mb-1">
                                                                        <img src="{{ asset('storage/' . $option->option_image) }}" 
                                                                             alt="{{ $option->option_image_alt ?? 'Option image' }}" 
                                                                             class="img-fluid option_image" 
                                                                             >
                                                                    </div>
                                                                @endif
                                                                
                                                                <!-- Option Text -->
                                                                <span>{{ $option->option_text }}</span>
                                                                
                                                                <!-- Correct Answer Icon -->
                                                                @if($option->is_correct)
                                                                    <i class="bi bi-check-circle-fill float-end"></i>
                                                                @endif
                                                                
                                                                <!-- Selected Answer Icon -->
                                                                @if(in_array($option->id, $answer->selected_options ?? []))
                                                                    <i class="bi bi-check-square-fill float-end me-2"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($question->type == 'numerical')
                                                <div class="alert alert-info">
                                                    <strong>Your Answer:</strong> {{ $answer->answer_text ?? 'Not answered' }}<br>
                                                    <strong>Correct Answer:</strong> 
                                                    @php
                                                        $correctOption = $question->options->where('is_correct', true)->first();
                                                    @endphp
                                                    {{ $correctOption->option_text ?? 'N/A' }}
                                                    @if($correctOption && $correctOption->option_image)
                                                        <br>
                                                        <img src="{{ asset('storage/' . $correctOption->option_image) }}" 
                                                             alt="{{ $correctOption->option_image_alt ?? 'Correct answer image' }}" 
                                                             class="img-fluid mt-1" 
                                                             style="max-height: 80px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- Explanation -->
                                            @if($question->explanation && ($answer->selected_options || $answer->answer_text))
                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <strong>Explanation:</strong><br>
                                                    {!! nl2br(e($question->explanation)) !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house"></i> Go to Dashboard
                        </a>
                        <a href="{{ route('student.exams.index') }}" class="btn btn-secondary">
                            <i class="bi bi-journal"></i> View All Exams
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .question-content img {
        max-height: 200px;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    
    .option-image-thumb {
        max-height: 60px;
        border: 1px solid #ddd;
        padding: 3px;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    
    .bg-success.text-white .option-image-thumb {
        border-color: #fff;
    }
    
    .bg-danger.text-white .option-image-thumb {
        border-color: #fff;
    }
    
    .score-circle {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: #f8f9fa;
        border: 5px solid #dee2e6;
    }
    
    .card.border-success {
        border-left: 4px solid #28a745 !important;
    }
    
    .card.border-danger {
        border-left: 4px solid #dc3545 !important;
    }
    
    .card.border-secondary {
        border-left: 4px solid #6c757d !important;
    }
    
    .question_image{
        min-height: 400px; width:auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;
    }
    
    .option_image{
        min-height: 200px; width:auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Question Analysis Chart
        const questionCanvas = document.getElementById('questionChart');
        if (questionCanvas) {
            const questionCtx = questionCanvas.getContext('2d');
            new Chart(questionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Correct', 'Incorrect', 'Unanswered'],
                    datasets: [{
                        data: [
                            {{ $studentExam->correct_answers }},
                            {{ $studentExam->incorrect_answers }},
                            {{ $studentExam->unanswered }}
                        ],
                        backgroundColor: ['#28a745', '#dc3545', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Score Chart
        const scoreCanvas = document.getElementById('scoreChart');
        if (scoreCanvas) {
            const scoreCtx = scoreCanvas.getContext('2d');
            new Chart(scoreCtx, {
                type: 'bar',
                data: {
                    labels: ['Your Score', 'Passing Score', 'Average Score', 'Highest Score'],
                    datasets: [{
                        label: 'Percentage',
                        data: [
                            {{ $studentExam->percentage }},
                            {{ $studentExam->exam->passing_percentage }},
                            {{ $studentExam->exam->average_score ?? 0 }},
                            {{ $studentExam->exam->highest_score ?? 0 }}
                        ],
                        backgroundColor: ['#007bff', '#ffc107', '#17a2b8', '#28a745']
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    });
</script>
@endpush