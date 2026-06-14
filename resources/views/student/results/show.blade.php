@extends('layouts.student')

@section('title', 'Result Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Result Details: {{ $result->exam->title }}</h1>
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
                {{ strtoupper($result->status) }}
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Score Circle -->
                <div class="col-md-3 text-center mb-4">
                    <div class="score-circle position-relative d-inline-block">
                        <canvas id="scoreCircle" width="150" height="150"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h2 class="mb-0 fw-bold">{{ round($result->percentage) }}%</h2>
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
                                    <h3 class="text-success mb-0">{{ $result->correct_answers }}</h3>
                                    <p class="mb-0">Correct</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-danger mb-0">{{ $result->incorrect_answers }}</h3>
                                    <p class="mb-0">Incorrect</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-secondary mb-0">{{ $result->unanswered }}</h3>
                                    <p class="mb-0">Unanswered</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-borderless">
                        <tr>
                            <td width="150">Total Marks:</td>
                            <td><strong>{{ $result->obtained_marks }} / {{ $result->total_marks }}</strong></td>
                        </tr>
                        <tr>
                            <td>Time Taken:</td>
                            <td><strong>{{ $result->formatted_time }}</strong></td>
                        </tr>
                        <tr>
                            <td>Accuracy:</td>
                            <td><strong>{{ $result->accuracy_percentage }}%</strong></td>
                        </tr>
                        <tr>
                            <td>Speed:</td>
                            <td><strong>{{ $result->speed_per_minute }} questions/min</strong></td>
                        </tr>
                        @if($result->rank)
                        <tr>
                            <td>Rank:</td>
                            <td>
                                <strong class="text-primary">#{{ $result->rank }}</strong>
                                <small class="text-muted">(out of {{ $result->total_participants }} students)</small>
                            </td>
                        </tr>
                        @endif
                        @if($result->percentile)
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
    @if(!empty($analysis['topic']))
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Topic-wise Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Questions</th>
                            <th>Correct</th>
                            <th>Incorrect</th>
                            <th>Unanswered</th>
                            <th>Accuracy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysis['topic'] as $topic)
                        <tr>
                            <td>{{ $topic['name'] }}</td>
                            <td>{{ $topic['total'] }}</td>
                            <td class="text-success">{{ $topic['correct'] }}</td>
                            <td class="text-danger">{{ $topic['incorrect'] }}</td>
                            <td class="text-secondary">{{ $topic['unanswered'] }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $topic['accuracy'] }}%">
                                        {{ $topic['accuracy'] }}%
                                    </div>
                                </div>
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
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Answer Review</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="showOnlyIncorrect">
                <label class="form-check-label" for="showOnlyIncorrect">Show only incorrect</label>
            </div>
        </div>
        <div class="card-body">
            @foreach($answers as $index => $answer)
                @php
                    $question = $answer->question;
                    $isCorrect = $answer->is_correct;
                    $hasAnswer = $answer->selected_options || $answer->answer_text;
                @endphp
                <div class="answer-card mb-4 p-3 rounded
                    {{ $isCorrect ? 'bg-success-light' : ($hasAnswer ? 'bg-danger-light' : 'bg-secondary-light') }}"
                    data-correct="{{ $isCorrect ? 'true' : 'false' }}"
                    data-has-answer="{{ $hasAnswer ? 'true' : 'false' }}">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">
                            Question {{ $index + 1 }}
                            @if($isCorrect)
                                <span class="badge bg-success ms-2">Correct</span>
                            @elseif($hasAnswer)
                                <span class="badge bg-danger ms-2">Incorrect</span>
                            @else
                                <span class="badge bg-secondary ms-2">Unanswered</span>
                            @endif
                        </h6>
                        <span class="badge bg-info">{{ $question->marks }} marks</span>
                    </div>

                    <div class="question-text mb-3">
                        {!! $question->question_text !!}
                    </div>

                    @if($question->type == 'single_choice' || $question->type == 'multiple_choice')
                        <div class="row">
                            @foreach($question->options as $option)
                                @php
                                    $isSelected = in_array($option->id, $answer->selected_options ?? []);
                                    $isCorrectOption = $option->is_correct;
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="p-2 rounded
                                        {{ $isCorrectOption ? 'bg-success text-white' : '' }}
                                        {{ $isSelected && !$isCorrectOption ? 'bg-danger text-white' : '' }}
                                        {{ $isSelected && $isCorrectOption ? 'bg-success text-white' : '' }}
                                        {{ !$isSelected && !$isCorrectOption ? 'bg-light' : '' }}">
                                        {{ $option->option_text }}
                                        @if($isCorrectOption)
                                            <i class="bi bi-check-circle-fill float-end"></i>
                                        @endif
                                        @if($isSelected)
                                            <i class="bi bi-check-square-fill float-end me-2"></i>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($question->type == 'numerical')
                        <div class="alert alert-info">
                            <strong>Your Answer:</strong> {{ $answer->answer_text ?? 'Not answered' }}<br>
                            <strong>Correct Answer:</strong> {{ $question->options->first()->option_text ?? 'N/A' }}
                        </div>
                    @endif

                    @if($question->explanation && ($hasAnswer || $isCorrect === false))
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Explanation:</strong><br>
                            {{ $question->explanation }}
                        </div>
                    @endif
                </div>
            @endforeach
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
    @media print {
        .btn, .form-switch, .modal, .footer {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Score Circle
        const scoreCtx = document.getElementById('scoreCircle').getContext('2d');
        new Chart(scoreCtx, {
            type: 'doughnut',
            data: {
                labels: ['Obtained', 'Remaining'],
                datasets: [{
                    data: [{{ $result->percentage }}, {{ 100 - $result->percentage }}],
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

        // Section Chart
        @if(!empty($analysis['sectional']))
        const sectionCtx = document.getElementById('sectionChart').getContext('2d');
        new Chart(sectionCtx, {
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
        @endif

        // Difficulty Chart
        @if(!empty($analysis['difficulty']))
        const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
        new Chart(difficultyCtx, {
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
        @endif

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
                }
            },
            error: function() {
                $('#compareContent').html('<p class="text-danger text-center">Error loading comparison</p>');
            }
        });
    }

    function displayComparison(data) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <h3 class="text-primary">${data.your_score}%</h3>
                            <p class="mb-0">Your Score</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <h3 class="text-info">${data.overall_average}%</h3>
                            <p class="mb-0">Overall Average</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6 class="alert-heading">Rank: #${data.rank} out of ${data.total_participants} students</h6>
                <p class="mb-0">You are in the top ${100 - data.percentile}% of participants.</p>
            </div>

            <h6 class="mt-4 mb-3">Top Performers</h6>
            <div class="list-group">
        `;

        data.toppers.forEach((topper, index) => {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-warning me-2">#${index + 1}</span>
                        ${topper.name}
                    </div>
                    <span class="badge bg-success">${topper.score}%</span>
                </div>
            `;
        });

        html += '</div>';

        $('#compareContent').html(html);
    }
</script>
@endpush
