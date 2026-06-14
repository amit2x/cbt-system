@extends('layouts.instructor')

@section('title', 'Feedback Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Feedback Details</h1>
        <div>
            <a href="{{ route('instructor.feedback.responses', $feedback) }}" class="btn btn-success">
                <i class="bi bi-bar-chart"></i> View Responses
            </a>
            <a href="{{ route('instructor.feedback.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Feedback Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Feedback Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Feedback Code:</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="feedbackCode"
                                                value="{{ $feedback->code }}" readonly>
                                            <button class="btn btn-primary" onclick="copyCode()">Copy</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Batch:</th>
                                    <td>{{ $feedback->batch->name }} ({{ $feedback->batch->course->title }})</td>
                                </tr>
                                <tr>
                                    <th>Instructor:</th>
                                    <td>{{ $feedback->instructor->name }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        <span class="badge bg-{{ $feedback->type == 'quick' ? 'success' : 'primary' }}">
                                            {{ ucfirst($feedback->type) }} Feedback
                                        </span>
                                    </td>
                                </tr>
                                </tr>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span
                                            class="badge bg-{{ $feedback->status == 'active' ? 'success' : ($feedback->status == 'expired' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($feedback->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $feedback->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Expires:</th>
                                    <td>
                                        @if($feedback->expires_at)
                                        {{ $feedback->expires_at->format('d M Y h:i A') }}
                                        @if($feedback->expires_at->isFuture())
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Expired</span>
                                        @endif
                                        @else
                                        Never
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Responses:</th>
                                    <td>
                                        <span class="badge bg-primary">{{ $responsesCount }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions (for detailed feedback) -->
            @if($feedback->type == 'detailed' && $feedback->questions)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Feedback Questions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($feedback->questions as $index => $question)
                        <div class="list-group-item">
                            <strong>Q{{ $index + 1 }}:</strong> {{ $question }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Share Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Share Feedback Link</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <h4 class="text-primary">{{ $feedback->code }}</h4>
                        <p class="text-muted">Share this code with students</p>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="shareLink" value="{{ $shareUrl }}" readonly>
                        <button class="btn btn-primary" onclick="copyLink()">Copy</button>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Students can access feedback using this code or link.
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('instructor.feedback.responses', $feedback) }}" class="btn btn-info">
                            <i class="bi bi-bar-chart"></i> View Responses
                        </a>
                        @if($feedback->status == 'active')
                        <form method="POST" action="{{ route('instructor.feedback.close', $feedback) }}">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-warning w-100"
                                onclick="return confirm('Close this feedback session? Students will no longer be able to submit responses.')">
                                <i class="bi bi-lock"></i> Close Feedback
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('instructor.feedback.export', $feedback) }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Export Responses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyCode() {
        const codeInput = document.getElementById('feedbackCode');
        codeInput.select();
        document.execCommand('copy');
        toastr.success('Feedback code copied to clipboard!');
    }

    function copyLink() {
        const linkInput = document.getElementById('shareLink');
        linkInput.select();
        document.execCommand('copy');
        toastr.success('Feedback link copied to clipboard!');
    }
</script>
@endpush