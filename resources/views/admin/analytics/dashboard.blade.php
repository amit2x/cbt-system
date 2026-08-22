@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid p-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Analytics Dashboard</h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="generateReport()">
                <i class="bi bi-file-earmark-plus"></i> Generate Report
            </button>
            <a href="{{ route('admin.analytics.reports') }}" class="btn btn-info">
                <i class="bi bi-files"></i> View Reports
            </a>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <!--<div class="row mb-4">-->
    <!--    <div class="col-xl-2 col-md-4 mb-3">-->
    <!--        <a href="{{ route('admin.analytics.exams') }}" class="text-decoration-none">-->
    <!--            <div class="card shadow h-100 border-left-primary">-->
    <!--                <div class="card-body text-center">-->
    <!--                    <i class="bi bi-pencil-square fs-1 text-primary"></i>-->
    <!--                    <h6 class="mt-2 mb-0">Exam Analytics</h6>-->
    <!--                    <small class="text-muted">View exam performance</small>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--    <div class="col-xl-2 col-md-4 mb-3">-->
    <!--        <a href="{{ route('admin.analytics.students') }}" class="text-decoration-none">-->
    <!--            <div class="card shadow h-100 border-left-success">-->
    <!--                <div class="card-body text-center">-->
    <!--                    <i class="bi bi-people fs-1 text-success"></i>-->
    <!--                    <h6 class="mt-2 mb-0">Student Analytics</h6>-->
    <!--                    <small class="text-muted">Track student progress</small>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--    <div class="col-xl-2 col-md-4 mb-3">-->
    <!--        <a href="{{ route('admin.analytics.batches') }}" class="text-decoration-none">-->
    <!--            <div class="card shadow h-100 border-left-info">-->
    <!--                <div class="card-body text-center">-->
    <!--                    <i class="bi bi-grid-3x3-gap-fill fs-1 text-info"></i>-->
    <!--                    <h6 class="mt-2 mb-0">Batch Analytics</h6>-->
    <!--                    <small class="text-muted">Batch performance</small>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--    <div class="col-xl-2 col-md-4 mb-3">-->
    <!--        <a href="{{ route('admin.analytics.questions') }}" class="text-decoration-none">-->
    <!--            <div class="card shadow h-100 border-left-warning">-->
    <!--                <div class="card-body text-center">-->
    <!--                    <i class="bi bi-question-circle fs-1 text-warning"></i>-->
    <!--                    <h6 class="mt-2 mb-0">Question Analytics</h6>-->
    <!--                    <small class="text-muted">Question analysis</small>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--    <div class="col-xl-2 col-md-4 mb-3">-->
    <!--        <a href="{{ route('admin.analytics.dashboard') }}" class="text-decoration-none">-->
    <!--            <div class="card shadow h-100 border-left-secondary">-->
    <!--                <div class="card-body text-center">-->
    <!--                    <i class="bi bi-speedometer2 fs-1 text-secondary"></i>-->
    <!--                    <h6 class="mt-2 mb-0">Overview</h6>-->
    <!--                    <small class="text-muted">Dashboard home</small>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--    <div class="col-xl-2 col-md-4 mb-3">-->
    <!--        <a href="{{ route('admin.analytics.reports') }}" class="text-decoration-none">-->
    <!--            <div class="card shadow h-100 border-left-danger">-->
    <!--                <div class="card-body text-center">-->
    <!--                    <i class="bi bi-file-text fs-1 text-danger"></i>-->
    <!--                    <h6 class="mt-2 mb-0">Saved Reports</h6>-->
    <!--                    <small class="text-muted">View saved reports</small>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--</div>-->

