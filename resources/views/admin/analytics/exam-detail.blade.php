@extends('layouts.admin')

@section('title', 'Exam Analytics - ' . $exam->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $exam->title }} - Analytics</h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="exportAnalytics()">
                <i class="bi bi-download"></i> Export Report
            </button>
            <a href="{{ route('admin.analytics.exams') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Exams
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Participants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['overview']['total_participants'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['overview']['average_score'] ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart-fill fa-2x text-gray-300"></i>
                        </div>
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
                                Pass Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['performance_metrics']['pass_rate'] ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
                        </div>
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
                                Avg Time Taken</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $analytics['performance_metrics']['average_time_taken'] ?? 0 }} min
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Score Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="scoreDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Performance -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Question-wise Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Difficulty</th>
                            <th>Attempts</th>
                            <th>Correct</th>
                            <th>Incorrect</th>
                            <th>Accuracy</th>
                            <th>Avg Time</th>
                            <th>Effectiveness</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questionPerformance as $performance)
                            @if($performance)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.analytics.question-detail', $performance['question_id']) }}">
                                        {{ Str::limit($performance['question_text'], 100) }}
                                    </a>
                                </td>
                                <td>{{ $performance['type'] }}</td>
                                <td>
                                    <span class="badge bg-{{ $performance['difficulty'] == 'hard' ? 'danger' : ($performance['difficulty'] == 'medium' ? 'warning' : 'success') }}">
                                        {{ ucfirst($performance['difficulty']) }}
                                    </span>
                                </td>
                                <td>{{ $performance['metrics']['total_attempts'] }}</td>
                                <td class="text-success">{{ $performance['metrics']['correct'] }}</td>
                                <td class="text-danger">{{ $performance['metrics']['incorrect'] }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ $performance['metrics']['accuracy_rate'] }}%">
                                            {{ $performance['metrics']['accuracy_rate'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $performance['metrics']['avg_time'] }}</td>
                                <td>
                                    <span class="badge bg-{{
                                        $performance['metrics']['effectiveness_level'] == 'good' ? 'success' :
                                        ($performance['metrics']['effectiveness_level'] == 'average' ? 'warning' : 'danger')
                                    }}">
                                        {{ ucfirst($performance['metrics']['effectiveness_level']) }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Student Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Correct</th>
                            <th>Incorrect</th>
                            <th>Time Taken</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $result)
                            <tr>
                                <td>#{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $result->student->avatar_url }}" class="rounded-circle me-2" width="30" height="30">
                                        <div>
                                            {{ $result->student->name }}
                                            <br>
                                            <small class="text-muted">{{ $result->student->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $result->obtained_marks }}/{{ $result->total_marks }}</td>
                                <td>
                                    <span class="badge bg-{{ $result->percentage >= 60 ? 'success' : 'warning' }}">
                                        {{ round($result->percentage, 1) }}%
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
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $result->student_id) }}?exam_id={{ $exam->id }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $students->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Score Distribution Chart
        const distCtx = document.getElementById('scoreDistributionChart').getContext('2d');
        new Chart(distCtx, {
            type: 'bar',
            data: {
                labels: ['90-100%', '75-89%', '60-74%', '40-59%', 'Below 40%'],
                datasets: [{
                    label: 'Number of Students',
                    data: [
                        {{ $analytics['distribution']['90_100'] ?? 0 }},
                        {{ $analytics['distribution']['75_89'] ?? 0 }},
                        {{ $analytics['distribution']['60_74'] ?? 0 }},
                        {{ $analytics['distribution']['40_59'] ?? 0 }},
                        {{ $analytics['distribution']['below_40'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#dc3545'
                    ]
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });

        // Performance Chart
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(perfCtx, {
            type: 'radar',
            data: {
                labels: ['Average Score', 'Pass Rate', 'Accuracy', 'Speed', 'Participation'],
                datasets: [{
                    label: 'Performance Metrics',
                    data: [
                        {{ $analytics['overview']['average_score'] ?? 0 }},
                        {{ $analytics['performance_metrics']['pass_rate'] ?? 0 }},
                        {{ $analytics['performance_metrics']['average_accuracy'] ?? 0 }},
                        {{ min(100, ($analytics['performance_metrics']['average_speed'] ?? 0) * 10) }},
                        {{ $analytics['overview']['participation_rate'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)'
                }]
            },
            options: {
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    });

    function exportAnalytics() {
        // Implement export functionality
        window.location.href = '{{ route("admin.analytics.export", $exam->id) }}';

    }
</script>
@endpush
