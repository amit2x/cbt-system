@extends('layouts.instructor')

@section('title', 'Live Monitoring')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Live Exam Monitoring</h1>
    </div>

    @if($activeSessions->count() > 0)
        <!-- Active Sessions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Active Monitoring Sessions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($activeSessions as $session)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-left-success shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">{{ $session->session_name }}</h5>
                                        <span class="badge bg-success">LIVE</span>
                                    </div>
                                    <p class="card-text small text-muted mb-2">
                                        {{ $session->exam->title }}
                                        @if($session->batch)
                                            <br>Batch: {{ $session->batch->name }}
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between mb-3">
                                        <div>
                                            <span class="fw-bold">{{ $session->active_students }}</span>
                                            <small class="text-muted"> active</small>
                                        </div>
                                        <div>
                                            <span class="fw-bold">{{ $session->warning_count }}</span>
                                            <small class="text-muted"> warnings</small>
                                        </div>
                                        <div>
                                            <span class="fw-bold">{{ $session->duration }}</span>
                                            <small class="text-muted"> min</small>
                                        </div>
                                    </div>
                                    <a href="{{ route('instructor.live-monitoring.show', $session) }}"
                                       class="btn btn-primary w-100">
                                        <i class="bi bi-camera-video"></i> Monitor Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Available Exams to Monitor -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Available Exams to Monitor</h6>
        </div>
        <div class="card-body">
            @if($activeExams->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Course</th>
                                <th>Batch</th>
                                <th>Duration</th>
                                <th>Ends At</th>
                                <th>Students</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeExams as $exam)
                                <tr>
                                    <td>
                                        <strong>{{ $exam->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</small>
                                    </td>
                                    <td>{{ $exam->course->title ?? 'N/A' }}</td>
                                    <td>
                                        @if($exam->batch)
                                            {{ $exam->batch->name }}
                                        @else
                                            <select class="form-select form-select-sm batch-select"
                                                    data-exam-id="{{ $exam->id }}">
                                                <option value="">All Batches</option>
                                                @foreach($exam->batches as $batch)
                                                    <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td>{{ $exam->formatted_duration }}</td>
                                    <td>
                                        @if($exam->end_time)
                                            {{ $exam->end_time->format('H:i') }}
                                            <br>
                                            <small class="text-muted">{{ $exam->end_time->diffForHumans() }}</small>
                                        @else
                                            Not scheduled
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $exam->total_students }}</span>
                                        <br>
                                        <small class="text-muted">enrolled</small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success start-monitoring"
                                                data-exam-id="{{ $exam->id }}">
                                            <i class="bi bi-play-circle"></i> Start Monitoring
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-camera-video-off fs-1 text-muted"></i>
                    <p class="mt-2">No active exams available for monitoring.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Start Monitoring Modal -->
<div class="modal fade" id="startMonitoringModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="startMonitoringForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Start Monitoring Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="exam_id" id="monitor_exam_id">

                    <div class="mb-3">
                        <label for="batch_id" class="form-label">Select Batch (Optional)</label>
                        <select class="form-control" id="monitor_batch_id" name="batch_id">
                            <option value="">All Batches</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Monitoring Settings</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox"
                                   id="track_tab_switches" name="settings[track_tab_switches]" value="1" checked>
                            <label class="form-check-label" for="track_tab_switches">
                                Track Tab Switches
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox"
                                   id="enable_proctoring" name="settings[enable_proctoring]" value="1">
                            <label class="form-check-label" for="enable_proctoring">
                                Enable Proctoring (Capture Screenshots)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox"
                                   id="auto_terminate" name="settings[alert_settings][auto_terminate]" value="1" checked>
                            <label class="form-check-label" for="auto_terminate">
                                Auto-terminate after max warnings
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="screenshot_interval" class="form-label">Screenshot Interval (seconds)</label>
                        <input type="number" class="form-control" id="screenshot_interval"
                               name="settings[screenshot_interval]" value="30" min="10" max="300">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Monitoring</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.start-monitoring').on('click', function() {
            const examId = $(this).data('exam-id');
            const row = $(this).closest('tr');

            $('#monitor_exam_id').val(examId);

            // Get batch options for this exam
            const batchSelect = row.find('.batch-select');
            const options = batchSelect.html();
            $('#monitor_batch_id').html(options);

            $('#startMonitoringForm').attr('action', `/instructor/live-monitoring/start/${examId}`);
            $('#startMonitoringModal').modal('show');
        });
    });
</script>
@endpush
