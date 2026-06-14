@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="404">404</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Map Background -->
            <circle cx="150" cy="100" r="80" fill="#f0f3f8" stroke="#4e73df" stroke-width="2" stroke-dasharray="5 5" class="rotate-element"/>

            <!-- Location Marker -->
            <circle cx="150" cy="100" r="15" fill="#4e73df" fill-opacity="0.2" class="floating-element"/>
            <circle cx="150" cy="100" r="10" fill="#4e73df" fill-opacity="0.4"/>
            <circle cx="150" cy="100" r="5" fill="#4e73df"/>

            <!-- Question Marks -->
            <text x="200" y="50" font-size="40" fill="#4e73df" fill-opacity="0.3" class="floating-element">?</text>
            <text x="220" y="80" font-size="30" fill="#4e73df" fill-opacity="0.3" class="floating-element" style="animation-delay: 0.5s;">?</text>
            <text x="190" y="120" font-size="35" fill="#4e73df" fill-opacity="0.3" class="floating-element" style="animation-delay: 1s;">?</text>

            <!-- Path Lines -->
            <path d="M50 150 L120 120 L180 140 L250 100" stroke="#4e73df" stroke-width="2" stroke-dasharray="5 5" fill="none" opacity="0.3"/>
            <path d="M80 50 L150 70 L220 40" stroke="#4e73df" stroke-width="2" stroke-dasharray="5 5" fill="none" opacity="0.3"/>

            <!-- Search Icon -->
            <circle cx="100" cy="170" r="15" fill="none" stroke="#4e73df" stroke-width="2"/>
            <line x1="110" y1="180" x2="120" y2="190" stroke="#4e73df" stroke-width="2"/>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Page Not Found</h1>

    <!-- Error Message -->
    <p class="error-message">
        Oops! It seems like you've wandered off the beaten path. The page you're looking for doesn't exist or has been moved.
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
    </div>

    <!-- Search Suggestions -->
    <div class="mt-4 pt-4 border-top">
        <p class="small text-muted mb-2">You might want to visit:</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ route('login') }}" class="text-decoration-none small text-primary">Login</a>
            <span class="text-muted">•</span>
            <a href="#" class="text-decoration-none small text-primary">Help Center</a>
            <span class="text-muted">•</span>
            <a href="{{ route('contact') }}" class="text-decoration-none small text-primary">Contact Support</a>
        </div>
    </div>
</div>
@endsection
