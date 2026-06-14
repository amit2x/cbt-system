@extends('errors.layout')

@section('title', '503 - Maintenance Mode')

@section('content')
<div class="text-center">
    <!-- Error Code -->
    <div class="error-code" data-text="503">503</div>

    <!-- Illustration -->
    <div class="error-illustration">
        <svg class="illustration-svg" width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Maintenance Background -->
            <circle cx="150" cy="100" r="80" fill="#d4edda" stroke="#28a745" stroke-width="2" stroke-dasharray="5 5"/>

            <!-- Wrench -->
            <path d="M120 70 L140 90 L110 120 L90 100 L120 70" fill="#28a745" fill-opacity="0.1" stroke="#28a745" stroke-width="2"/>
            <rect x="135" y="85" width="30" height="10" rx="5" fill="#28a745" fill-opacity="0.2" stroke="#28a745" stroke-width="2" transform="rotate(45 150 90)"/>

            <!-- Gear -->
            <circle cx="190" cy="120" r="20" fill="none" stroke="#28a745" stroke-width="3"/>
            <circle cx="190" cy="120" r="10" fill="#28a745" fill-opacity="0.2"/>
            <line x1="190" y1="100" x2="190" y2="140" stroke="#28a745" stroke-width="2"/>
            <line x1="170" y1="120" x2="210" y2="120" stroke="#28a745" stroke-width="2"/>
            <line x1="175" y1="105" x2="205" y2="135" stroke="#28a745" stroke-width="2"/>
            <line x1="175" y1="135" x2="205" y2="105" stroke="#28a745" stroke-width="2"/>

            <!-- Progress Bar -->
            <rect x="100" y="160" width="100" height="10" rx="5" fill="#e9ecef" stroke="#28a745" stroke-width="1"/>
            <rect x="100" y="160" width="60" height="10" rx="5" fill="#28a745" fill-opacity="0.5">
                <animate attributeName="width" values="60;80;60" dur="3s" repeatCount="indefinite"/>
            </rect>

            <!-- Construction Signs -->
            <text x="80" y="50" font-size="20" fill="#28a745" fill-opacity="0.3">🚧</text>
            <text x="200" y="180" font-size="20" fill="#28a745" fill-opacity="0.3">🔧</text>
        </svg>
    </div>

    <!-- Error Title -->
    <h1 class="error-title">Under Maintenance</h1>

    <!-- Error Message -->
    <p class="error-message">
        <i class="bi bi-tools text-success me-2"></i>
        We're currently performing scheduled maintenance to improve your experience. We'll be back shortly!
    </p>

    <!-- Estimated Time -->
    <div class="mb-4 p-3 bg-light rounded d-inline-block">
        <small class="text-muted">⏱️ Estimated time remaining</small>
        <div class="h4 mb-0 text-success" id="countdown">30:00</div>
    </div>

    <!-- Action Buttons -->
    <div class="error-actions">
        <button onclick="location.reload()" class="btn-error btn-primary">
            <i class="bi bi-arrow-repeat"></i>
            Check Again
        </button>
        <a href="{{ route('contact') }}" class="btn-error btn-outline">
            <i class="bi bi-envelope"></i>
            Notify Me
        </a>
    </div>

    <!-- Social Media -->
    <div class="mt-4 pt-4 border-top">
        <p class="small text-muted mb-2">Follow us for updates:</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#" class="text-decoration-none text-primary fs-5"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="text-decoration-none text-primary fs-5"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-decoration-none text-primary fs-5"><i class="bi bi-linkedin"></i></a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Countdown timer for 30 minutes
    let minutes = 29;
    let seconds = 60;
    const timerEl = document.getElementById('countdown');

    function updateTimer() {
        if (seconds === 0) {
            if (minutes === 0) {
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
