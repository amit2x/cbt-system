@extends('layouts.student')

@section('title', 'Already Responded')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow text-center">
                <div class="card-body py-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h3 class="mt-4">Thank You!</h3>
                    <p class="text-muted">You have already submitted feedback for this session.</p>
                    
                    @if(isset($feedback))
                        <div class="alert alert-success mt-4">
                            {{-- Safe optional chaining fallback to prevent application crashes --}}
                            <p class="mb-0"><strong>{{ $feedback->batch->course->title ?? 'Course Title Unavailable' }}</strong></p>
                            <p class="mb-0">Batch: {{ $feedback->batch->name ?? 'N/A' }}</p>
                            <p class="mb-0">Instructor: {{ $feedback->instructor->name ?? 'N/A' }}</p>
                        </div>
                    @endif

                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary mt-3">
                        {{-- Fixed icon handle from bi-house to bi-house-door --}}
                        <i class="bi bi-house-door me-1"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
