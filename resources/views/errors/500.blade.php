@extends('errors.layout')

@section('title', '500 - Server Error')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="500">500</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Server Background -->
            <circle cx="150" cy="100" r="80" fill="#e7f1ff" stroke="#0d6efd" stroke-width="2" stroke-dasharray="5 5"/>

            <!-- Server Rack -->
            <rect x="100" y="60" width="100" height="80" rx="10" fill="#0d6efd" fill-opacity="0.1" stroke="#0d6efd" stroke-width="2"/>

            <!-- Server Lights -->
            <circle cx="120" cy="80" r="5" fill="#0d6efd" fill-opacity="0.5" class="floating-element"/>
            <circle cx="140" cy="80" r="5" fill="#0d6efd" fill-opacity="0.5" class="floating-element" style="animation-delay: 0.2s;"/>
            <circle cx="160" cy="80" r="5" fill="#0d6efd" fill-opacity="0.5" class="floating-element" style="animation-delay: 0.4s;"/>
            <circle cx="180" cy="80" r="5" fill="#0d6efd" fill-opacity="0.5" class="floating-element" style="animation-delay: 0.6s;"/>

            <!-- Smoke/Steam -->
            <path d="M130 120 Q140 110 150 120 T170 120" stroke="#0d6efd" stroke-width="2" fill="none" opacity="0.5" stroke-dasharray="3 3">
                <animate attributeName="d" values="M130 120 Q140 110 150 120 T170 120;M130 115 Q140 105 150 115 T170 115;M130 120 Q140 110 150 120 T170 120" dur="3s" repeatCount="indefinite"/>
            </path>

            <!-- Warning Symbol -->
            <text x="200" y="50" font-size="25" fill="#0d6efd" fill-opacity="0.3">!</text>
            <circle cx="200" cy="45" r="12" fill="none" stroke="#0d6efd" stroke-width="2" opacity="0.3"/>

            <!-- Cog Wheels -->
            <circle cx="80" y="160" r="10" fill="none" stroke="#0d6efd" stroke-width="2" stroke-dasharray="2 2" class="rotate-element"/>
            <circle cx="80" y="160" r="5" fill="#0d6efd" fill-opacity="0.3"/>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Server Error</h1>

    <!-- Error Message -->
    <p class="error-message">
        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
        Oops! Something went wrong on our end. Our team has been notified and is working on it.
    </p>

    <!-- Action Buttons -->
    <div class="error-actions">
        <a href="{{ url()->previous() }}" class="btn-error btn-outline">
            <i class="bi bi-arrow-left"></i>
            Go Back
        </a>
        <a href="{{ url('/') }}" class="btn-error btn-primary">
            <i class="bi bi-house"></i>
            Return Home
        </a>
        <button onclick="location.reload()" class="btn-error btn-outline">
            <i class="bi bi-arrow-repeat"></i>
            Try Again
        </button>
    </div>

    <!-- Error Details (for debugging) -->
    @if(config('app.debug'))
    <div class="mt-4 p-3 bg-light rounded-3 text-start">
        <p class="small text-muted mb-2"><i class="bi bi-code-slash"></i> Error Details:</p>
        <pre class="small bg-white p-2 rounded" style="max-height: 200px; overflow: auto;">{{ $exception->getMessage() ?? 'No additional details available.' }}</pre>
    </div>
    @else
    <div class="mt-4">
        <p class="small text-muted">
            Need immediate assistance? <a href="{{ route('contact') }}" class="text-decoration-none">Contact Support</a>
        </p>
    </div>
    @endif
</div>
@endsection
