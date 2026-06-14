@extends('layouts.instructor')

@section('title', 'My Batches')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Batches</h1>
    </div>

    <div class="row">
        @forelse($batches as $batch)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">{{ $batch->name }}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <strong>Course:</strong> {{ $batch->course->title }}<br>
                        <strong>Duration:</strong> {{ $batch->start_date->format('d M Y') }} - {{
                        $batch->end_date->format('d M Y') }}<br>
                        <strong>Status:</strong>
                        <span
                            class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($batch->status) }}
                        </span>
                    </p>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Students:</span>
                            <span class="fw-bold">{{ $batch->students_count }}/{{ $batch->capacity }}</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success"
                                style="width: {{ $batch->students_count > 0 ? ($batch->students_count / $batch->capacity) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('instructor.batches.show', $batch) }}" class="btn btn-primary w-100">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-grid-3x3-gap-fill fs-1 text-muted"></i>
                <p class="mt-3">No batches assigned yet.</p>
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $batches->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection