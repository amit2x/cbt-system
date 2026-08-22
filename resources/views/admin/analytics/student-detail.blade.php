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
                    <img src="{{ $student->avatar_url ?? asset('images/default-avatar.png') }}" class="rounded-circle mb-3" width="120" height="120">
                    <h5>{{ $student->name }}</h5>
                    <p class="text-muted">{{ $student->email }}</p>
                    <p class="mb-0">Member since: {{ $student->created_at ? $student->created_at->format('d M Y') : 'N/A' }}</p>
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
                    @php
                        $strengths = isset($profile['strengths']) && is_array($profile['strengths']) 
                            ? $profile['strengths'] 
                            : [];
                    @endphp
                    @if(!empty($strengths))
                        <div class="list-group">
                            @foreach($strengths as $strength)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $strength['topic'] ?? 'Unknown' }}</span>
                                    <span class="badge bg-success">{{ $strength['accuracy'] ?? 0 }}%</span>
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
                    @php
                        $weaknesses = isset($profile['weaknesses']) && is_array($profile['weaknesses']) 
                            ? $profile['weaknesses'] 
                            : [];
                    @endphp
                    @if(!empty($weaknesses))
                        <div class="list-group">
                            @foreach($weaknesses as $weakness)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $weakness['topic'] ?? 'Unknown' }}</span>
                                    <span class="badge bg-danger">{{ $weakness['accuracy'] ?? 0 }}%</span>
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
            @php
                $topicAnalysis = isset($profile['topic_analysis']) && is_array($profile['topic_analysis']) 
                    ? $profile['topic_analysis'] 
                    : [];
            @endphp
            @if(!empty($topicAnalysis))
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Topic</th>
                                <th class="text-center">Questions</th>
                                <th class="text-center">Correct</th>
                                <th class="text-center">Incorrect</th>
                                <th class="text-center" style="min-width: 140px;">Accuracy</th>
                                <th class="text-center">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topicAnalysis as $topic)
                                @php
                                    // Safely extract values with defaults
                                    $topicName = $topic['topic'] ?? $topic['name'] ?? 'Uncategorized';
                                    $total = (int) ($topic['total'] ?? 0);
                                    $correct = (int) ($topic['correct'] ?? 0);
                                    $incorrect = (int) ($topic['incorrect'] ?? ($total - $correct));
                                    $accuracy = (float) ($topic['accuracy'] ?? 0);
                                    
                                    // Determine color based on accuracy
                                    if ($accuracy >= 70) {
                                        $color = 'success';
                                        $icon = 'bi-check-circle-fill';
                                    } elseif ($accuracy >= 50) {
                                        $color = 'warning';
                                        $icon = 'bi-exclamation-triangle-fill';
                                    } else {
                                        $color = 'danger';
                                        $icon = 'bi-x-circle-fill';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $topicName }}</strong>
                                        @if($total > 0)
                                            <span class="badge bg-secondary ms-2">{{ $total }} Q</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $total }}</td>
                                    <td class="text-center text-success fw-bold">{{ $correct }}</td>
                                    <td class="text-center text-danger fw-bold">{{ $incorrect }}</td>
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
                                    <td>
                                        @if($total > 0)
                                            <div class="d-flex align-items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @php
                                                        $filled = $i <= ceil($accuracy / 20);
                                                    @endphp
                                                    <i class="bi bi-star{{ $filled ? '-fill' : '' }} text-{{ $filled ? 'warning' : 'secondary' }}"></i>
                                                @endfor
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @php
                            $totalAll = collect($topicAnalysis)->sum('total');
                            $correctAll = collect($topicAnalysis)->sum('correct');
                            $incorrectAll = collect($topicAnalysis)->sum(function($item) {
                                return $item['incorrect'] ?? ($item['total'] - $item['correct']);
                            });
                        @endphp
                        <tfoot class="table-light">
                            <tr>
                                <td><strong>Total</strong></td>
                                <td class="text-center fw-bold">{{ $totalAll }}</td>
                                <td class="text-center text-success fw-bold">{{ $correctAll }}</td>
                                <td class="text-center text-danger fw-bold">{{ $incorrectAll }}</td>
                                <td colspan="2"></td>
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
                    <small class="text-muted">Questions may not have tags assigned or student hasn't attempted any exams.</small>
                </div>
            @endif
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
                        @forelse($recentResults as $result)
                            <tr>
                                <td>{{ $result->created_at ? $result->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.analytics.exam-detail', $result->exam_id) }}">
                                        {{ $result->exam->title ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>{{ $result->obtained_marks ?? 0 }}/{{ $result->total_marks ?? 0 }}</td>
                                <td>
                                    <span class="badge bg-{{ ($result->percentage ?? 0) >= 60 ? 'success' : 'warning' }}">
                                        {{ round($result->percentage ?? 0, 1) }}%
                                    </span>
                                </td>
                                <td class="text-success">{{ $result->correct_answers ?? 0 }}</td>
                                <td class="text-danger">{{ $result->incorrect_answers ?? 0 }}</td>
                                <td>{{ isset($result->time_taken_seconds) ? gmdate('i:s', $result->time_taken_seconds) : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ ($result->status ?? '') == 'passed' ? 'success' : 'danger' }}">
                                        {{ ucfirst($result->status ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $student->id) }}?exam_id={{ $result->exam_id ?? 0 }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No recent results available</td>
                            </tr>
                        @endforelse
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
                @php
                    $predictedScore = $profile['prediction']['predicted_score'] ?? null;
                    $confidence = $profile['prediction']['confidence'] ?? 'N/A';
                    $totalExams = $profile['student_info']['total_exams'] ?? 0;
                @endphp
                @if($predictedScore)
                    <h2 class="display-4 text-primary">{{ $predictedScore }}%</h2>
                    <p class="lead">Predicted Score for Next Exam</p>
                    <p class="text-muted">Confidence: {{ ucfirst($confidence) }}</p>
                    <p>Based on {{ $totalExams }} previous exams</p>
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
        // Safely get trend data
        const trends = @json($profile['trends'] ?? []);
        const dates = trends.dates ?? [];
        const scores = trends.scores ?? [];
        const accuracies = trends.accuracies ?? [];

        // Trend Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Score %',
                        data: scores,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Accuracy %',
                        data: accuracies,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1,
                        fill: true
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

        // Metrics Chart
        const metricsCtx = document.getElementById('metricsChart');
        if (metricsCtx) {
            const performance = @json($profile['performance'] ?? []);
            const studentInfo = @json($profile['student_info'] ?? []);
            
            new Chart(metricsCtx.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: ['Average Score', 'Average Accuracy', 'Consistency', 'Improvement Rate', 'Pass Rate'],
                    datasets: [{
                        label: 'Performance Metrics',
                        data: [
                            performance.average_score ?? 0,
                            performance.average_accuracy ?? 0,
                            performance.consistency_score ?? 0,
                            Math.max(0, Math.min(100, (performance.improvement_rate ?? 0) + 50)),
                            studentInfo.pass_rate ?? 0
                        ],
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)'
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
        }
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