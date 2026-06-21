@extends('layouts.admin')

@section('title', 'Student Analytics - ' . $student->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $student->name }} - Analytics</h1>
        <div>
            <select class="form-select d-inline-block w-auto me-2" id="batchFilter" onchange="filterByBatch()">
                <option value="">All Batches</option>
                @foreach($batches as $batch)
                    <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                        {{ $batch->name }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn btn-primary" onclick="exportStudentReport()">
                <i class="bi bi-download"></i> Export Report
            </button>
            <a href="{{ route('admin.analytics.students') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Student Info -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <img src="{{ $student->avatar_url }}" class="rounded-circle mb-3" width="120" height="120">
                    <h5>{{ $student->name }}</h5>
                    <p class="text-muted">{{ $student->email }}</p>
                    <p class="mb-0">Member since: {{ $student->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $profile['student_info']['total_exams'] ?? 0 }}</h3>
                            <p class="mb-0">Exams Taken</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $profile['student_info']['exams_passed'] ?? 0 }}</h3>
                            <p class="mb-0">Exams Passed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $profile['student_info']['pass_rate'] ?? 0 }}%</h3>
                            <p class="mb-0">Pass Rate</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ $profile['performance']['average_score'] ?? 0 }}%</h3>
                            <p class="mb-0">Average Score</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>{{ $profile['performance']['average_accuracy'] ?? 0 }}%</h3>
                            <p class="mb-0">Average Accuracy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <canvas id="metricsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Strengths & Weaknesses -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Strengths</h6>
                </div>
                <div class="card-body">
                    @if(!empty($profile['strengths']))
                        <div class="list-group">
                            @foreach($profile['strengths'] as $strength)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $strength['topic'] }}</span>
                                    <span class="badge bg-success">{{ $strength['accuracy'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No significant strengths identified yet</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">Areas for Improvement</h6>
                </div>
                <div class="card-body">
                    @if(!empty($profile['weaknesses']))
                        <div class="list-group">
                            @foreach($profile['weaknesses'] as $weakness)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $weakness['topic'] }}</span>
                                    <span class="badge bg-danger">{{ $weakness['accuracy'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No weaknesses identified yet</p>
                    @endif
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
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Questions</th>
                            <th>Correct</th>
                            <th>Accuracy</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profile['topic_analysis'] as $topic)
                            <tr>
                                <td>{{ $topic['topic'] }}</td>
                                <td>{{ $topic['total'] }}</td>
                                <td class="text-success">{{ $topic['correct'] }}</td>
                                <td>{{ $topic['accuracy'] }}%</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $topic['accuracy'] >= 70 ? 'success' : ($topic['accuracy'] >= 50 ? 'warning' : 'danger') }}"
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

    <!-- Recent Results -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Exam Results</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Exam</th>
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
                        @foreach($recentResults as $result)
                            <tr>
                                <td>{{ $result->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.analytics.exam-detail', $result->exam_id) }}">
                                        {{ $result->exam->title }}
                                    </a>
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
                                    <a href="{{ route('admin.analytics.student-detail', $student->id) }}?exam_id={{ $result->exam_id }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Prediction Modal -->
<div class="modal fade" id="predictionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Performance Prediction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                @if($profile['prediction']['predicted_score'])
                    <h2 class="display-4 text-primary">{{ $profile['prediction']['predicted_score'] }}%</h2>
                    <p class="lead">Predicted Score for Next Exam</p>
                    <p class="text-muted">Confidence: {{ ucfirst($profile['prediction']['confidence']) }}</p>
                    <p>Based on {{ $profile['student_info']['total_exams'] }} previous exams</p>
                @else
                    <p class="lead">Not enough data for prediction</p>
                    <p>Need at least 3 exams to generate prediction</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($profile['trends']['dates'] ?? []),
                datasets: [{
                    label: 'Score %',
                    data: @json($profile['trends']['scores'] ?? []),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                }, {
                    label: 'Accuracy %',
                    data: @json($profile['trends']['accuracies'] ?? []),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Metrics Chart
        const metricsCtx = document.getElementById('metricsChart').getContext('2d');
        new Chart(metricsCtx, {
            type: 'radar',
            data: {
                labels: ['Average Score', 'Average Accuracy', 'Consistency', 'Improvement Rate', 'Pass Rate'],
                datasets: [{
                    label: 'Performance Metrics',
                    data: [
                        {{ $profile['performance']['average_score'] ?? 0 }},
                        {{ $profile['performance']['average_accuracy'] ?? 0 }},
                        {{ $profile['performance']['consistency_score'] ?? 0 }},
                        {{ max(0, min(100, ($profile['performance']['improvement_rate'] ?? 0) + 50)) }},
                        {{ $profile['student_info']['pass_rate'] ?? 0 }}
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

    function filterByBatch() {
        const batchId = $('#batchFilter').val();
        const url = new URL(window.location.href);
        if (batchId) {
            url.searchParams.set('batch_id', batchId);
        } else {
            url.searchParams.delete('batch_id');
        }
        window.location.href = url.toString();
    }

    function exportStudentReport() {
        // Implement export functionality
        const batchId = $('#batchFilter').val();
        let url = '{{ route("admin.analytics.export-student", $student->id) }}';
        if (batchId) {
            url += '?batch_id=' + batchId;
        }
        window.location.href = url;
    }

    function showPrediction() {
        $('#predictionModal').modal('show');
    }
</script>
@endpush