<div class="row mb-4">
    <!-- Exam Analytics -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.analytics.exams') }}" class="text-decoration-none">
            <div class="card shadow h-100 border-left-primary">
                <div class="card-body text-center px-1 px-sm-2">
                    <i class="bi bi-pencil-square fs-1 text-primary"></i>
                    <h6 class="mt-2 mb-0">Exam Analytics</h6>
                    <small class="text-muted">View exam performance</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Student Analytics -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.analytics.students') }}" class="text-decoration-none">
            <div class="card shadow h-100 border-left-success">
                <div class="card-body text-center px-1 px-sm-2">
                    <i class="bi bi-people fs-1 text-success"></i>
                    <h6 class="mt-2 mb-0">Student Analytics</h6>
                    <small class="text-muted">Track student progress</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Batch Analytics -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.analytics.batches') }}" class="text-decoration-none">
            <div class="card shadow h-100 border-left-info">
                <div class="card-body text-center px-1 px-sm-2">
                    <i class="bi bi-grid-3x3-gap-fill fs-1 text-info"></i>
                    <h6 class="mt-2 mb-0">Batch Analytics</h6>
                    <small class="text-muted">Batch performance</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Question Analytics -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.analytics.questions') }}" class="text-decoration-none">
            <div class="card shadow h-100 border-left-warning">
                <div class="card-body text-center px-1 px-sm-2">
                    <i class="bi bi-question-circle fs-1 text-warning"></i>
                    <h6 class="mt-2 mb-0">Question Analytics</h6>
                    <small class="text-muted">Question analysis</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Overview -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.analytics.dashboard') }}" class="text-decoration-none">
            <div class="card shadow h-100 border-left-secondary">
                <div class="card-body text-center px-1 px-sm-2">
                    <i class="bi bi-speedometer2 fs-1 text-secondary"></i>
                    <h6 class="mt-2 mb-0">Overview</h6>
                    <small class="text-muted">Dashboard home</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Saved Reports -->
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <a href="{{ route('admin.analytics.reports') }}" class="text-decoration-none">
            <div class="card shadow h-100 border-left-danger">
                <div class="card-body text-center px-1 px-sm-2">
                    <i class="bi bi-file-text fs-1 text-danger"></i>
                    <h6 class="mt-2 mb-0">Saved Reports</h6>
                    <small class="text-muted">View saved reports</small>
                </div>
            </div>
        </a>
    </div>
</div>

    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.dashboard') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        <span class="input-group-text">to</span>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary mt-4">
                        <i class="bi bi-filter"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overview Cards -->
    <!--<div class="row mb-4">-->
    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-primary shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">-->
    <!--                            Total Exams</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $overallAnalytics['overview']['total_exams'] ?? 0 }}-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-journal-bookmark-fill fa-2x text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.exams') }}" class="small text-primary">View all exams →</a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-success shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">-->
    <!--                            Total Results</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $overallAnalytics['overview']['total_results'] ?? 0 }}-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <span class="small text-success">Pass Rate: {{ $overallAnalytics['overview']['pass_rate'] ?? 0 }}%</span>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-info shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">-->
    <!--                            Active Students</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $overallAnalytics['overview']['active_students'] ?? 0 }}-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-people-fill fa-2x text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.students') }}" class="small text-info">View all students →</a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <!--    <div class="col-xl-3 col-md-6 mb-4">-->
    <!--        <div class="card border-left-warning shadow h-100 py-2">-->
    <!--            <div class="card-body">-->
    <!--                <div class="row no-gutters align-items-center">-->
    <!--                    <div class="col mr-2">-->
    <!--                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">-->
    <!--                            Average Score</div>-->
    <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">-->
    <!--                            {{ $overallAnalytics['overview']['average_score'] ?? 0 }}%-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="col-auto">-->
    <!--                        <i class="bi bi-bar-chart-fill fa-2x text-gray-300"></i>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.exams') }}" class="small text-warning">View detailed analysis →</a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
<div class="row mb-4">
    <!-- Total Exams -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Exams</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $overallAnalytics['overview']['total_exams'] ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-bookmark-fill fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.exams') }}" class="small text-primary text-nowrap">View all exams →</a>
            </div>
        </div>
    </div>

    <!-- Total Results -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Results</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $overallAnalytics['overview']['total_results'] ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <span class="small text-success text-nowrap">Pass Rate: {{ $overallAnalytics['overview']['pass_rate'] ?? 0 }}%</span>
            </div>
        </div>
    </div>

    <!-- Active Students -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Active Students</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $overallAnalytics['overview']['active_students'] ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people-fill fs-2 text-info"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.students') }}" class="small text-info text-nowrap">View all students →</a>
            </div>
        </div>
    </div>

    <!-- Average Score -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Average Score</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $overallAnalytics['overview']['average_score'] ?? 0 }}%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-bar-chart-fill fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.exams') }}" class="small text-warning text-nowrap">View details →</a>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <!-- Daily Activity Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Activity</h6>
                    <a href="{{ route('admin.analytics.exams') }}" class="btn btn-sm btn-primary">View All Exams</a>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance by Course -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Performance by Course</h6>
                    <a href="{{ route('admin.analytics.batches') }}" class="btn btn-sm btn-primary">View Batches</a>
                </div>
                <div class="card-body">
                    <canvas id="courseChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Links Row -->
    <div class="row">
        <!-- Exam Analytics Link -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-pencil-square me-2"></i>Exam Analytics</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Analyze exam performance, score distribution, and question effectiveness.</p>
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-primary">{{ $overallAnalytics['overview']['total_exams'] ?? 0 }} Exams</span>
                        <a href="{{ route('admin.analytics.exams') }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Analytics Link -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-people me-2"></i>Student Analytics</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Track individual student performance, progress, and engagement.</p>
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-success">{{ $overallAnalytics['overview']['active_students'] ?? 0 }} Active</span>
                        <a href="{{ route('admin.analytics.students') }}" class="btn btn-sm btn-outline-success">View</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Analytics Link -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Batch Analytics</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Compare batch performance, pass rates, and student engagement.</p>
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-info">{{ $overallAnalytics['overview']['total_batches'] ?? 0 }} Batches</span>
                        <a href="{{ route('admin.analytics.batches') }}" class="btn btn-sm btn-outline-info">View</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Analytics Link -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-question-circle me-2"></i>Question Analytics</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Analyze question difficulty, discrimination, and effectiveness.</p>
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-warning">{{ $overallAnalytics['overview']['total_questions'] ?? 0 }} Questions</span>
                        <a href="{{ route('admin.analytics.questions') }}" class="btn btn-sm btn-outline-warning">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Exams -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Most Popular Exams</h6>
                    <a href="{{ route('admin.analytics.exams') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Attempts</th>
                                    <th>Avg Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularExams as $exam)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.analytics.exam-detail', $exam) }}">
                                                {{ $exam->title }}
                                            </a>
                                        </td>
                                        <td>{{ $exam->student_exams_count }}</td>
                                        <td>{{ round($exam->results()->avg('percentage') ?? 0, 1) }}%</td>
                                        <td>
                                            <a href="{{ route('admin.analytics.exam-detail', $exam) }}"
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

        <!-- Recent Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Reports</h6>
                    <button class="btn btn-sm btn-success" onclick="generateReport()">
                        <i class="bi bi-plus-circle"></i> New Report
                    </button>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($recentReports as $report)
                            <a href="{{ route('admin.analytics.show-report', $report) }}"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $report->title }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $report->report_type_name }} •
                                            {{ $report->created_at->format('d M Y H:i') }} •
                                            by {{ $report->generator->name }}
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">{{ $report->report_type }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-muted text-center py-3">No reports generated yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Growth Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Student Growth (Last 6 Months)</h6>
                    <a href="{{ route('admin.analytics.students') }}" class="btn btn-sm btn-primary">View All Students</a>
                </div>
                <div class="card-body">
                    <canvas id="studentGrowthChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics Row -->
    <!--<div class="row">-->
    <!--    <div class="col-lg-3 col-md-6 mb-4">-->
    <!--        <div class="card shadow h-100">-->
    <!--            <div class="card-body">-->
    <!--                <div class="d-flex align-items-center">-->
    <!--                    <div class="bg-primary bg-opacity-10 p-3 rounded me-3">-->
    <!--                        <i class="bi bi-question-circle text-primary fs-2"></i>-->
    <!--                    </div>-->
    <!--                    <div>-->
    <!--                        <h6 class="mb-0 text-muted">Question Bank</h6>-->
    <!--                        <h4 class="mb-0">{{ $overallAnalytics['overview']['total_questions'] ?? 0 }}</h4>-->
    <!--                        <small class="text-muted">Active: {{ $overallAnalytics['overview']['active_questions'] ?? 0 }}</small>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.questions') }}" class="btn btn-sm btn-outline-primary w-100">-->
    <!--                    View Question Analytics-->
    <!--                </a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    
    <!--    <div class="col-lg-3 col-md-6 mb-4">-->
    <!--        <div class="card shadow h-100">-->
    <!--            <div class="card-body">-->
    <!--                <div class="d-flex align-items-center">-->
    <!--                    <div class="bg-success bg-opacity-10 p-3 rounded me-3">-->
    <!--                        <i class="bi bi-trophy text-success fs-2"></i>-->
    <!--                    </div>-->
    <!--                    <div>-->
    <!--                        <h6 class="mb-0 text-muted">Top Performers</h6>-->
    <!--                        <h4 class="mb-0">{{ $overallAnalytics['overview']['top_performers_count'] ?? 0 }}</h4>-->
    <!--                        <small class="text-muted">Top students</small>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.students') }}" class="btn btn-sm btn-outline-success w-100">-->
    <!--                    View Top Performers-->
    <!--                </a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    
    <!--    <div class="col-lg-3 col-md-6 mb-4">-->
    <!--        <div class="card shadow h-100">-->
    <!--            <div class="card-body">-->
    <!--                <div class="d-flex align-items-center">-->
    <!--                    <div class="bg-info bg-opacity-10 p-3 rounded me-3">-->
    <!--                        <i class="bi bi-grid-3x3-gap-fill text-info fs-2"></i>-->
    <!--                    </div>-->
    <!--                    <div>-->
    <!--                        <h6 class="mb-0 text-muted">Active Batches</h6>-->
    <!--                        <h4 class="mb-0">{{ $overallAnalytics['overview']['active_batches'] ?? 0 }}</h4>-->
    <!--                        <small class="text-muted">Ongoing batches</small>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.batches') }}" class="btn btn-sm btn-outline-info w-100">-->
    <!--                    View Batch Analytics-->
    <!--                </a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    
    <!--    <div class="col-lg-3 col-md-6 mb-4">-->
    <!--        <div class="card shadow h-100">-->
    <!--            <div class="card-body">-->
    <!--                <div class="d-flex align-items-center">-->
    <!--                    <div class="bg-warning bg-opacity-10 p-3 rounded me-3">-->
    <!--                        <i class="bi bi-file-text text-warning fs-2"></i>-->
    <!--                    </div>-->
    <!--                    <div>-->
    <!--                        <h6 class="mb-0 text-muted">Saved Reports</h6>-->
    <!--                        <h4 class="mb-0">{{ $overallAnalytics['overview']['saved_reports'] ?? 0 }}</h4>-->
    <!--                        <small class="text-muted">Generated reports</small>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="card-footer bg-transparent border-0">-->
    <!--                <a href="{{ route('admin.analytics.reports') }}" class="btn btn-sm btn-outline-warning w-100">-->
    <!--                    View All Reports-->
    <!--                </a>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
    
    <div class="row">
    <!-- Question Bank -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-25 p-3 rounded me-2 me-sm-3">
                        <i class="bi bi-question-circle text-white fs-2"></i>
                    </div>
                    <div class="text-truncate">
                        <h6 class="mb-0 text-muted small fw-bold text-wrap">Question Bank</h6>
                        <h4 class="mb-0 fw-bold">{{ $overallAnalytics['overview']['total_questions'] ?? 0 }}</h4>
                        <small class="text-muted text-truncate d-block">Active: {{ $overallAnalytics['overview']['active_questions'] ?? 0 }}</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.questions') }}" class="btn btn-sm btn-outline-primary w-100 text-truncate">
                    Questions
                </a>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-25 p-3 rounded me-2 me-sm-3">
                        <i class="bi bi-trophy text-success fs-2"></i>
                    </div>
                    <div class="text-truncate">
                        <h6 class="mb-0 text-muted small fw-bold text-wrap">Top Performers</h6>
                        <h4 class="mb-0 fw-bold">{{ $overallAnalytics['overview']['top_performers_count'] ?? 0 }}</h4>
                        <small class="text-muted text-truncate d-block">Top students</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.students') }}" class="btn btn-sm btn-outline-success w-100 text-truncate">
                    Performers
                </a>
            </div>
        </div>
    </div>

    <!-- Active Batches -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-25 p-3 rounded me-2 me-sm-3">
                        <i class="bi bi-grid-3x3-gap-fill text-info fs-2"></i>
                    </div>
                    <div class="text-truncate">
                        <h6 class="mb-0 text-muted small fw-bold text-wrap">Active Batches</h6>
                        <h4 class="mb-0 fw-bold">{{ $overallAnalytics['overview']['active_batches'] ?? 0 }}</h4>
                        <small class="text-muted text-truncate d-block">Ongoing</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.batches') }}" class="btn btn-sm btn-outline-info w-100 text-truncate">
                    Batches
                </a>
            </div>
        </div>
    </div>

    <!-- Saved Reports -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-25 p-3 rounded me-2 me-sm-3">
                        <i class="bi bi-file-text text-warning fs-2"></i>
                    </div>
                    <div class="text-truncate">
                        <h6 class="mb-0 text-muted small fw-bold text-wrap">Saved Reports</h6>
                        <h4 class="mb-0 fw-bold">{{ $overallAnalytics['overview']['saved_reports'] ?? 0 }}</h4>
                        <small class="text-muted text-truncate d-block">Generated</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 px-2 px-sm-3">
                <a href="{{ route('admin.analytics.reports') }}" class="btn btn-sm btn-outline-warning w-100 text-truncate">
                    Reports
                </a>
            </div>
        </div>
    </div>
