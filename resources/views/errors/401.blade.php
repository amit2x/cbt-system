@extends('errors.layout')

@section('title', '401 - Unauthorized')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="401">401</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Shield Background -->
            <circle cx="150" cy="100" r="80" fill="#fff3cd" stroke="#ffc107" stroke-width="2" stroke-dasharray="5 5"/>

            <!-- Shield -->
            <path d="M150 50 L190 70 L190 110 L150 150 L110 110 L110 70 L150 50" fill="#ffc107" fill-opacity="0.1" stroke="#ffc107" stroke-width="2"/>

            <!-- Lock -->
            <rect x="140" y="95" width="20" height="15" rx="3" fill="#ffc107" fill-opacity="0.3" stroke="#ffc107" stroke-width="2"/>
            <circle cx="150" cy="90" r="5" fill="none" stroke="#ffc107" stroke-width="2"/>

            <!-- Key Icon -->
            <path d="M200 130 L220 150 L210 160 L190 140" stroke="#ffc107" stroke-width="2" fill="none"/>
            <circle cx="180" cy="130" r="8" fill="none" stroke="#ffc107" stroke-width="2"/>

            <!-- Question Marks -->
            <text x="100" y="170" font-size="20" fill="#ffc107" fill-opacity="0.3">?</text>
            <text x="120" y="160" font-size="25" fill="#ffc107" fill-opacity="0.3">?</text>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Unauthorized Access</h1>

    <!-- Error Message -->
    <p class="error-message">
        <i class="bi bi-shield-lock-fill text-warning me-2"></i>
        You need to be authenticated to access this page. Please log in to continue.
    </p>

    <!-- Action Buttons -->
    <div class="error-actions">
        <a href="{{ route('login') }}" class="btn-error btn-primary">
            <i class="bi bi-box-arrow-in-right"></i>
            Login
        </a>
        <a href="{{ route('register') }}" class="btn-error btn-outline">
            <i class="bi bi-person-plus"></i>
            Register
        </a>
        <a href="{{ url('/') }}" class="btn-error btn-outline">
            <i class="bi bi-house"></i>
            Home
        </a>
    </div>

    <!-- Help -->
    <div class="mt-4">
        <p class="small text-muted">
            Already have an account? <a href="{{ route('login') }}" class="text-decoration-none">Login here</a>
        </p>
    </div>
</div>
@endsection
