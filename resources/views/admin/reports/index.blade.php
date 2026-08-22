@extends('layouts.admin')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reports Dashboard</h1>
    </div>

    <!-- Statistics Cards -->
<div class="row">
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
                        <i class="bi bi-journal-bookmark-fill fs-2 text-primary"></i>
                    </div>
                </div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_results'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Score -->
    <div class="col-xl-3 col-md-6 col-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Average Score</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up-arrow fs-2 text-info"></i>
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
                        <i class="bi bi-trophy-fill fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <!-- Quick Access Cards -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Reports</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.reports.exams') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-text me-2 text-primary"></i>
                                Exam-wise Reports
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.reports.students') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-people me-2 text-success"></i>
                                Student Performance Reports
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.reports.batches') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-grid-3x3-gap-fill me-2 text-info"></i>
                                Batch Performance Reports
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.reports.question-analysis') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-pie-chart-fill me-2 text-warning"></i>
                                Question Analysis
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Results</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentResults as $result)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $result->student->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $result->exam->title }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                            {{ $result->percentage }}%
                                        </span>
                                        <br>
                                        <small>{{ $result->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performers (Last 30 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student</th>
                                    <th>Exam</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topPerformers as $index => $result)
                                    <tr>
                                        <td>#{{ $index + 1 }}</td>
                                        <td>{{ $result->student->name }}</td>
                                        <td>{{ $result->exam->title }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ $result->percentage }}%</span>
                                        </td>
                                        <td>{{ $result->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
