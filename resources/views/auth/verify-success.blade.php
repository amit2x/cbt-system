@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Email Verified')

@section('content')
<!-- Animated Background with Educational Icons -->
<div class="background-elements">
    <!-- Success Icons -->
    <div class="floating-icon" style="top: 10%; left: 5%;">✅</div>
    <div class="floating-icon" style="top: 20%; right: 8%;">✓</div>
    <div class="floating-icon" style="bottom: 15%; left: 10%;">🎉</div>
    <div class="floating-icon" style="bottom: 25%; right: 12%;">✨</div>

    <!-- Books -->
    <div class="floating-icon book-1">📚</div>
    <div class="floating-icon book-2">📖</div>

    <!-- Email Icons -->
    <div class="floating-icon" style="top: 40%; left: 20%;">📧</div>
    <div class="floating-icon" style="top: 60%; right: 15%;">📨</div>

    <!-- Graduation Caps -->
    <div class="floating-icon grad-1">🎓</div>
    <div class="floating-icon grad-2">🎓</div>
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
                <!-- Success Icon -->
                <div class="display-1 mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>

                <h3 class="mb-3">Email Verified Successfully!</h3>

                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Your email has been verified.</strong>
                    <p class="mb-0 mt-2">
                        Thank you for verifying your email address. You can now access all features of your account.
                    </p>
                </div>

                <!-- Benefits -->
                <div class="benefits-section mb-4">
                    <h5 class="mb-3">What you can do now:</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="benefit-item p-3 bg-light rounded-3 text-center">
                                <i class="bi bi-pencil-square fs-2 text-primary mb-2"></i>
                                <h6>Take Exams</h6>
                                <p class="small text-muted mb-0">Access all available exams</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="benefit-item p-3 bg-light rounded-3 text-center">
                                <i class="bi bi-bar-chart fs-2 text-primary mb-2"></i>
                                <h6>View Results</h6>
                                <p class="small text-muted mb-0">Check your performance</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="benefit-item p-3 bg-light rounded-3 text-center">
                                <i class="bi bi-chat fs-2 text-primary mb-2"></i>
                                <h6>Give Feedback</h6>
                                <p class="small text-muted mb-0">Share your thoughts</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="benefit-item p-3 bg-light rounded-3 text-center">
                                <i class="bi bi-trophy fs-2 text-primary mb-2"></i>
                                <h6>Track Progress</h6>
                                <p class="small text-muted mb-0">Monitor your improvement</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-3">
                    <a href="{{ route('student.dashboard') }}" class="btn-submit text-center text-decoration-none">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Go to Dashboard
                    </a>

                    <a href="{{ route('student.exams.index') }}" class="btn btn-outline-primary py-3 rounded-3 text-decoration-none">
                        <i class="bi bi-pencil-square me-2"></i>
                        Browse Available Exams
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
    .benefit-item {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        height: 100%;
    }

    .benefit-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-outline-primary {
        background: transparent;
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        color: white;
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

    // Celebration effect
    document.addEventListener('DOMContentLoaded', function() {
        // Add subtle celebration effect
        const icons = document.querySelectorAll('.floating-icon');
        icons.forEach((icon, index) => {
            setTimeout(() => {
                icon.style.transition = 'transform 0.5s ease';
                icon.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    icon.style.transform = 'scale(1)';
                }, 500);
            }, index * 100);
        });
    });
</script>
@endpush
