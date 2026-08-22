@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div>
            <span class="badge bg-info me-2">
                <i class="bi bi-clock"></i> {{ now()->format('d M Y, h:i A') }}
            </span>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" onclick="window.location.reload()">
                <i class="bi bi-arrow-repeat"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Welcome Card -->
<div class="card shadow mb-4 text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
    <div class="card-body py-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h4>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-people me-1"></i> {{ $stats['total_students'] }} Students | 
                    <i class="bi bi-book me-1"></i> {{ $stats['total_courses'] }} Courses | 
                    <i class="bi bi-pencil-square me-1"></i> {{ $stats['total_exams'] }} Exams
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <i class="bi bi-bar-chart-fill display-4 opacity-50"></i>
            </div>
        </div>
    </div>
</div>


    <!-- Statistics Cards -->
    <div class="row">
        <!-- Students -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <i class="bi bi-people me-1"></i> Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_students'] }}
                            </div>
                            <small class="text-muted">Active: {{ $stats['total_students'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <i class="bi bi-book me-1"></i> Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_courses'] }}
                            </div>
                            <small class="text-muted">Active: {{ $stats['active_courses'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-book-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exams -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <i class="bi bi-pencil-square me-1"></i> Exams
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_exams'] }}
                            </div>
                            <small class="text-muted">Published: {{ $stats['published_exams'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil-square-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <i class="bi bi-bar-chart me-1"></i> Results
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_results'] }}
                            </div>
                            <small class="text-muted">Attempts: {{ $stats['total_attempts'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Row -->
    <div class="row">
        <!-- Average Score -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-success">
                                <i class="bi bi-trophy me-1"></i> Average Score
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($averageScore, 2) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pass Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-success">
                                <i class="bi bi-check-circle me-1"></i> Pass Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($passRate, 2) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engagement Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-info">
                                <i class="bi bi-graph-up me-1"></i> Engagement Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($engagementRate, 2) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up-arrow fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 text-primary">
                                <i class="bi bi-question-circle me-1"></i> Questions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_questions'] }}
                            </div>
                            <small class="text-muted">Active: {{ $stats['active_questions'] }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-question-circle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Daily Activity Chart -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Activity (Last 30 Days)</h6>
                    <span class="badge bg-info">Live</span>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Course Performance -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Performance</h6>
                </div>
                <div class="card-body">
                    @if($coursePerformance->count() > 0)
                        <div class="list-group">
                            @foreach($coursePerformance as $course)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ Str::limit($course['name'], 20) }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $course['exams'] }} exams • {{ $course['batches'] }} batches
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $course['avg_score'] >= 70 ? 'success' : ($course['avg_score'] >= 50 ? 'warning' : 'danger') }}">
                                                {{ $course['avg_score'] }}%
                                            </span>
                                            <br>
                                            <small class="text-muted">Pass Rate: {{ $course['pass_rate'] }}%</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $course['avg_score'] >= 70 ? 'success' : ($course['avg_score'] >= 50 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $course['avg_score'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No course data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trend (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Students -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Students</h6>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentStudents->count() > 0)
                        @foreach($recentStudents as $student)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $student->avatar_url }}" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <strong>{{ $student->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $student->email }}</small>
                                    </div>
                                </div>
                                <span class="text-muted small">{{ $student->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No students registered yet</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Exams -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Exams</h6>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentExams->count() > 0)
                        @foreach($recentExams as $exam)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div>
                                    <strong>{{ $exam->title }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-book"></i> {{ $exam->course->title ?? 'N/A' }} |
                                        <i class="bi bi-clock"></i> {{ $exam->duration_minutes }} min
                                    </small>
                                </div>
                                <span class="badge bg-{{ $exam->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($exam->status) }}
                                </span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No exams created yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers and Batch Performance -->
    <div class="row">
        <!-- Top Performers -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performers</h6>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-primary">View Reports</a>
                </div>
                <div class="card-body">
                    @if($topPerformers->count() > 0)
                        @foreach($topPerformers->take(5) as $result)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <span class="badge bg-warning">#{{ $loop->iteration }}</span>
                                    </div>
                                    <div>
                                        <strong>{{ $result->student->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $result->exam->title }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $result->percentage >= 70 ? 'success' : 'warning' }}">
                                        {{ $result->percentage }}%
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $result->status }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No results available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Batch Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Performance</h6>
                    <a href="{{ route('admin.batches.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($batchPerformance->count() > 0)
                        @foreach($batchPerformance as $batch)
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $batch->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $batch->course->title ?? 'N/A' }} • 
                                            {{ $batch->students_count }} students • 
                                            {{ $batch->exams_count }} exams
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $batch->average_score >= 70 ? 'success' : ($batch->average_score >= 50 ? 'warning' : 'danger') }}">
                                            {{ round($batch->average_score, 2) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-{{ $batch->average_score >= 70 ? 'success' : ($batch->average_score >= 50 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $batch->average_score }}%"></div>
                                </div>
                            </div>
                        @endforeach
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
    document.addEventListener('DOMContentLoaded', (event) => {

        // Daily Activity Chart
        const dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: @json($dailyActivity['labels']),
                datasets: [{
                    label: 'Attempts',
                    data: @json($dailyActivity['attempts']),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Average Score',
                    data: @json($dailyActivity['avg_score']),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
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

        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyTrend['labels']),
                datasets: [{
                    label: 'Attempts',
                    data: @json($monthlyTrend['attempts']),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: '#4e73df',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Average Score',
                    data: @json($monthlyTrend['avg_score']),
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: '#1cc88a',
                    borderWidth: 1,
                    yAxisID: 'y1',
                    type: 'line'
                }]
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
    });
</script>
@endpush