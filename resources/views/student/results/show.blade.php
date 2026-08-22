@extends('layouts.student')

@section('title', 'Result Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Result Details: {{ $result->exam->title ?? 'N/A' }}</h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('student.results.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Results
            </a>
        </div>
    </div>

    <!-- Result Summary Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Exam Summary</h6>
            <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }} fs-6">
                {{ strtoupper($result->status ?? 'N/A') }}
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Score Circle -->
                <div class="col-md-3 text-center mb-4">
                    <div class="score-circle position-relative d-inline-block">
                        <canvas id="scoreCircle" width="150" height="150"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h2 class="mb-0 fw-bold">{{ round($result->percentage ?? 0) }}%</h2>
                            <small class="text-muted">Score</small>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-success mb-0">{{ $result->correct_answers ?? 0 }}</h3>
                                    <p class="mb-0">Correct</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-danger mb-0">{{ $result->incorrect_answers ?? 0 }}</h3>
                                    <p class="mb-0">Incorrect</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-secondary mb-0">{{ $result->unanswered ?? 0 }}</h3>
                                    <p class="mb-0">Unanswered</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-borderless">
                        <tr>
                            <td width="150">Total Marks:</td>
                            <td><strong>{{ $result->obtained_marks ?? 0 }} / {{ $result->total_marks ?? 0 }}</strong></td>
                        </tr>
                        <tr>
                            <td>Time Taken:</td>
                            <td><strong>{{ $result->formatted_time ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Accuracy:</td>
                            <td><strong>{{ $result->accuracy_percentage ?? 0 }}%</strong></td>
                        </tr>
                        <tr>
                            <td>Speed:</td>
                            <td><strong>{{ $result->speed_per_minute ?? 0 }} questions/min</strong></td>
                        </tr>
                        @if($result->rank ?? false)
                        <tr>
                            <td>Rank:</td>
                            <td>
                                <strong class="text-primary">#{{ $result->rank }}</strong>
                                <small class="text-muted">(out of {{ $result->total_participants ?? 0 }} students)</small>
                            </td>
                        </tr>
                        @endif
                        @if($result->percentile ?? false)
                        <tr>
                            <td>Percentile:</td>
                            <td><strong>{{ $result->percentile }}%</strong></td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Section-wise Performance</h6>
                </div>
                <div class="card-body">
                    <canvas id="sectionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Difficulty Analysis</h6>
                </div>
                <div class="card-body">
                    <canvas id="difficultyChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Topic Analysis -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Topic-wise Performance</h6>
    </div>
    <div class="card-body">
        @if(!empty($analysis['topic']) && count($analysis['topic']) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Topic</th>
                            <th class="text-center">Questions</th>
                            <th class="text-center">Correct</th>
                            <th class="text-center">Incorrect</th>
                            <th class="text-center">Unanswered</th>
                            <th class="text-center" style="min-width: 140px;">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysis['topic'] as $topic)
                            @php
                                $total = (int) ($topic['total'] ?? 0);
                                $correct = (int) ($topic['correct'] ?? 0);
                                $incorrect = (int) ($topic['incorrect'] ?? 0);
                                $unanswered = (int) ($topic['unanswered'] ?? 0);
                                $accuracy = (float) ($topic['accuracy'] ?? 0);
                                $name = $topic['name'] ?? 'Uncategorized';
                                
                                if ($accuracy >= 70) {
                                    $color = 'success';
                                    $icon = 'bi-check-circle-fill';
                                } elseif ($accuracy >= 40) {
                                    $color = 'warning';
                                    $icon = 'bi-exclamation-triangle-fill';
                                } else {
                                    $color = 'danger';
                                    $icon = 'bi-x-circle-fill';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $name }}</strong>
                                    @if($total > 0)
                                        <span class="badge bg-secondary ms-2">{{ $total }} Q</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $total }}</td>
                                <td class="text-center text-success fw-bold">{{ $correct }}</td>
                                <td class="text-center text-danger fw-bold">{{ $incorrect }}</td>
                                <td class="text-center text-secondary">{{ $unanswered }}</td>
                                <td>
                                    @if($total > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 22px; background-color: #e9ecef; border-radius: 4px;">
                                                <div class="progress-bar progress-bar-striped bg-{{ $color }}"
                                                     role="progressbar"
                                                     style="width: {{ $accuracy }}%; transition: width 0.5s ease;"
                                                     aria-valuenow="{{ $accuracy }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    {{ $accuracy }}%
                                                </div>
                                            </div>
                                            <i class="bi {{ $icon }} text-{{ $color }}"></i>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @php
                        $totalAll = collect($analysis['topic'])->sum('total');
                        $correctAll = collect($analysis['topic'])->sum('correct');
                        $incorrectAll = collect($analysis['topic'])->sum('incorrect');
                        $unansweredAll = collect($analysis['topic'])->sum('unanswered');
                    @endphp
                    <tfoot class="table-light">
                        <tr>
                            <td><strong>Total</strong></td>
                            <td class="text-center fw-bold">{{ $totalAll }}</td>
                            <td class="text-center text-success fw-bold">{{ $correctAll }}</td>
                            <td class="text-center text-danger fw-bold">{{ $incorrectAll }}</td>
                            <td class="text-center text-secondary">{{ $unansweredAll }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <div class="mb-2">
                    <i class="bi bi-tags fs-1 text-muted"></i>
                </div>
                <p class="text-muted mb-0">No topic analysis data available.</p>
                <small class="text-muted">Questions may not have tags assigned.</small>
            </div>
        @endif
    </div>
</div>

    <!-- Answer Review -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Answer Review</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="showOnlyIncorrect">
                <label class="form-check-label" for="showOnlyIncorrect">Show only incorrect</label>
            </div>
        </div>
        <div class="card-body">
            @if($answers && count($answers) > 0)
                @foreach($answers as $index => $answer)
                    @php
                        $question = $answer->question ?? null;
                        if (!$question) continue;
                        $isCorrect = $answer->is_correct ?? false;
                        $hasAnswer = $answer->selected_options || $answer->answer_text;
                    @endphp
                    <div class="answer-card mb-4 p-3 rounded
                        {{ $isCorrect ? 'bg-success-light' : ($hasAnswer ? 'bg-danger-light' : 'bg-secondary-light') }}"
                        data-correct="{{ $isCorrect ? 'true' : 'false' }}"
                        data-has-answer="{{ $hasAnswer ? 'true' : 'false' }}">

                        <!-- Main Flex Header Wrapper -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-2 border-bottom">
                            
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
                                    <i class="bi bi-star-fill me-1 text-info"></i> Max: {{ $question->marks ?? 0 }} Marks
                                </span>
                                <span class="badge bg-success text-dark bg-opacity-10 border {{ $isCorrect ? 'border-success' : 'border-danger'}} ">
                                    <i class="bi bi-award-fill me-1 {{ $isCorrect ? 'text-success' : 'text-danger'}}"></i> Obtained: {{ $answer->marks_obtained ?? 0 }}
                                </span>
                                <span class="badge bg-dark text-dark bg-opacity-10 border border-dark">
                                    <i class="bi bi-clock me-1 text-dark"></i> {{ $answer->time_taken_seconds ?? 0 }} Sec
                                </span>
                                
                                @if($answer->is_marked_for_review ?? false)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-bookmark-star-fill me-1"></i> Review
                                    </span>
                                @endif
            
                                <span class="badge bg-secondary text-dark bg-opacity-10 border border-secondary">
                                    <i class="bi bi-eye me-1 text-secondary"></i> 
                                    {{ ($answer->is_visited ?? false) ? 'Visited' : 'Not Visited' }} 
                                    @if(($answer->visit_count ?? 0) > 0)
                                        ({{ $answer->visit_count }}x)
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Question Text with Image -->
                        <div class="question-text mb-3">
                            @if($question->question_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $question->question_image) }}" 
                                         alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                                         class="img-fluid question_image" 
                                         >
                                </div>
                            @endif
                            <p>{!! $question->question_text ?? 'No question text available' !!}</p>
                        </div>

                        @if($question->type == 'single_choice' || $question->type == 'multiple_choice')
                            <div class="row">
                                @if($question->options && count($question->options) > 0)
                                    @foreach($question->options as $option)
                                        @php
                                            $isSelected = in_array($option->id, $answer->selected_options ?? []);
                                            $isCorrectOption = $option->is_correct ?? false;
                                        @endphp
                                        <div class="col-md-6 mb-2">
                                            <div class="p-2 rounded
                                                {{ $isCorrectOption ? 'bg-success text-white' : '' }}
                                                {{ $isSelected && !$isCorrectOption ? 'bg-danger text-white' : '' }}
                                                {{ $isSelected && $isCorrectOption ? 'bg-success text-white' : '' }}
                                                {{ !$isSelected && !$isCorrectOption ? 'bg-light' : '' }}">
                                                
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
                                                <span>{{ $option->option_text ?? '' }}</span>
                                                
                                                <!-- Correct Answer Icon -->
                                                @if($isCorrectOption)
                                                    <i class="bi bi-check-circle-fill float-end"></i>
                                                @endif
                                                
                                                <!-- Selected Answer Icon -->
                                                @if($isSelected)
                                                    <i class="bi bi-check-square-fill float-end me-2"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <p class="text-muted">No options available for this question.</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($question->type == 'numerical')
                            @php
                                $correctOption = $question->options ? $question->options->where('is_correct', true)->first() : null;
                            @endphp
                            <div class="alert alert-info">
                                <strong>Your Answer:</strong> 
                                @if($answer->answer_text)
                                    {{ $answer->answer_text }}
                                @elseif($answer->selected_options && count($answer->selected_options) > 0)
                                    {{ $answer->selected_options[0] }}
                                @else
                                    Not answered
                                @endif
                                <br>
                                <strong>Correct Answer:</strong> 
                                @if($correctOption)
                                    {{ $correctOption->option_text ?? 'N/A' }}
                                    @if($correctOption->option_image)
                                        <br>
                                        <img src="{{ asset('storage/' . $correctOption->option_image) }}" 
                                             alt="{{ $correctOption->option_image_alt ?? 'Correct answer image' }}" 
                                             class="img-fluid mt-1" 
                                             style="max-height: 80px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        @endif

                        @if($question->explanation && ($hasAnswer || $isCorrect === false))
                            <div class="mt-3 p-3 bg-light rounded">
                                <strong>Explanation:</strong><br>
                                {!! nl2br(e($question->explanation)) !!}
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <p class="text-muted">No answers available for this exam.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Compare Modal -->
<div class="modal fade" id="compareModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compare with Peers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="compareContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-success-light {
        background-color: #d4edda;
        border-left: 4px solid #28a745;
    }
    .bg-danger-light {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
    }
    .bg-secondary-light {
        background-color: #e2e3e5;
        border-left: 4px solid #6c757d;
    }
    .answer-card {
        transition: all 0.3s;
    }
    .answer-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .question-text img {
        max-height: 200px;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .score-circle {
        display: inline-block;
    }
    @media print {
        .btn, .form-switch, .modal, .footer {
            display: none !important;
        }
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Score Circle
        const scoreCanvas = document.getElementById('scoreCircle');
        if (scoreCanvas) {
            const scoreCtx = scoreCanvas.getContext('2d');
            const percentage = {{ $result->percentage ?? 0 }};
            new Chart(scoreCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Obtained', 'Remaining'],
                    datasets: [{
                        data: [percentage, 100 - percentage],
                        backgroundColor: ['#28a745', '#e9ecef'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    responsive: false,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    }
                }
            });
        }

        // Section Chart
        const sectionCtx = document.getElementById('sectionChart');
        if (sectionCtx) {
            @if(!empty($analysis['sectional']))
            const ctx = sectionCtx.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json(collect($analysis['sectional'])->pluck('section_name')),
                    datasets: [
                        {
                            label: 'Correct',
                            data: @json(collect($analysis['sectional'])->pluck('correct')),
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'Incorrect',
                            data: @json(collect($analysis['sectional'])->pluck('incorrect')),
                            backgroundColor: '#dc3545'
                        },
                        {
                            label: 'Unanswered',
                            data: @json(collect($analysis['sectional'])->pluck('unanswered')),
                            backgroundColor: '#6c757d'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });
            @else
            sectionCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No section data available</p>';
            @endif
        }

        // Difficulty Chart
        const difficultyCtx = document.getElementById('difficultyChart');
        if (difficultyCtx) {
            @if(!empty($analysis['difficulty']))
            const ctx = difficultyCtx.getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Easy', 'Medium', 'Hard'],
                    datasets: [{
                        label: 'Accuracy %',
                        data: [
                            {{ $analysis['difficulty']['easy']['accuracy'] ?? 0 }},
                            {{ $analysis['difficulty']['medium']['accuracy'] ?? 0 }},
                            {{ $analysis['difficulty']['hard']['accuracy'] ?? 0 }}
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
            @else
            difficultyCtx.parentElement.innerHTML = '<p class="text-center text-muted py-4">No difficulty data available</p>';
            @endif
        }

        // Filter answers
        $('#showOnlyIncorrect').on('change', function() {
            if ($(this).is(':checked')) {
                $('.answer-card[data-correct="true"]').hide();
                $('.answer-card[data-correct="false"][data-has-answer="true"]').show();
            } else {
                $('.answer-card').show();
            }
        });
    });

    function compareWithPeers() {
        $('#compareModal').modal('show');

        $.ajax({
            url: '{{ route("student.results.compare", $result) }}',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    displayComparison(response.data);
                } else {
                    $('#compareContent').html('<p class="text-danger text-center">' + (response.message || 'Error loading comparison') + '</p>');
                }
            },
            error: function(xhr) {
                $('#compareContent').html('<p class="text-danger text-center">Error loading comparison. Please try again.</p>');
                console.error('Compare error:', xhr.responseText);
            }
        });
    }

    function displayComparison(data) {
        if (!data) {
            $('#compareContent').html('<p class="text-muted text-center">No comparison data available.</p>');
            return;
        }

        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <h3 class="text-primary">${data.your_score || 0}%</h3>
                            <p class="mb-0">Your Score</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <h3 class="text-info">${data.overall_average || 0}%</h3>
                            <p class="mb-0">Overall Average</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6 class="alert-heading">Rank: #${data.rank || 'N/A'} out of ${data.total_participants || 0} students</h6>
                <p class="mb-0">You are in the top ${100 - (data.percentile || 0)}% of participants.</p>
            </div>
        `;

        if (data.toppers && data.toppers.length > 0) {
            html += `<h6 class="mt-4 mb-3">Top Performers</h6>
            <div class="list-group">`;

            data.toppers.forEach((topper, index) => {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning me-2">#${index + 1}</span>
                            ${topper.name || 'Anonymous'}
                        </div>
                        <span class="badge bg-success">${topper.score || 0}%</span>
                    </div>
                `;
            });

            html += '</div>';
        }

        $('#compareContent').html(html);
    }
</script>
@endpush