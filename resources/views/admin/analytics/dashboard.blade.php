@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid">
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
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Exams</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overallAnalytics['overview']['total_exams'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-journal-bookmark-fill fa-2x text-gray-300"></i>
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
                                Total Results</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overallAnalytics['overview']['total_results'] ?? 0 }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overallAnalytics['overview']['active_students'] ?? 0 }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overallAnalytics['overview']['average_score'] ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Activity Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Activity</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance by Course -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance by Course</h6>
                </div>
                <div class="card-body">
                    <canvas id="courseChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Popular Exams -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Most Popular Exams</h6>
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Reports</h6>
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
                            <p class="text-muted text-center">No reports generated yet</p>
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Student Growth (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="studentGrowthChart" height="100"></canvas>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
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
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                    ]
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
