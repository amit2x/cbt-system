@extends('errors.layout')

@section('title', '429 - Too Many Requests')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="429">429</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Speed Limit Background -->
            <circle cx="150" cy="100" r="80" fill="#f8d7da" stroke="#e74a3b" stroke-width="2" stroke-dasharray="5 5"/>

            <!-- Speedometer -->
            <circle cx="150" cy="100" r="50" fill="white" stroke="#e74a3b" stroke-width="3"/>
            <circle cx="150" cy="100" r="40" fill="none" stroke="#e74a3b" stroke-width="1" stroke-dasharray="2 2"/>

            <!-- Speedometer Numbers -->
            <text x="185" y="80" font-size="10" fill="#e74a3b">120</text>
            <text x="195" y="110" font-size="10" fill="#e74a3b">140</text>
            <text x="175" y="135" font-size="10" fill="#e74a3b">160</text>
            <text x="125" y="135" font-size="10" fill="#e74a3b">180</text>
            <text x="105" y="110" font-size="10" fill="#e74a3b">200</text>

            <!-- Speedometer Needle -->
            <line x1="150" y1="100" x2="180" y2="70" stroke="#e74a3b" stroke-width="3" stroke-linecap="round" transform="rotate(135 150 100)"/>

            <!-- Warning Lights -->
            <circle cx="120" cy="70" r="8" fill="#e74a3b" fill-opacity="0.3" class="floating-element"/>
            <circle cx="120" cy="70" r="4" fill="#e74a3b"/>

            <!-- Alert Icons -->
            <text x="70" y="160" font-size="25" fill="#e74a3b" fill-opacity="0.3" class="floating-element" style="animation-delay: 0.2s;">⚠️</text>
            <text x="210" y="50" font-size="20" fill="#e74a3b" fill-opacity="0.3" class="floating-element" style="animation-delay: 0.5s;">⚠️</text>

            <!-- Barrier -->
            <line x1="50" y1="180" x2="250" y2="180" stroke="#e74a3b" stroke-width="4" stroke-dasharray="5 5"/>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Too Many Requests</h1>

    <!-- Error Message -->
    <p class="error-message">
        <i class="bi bi-speedometer2 text-danger me-2"></i>
        You've been making too many requests. Please slow down and try again later.
    </p>

    <!-- Wait Time -->
    <div class="mb-4 p-3 bg-light rounded d-inline-block">
        <small class="text-muted">⏱️ Try again in</small>
        <div class="h3 mb-0 text-danger" id="timer">00:00</div>
    </div>

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

    <!-- Tips -->
    <div class="mt-4 p-3 bg-light rounded-3">
        <p class="small text-muted mb-1"><i class="bi bi-lightbulb text-warning"></i> Tips to avoid rate limiting:</p>
        <ul class="small text-muted mb-0 text-start">
            <li>Don't refresh the page repeatedly</li>
            <li>Wait a few seconds between actions</li>
            <li>Check your internet connection</li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
    // Countdown timer for 60 seconds
    let minutes = 1;
    let seconds = 0;
    const timerEl = document.getElementById('timer');

    function updateTimer() {
        if (seconds === 0) {
            if (minutes === 0) {
                timerEl.textContent = '00:00';
                return;
            }
            minutes--;
            seconds = 59;
        } else {
            seconds--;
        }

        timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    setInterval(updateTimer, 1000);
</script>
@endpush
@endsection
