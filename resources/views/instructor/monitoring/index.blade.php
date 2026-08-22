{{-- resources/views/instructor/monitoring/index.blade.php --}}

@extends('layouts.instructor')

@section('title', 'Live Monitoring')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Live Monitoring</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startSessionModal">
            <i class="bi bi-play-circle"></i> Start New Session
        </button>
    </div>

    <!-- Active Sessions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-broadcast text-success me-2"></i>
                Active Sessions
                <span class="badge bg-success ms-2">{{ $activeSessions->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($activeSessions->count() > 0)
                <div class="row">
                    @foreach($activeSessions as $session)
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="font-weight-bold text-primary mb-1">
                                                {{ $session->session_name }}
                                            </h6>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-journal me-1"></i>
                                                {{ $session->exam->title ?? 'N/A' }}
                                            </p>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-people me-1"></i>
                                                {{ $session->active_students }}/{{ $session->total_students }} Active
                                            </p>
                                            <p class="mb-0 text-muted small">
                                                <i class="bi bi-clock me-1"></i>
                                                Started: {{ $session->started_at->diffForHumans() }}
                                            </p>
                                            <p class="mb-0 text-muted small">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Warnings: {{ $session->warning_count }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="badge bg-success animate-pulse">Live</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('instructor.monitoring.show', $session) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Monitor
                                        </a>
                                        <form action="{{ route('instructor.monitoring.end', $session) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('End this monitoring session?')">
                                                <i class="bi bi-stop-circle"></i> End
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-broadcast fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No active monitoring sessions</p>
                    <p class="text-muted small">Start a new session to begin monitoring students</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Ended Sessions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-2"></i>
                Recent Sessions
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Session Name</th>
                            <th>Exam</th>
                            <th>Duration</th>
                            <th>Students</th>
                            <th>Warnings</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($endedSessions as $session)
                            <tr>
                                <td>{{ $session->session_name }}</td>
                                <td>{{ $session->exam->title ?? 'N/A' }}</td>
                                <td>{{ $session->formatted_duration }}</td>
                                <td>{{ $session->total_students }}</td>
                                <td>
                                    <span class="badge bg-{{ $session->warning_count > 0 ? 'warning' : 'secondary' }}">
                                        {{ $session->warning_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Ended</span>
                                </td>
                                <td>
                                    <a href="{{ route('instructor.monitoring.show', $session) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('instructor.monitoring.export', $session) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No ended sessions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Start Session Modal -->
<div class="modal fade" id="startSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-play-circle me-2"></i>Start Monitoring Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('instructor.monitoring.start') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="exam_id" class="form-label">Select Exam <span class="text-danger">*</span></label>
                        <select name="exam_id" id="exam_id" class="form-control" required>
                            <option value="">Select Exam</option>
                            @foreach($availableExams ?? [] as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="session_name" class="form-label">Session Name</label>
                        <input type="text" name="settings[session_name]" id="session_name" 
                               class="form-control" 
                               value="Monitoring Session {{ now()->format('d M Y H:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Monitoring Settings</label>
                        <div class="form-check">
                            <input type="checkbox" name="settings[track_tab_switches]" 
                                   class="form-check-input" id="track_tab" value="1" checked>
                            <label class="form-check-label" for="track_tab">Track Tab Switches</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="settings[enable_proctoring]" 
                                   class="form-check-input" id="enable_proctoring" value="1" checked>
                            <label class="form-check-label" for="enable_proctoring">Enable Proctoring</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="screenshot_interval" class="form-label">Screenshot Interval (seconds)</label>
                        <input type="number" name="settings[screenshot_interval]" 
                               id="screenshot_interval" class="form-control" value="60" min="10">
                        <small class="text-muted">How often to capture screenshots</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Start Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



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
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
</style>
@endpush
@endsection