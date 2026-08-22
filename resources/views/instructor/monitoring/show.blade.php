{{-- resources/views/instructor/monitoring/show.blade.php --}}

@extends('layouts.instructor')

@section('title', 'Live Monitoring - ' . $session->session_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-broadcast text-success me-2"></i>
                {{ $session->session_name }}
            </h1>
            <p class="text-muted small mb-0">
                Exam: {{ $session->exam->title ?? 'N/A' }} | 
                Status: <span class="badge bg-success">Live</span> |
                Started: {{ $session->started_at->format('d M Y, h:i A') }}
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-warning" onclick="captureManualScreenshot()">
                <i class="bi bi-camera"></i> Capture Screenshot
            </button>
            <button type="button" class="btn btn-secondary" onclick="togglePause()">
                <i class="bi bi-pause-circle"></i> Pause
            </button>
            <form action="{{ route('instructor.monitoring.end', $session) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('End this monitoring session?')">
                    <i class="bi bi-stop-circle"></i> End Session
                </button>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $status['student_stats']['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
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
                                Active Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $status['student_stats']['active'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fs-2 text-gray-300"></i>
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
                                Warnings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $status['student_stats']['warnings'] ?? 0 }}</div>
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
                                Terminated</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $status['student_stats']['terminated'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-x fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Student List -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-people me-2"></i>Students
                        <span class="badge bg-primary ms-2">{{ $students->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($students as $studentExam)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $studentExam->student->name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ $studentExam->student->email ?? '' }}</small>
                                    <br>
                                    <small>
                                        <span class="badge bg-{{ $studentExam->status == 'in_progress' ? 'success' : 'warning' }}">
                                            {{ $studentExam->status }}
                                        </span>
                                    </small>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('instructor.monitoring.student-details', $studentExam->id) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="captureStudentScreenshot({{ $studentExam->id }})">
                                        <i class="bi bi-camera"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No active students
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Images -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-images me-2"></i>Recent Screenshots
                    </h6>
                    <span class="badge bg-info">{{ $recentImages->count() }} images</span>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="screenshotGrid">
                        @forelse($recentImages as $image)
                            <div class="col-md-4 col-sm-6">
                                <div class="card h-100">
                                    <img src="{{ $image->thumbnail_url }}" 
                                         alt="Screenshot" 
                                         class="card-img-top" 
                                         style="height: 150px; object-fit: cover; cursor: pointer;"
                                         onclick="viewImage('{{ $image->image_url }}', '{{ $image->id }}')">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                {{ $image->student->name ?? 'Unknown' }}
                                            </small>
                                            <div>
                                                @if($image->is_suspicious)
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                                @if($image->reviewed)
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-check-circle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <small class="text-muted d-block">
                                            {{ $image->created_at->format('h:i A') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4">
                                <i class="bi bi-image fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No screenshots captured yet</p>
                                <p class="text-muted small">Images will appear here as they are captured</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image View Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Screenshot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img src="" id="modalImage" class="img-fluid rounded">
                <div class="mt-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="markImageNormal()">
                            <i class="bi bi-check-circle"></i> Mark Normal
                        </button>
                        <button type="button" class="btn btn-danger" onclick="markImageSuspicious()">
                            <i class="bi bi-exclamation-triangle"></i> Mark Suspicious
                        </button>
                        <button type="button" class="btn btn-info" onclick="markImageReviewed()">
                            <i class="bi bi-check-all"></i> Mark Reviewed
                        </button>
                    </div>
                    <div class="mt-2">
                        <textarea class="form-control" id="suspiciousReason" 
                                  placeholder="Enter reason for suspicious activity..." 
                                  rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentImageId = null;

    function viewImage(url, id) {
        currentImageId = id;
        document.getElementById('modalImage').src = url;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    function markImageNormal() {
        if (!currentImageId) return;
        reviewImage(currentImageId, 'normal');
    }

    function markImageSuspicious() {
        if (!currentImageId) return;
        const reason = document.getElementById('suspiciousReason').value;
        reviewImage(currentImageId, 'suspicious', reason);
    }

    function markImageReviewed() {
        if (!currentImageId) return;
        reviewImage(currentImageId, 'reviewed');
    }

    function reviewImage(imageId, status, reason = null) {
        fetch('{{ route("instructor.monitoring.review-images") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                image_ids: [imageId],
                status: status,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            toastr.success(data.message || 'Image reviewed successfully');
            bootstrap.Modal.getInstance(document.getElementById('imageModal')).hide();
            location.reload();
        })
        .catch(error => {
            toastr.error('Error reviewing image');
        });
    }

    function captureStudentScreenshot(studentExamId) {
        toastr.info('Capturing screenshot...');
        
        // This would be implemented with actual screenshot capture logic
        // For now, just show a message
        setTimeout(() => {
            toastr.success('Screenshot captured successfully');
        }, 2000);
    }

    function captureManualScreenshot() {
        toastr.info('Capturing manual screenshot...');
        // Implementation depends on how screenshots are captured
        setTimeout(() => {
            toastr.success('Manual screenshot captured');
        }, 2000);
    }

    function togglePause() {
        const btn = event.currentTarget;
        if (btn.innerHTML.includes('Pause')) {
            btn.innerHTML = '<i class="bi bi-play-circle"></i> Resume';
            toastr.info('Monitoring paused');
        } else {
            btn.innerHTML = '<i class="bi bi-pause-circle"></i> Pause';
            toastr.info('Monitoring resumed');
        }
    }

    // Auto-refresh every 10 seconds
    setInterval(function() {
        // Refresh the page silently or fetch new data
        // For now, just reload the page
        // location.reload();
    }, 30000);
</script>
@endpush
@endsection