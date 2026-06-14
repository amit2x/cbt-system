@extends('layouts.instructor')

@section('title', 'All Feedback Sessions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Feedback Sessions</h1>
        <a href="{{ route('instructor.feedback.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Feedback
        </a>
    </div>

    <!-- Feedback List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Batch</th>
                            <th>Course</th>
                            <th>Type</th>
                            <th>Responses</th>
                            <th>Status</th>
                            <th>Expires At</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedbacks as $feedback)
                        <tr>
                            <td>{{ $feedback->id }}</td>
                            <td>
                                <span class="badge bg-info">{{ $feedback->code }}</span>
                            </td>
                            <td>{{ $feedback->batch->name }}</td>
                            <td>{{ $feedback->batch->course->title ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $feedback->type == 'quick' ? 'success' : 'primary' }}">
                                    {{ ucfirst($feedback->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $feedback->responses_count ??
                                    $feedback->responses->count() }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $feedback->status == 'active' ? 'success' : ($feedback->status == 'expired' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($feedback->status) }}
                                </span>
                            </td>
                            <td>
                                @if($feedback->expires_at)
                                {{ $feedback->expires_at->format('d M Y') }}
                                @else
                                Never
                                @endif
                            </td>
                            <td>{{ $feedback->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('instructor.feedback.show', $feedback) }}"
                                        class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('instructor.feedback.responses', $feedback) }}"
                                        class="btn btn-sm btn-success" title="Responses">
                                        <i class="bi bi-bar-chart"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="deleteFeedback({{ $feedback->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-chat-dots fs-1 text-muted"></i>
                                <p class="mt-2">No feedback sessions created yet.</p>
                                <a href="{{ route('instructor.feedback.create') }}" class="btn btn-primary mt-2">
                                    Create Your First Feedback
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $feedbacks->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this feedback session?</p>
                <p class="text-danger">This action cannot be undone. All responses will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteFeedback(id) {
        $('#deleteForm').attr('action', '/instructor/feedback/' + id);
        $('#deleteModal').modal('show');
    }
</script>
@endpush