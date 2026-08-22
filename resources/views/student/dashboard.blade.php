{{-- resources/views/student/dashboard.blade.php --}}

@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-house-door me-2"></i>Welcome Back, {{ Auth::user()->name }}!
            </h1>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i> 
                {{ now()->format('l, d M Y') }}
            </p>
        </div>
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
                    <i class="bi bi-mortarboard display-1 text-white opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Enrolled Courses -->
        <!--<div class="col-xl-3 col-md-6 col-6 mb-4">-->
        <!--    <div class="card border-left-primary shadow h-100 py-2">-->
        <!--        <div class="card-body">-->
        <!--            <div class="row g-0 align-items-center">-->
        <!--                <div class="col mr-2">-->
        <!--                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">-->
        <!--                        Enrolled Courses</div>-->
        <!--                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $enrolledCourses ?? 0 }}</div>-->
        <!--                </div>-->
        <!--                <div class="col-auto">-->
        <!--                    <i class="bi bi-book fs-2 text-primary"></i>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        
        <div class="col-xl-3 col-md-6 col-6 mb-4"> 
            <a href="#courses" class="card border-left-primary shadow h-100 py-2 text-decoration-none custom-hover-card"> 
                <div class="card-body"> 
                    <div class="row g-0 align-items-center"> 
                        <div class="col mr-2"> 
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Enrolled Courses</div> 
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $enrolledCourses ?? 0 }}</div> 
                        </div> 
                        <div class="col-auto"> 
                            <i class="bi bi-book fs-2 text-primary"></i> 
                        </div> 
                    </div> 
                </div> 
            </a> 
        </div>


        <!-- Exams Taken -->
        <div class="col-xl-3 col-md-6 col-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Exams Taken</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $examsTaken ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil-square fs-2 text-success"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $averageScore ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rank -->
        <div class="col-xl-3 col-md-6 col-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Class Rank</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($classRank)
                                    #{{ $classRank }}
                                    <small class="text-muted fs-6">/ {{ $totalStudents }}</small>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy-fill fs-2 text-warning"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>My Performance Trend
                    </h6>
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>Subject-wise Performance
                    </h6>
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-calendar-event me-2"></i>Upcoming Exams
                    </h6>
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>Recent Results
                    </h6>
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

    <!-- My Courses -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-book me-2"></i>My Courses
                <span class="badge bg-primary ms-2">{{ $courses->count() }}</span>
            </h6>
        </div>
        <div class="card-body" id="courses">
            @if($courses->count() > 0)
                <div class="row">
                    @foreach($courses as $course)
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm hover-shadow">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="course-icon me-3">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                                <i class="bi bi-mortarboard fs-2 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold text-primary mb-1">
                                                {{ $course->title }}
                                            </h6>
                                            <p class="text-muted small mb-2">
                                                {{ Str::limit($course->description, 80) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-{{ $course->status == 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($course->status) }}
                                                </span>
                                                <a href="{{ route('student.course.detail', $course) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <div class="row g-0 text-center">
                                        <div class="col-4 border-end">
                                            <span class="d-block fw-bold">{{ $course->batches->count() }}</span>
                                            <small class="text-muted">Batches</small>
                                        </div>
                                        <div class="col-4 border-end">
                                            <span class="d-block fw-bold">{{ $course->exams->count() }}</span>
                                            <small class="text-muted">Exams</small>
                                        </div>
                                        <div class="col-4">
                                            <span class="d-block fw-bold">{{ $course->modules->count() }}</span>
                                            <small class="text-muted">Modules</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-book fs-1 text-muted"></i>
                    <p class="text-muted mt-2">You are not enrolled in any courses yet.</p>
                    <p class="text-muted small">Contact your administrator for course enrollment.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- My Batches -->
    <div class="card shadow mb-4" id="batches">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-collection me-2"></i>My Batches
                <span class="badge bg-success ms-2">{{ $batches->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($batches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Batch Name</th>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Duration</th>
                                <th>Students</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                                <tr>
                                    <td>
                                        <strong>{{ $batch->name }}</strong>
                                        @if($batch->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($batch->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.course.detail', $batch->course) }}" 
                                           class="text-decoration-none">
                                            {{ $batch->course->title }}
                                        </a>
                                    </td>
                                    <td>{{ $batch->instructor->name ?? 'N/A' }}</td>
                                    <td>
                                        <small>
                                            <i class="bi bi-calendar-start me-1"></i>
                                            {{ $batch->start_date->format('d M Y') }}
                                            <br>
                                            <i class="bi bi-calendar-end me-1"></i>
                                            {{ $batch->end_date->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $batch->students_count }}/{{ $batch->capacity }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'upcoming' => 'secondary',
                                                'ongoing' => 'success',
                                                'completed' => 'primary'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$batch->status] ?? 'secondary' }}">
                                            {{ ucfirst($batch->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $batch->progress >= 70 ? 'success' : ($batch->progress >= 40 ? 'warning' : 'info') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $batch->progress }}%"
                                                 aria-valuenow="{{ $batch->progress }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ round($batch->progress) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('student.batch.detail', $batch) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($batch->status == 'ongoing')
                                                <a href="{{ route('student.batch.exams', $batch) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-journal"></i> Exams
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-collection fs-1 text-muted"></i>
                    <p class="text-muted mt-2">You are not enrolled in any batches yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="row">
        <!-- Study Materials -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-file-earmark-text me-2"></i>Recommended Study Materials
                    </h6>
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
                                <a href="{{ $material['download_url'] ?? '#' }}" class="btn btn-sm btn-outline-primary">
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-fire me-2"></i>Study Streak
                    </h6>
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
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-trophy me-2"></i>Recent Achievements
                    </h6>
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

    <!-- Recent Activity -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-2"></i>Recent Activity
            </h6>
        </div>
        <div class="card-body">
            @if($recentActivities && count($recentActivities) > 0)
                <div class="timeline">
                    @foreach($recentActivities as $activity)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $activity['color'] ?? 'primary' }}"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                        <p class="text-muted small mb-0">{{ $activity['description'] }}</p>
                                    </div>
                                    <small class="text-muted">{{ $activity['time'] }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3">
                    <p class="text-muted">No recent activity</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .course-icon .rounded-circle {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }
    .timeline-content {
        padding-left: 5px;
    }
    
    /* Progress Bar */
    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
    }
    
    /* Cards */
    .card-header .badge {
        font-size: 0.8rem;
    }
    
    /* Enables smooth scrolling across the entire site */
html {
  scroll-behavior: smooth;
}

/* Card hover animation */
.custom-hover-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.custom-hover-card:hover {
  transform: translateY(-5px); /* Lifts the card slightly */
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; /* Deepens the shadow */
}

</style>
@endpush

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