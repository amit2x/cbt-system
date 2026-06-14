@extends('errors.layout')

@section('title', '403 - Forbidden')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="403">403</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Lock Background -->
            <circle cx="150" cy="100" r="80" fill="#fee2e2" stroke="#dc3545" stroke-width="2" stroke-dasharray="5 5"/>

            <!-- Lock Body -->
            <rect x="120" y="70" width="60" height="50" rx="10" fill="#dc3545" fill-opacity="0.1" stroke="#dc3545" stroke-width="2"/>
            <rect x="130" y="80" width="40" height="30" rx="5" fill="#dc3545" fill-opacity="0.2"/>

            <!-- Lock Shackle -->
            <path d="M140 70 L140 50 Q150 30 160 50 L160 70" stroke="#dc3545" stroke-width="4" fill="none"/>

            <!-- Keyhole -->
            <circle cx="150" cy="95" r="5" fill="#dc3545" fill-opacity="0.3"/>

            <!-- Warning Signs -->
            <text x="50" y="40" font-size="20" fill="#dc3545" fill-opacity="0.3" class="floating-element">⚠️</text>
            <text x="230" y="160" font-size="25" fill="#dc3545" fill-opacity="0.3" class="floating-element" style="animation-delay: 0.3s;">🚫</text>

            <!-- X Marks -->
            <line x1="200" y1="40" x2="220" y2="60" stroke="#dc3545" stroke-width="2" opacity="0.3"/>
            <line x1="220" y1="40" x2="200" y2="60" stroke="#dc3545" stroke-width="2" opacity="0.3"/>

            <!-- Barrier Lines -->
            <line x1="80" y1="150" x2="220" y2="150" stroke="#dc3545" stroke-width="3" stroke-dasharray="5 5"/>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Access Forbidden</h1>

    <!-- Error Message -->
    <p class="error-message">
        <i class="bi bi-shield-lock-fill text-danger me-2"></i>
        Sorry, you don't have permission to access this page. This area is restricted.
    </p>

    <!-- Action Buttons -->
    <div class="error-actions">
        <a href="{{ url()->previous() }}" class="btn-error btn-outline">
            <i class="bi bi-arrow-left"></i>
            Go Back
        </a>
        @auth
            <a href="{{ route('home') }}" class="btn-error btn-primary">
                <i class="bi bi-speedometer2"></i>
                Go to Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-error btn-primary">
                <i class="bi bi-box-arrow-in-right"></i>
                Login
            </a>
        @endauth
    </div>

    <!-- Help Text -->
    <div class="mt-4 small text-muted">
        <p class="mb-1">If you believe this is a mistake, please contact your administrator.</p>
        <a href="{{ route('contact') }}" class="text-decoration-none">Contact Support →</a>
    </div>
</div>
@endsection
