{{-- resources/views/admin/monitoring/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Monitoring Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-broadcast text-success me-2"></i>Monitoring Dashboard
        </h1>
        <div>
            <span class="badge bg-primary fs-6 me-2">
                <i class="bi bi-clock me-1"></i> {{ now()->format('d M Y, h:i A') }}
            </span>
            <button type="button" class="btn btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <i class="bi bi-broadcast me-1"></i> Active Sessions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeSessions->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-play-circle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <i class="bi bi-people me-1"></i> Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeSessions->sum('total_students') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <i class="bi bi-exclamation-triangle me-1"></i> Total Warnings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeSessions->sum('warning_count') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <i class="bi bi-person-x me-1"></i> Terminated</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeSessions->sum('terminated_count') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-x fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-broadcast text-success me-2"></i>
                Active Monitoring Sessions
                <span class="badge bg-success ms-2">{{ $activeSessions->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Session Name</th>
                            <th>Exam</th>
                            <th>Instructor</th>
                            <th>Batch</th>
                            <th>Students</th>
                            <th>Active</th>
                            <th>Warnings</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeSessions as $session)
                            <tr>
                                <td>
                                    <strong>{{ $session->session_name }}</strong>
                                    <br>
                                    <small class="text-muted">ID: #{{ $session->id }}</small>
                                </td>
                                <td>{{ $session->exam->title ?? 'N/A' }}</td>
                                <td>{{ $session->instructor->name ?? 'N/A' }}</td>
                                <td>{{ $session->batch->name ?? 'N/A' }}</td>
                                <td>{{ $session->total_students }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $session->active_students }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $session->warning_count > 5 ? 'danger' : ($session->warning_count > 2 ? 'warning' : 'secondary') }}">
                                        {{ $session->warning_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $session->formatted_duration }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success animate-pulse">Live</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.monitoring.show', $session) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.monitoring.end', $session) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('End this monitoring session?')">
                                                <i class="bi bi-stop-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No active monitoring sessions
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ended Sessions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-2"></i>
                Ended Sessions (Last 10)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Session</th>
                            <th>Exam</th>
                            <th>Instructor</th>
                            <th>Duration</th>
                            <th>Students</th>
                            <th>Warnings</th>
                            <th>Ended At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($endedSessions as $session)
                            <tr>
                                <td>{{ $session->session_name }}</td>
                                <td>{{ $session->exam->title ?? 'N/A' }}</td>
                                <td>{{ $session->instructor->name ?? 'N/A' }}</td>
                                <td>{{ $session->formatted_duration }}</td>
                                <td>{{ $session->total_students }}</td>
                                <td>
                                    <span class="badge bg-{{ $session->warning_count > 5 ? 'danger' : 'secondary' }}">
                                        {{ $session->warning_count }}
                                    </span>
                                </td>
                                <td>{{ $session->ended_at ? $session->ended_at->diffForHumans() : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.monitoring.show', $session) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No ended sessions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!--HELP-->
<div class="container my-4">

    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-diagram-3"></i> Exam Monitoring Lifecycle
            </h4>
        </div>

        <div class="card-body">

            <div class="timeline">

                <div class="mb-4">
                    <h5 class="text-primary">1. Student Starts Exam</h5>
                    <p class="mb-1">
                        Student begins the examination.
                    </p>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary">2. Student Becomes Active</h5>
                    <p class="mb-1">
                        Status changes to:
                    </p>

                    <span class="badge bg-success">in_progress</span>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="alert alert-warning">
                    <h5 class="mb-2">
                        ✅ 3. Instructor Starts Monitoring Session
                    </h5>

                    <ul class="mb-0">
                        <li>Must be started after students begin the exam.</li>
                        <li>Can also be started anytime while the exam is in progress.</li>
                    </ul>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        4. Monitoring Session Captures
                    </div>

                    <div class="card-body">
                        <ul class="mb-0">
                            <li>📸 Screenshots at configured intervals</li>
                            <li>🗂 Tab switching activity</li>
                            <li>🖱 Mouse events</li>
                            <li>⚠ Suspicious activities</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="card border-success mb-4">
                    <div class="card-header bg-success text-white">
                        5. Real-Time Monitoring
                    </div>

                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Instructor sees active students.</li>
                            <li>Views captured screenshots.</li>
                            <li>Receives suspicious activity alerts.</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary">6. Exam Continues Until Completion</h5>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary">7. Instructor Ends Monitoring Session</h5>
                </div>

                <div class="text-center mb-3">
                    <i class="bi bi-arrow-down fs-3 text-secondary"></i>
                </div>

                <div class="alert alert-success mb-0">
                    <h5>8. Proctoring Data Saved</h5>

                    <p class="mb-0">
                        All screenshots, events, suspicious activities, and monitoring logs
                        are securely stored for future review.
                    </p>
                </div>

            </div>

        </div>
    </div>

    <!-- Key Points -->

    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Key Points</h5>
        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th width="30%">Aspect</th>
                    <th>Explanation</th>
                </tr>
                </thead>

                <tbody>

                <tr>
                    <td><strong>When to Start</strong></td>
                    <td>
                        Start monitoring only while the exam is in progress after students have begun.
                    </td>
                </tr>

                <tr>
                    <td><strong>Pre-Exam</strong></td>
                    <td class="text-danger">
                        Cannot start before students begin because there are no active candidates.
                    </td>
                </tr>

                <tr>
                    <td><strong>Real-Time Monitoring</strong></td>
                    <td>
                        Instructor sees live student activities during the examination.
                    </td>
                </tr>

                <tr>
                    <td><strong>Post-Exam Review</strong></td>
                    <td>
                        All captured data remains available for future investigation.
                    </td>
                </tr>

                <tr>
                    <td><strong>Active Students</strong></td>
                    <td>
                        Only students with <span class="badge bg-success">started</span> or
                        <span class="badge bg-success">in_progress</span> status are monitored.
                    </td>
                </tr>

                <tr>
                    <td><strong>Inactive Students</strong></td>
                    <td>
                        Students who have not started or already completed the exam are not shown.
                    </td>
                </tr>

                </tbody>

            </table>

        </div>
    </div>

    <!-- How To Use -->

    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">How to Use</h5>
        </div>

        <div class="card-body">

            <ol class="mb-0">

                <li>Students start the examination from the Student Dashboard.</li>

                <li>Instructor opens the Monitoring Dashboard.</li>

                <li>Click <span class="badge bg-success">Start New Session</span>.</li>

                <li>Select the running examination.</li>

                <li>Monitoring session begins capturing proctoring data.</li>

                <li>Instructor watches students in real-time.</li>

                <li>After the exam, click <span class="badge bg-danger">End Session</span>.</li>

                <li>Review all saved screenshots and suspicious activities later.</li>

            </ol>

        </div>
    </div>

    <!-- Important Notes -->

    <div class="alert alert-warning mt-4">

        <h5 class="mb-3">
            ⚠ Important Notes
        </h5>

        <ul class="mb-0">

            <li>Session must start after students begin the examination.</li>

            <li>Only active examinations can be monitored.</li>

            <li>Screenshots are automatically captured at configured intervals (e.g., every 60 seconds).</li>

            <li>Instructor can manually capture screenshots whenever required.</li>

            <li>All proctoring data is securely stored in the database.</li>

            <li>Administrators can review monitoring sessions across all instructors.</li>

        </ul>

    </div>

</div>

@push('styles')
<style>
    .animate-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
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
    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }
</style>
@endpush
@endsection