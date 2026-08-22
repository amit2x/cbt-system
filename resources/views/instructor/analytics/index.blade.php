@extends('layouts.instructor')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Analytics Dashboard</h1>
        <div>
            <a href="{{ route('instructor.analytics.exams') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil-square"></i> Exam Analytics
            </a>
            <a href="{{ route('instructor.analytics.students') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-people"></i> Student Analytics
            </a>
            <a href="{{ route('instructor.analytics.batches') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-grid-3x3-gap-fill"></i> Batch Analytics
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Total Exams -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Exams</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_exams'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-pencil-square fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Students -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Students</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avg Score -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Avg Score</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-bar-chart fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass Rate -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pass Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pass_rate'] }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-trophy fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trend (Last 30 Days)</h6>
                </div>
                <div class="card-body" style="height:300px;">
                    <canvas id="trendChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Exams and Top Students -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Exams</h6>
                    <a href="{{ route('instructor.analytics.exams') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($exams->count() > 0)
                        <div class="list-group">
                            @foreach($exams as $exam)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $exam['title'] }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-grid-3x3-gap-fill"></i> {{ $exam['batch'] }} |
                                                <i class="bi bi-people"></i> {{ $exam['attempts'] }} attempts
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $exam['avg_score'] >= 70 ? 'success' : ($exam['avg_score'] >= 50 ? 'warning' : 'danger') }}">
                                                {{ $exam['avg_score'] }}%
                                            </span>
                                            <br>
                                            <small class="text-muted">Pass Rate: {{ $exam['pass_rate'] }}%</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $exam['avg_score'] >= 70 ? 'success' : ($exam['avg_score'] >= 50 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $exam['avg_score'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No exams found</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Students</h6>
                    <a href="{{ route('instructor.analytics.students') }}" class="btn btn-sm btn-success">View All</a>
                </div>
                <div class="card-body">
                    @if($topStudents->count() > 0)
                        <div class="list-group">
                            @foreach($topStudents as $student)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $student['name'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $student['exams'] }} exams taken</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">{{ $student['avg_score'] }}%</span>
                                            <br>
                                            <small class="text-muted">Best: {{ $student['best_score'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No student data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Performance -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Performance</h6>
                    <a href="{{ route('instructor.analytics.batches') }}" class="btn btn-sm btn-info">View All</a>
                </div>
                <div class="card-body">
                    @if($batchPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Batch</th>
                                        <th>Course</th>
                                        <th>Students</th>
                                        <th>Exams</th>
                                        <th>Avg Score</th>
                                        <th>Pass Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batchPerformance as $batch)
                                        <tr>
                                            <td><strong>{{ $batch['name'] }}</strong></td>
                                            <td>{{ $batch['course'] }}</td>
                                            <td>{{ $batch['students'] }}</td>
                                            <td>{{ $batch['exams'] }}</td>
                                            <td>
                                                <span class="badge bg-{{ $batch['avg_score'] >= 70 ? 'success' : ($batch['avg_score'] >= 50 ? 'warning' : 'danger') }}">
                                                    {{ $batch['avg_score'] }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $batch['pass_rate'] >= 70 ? 'success' : ($batch['pass_rate'] >= 50 ? 'warning' : 'danger') }}">
                                                    {{ $batch['pass_rate'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No batch data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($trend['labels']),
                datasets: [
                    {
                        label: 'Attempts',
                        data: @json($trend['attempts']),
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Average Score',
                        data: @json($trend['avg_score']),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
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
                            text: 'Score %'
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
    });
</script>
@endpush