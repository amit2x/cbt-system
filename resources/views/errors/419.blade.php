@extends('errors.layout')

@section('title', '419 - Session Expired')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="419">419</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Clock Background -->
            <circle cx="150" cy="100" r="80" fill="#fff3cd" stroke="#ffc107" stroke-width="2" stroke-dasharray="5 5"/>

            <!-- Clock Face -->
            <circle cx="150" cy="100" r="50" fill="white" stroke="#ffc107" stroke-width="3"/>

            <!-- Clock Numbers -->
            <text x="145" y="70" font-size="12" fill="#ffc107" text-anchor="middle">12</text>
            <text x="185" y="105" font-size="12" fill="#ffc107">3</text>
            <text x="145" y="140" font-size="12" fill="#ffc107">6</text>
            <text x="105" y="105" font-size="12" fill="#ffc107">9</text>

            <!-- Clock Hands -->
            <line x1="150" y1="100" x2="150" y2="70" stroke="#ffc107" stroke-width="3" stroke-linecap="round" transform="rotate(30 150 100)" class="rotate-element"/>
            <line x1="150" y1="100" x2="190" y2="100" stroke="#ffc107" stroke-width="2" stroke-linecap="round" transform="rotate(45 150 100)" class="rotate-element" style="animation-duration: 20s;"/>

            <!-- Expired Stamp -->
            <text x="200" y="50" font-size="18" fill="#ffc107" fill-opacity="0.5" transform="rotate(-15 200 50)">EXPIRED</text>

            <!-- Hourglass -->
            <path d="M120 150 L180 150 L160 120 L140 120 L120 150" stroke="#ffc107" stroke-width="2" fill="none"/>
            <path d="M140 120 L140 90" stroke="#ffc107" stroke-width="2"/>
            <path d="M160 120 L160 90" stroke="#ffc107" stroke-width="2"/>
            <circle cx="150" cy="85" r="5" fill="#ffc107" fill-opacity="0.3"/>

            <!-- Sand Particles -->
            <circle cx="150" cy="100" r="2" fill="#ffc107" class="floating-element" style="animation-delay: 0.1s;"/>
            <circle cx="145" cy="105" r="2" fill="#ffc107" class="floating-element" style="animation-delay: 0.3s;"/>
            <circle cx="155" cy="105" r="2" fill="#ffc107" class="floating-element" style="animation-delay: 0.5s;"/>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Session Expired</h1>

    <!-- Error Message -->
    <p class="error-message">
        <i class="bi bi-hourglass-split text-warning me-2"></i>
        Your session has timed out due to inactivity. Please log in again to continue.
    </p>

    <!-- Action Buttons -->
    <div class="error-actions">
        <a href="{{ route('login') }}" class="btn-error btn-primary">
            <i class="bi bi-box-arrow-in-right"></i>
            Login Again
        </a>
        <a href="{{ url('/') }}" class="btn-error btn-outline">
            <i class="bi bi-house"></i>
            Home Page
        </a>
    </div>

    <!-- Auto Redirect Info -->
    <div class="mt-4 pt-4 border-top">
        <p class="small text-muted mb-0">
            <i class="bi bi-info-circle"></i>
            You will be redirected to the login page in <span id="countdown">5</span> seconds.
        </p>
    </div>
</div>

@push('scripts')
<script>
    let seconds = 5;
    const countdownEl = document.getElementById('countdown');

    const interval = setInterval(() => {
        seconds--;
        if (countdownEl) countdownEl.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = '{{ route("login") }}';
        }
    }, 1000);
</script>
@endpush
@endsection
