@extends('layouts.instructor')

@section('title', 'Live Monitoring: ' . $data['session']['name'])

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $data['session']['name'] }}</h1>
            <span class="text-muted">Started {{ $data['session']['duration'] }} minutes ago</span>
        </div>
        <div>
            <button type="button" class="btn btn-warning" onclick="endSession()">
                <i class="bi bi-stop-circle"></i> End Session
            </button>
            <a href="{{ route('instructor.live-monitoring.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Active Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-count">
                                {{ $data['overview']['active_students'] }}
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $data['overview']['completed'] }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Warnings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="warning-count">
                                {{ $data['overview']['warnings'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Terminated</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $data['overview']['terminated'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Activity Timeline (Last 30 Minutes)</h6>
        </div>
        <div class="card-body">
            <canvas id="activityChart" height="80"></canvas>
        </div>
    </div>

    <div class="row">
        <!-- Students List -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Active Students</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="students-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Started</th>
                                    <th>Progress</th>
                                    <th>Answered</th>
                                    <th>Marked</th>
                                    <th>Warnings</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['students'] as $student)
                                    <tr class="student-row" data-student-id="{{ $student['id'] }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $student['avatar'] }}"
                                                     class="rounded-circle me-2" width="30" height="30">
                                                <div>
                                                    <strong>{{ $student['name'] }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $student['email'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $student['started_at'] }}</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{ $student['progress'] }}%"></div>
                                            </div>
                                            <small>{{ round($student['progress']) }}%</small>
                                        </td>
                                        <td>{{ $student['answered'] }}</td>
                                        <td>{{ $student['marked'] }}</td>
                                        <td>
                                            <span class="badge bg-{{
                                                $student['warnings'] >= 5 ? 'danger' :
                                                ($student['warnings'] >= 3 ? 'warning' :
                                                ($student['warnings'] > 0 ? 'info' : 'secondary'))
                                            }}">
                                                {{ $student['warnings'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($student['has_suspicious_images'])
                                                <span class="badge bg-danger" title="Suspicious activity detected">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                </span>
                                            @endif
                                            <span class="badge bg-{{
                                                $student['status'] == 'critical' ? 'danger' :
                                                ($student['status'] == 'warning' ? 'warning' :
                                                ($student['status'] == 'attention' ? 'info' : 'success'))
                                            }}">
                                                {{ ucfirst($student['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info view-student"
                                                    data-student-id="{{ $student['id'] }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning warn-student"
                                                    data-student-id="{{ $student['id'] }}">
                                                <i class="bi bi-exclamation-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts Panel -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Live Alerts</h6>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div id="alerts-container">
                        @forelse($data['recent_alerts'] as $alert)
                            <div class="alert alert-{{ $alert['severity'] }} alert-dismissible fade show mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $alert['student'] }}</strong>
                                    <small>{{ $alert['time'] }}</small>
                                </div>
                                <p class="mb-0 small">{{ $alert['description'] }}</p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @empty
                            <p class="text-muted text-center">No recent alerts</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="studentModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="warningForm">
                    <input type="hidden" id="warning_student_id">
                    <div class="mb-3">
                        <label for="warning_message" class="form-label">Warning Message</label>
                        <textarea class="form-control" id="warning_message" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="sendWarning()">Send Warning</button>
            </div>
        </div>
    </div>
</div>

<!-- Terminate Modal -->
<div class="modal fade" id="terminateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terminate Exam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="terminateForm">
                    <input type="hidden" id="terminate_student_id">
                    <div class="mb-3">
                        <label for="terminate_reason" class="form-label">Reason for Termination</label>
                        <textarea class="form-control" id="terminate_reason" rows="3" required></textarea>
                    </div>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        This action cannot be undone. The student's exam will be immediately terminated.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="terminateStudent()">Terminate Exam</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let sessionId = {{ $data['session']['id'] }};
    let updateInterval;
    let activityChart;

    $(document).ready(function() {
        // Initialize activity chart
        const ctx = document.getElementById('activityChart').getContext('2d');
        activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($data['activity_chart']['labels']),
                datasets: [{
                    label: 'Activity',
                    data: @json($data['activity_chart']['data']),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Start auto-refresh
        startAutoRefresh();

        // View student details
        $('.view-student').on('click', function() {
            const studentId = $(this).data('student-id');
            viewStudentDetails(studentId);
        });

        // Warn student
        $('.warn-student').on('click', function() {
            const studentId = $(this).data('student-id');
            $('#warning_student_id').val(studentId);
            $('#warningModal').modal('show');
        });
    });

    function startAutoRefresh() {
        updateInterval = setInterval(refreshData, 5000); // Refresh every 5 seconds
    }

    function refreshData() {
        $.ajax({
            url: `/instructor/live-monitoring/${sessionId}/updates`,
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    updateDashboard(response.data);
                }
            },
            error: function() {
                console.error('Failed to refresh data');
            }
        });
    }

    function updateDashboard(data) {
        // Update counts
        $('#active-count').text(data.overview.active_students);
        $('#warning-count').text(data.overview.warnings);

        // Update students table
        let studentsHtml = '';
        data.students.forEach(student => {
            studentsHtml += `
                <tr class="student-row" data-student-id="${student.id}">
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${student.avatar}" class="rounded-circle me-2" width="30" height="30">
                            <div>
                                <strong>${student.name}</strong>
                                <br>
                                <small class="text-muted">${student.email}</small>
                            </div>
                        </div>
                    </td>
                    <td>${student.started_at}</td>
                    <td>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" style="width: ${student.progress}%"></div>
                        </div>
                        <small>${Math.round(student.progress)}%</small>
                    </td>
                    <td>${student.answered}</td>
                    <td>${student.marked}</td>
                    <td>
                        <span class="badge bg-${getWarningBadgeClass(student.warnings)}">
                            ${student.warnings}
                        </span>
                    </td>
                    <td>
                        ${student.has_suspicious_images ?
                            '<span class="badge bg-danger" title="Suspicious activity detected"><i class="bi bi-exclamation-triangle"></i></span>' : ''}
                        <span class="badge bg-${getStatusBadgeClass(student.status)}">
                            ${student.status.charAt(0).toUpperCase() + student.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-student"
                                onclick="viewStudentDetails(${student.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning warn-student"
                                onclick="prepareWarning(${student.id})">
                            <i class="bi bi-exclamation-circle"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#students-table tbody').html(studentsHtml);

        // Update alerts
        let alertsHtml = '';
        data.recent_alerts.forEach(alert => {
            alertsHtml += `
                <div class="alert alert-${alert.severity} alert-dismissible fade show mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>${alert.student}</strong>
                        <small>${alert.time}</small>
                    </div>
                    <p class="mb-0 small">${alert.description}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        });
        $('#alerts-container').html(alertsHtml || '<p class="text-muted text-center">No recent alerts</p>');

        // Update activity chart
        activityChart.data.labels = data.activity_chart.labels;
        activityChart.data.datasets[0].data = data.activity_chart.data;
        activityChart.update();
    }

    function getWarningBadgeClass(warnings) {
        if (warnings >= 5) return 'danger';
        if (warnings >= 3) return 'warning';
        if (warnings > 0) return 'info';
        return 'secondary';
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'critical': 'danger',
            'warning': 'warning',
            'attention': 'info',
            'good': 'success'
        };
        return classes[status] || 'secondary';
    }

    function viewStudentDetails(studentId) {
        $('#studentModal').modal('show');

        $.ajax({
            url: `/instructor/live-monitoring/${sessionId}/student/${studentId}`,
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    displayStudentDetails(response.data);
                }
            },
            error: function() {
                $('#studentModalBody').html('<p class="text-danger text-center">Error loading student details</p>');
            }
        });
    }

    function displayStudentDetails(data) {
        let timelineHtml = '';
        data.timeline.forEach(event => {
            timelineHtml += `
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="me-3">
                            <span class="badge bg-${event.color}">
                                <i class="bi bi-${event.icon}"></i>
                            </span>
                        </div>
                        <div>
                            <strong>${event.time.format('H:i:s')}</strong>
                            <p class="mb-0">${event.description}</p>
                        </div>
                    </div>
                </div>
            `;
        });

        let html = `
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <img src="${data.student.avatar}" class="rounded-circle mb-2" width="100" height="100">
                        <h5>${data.student.name}</h5>
                        <p class="text-muted">${data.student.email}</p>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Exam Progress</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>Started:</td>
                                    <td>${data.exam_progress.started_at}</td>
                                </tr>
                                <tr>
                                    <td>Time Elapsed:</td>
                                    <td>${data.exam_progress.time_elapsed}</td>
                                </tr>
                                <tr>
                                    <td>Progress:</td>
                                    <td>${data.exam_progress.progress}%</td>
                                </tr>
                                <tr>
                                    <td>Answered:</td>
                                    <td>${data.exam_progress.answered}</td>
                                </tr>
                                <tr>
                                    <td>Marked:</td>
                                    <td>${data.exam_progress.marked}</td>
                                </tr>
                                <tr>
                                    <td>Tab Switches:</td>
                                    <td>${data.exam_progress.tab_switches}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" onclick="prepareWarning(${data.student.id})">
                            <i class="bi bi-exclamation-circle"></i> Send Warning
                        </button>
                        <button class="btn btn-danger" onclick="prepareTerminate(${data.student.id})">
                            <i class="bi bi-x-circle"></i> Terminate Exam
                        </button>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Activity Timeline</h6>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            ${timelineHtml || '<p class="text-muted">No activity recorded</p>'}
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#studentModalBody').html(html);
    }

    function prepareWarning(studentId) {
        $('#warning_student_id').val(studentId);
        $('#warningModal').modal('show');
        $('#studentModal').modal('hide');
    }

    function sendWarning() {
        const studentId = $('#warning_student_id').val();
        const message = $('#warning_message').val();

        if (!message) {
            toastr.error('Please enter a warning message');
            return;
        }

        $.ajax({
            url: `/instructor/live-monitoring/${sessionId}/student/${studentId}/warn`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message: message
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success('Warning sent successfully');
                    $('#warningModal').modal('hide');
                    $('#warning_message').val('');
                }
            },
            error: function() {
                toastr.error('Error sending warning');
            }
        });
    }

    function prepareTerminate(studentId) {
        $('#terminate_student_id').val(studentId);
        $('#terminateModal').modal('show');
        $('#studentModal').modal('hide');
    }

    function terminateStudent() {
        const studentId = $('#terminate_student_id').val();
        const reason = $('#terminate_reason').val();

        if (!reason) {
            toastr.error('Please enter a reason for termination');
            return;
        }

        if (!confirm('Are you absolutely sure you want to terminate this student\'s exam?')) {
            return;
        }

        $.ajax({
            url: `/instructor/live-monitoring/${sessionId}/student/${studentId}/terminate`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: reason
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success('Student exam terminated');
                    $('#terminateModal').modal('hide');
                    $('#terminate_reason').val('');
                }
            },
            error: function() {
                toastr.error('Error terminating exam');
            }
        });
    }

    function endSession() {
        if (confirm('Are you sure you want to end this monitoring session?')) {
            $.ajax({
                url: `/instructor/live-monitoring/${sessionId}/end`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.href = '{{ route("instructor.live-monitoring.index") }}';
                },
                error: function() {
                    toastr.error('Error ending session');
                }
            });
        }
    }

    // Cleanup on page unload
    $(window).on('unload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
</script>
@endpush
