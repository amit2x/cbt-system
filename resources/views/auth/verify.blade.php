@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Verify Email')

@section('content')
<!-- Animated Background with Educational Icons -->
<div class="background-elements">
    <!-- Books -->
    <div class="floating-icon book-1">📚</div>
    <div class="floating-icon book-2">📖</div>
    <div class="floating-icon book-3">📕</div>
    <div class="floating-icon book-4">📗</div>

    <!-- Pens and Pencils -->
    <div class="floating-icon pen-1">✏️</div>
    <div class="floating-icon pen-2">🖊️</div>

    <!-- Email/Icons -->
    <div class="floating-icon" style="top: 25%; left: 20%;">📧</div>
    <div class="floating-icon" style="bottom: 35%; right: 15%;">📨</div>
    <div class="floating-icon" style="top: 60%; left: 35%;">📬</div>

    <!-- Verification Badges -->
    <div class="floating-icon" style="top: 15%; right: 30%;">✅</div>
    <div class="floating-icon" style="bottom: 45%; left: 45%;">✓</div>

    <!-- Additional Icons -->
    <div class="floating-icon" style="top: 70%; right: 40%;">🔐</div>
    <div class="floating-icon" style="bottom: 20%; left: 60%;">🔒</div>
</div>

<!-- Gradient Overlay -->
<div class="overlay-gradient"></div>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Brand Section -->
            <div class="brand-section">
                <div class="logo-wrapper">
                    @if(setting('app_logo'))
                        <img src="{{ setting('app_logo') }}" alt="{{ setting('app_name') }}">
                    @else
                        <h1 class="brand-title">{{ setting('app_name', 'CBT System') }}</h1>
                    @endif
                </div>
                <p class="brand-subtitle">Verify Your Email Address</p>
            </div>

            <!-- Main Content -->
            <div class="text-center mb-4">
                <div class="display-1 mb-3">
                    <i class="bi bi-envelope-check-fill text-primary" style="font-size: 5rem;"></i>
                </div>

                <h4 class="mb-3">Almost there!</h4>

                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                <div class="alert alert-info text-start mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Verify your email</strong>
                    <p class="mb-0 mt-2">
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                        {{ __('If you did not receive the email') }},
                    </p>
                </div>

                <!-- Resend Verification Form -->
                <form method="POST" action="{{ route('verification.resend') }}" id="resendForm" class="mt-4">
                    @csrf

                    <div class="verification-steps mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <i class="bi bi-envelope fs-2 text-primary"></i>
                                    <p class="mb-0 mt-2 small">1. Check your inbox</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <i class="bi bi-envelope-open fs-2 text-primary"></i>
                                    <p class="mb-0 mt-2 small">2. Open the email</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <i class="bi bi-check-circle fs-2 text-primary"></i>
                                    <p class="mb-0 mt-2 small">3. Click verify link</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn-submit" id="resendButton">
                            <i class="bi bi-arrow-repeat me-2"></i>
                            {{ __('Resend Verification Email') }}
                        </button>

                        <a href="{{ route('logout') }}" class="btn btn-outline-secondary py-3 rounded-3 text-decoration-none"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </form>

                <!-- Help Section -->
                <div class="mt-4 p-3 bg-light rounded-3">
                    <h6 class="mb-2"><i class="bi bi-question-circle me-2"></i>Need Help?</h6>
                    <p class="small text-muted mb-0">
                        Check your spam folder if you don't see the email.
                        Still having trouble? <a href="#" class="text-decoration-none">Contact Support</a>
                    </p>
                </div>

                <!-- Footer -->
                <div class="footer-note mt-4">
                    <p class="copyright">© {{ date('Y') }} {{ setting('app_name', 'CBT System') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .verification-steps .bg-light {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .verification-steps .bg-light:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-outline-secondary {
        background: transparent;
        border: 2px solid #E5EAF0;
        color: #546E7A;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        background: #E5EAF0;
        color: #0A2540;
        border-color: #0A2540;
    }
</style>
@endpush
@push('scripts')

<script>
    // Loading state on form submit
    document.getElementById('resendForm')?.addEventListener('submit', function(e) {
        const resendButton = document.getElementById('resendButton');
        if (resendButton) {
            resendButton.classList.add('loading');
            resendButton.disabled = true;

            // Change button text
            resendButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
        }
    });

    // Dynamic animation for floating elements
    document.addEventListener('mousemove', function(e) {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;

        const elements = document.querySelectorAll('.floating-icon');
        elements.forEach((el, index) => {
            const speed = 10 + (index % 5) * 2;
            const x = (mouseX * speed) - (speed/2);
            const y = (mouseY * speed) - (speed/2);
            el.style.transform = `translate(${x}px, ${y}px)`;
        });
    });
</script>
@endpush