</div>


    <!-- Analytics Quick Links Banner -->
    <div class="card shadow mb-4 bg-gradient-primary text-white">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Comprehensive Analytics</h5>
                    <p class="mb-0 opacity-75 text-dark small">Explore detailed analytics across all aspects of your platform</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('admin.analytics.exams') }}" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-pencil-square me-1"></i> Exams
                    </a>
                    <a href="{{ route('admin.analytics.students') }}" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-people me-1"></i> Students
                    </a>
                    <a href="{{ route('admin.analytics.batches') }}" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Batches
                    </a>
                    <a href="{{ route('admin.analytics.questions') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-question-circle me-1"></i> Questions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.analytics.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Custom Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-control" id="report_type" name="report_type" required>
                            <option value="">Select Type</option>
                            <option value="overall">Overall Analytics</option>
                            <option value="exam">Exam Report</option>
                            <option value="student">Student Report</option>
                            <option value="batch">Batch Report</option>
                            <option value="question">Question Analysis</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Report Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                    </div>

                    <div id="dynamic-fields"></div>

                    <div class="mb-3">
                        <label for="format" class="form-label">Export Format (Optional)</label>
                        <select class="form-control" id="format" name="format">
                            <option value="">JSON Only</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary { border-left: 4px solid #4e73df; }
    .border-left-success { border-left: 4px solid #1cc88a; }
    .border-left-info { border-left: 4px solid #36b9cc; }
    .border-left-warning { border-left: 4px solid #f6c23e; }
    .border-left-danger { border-left: 4px solid #e74a3b; }
    .border-left-secondary { border-left: 4px solid #858796; }
    .bg-opacity-10 { opacity: 0.1; }
    .card-footer a { text-decoration: none; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Activity Chart
        const dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: @json(collect($overallAnalytics['daily_activity'] ?? [])->pluck('date')),
                datasets: [{
                    label: 'Attempts',
                    data: @json(collect($overallAnalytics['daily_activity'] ?? [])->pluck('attempts')),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    yAxisID: 'y'
                }, {
                    label: 'Average Score',
                    data: @json(collect($overallAnalytics['daily_activity'] ?? [])->pluck('avg_score')),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
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
                }
            }
        });

        // Course Performance Chart
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'doughnut',
            data: {
                labels: @json(collect($overallAnalytics['performance_by_course'] ?? [])->pluck('title')),
                datasets: [{
                    data: @json(collect($overallAnalytics['performance_by_course'] ?? [])->pluck('avg_score')),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
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

        // Student Growth Chart
        const growthCtx = document.getElementById('studentGrowthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: @json(collect($overallAnalytics['student_growth'] ?? [])->pluck('date')),
                datasets: [{
                    label: 'New Students',
                    data: @json(collect($overallAnalytics['student_growth'] ?? [])->pluck('count')),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    }
                }
            }
        });

        // Report type change handler
        $('#report_type').on('change', function() {
            const type = $(this).val();
            let html = '';

            if (type === 'exam') {
                html = `
                    <div class="mb-3">
                        <label for="exam_id" class="form-label">Select Exam</label>
                        <select class="form-control" id="exam_id" name="exam_id" required>
                            <option value="">Choose exam...</option>
                            @foreach(\App\Models\Exam::all() as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
            } else if (type === 'student') {
                html = `
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select class="form-control" id="student_id" name="student_id" required>
                            <option value="">Choose student...</option>
                            @foreach(\App\Models\User::role('student')->get() as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="batch_id" class="form-label">Filter by Batch (Optional)</label>
                        <select class="form-control" id="batch_id" name="batch_id">
                            <option value="">All Batches</option>
                            @foreach(\App\Models\Batch::all() as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
            } else if (type === 'batch') {
                html = `
                    <div class="mb-3">
                        <label for="batch_id" class="form-label">Select Batch</label>
                        <select class="form-control" id="batch_id" name="batch_id" required>
                            <option value="">Choose batch...</option>
                            @foreach(\App\Models\Batch::all() as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
            } else if (type === 'question') {
                html = `
                    <div class="mb-3">
                        <label for="question_id" class="form-label">Select Question</label>
                        <select class="form-control" id="question_id" name="question_id" required>
                            <option value="">Choose question...</option>
                            @foreach(\App\Models\Question::limit(50)->get() as $question)
                                <option value="{{ $question->id }}">{{ Str::limit(strip_tags($question->question_text), 100) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exam_id" class="form-label">Filter by Exam (Optional)</label>
                        <select class="form-control" id="exam_id" name="exam_id">
                            <option value="">All Exams</option>
                            @foreach(\App\Models\Exam::all() as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>
                `;
            }

            $('#dynamic-fields').html(html);
        });
    });

    function generateReport() {
        $('#reportModal').modal('show');
    }
</script>
@endpush