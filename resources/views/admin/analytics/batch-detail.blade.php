@extends('layouts.admin')

@section('title', 'Batch Analytics - ' . $batch->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $batch->name }} - Analytics</h1>
        <div>
            <div class="input-group me-2 d-inline-flex w-auto">
                <input type="date" class="form-control" id="dateFrom" value="{{ $dateFrom }}">
                <span class="input-group-text">to</span>
                <input type="date" class="form-control" id="dateTo" value="{{ $dateTo }}">
                <button class="btn btn-primary" onclick="applyDateFilter()">
                    <i class="bi bi-filter"></i> Apply
                </button>
            </div>
            <button type="button" class="btn btn-success" onclick="exportBatchReport()">
                <i class="bi bi-download"></i> Export Report
            </button>
            <a href="{{ route('admin.analytics.batches') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if($report)
    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['batch_info']['total_students'] }}
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
                                Active Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['batch_info']['active_students'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check-fill fa-2x text-gray-300"></i>
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
                                Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['overall_performance']['average_score'] }}%
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pass Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $report['overall_performance']['pass_rate'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy-fill fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Student Performance Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Breakdown -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Exam-wise Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Attempts</th>
                            <th>Average Score</th>
                            <th>Pass Rate</th>
                            <th>Top Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['exam_breakdown'] as $exam)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.analytics.exam-detail', $exam['exam_id']) }}">
                                        {{ $exam['exam_title'] }}
                                    </a>
                                </td>
                                <td>{{ $exam['attempts'] }}</td>
                                <td>{{ $exam['average_score'] }}%</td>
                                <td>{{ $exam['pass_rate'] }}%</td>
                                <td>
                                    @php
                                        $topResult = \App\Models\Result::where('exam_id', $exam['exam_id'])
                                            ->where('batch_id', $batch->id)
                                            ->orderBy('percentage', 'desc')
                                            ->first();
                                    @endphp
                                    @if($topResult)
                                        {{ round($topResult->percentage, 1) }}%
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.exam-detail', $exam['exam_id']) }}"
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

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Top Performers</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($report['top_performers'] as $performer)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $performer['student_name'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $performer['exams_taken'] }} exams</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success fs-6">{{ $performer['average_score'] }}%</span>
                                    <br>
                                    <small>Best: {{ $performer['best_score'] }}%</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Need Improvement</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($report['needs_improvement'] as $student)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $student['student_name'] }}</strong>
                                    <br>
                                    <small class="text-muted">Trend: {{ ucfirst($student['trend']) }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning fs-6">{{ $student['average_score'] }}%</span>
                                    <br>
                                    <small>{{ $student['exams_taken'] }} exams</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Students Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exams Taken</th>
                            <th>Average Score</th>
                            <th>Best Score</th>
                            <th>Pass Rate</th>
                            <th>Last Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php
                                $studentResults = $student->results->where('batch_id', $batch->id);
                                $avgScore = $studentResults->avg('percentage');
                                $bestScore = $studentResults->max('percentage');
                                $passCount = $studentResults->where('status', 'passed')->count();
                                $passRate = $studentResults->count() > 0 ? round(($passCount / $studentResults->count()) * 100, 1) : 0;
                                $lastActive = $studentResults->sortByDesc('created_at')->first();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $student->avatar_url }}" class="rounded-circle me-2" width="30" height="30">
                                        <div>
                                            <strong>{{ $student->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $student->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $studentResults->count() }}</td>
                                <td>
                                    <span class="badge bg-{{ $avgScore >= 60 ? 'success' : ($avgScore >= 40 ? 'warning' : 'danger') }}">
                                        {{ $avgScore ? round($avgScore, 1) : 0 }}%
                                    </span>
                                </td>
                                <td>{{ $bestScore ? round($bestScore, 1) : 0 }}%</td>
                                <td>{{ $passRate }}%</td>
                                <td>
                                    @if($lastActive)
                                        {{ $lastActive->created_at->format('d M Y') }}
                                    @else
                                        Never
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.student-detail', $student->id) }}?batch_id={{ $batch->id }}"
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
                {{ $students->links() }}
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No data available for the selected date range.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        @if($report)
        // Distribution Chart
        const distCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distCtx, {
            type: 'pie',
            data: {
                labels: ['Above 80%', '60-79%', '40-59%', 'Below 40%', 'Inactive'],
                datasets: [{
                    data: [
                        {{ $report['student_performance_distribution']['above_80'] }},
                        {{ $report['student_performance_distribution']['60_to_79'] }},
                        {{ $report['student_performance_distribution']['40_to_59'] }},
                        {{ $report['student_performance_distribution']['below_40'] }},
                        {{ $report['student_performance_distribution']['inactive'] }}
                    ],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6c757d']
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

        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json(collect($report['trends'])->pluck('date')),
                datasets: [{
                    label: 'Average Score',
                    data: @json(collect($report['trends'])->pluck('average')),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                }, {
                    label: 'Number of Attempts',
                    data: @json(collect($report['trends'])->pluck('count')),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.1,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Score %'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Attempts'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        @endif
    });

    function applyDateFilter() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const url = new URL(window.location.href);
        if (dateFrom) url.searchParams.set('date_from', dateFrom);
        if (dateTo) url.searchParams.set('date_to', dateTo);
        window.location.href = url.toString();
    }

    function exportBatchReport() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        let url = '{{ route("admin.analytics.export-batch", $batch->id) }}';
        const params = new URLSearchParams();
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (params.toString()) url += '?' + params.toString();
        window.location.href = url;
    }
</script>
@endpush
