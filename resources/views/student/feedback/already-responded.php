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
                    <div class="alert alert-success mt-4">
                        <p class="mb-0"><strong>{{ $feedback->batch->course->title }}</strong></p>
                        <p class="mb-0">Batch: {{ $feedback->batch->name }}</p>
                        <p class="mb-0">Instructor: {{ $feedback->instructor->name }}</p>
                    </div>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-house"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
