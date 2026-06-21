@extends('layouts.student')

@section('title', 'My Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Analytics</h1>
        <div>
            <span class="badge bg-info fs-6 me-2">
                <i class="bi bi-clock"></i> Last 30 days
            </span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Exams Taken</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_exams'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil-square fs-2 text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">Passed: {{ $stats['passed'] }}</span>
                        <span class="text-danger ms-2">Failed: {{ $stats['failed'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy fs-2 text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">Best: {{ $stats['highest_score'] }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Accuracy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_accuracy'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bullseye fs-2 text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-info">Correct: {{ $stats['total_correct'] }}</span>
                        <span class="text-danger ms-2">Incorrect: {{ $stats['total_incorrect'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pass Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pass_rate'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-muted">Time: {{ $stats['total_time_spent'] }} hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Performance -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Performance</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Difficulty Analysis</h6>
                </div>
                <div class="card-body">
                    <canvas id="difficultyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject & Type Performance -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subject-wise Performance</h6>
                </div>
                <div class="card-body">
                    @if(count($subjectPerformance) > 0)
                        <div class="list-group">
                            @foreach($subjectPerformance as $subject)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $subject['name'] }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $subject['total'] }} exams • {{ $subject['correct'] }} correct
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $subject['avg_score'] >= 70 ? 'success' : ($subject['avg_score'] >= 50 ? 'warning' : 'danger') }}">
                                                {{ $subject['avg_score'] }}%
                                            </span>
                                            <br>
                                            <small>Accuracy: {{ $subject['accuracy'] }}%</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $subject['avg_score'] >= 70 ? 'success' : ($subject['avg_score'] >= 50 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $subject['avg_score'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No subject data available</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Question Type Performance</h6>
                </div>
                <div class="card-body">
                    @if(count($typePerformance) > 0)
                        <div class="list-group">
                            @foreach($typePerformance as $type)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $type['type'] }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $type['total'] }} questions • {{ $type['correct'] }} correct
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $type['accuracy'] >= 70 ? 'success' : ($type['accuracy'] >= 50 ? 'warning' : 'danger') }}">
                                                {{ $type['accuracy'] }}%
                                            </span>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $type['accuracy'] >= 70 ? 'success' : ($type['accuracy'] >= 50 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $type['accuracy'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No question type data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Exams -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Recent Exam Results</h6>
            <a href="{{ route('student.results.index') }}" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Course</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Correct</th>
                            <th>Incorrect</th>
                            <th>Time Taken</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentExams as $result)
                            <tr>
                                <td>{{ $result->exam->title }}</td>
                                <td>{{ $result->exam->course->title ?? 'N/A' }}</td>
                                <td>{{ $result->obtained_marks }}/{{ $result->total_marks }}</td>
                                <td>
                                    <span class="badge bg-{{ $result->percentage >= 60 ? 'success' : 'warning' }}">
                                        {{ $result->percentage }}%
                                    </span>
                                </td>
                                <td class="text-success">{{ $result->correct_answers }}</td>
                                <td class="text-danger">{{ $result->incorrect_answers }}</td>
                                <td>{{ gmdate('i:s', $result->time_taken_seconds) }}</td>
                                <td>
                                    <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                        {{ ucfirst($result->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Performance Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($performanceTrend['labels']),
                datasets: [
                    {
                        label: 'Score %',
                        data: @json($performanceTrend['scores']),
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4
                    },
                    {
                        label: 'Accuracy %',
                        data: @json($performanceTrend['accuracy']),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage %'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Performance Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyData['labels']),
                datasets: [
                    {
                        label: 'Attempts',
                        data: @json($monthlyData['attempts']),
                        backgroundColor: 'rgba(78, 115, 223, 0.7)',
                        borderColor: '#4e73df',
                        borderWidth: 1,
                        order: 1
                    },
                    {
                        label: 'Average Score',
                        data: @json($monthlyData['avg_score']),
                        type: 'line',
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        order: 0,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Attempts'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        max: 100,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Average Score %'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Difficulty Chart
        const diffCtx = document.getElementById('difficultyChart').getContext('2d');
        new Chart(diffCtx, {
            type: 'bar',
            data: {
                labels: @json(collect($difficultyPerformance)->pluck('difficulty')),
                datasets: [
                    {
                        label: 'Correct',
                        data: @json(collect($difficultyPerformance)->pluck('correct')),
                        backgroundColor: 'rgba(28, 200, 138, 0.7)',
                        borderColor: '#1cc88a',
                        borderWidth: 1
                    },
                    {
                        label: 'Incorrect',
                        data: @json(collect($difficultyPerformance)->pluck('incorrect')),
                        backgroundColor: 'rgba(231, 74, 59, 0.7)',
                        borderColor: '#e74a3b',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1,
                        title: {
                            display: true,
                            text: 'Number of Questions'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush