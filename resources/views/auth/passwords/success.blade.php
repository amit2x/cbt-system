@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Password Reset Successful')

@section('content')
<!-- Animated Background with Educational Icons -->
<div class="background-elements">
    <!-- Success Icons -->
    <div class="floating-icon" style="top: 15%; left: 10%;">🎉</div>
    <div class="floating-icon" style="top: 35%; right: 15%;">✨</div>
    <div class="floating-icon" style="bottom: 25%; left: 20%;">🌟</div>
    <div class="floating-icon" style="bottom: 45%; right: 25%;">⭐</div>

    <!-- Books -->
    <div class="floating-icon book-1">📚</div>
    <div class="floating-icon book-2">📖</div>

    <!-- Security Icons -->
    <div class="floating-icon" style="top: 50%; left: 30%;">🔐</div>
    <div class="floating-icon" style="top: 70%; right: 35%;">🔒</div>
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
            </div>

            <!-- Success Content -->
            <div class="text-center">
                <!-- Success Animation -->
                <div class="success-animation mb-4">
                    <div class="checkmark-circle">
                        <div class="background-circle"></div>
                        <div class="checkmark draw"></div>
                    </div>
                </div>

                <h3 class="mb-3">Password Reset Successful!</h3>

                <div class="alert alert-success text-start mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Your password has been reset successfully.</strong>
                    <p class="mb-0 mt-2 small">
                        You can now log in with your new password.
                    </p>
                </div>

                <!-- Next Steps -->
                <div class="next-steps mb-4">
                    <h5 class="mb-3">What's Next?</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center h-100">
                                <i class="bi bi-box-arrow-in-right fs-2 text-primary mb-2"></i>
                                <h6>Login</h6>
                                <p class="small text-muted mb-0">Access your account with new password</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center h-100">
                                <i class="bi bi-shield-check fs-2 text-primary mb-2"></i>
                                <h6>Update Profile</h6>
                                <p class="small text-muted mb-0">Review your security settings</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-3">
                    <a href="{{ route('login') }}" class="btn-submit text-center text-decoration-none">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Proceed to Login
                    </a>

                    <a href="{{ url('/') }}" class="btn btn-outline-secondary py-3 rounded-3 text-decoration-none">
                        <i class="bi bi-house me-2"></i>
                        Return to Homepage
                    </a>
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
    /* Success Animation */
    .success-animation {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .checkmark-circle {
        width: 100px;
        height: 100px;
        position: relative;
        display: inline-block;
        vertical-align: top;
    }

    .background-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(145deg, #28a745, #20c997);
        position: absolute;
    }

    .checkmark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(45deg);
        width: 40px;
        height: 20px;
        border-left: 4px solid white;
        border-bottom: 4px solid white;
        animation: checkmark 0.5s ease-in-out forwards;
        opacity: 0;
    }

    @keyframes checkmark {
        0% {
            width: 0;
            height: 0;
            opacity: 0;
        }
        50% {
            width: 40px;
            height: 0;
            opacity: 1;
        }
        100% {
            width: 40px;
            height: 20px;
            opacity: 1;
        }
    }

    .next-steps .bg-light {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .next-steps .bg-light:hover {
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

    // Confetti effect on success
    document.addEventListener('DOMContentLoaded', function() {
        // Simple confetti effect (you can add a confetti library for better effects)
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
                document.body.appendChild(confetti);

                setTimeout(() => confetti.remove(), 3000);
            }, i * 100);
        }
    });
</script>
@endpush
