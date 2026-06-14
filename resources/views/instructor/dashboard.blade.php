@extends('layouts.instructor')

@section('title', 'Instructor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Instructor Dashboard</h1>
        <div>
            <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-sm btn-success shadow-sm me-2">
                <i class="bi bi-lightning"></i> Launch Quick Quiz
            </a>
            <a href="{{ route('instructor.live-monitoring.index') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-camera-video"></i> Live Monitoring
            </a>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="card shadow mb-4 bg-primary text-white">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h4>
                    <p class="mb-0 opacity-75">Here's what's happening with your courses and students today.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <i class="bi bi-mortarboard display-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Courses -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                My Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCourses ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-book fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('instructor.courses.index') }}" class="small text-primary">View all courses →</a>
                </div>
            </div>
        </div>

        <!-- Active Batches -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Batches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeBatches ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <span class="small text-success">{{ $totalStudents ?? 0 }} total students</span>
                </div>
            </div>
        </div>

        <!-- Total Exams -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Exams Created</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalExams ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil-square fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('instructor.exams.index') }}" class="small text-info">Manage exams →</a>
                </div>
            </div>
        </div>

        <!-- Average Performance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avg. Performance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $averageScore ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <span class="small text-warning">Across all exams</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Performance Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Overview</h6>
                    <select class="form-select form-select-sm w-auto" id="performancePeriod">
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Pass Rate</span>
                            <span class="fw-bold">{{ $passRate ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $passRate ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Completion Rate</span>
                            <span class="fw-bold">{{ $completionRate ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: {{ $completionRate ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Student Engagement</span>
                            <span class="fw-bold">{{ $engagementRate ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: {{ $engagementRate ?? 0 }}%"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Questions Created</span>
                        <span class="fw-bold">{{ $totalQuestions ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Quizzes Launched</span>
                        <span class="fw-bold">{{ $totalQuizzes ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Feedback Responses</span>
                        <span class="fw-bold">{{ $feedbackResponses ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Exams -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Exams</h6>
                    <a href="{{ route('instructor.exams.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recentExams) && count($recentExams) > 0)
                        <div class="list-group">
                            @foreach($recentExams as $exam)
                                <a href="{{ route('instructor.exams.show', $exam) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $exam->title }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-people"></i> {{ $exam->attempts_count ?? 0 }} attempts |
                                                <i class="bi bi-calendar"></i> {{ $exam->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $exam->status == 'published' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($exam->status) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No recent exams</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Active Students -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Currently Active Students</h6>
                    <a href="{{ route('instructor.live-monitoring.index') }}" class="btn btn-sm btn-success">Monitor Live</a>
                </div>
                <div class="card-body">
                    @if(isset($activeStudents) && count($activeStudents) > 0)
                        @foreach($activeStudents as $student)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $student->avatar_url }}" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <strong>{{ $student->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $student->exam_title ?? 'Taking exam' }}</small>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-success">Active</span>
                                    <small class="text-muted ms-2">{{ $student->time_elapsed ?? '5 min' }}</small>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('instructor.live-monitoring.index') }}" class="btn btn-sm btn-outline-primary">
                                View All Active Students
                            </a>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No active students at the moment</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="row">
        <!-- Feedback Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Feedback</h6>
                </div>
                <div class="card-body">
                    @if(isset($recentFeedback) && count($recentFeedback) > 0)
                        @foreach($recentFeedback as $feedback)
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $feedback->student_name }}</strong>
                                    <small class="text-muted">{{ $feedback->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= ($feedback->rating ?? 0) ? '-fill' : '' }} text-warning small"></i>
                                    @endfor
                                </div>
                                <p class="small text-muted mt-1 mb-0">"{{ Str::limit($feedback->comment, 50) }}"</p>
                            </div>
                        @endforeach
                        <a href="{{ route('instructor.feedback.responses', $latestFeedback ?? '') }}" class="btn btn-sm btn-outline-primary w-100">
                            View All Feedback
                        </a>
                    @else
                        <p class="text-muted text-center py-3">No feedback received yet</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Tasks</h6>
                </div>
                <div class="card-body">
                    @if(isset($upcomingTasks) && count($upcomingTasks) > 0)
                        @foreach($upcomingTasks as $task)
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-{{ $task['color'] }} bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi bi-{{ $task['icon'] }} text-{{ $task['color'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ $task['title'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $task['time'] }}</small>
                                </div>
                                <span class="badge bg-{{ $task['priority'] == 'high' ? 'danger' : 'warning' }}">
                                    {{ ucfirst($task['priority']) }}
                                </span>
                            </div>
                        @endforeach
                    @else
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-check-circle text-info"></i>
                            </div>
                            <div>
                                <strong>All caught up!</strong>
                                <br>
                                <small class="text-muted">No pending tasks</small>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <div class="d-grid">
                        <button class="btn btn-outline-primary btn-sm" onclick="createReminder()">
                            <i class="bi bi-plus-circle"></i> Add Reminder
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-lightning me-2"></i> Launch Quick Quiz
                        </a>
                        <a href="{{ route('instructor.exams.index') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-pencil-square me-2"></i> View Exam
                        </a>
                        <a href="{{ route('instructor.feedback.create') }}" class="btn btn-outline-info text-start">
                            <i class="bi bi-chat me-2"></i> Get Class Feedback
                        </a>
                        <a href="{{ route('instructor.live-monitoring.index') }}" class="btn btn-outline-warning text-start">
                            <i class="bi bi-camera-video me-2"></i> Monitor Live Exam
                        </a>
                        <a href="{{ route('instructor.analytics.index') }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-graph-up me-2"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    let performanceChart;

    function initChart(period = 'week') {
        let labels = [], scores = [], averages = [];

        // Sample data - replace with actual data from controller
        if (period === 'week') {
            labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            scores = [65, 72, 68, 85, 78, 82, 88];
            averages = [70, 70, 70, 70, 70, 70, 70];
        } else if (period === 'month') {
            labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            scores = [72, 78, 82, 85];
            averages = [75, 75, 75, 75];
        } else {
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            scores = [68, 72, 75, 80, 82, 85];
            averages = [70, 72, 73, 75, 76, 77];
        }

        if (performanceChart) {
            performanceChart.destroy();
        }

        performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Class Average',
                        data: averages,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Your Students',
                        data: scores,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
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
                            text: 'Score %'
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
    }

    // Initialize chart
    initChart();

    // Period change handler
    document.getElementById('performancePeriod').addEventListener('change', function(e) {
        initChart(e.target.value);
    });

    // Reminder function
    function createReminder() {
        // Implement reminder creation modal
        alert('Create reminder feature coming soon!');
    }
</script>
@endpush
@endsection
