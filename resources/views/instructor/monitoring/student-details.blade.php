{{-- resources/views/instructor/monitoring/student-details.blade.php --}}

@extends('layouts.instructor')

@section('title', 'Student Proctoring Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-person-badge me-2"></i>
                {{ $studentExam->student->name ?? 'Unknown' }}
            </h1>
            <p class="text-muted small mb-0">
                Exam: {{ $studentExam->exam->title ?? 'N/A' }} | 
                Status: <span class="badge bg-{{ $studentExam->status == 'in_progress' ? 'success' : 'secondary' }}">
                    {{ $studentExam->status }}
                </span>
            </p>
        </div>
        <a href="{{ route('instructor.monitoring.show', $studentExam->exam_id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Monitoring
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Images</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_images'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-images fs-2 text-gray-300"></i>
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
                                Suspicious Images</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['suspicious_images'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fs-2 text-gray-300"></i>
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
                                Tab Switches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['tab_switches'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-left-right fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Reviewed Images</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['reviewed_images'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-all fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Screenshots Gallery -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-images me-2"></i>Screenshots
                        <span class="badge bg-primary ms-2">{{ $images->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($images as $image)
                            <div class="col-md-4 col-sm-6">
                                <div class="card h-100">
                                    <img src="{{ $image->thumbnail_url }}" 
                                         alt="Screenshot" 
                                         class="card-img-top" 
                                         style="height: 150px; object-fit: cover; cursor: pointer;"
                                         onclick="viewImage('{{ $image->image_url }}', '{{ $image->id }}')">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block">
                                                    {{ $image->created_at->format('h:i A') }}
                                                </small>
                                                <small class="text-muted">
                                                    {{ $image->formatted_size }}
                                                </small>
                                            </div>
                                            <div>
                                                @if($image->is_suspicious)
                                                    <span class="badge bg-danger" title="Suspicious">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                                @if($image->reviewed)
                                                    <span class="badge bg-info" title="Reviewed">
                                                        <i class="bi bi-check-circle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4">
                                <i class="bi bi-image fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No screenshots available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>Activity Log
                        <span class="badge bg-info ms-2">{{ $activities->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($activities as $activity)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-{{ $activity->severity == 'critical' ? 'danger' : ($activity->severity == 'warning' ? 'warning' : 'info') }}">
                                        {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                                    </span>
                                    <small class="text-muted">{{ $activity->occurred_at->diffForHumans() }}</small>
                                </div>
                                @if($activity->description)
                                    <small class="text-muted d-block mt-1">{{ $activity->description }}</small>
                                @endif
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No activity logs
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
                    <div class="d-flex gap-2 flex-wrap">
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
</script>
@endpush
@endsection