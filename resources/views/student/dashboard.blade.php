@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Student Dashboard</h1>
        <div>
            @if(isset($pendingFeedbackCount) && $pendingFeedbackCount > 0)
                <a href="{{ route('student.feedback.pending') }}" class="btn btn-sm btn-warning shadow-sm me-2">
                    <i class="bi bi-chat"></i> {{ $pendingFeedbackCount }} Pending Feedback
                </a>
            @endif
            <a href="{{ route('student.exams.index') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-pencil-square"></i> View Exams
            </a>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="card shadow mb-4 bg-primary text-white">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2">Welcome back, {{ Auth::user()->name }}! 📚</h4>
                    <p class="mb-0 opacity-75">Ready to continue your learning journey? You have {{ $pendingExams ?? 0 }} pending exams.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <i class="bi bi-mortarboard display-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Enrolled Courses -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Enrolled Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $enrolledCourses ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-book fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exams Taken -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Exams Taken</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $examsTaken ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil-square fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Score -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $averageScore ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rank -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Class Rank</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">#{{ $classRank ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Progress -->
    <div class="row">
        <!-- Performance Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">My Performance Trend</h6>
                    <select class="form-select form-select-sm w-auto" id="performancePeriod">
                        <option value="5">Last 5 Exams</option>
                        <option value="10">Last 10 Exams</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Subject Performance -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subject-wise Performance</h6>
                </div>
                <div class="card-body">
                    @if(isset($subjectPerformance) && count($subjectPerformance) > 0)
                        @foreach($subjectPerformance as $subject)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $subject['name'] }}</span>
                                    <span class="fw-bold">{{ $subject['score'] }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $subject['score'] >= 80 ? 'success' : ($subject['score'] >= 60 ? 'info' : ($subject['score'] >= 40 ? 'warning' : 'danger')) }}"
                                         style="width: {{ $subject['score'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No subject data available</p>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>Overall Accuracy</span>
                        <span class="fw-bold">{{ $overallAccuracy ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Upcoming Exams -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Exams</h6>
                    <a href="{{ route('student.exams.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($upcomingExams) && count($upcomingExams) > 0)
                        @foreach($upcomingExams as $exam)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div>
                                    <strong>{{ $exam->title }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-book"></i> {{ $exam->course->title ?? 'N/A' }} |
                                        <i class="bi bi-clock"></i> {{ $exam->duration_minutes }} min |
                                        <i class="bi bi-calendar"></i> {{ $exam->start_time ? $exam->start_time->format('d M, h:i A') : 'Flexible' }}
                                    </small>
                                </div>
                                <div>
                                    @if($exam->start_time && $exam->start_time->isFuture())
                                        <span class="badge bg-warning">{{ $exam->start_time->diffForHumans() }}</span>
                                    @else
                                        <a href="{{ route('student.exams.instructions', $exam) }}" class="btn btn-sm btn-success">
                                            Start Now
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No upcoming exams</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Results -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Results</h6>
                    <a href="{{ route('student.results.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recentResults) && count($recentResults) > 0)
                        @foreach($recentResults as $result)
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div>
                                    <strong>{{ $result->exam->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $result->created_at->format('d M Y') }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $result->percentage >= 60 ? 'success' : 'danger' }} fs-6">
                                        {{ round($result->percentage, 1) }}%
                                    </span>
                                    <br>
                                    <small>{{ $result->obtained_marks }}/{{ $result->total_marks }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No results yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="row">
        <!-- Study Materials -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recommended Study Materials</h6>
                </div>
                <div class="card-body">
                    @if(isset($studyMaterials) && count($studyMaterials) > 0)
                        @foreach($studyMaterials as $material)
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <div class="bg-light p-2 rounded me-3">
                                    <i class="bi bi-file-pdf text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ $material['title'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $material['subject'] }}</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No materials available</p>
                    @endif

                    <a href="{{ route('student.study-materials') }}" class="btn btn-sm btn-outline-primary w-100">
                        Browse All Materials
                    </a>
                </div>
            </div>
        </div>

        <!-- Study Streak -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Study Streak</h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-1 text-warning mb-2">{{ $streakDays ?? 0 }}</div>
                    <p class="h5">Days Streak 🔥</p>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Study Time</span>
                            <span class="fw-bold">{{ $totalStudyTime ?? 0 }} hours</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $weeklyProgress ?? 0 }}%"></div>
                        </div>
                        <p class="small text-muted">This week's goal: {{ $weeklyGoal ?? 10 }} hours</p>
                    </div>

                    <div class="mt-3">
                        <span class="badge bg-{{ $streakDays >= 7 ? 'success' : 'warning' }} p-2">
                            <i class="bi bi-fire"></i> {{ $streakMessage ?? 'Keep it up!' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Achievements</h6>
                </div>
                <div class="card-body">
                    @if(isset($achievements) && count($achievements) > 0)
                        @foreach($achievements as $achievement)
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-{{ $achievement['color'] }} bg-opacity-10 p-2 rounded-circle me-3">
                                    <i class="bi bi-{{ $achievement['icon'] }} text-{{ $achievement['color'] }} fs-4"></i>
                                </div>
                                <div>
                                    <strong>{{ $achievement['title'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $achievement['description'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">Complete exams to earn achievements!</p>
                    @endif

                    <hr>

                    <div class="text-center">
                        <span class="small text-muted">Next achievement: {{ $nextAchievement ?? 'First Perfect Score' }}</span>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-info" style="width: {{ $nextAchievementProgress ?? 0 }}%"></div>
                        </div>
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

    function initChart(limit = 5) {
        // Get data from PHP
        const labels = @json($performanceLabels ?? []);
        const scores = @json($performanceScores ?? []);

        // Take only the last 'limit' items
        const displayLabels = limit === 'all' ? labels : labels.slice(-limit);
        const displayScores = limit === 'all' ? scores : scores.slice(-limit);

        if (performanceChart) {
            performanceChart.destroy();
        }

        performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [{
                    label: 'My Score %',
                    data: displayScores,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        title: {
                            display: true,
                            text: 'Score %'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Score: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize chart with default limit
    initChart(5);

    // Period change handler
    document.getElementById('performancePeriod').addEventListener('change', function(e) {
        initChart(e.target.value);
    });

    // Animate stats counters
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.round(current);
            }
        }, 20);
    }

    // Animate all counters
    document.querySelectorAll('.counter').forEach(counter => {
        const target = parseInt(counter.textContent);
        animateCounter(counter, target);
    });
</script>
@endpush
@endsection
