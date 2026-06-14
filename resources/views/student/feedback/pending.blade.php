@extends('layouts.student')

@section('title', 'Pending Feedback')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pending Feedback</h1>
        <a href=" {{ route('student.feedback.history') }} " class="btn btn-primary btn-sm">Feedback History</a>
    </div>

    <div class="row">
        @forelse($pendingFeedbacks as $feedback)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">Feedback Request</h5>
                </div>
                <div class="card-body">
                    <h6>{{ $feedback->batch->course->title }}</h6>
                    <p class="mb-2">
                        <i class="bi bi-people"></i> Batch: {{ $feedback->batch->name }}<br>
                        <i class="bi bi-person"></i> Instructor: {{ $feedback->instructor->name }}<br>
                        <i class="bi bi-clock"></i> Expires: {{ $feedback->expires_at?->format('d M Y H:i') ?? 'No
                        expiry' }}
                    </p>
                    <p class="text-muted small">
                        Type: {{ ucfirst($feedback->type) }} feedback
                        @if($feedback->questions)
                        <br>{{ count($feedback->questions) }} questions
                        @endif
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('student.feedback.show', $feedback->code) }}" class="btn btn-primary w-100">
                        <i class="bi bi-pencil-square"></i> Give Feedback
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No Pending Feedback</h4>
                <p class="text-muted">You've responded to all feedback requests.</p>
                <a href="{{ route('student.dashboard') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-house"></i> Go to Dashboard
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection