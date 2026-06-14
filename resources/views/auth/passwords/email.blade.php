@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Forgot Password')

@section('content')
<!-- Animated Background with Educational Icons -->
<div class="background-elements">
    <!-- Books -->
    <div class="floating-icon book-1">📚</div>
    <div class="floating-icon book-2">📖</div>
    <div class="floating-icon book-3">📕</div>
    <div class="floating-icon book-4">📗</div>

    <!-- Pens -->
    <div class="floating-icon pen-1">✏️</div>
    <div class="floating-icon pen-2">🖊️</div>

    <!-- Light Bulbs (Ideas) -->
    <div class="floating-icon bulb-1">💡</div>
    <div class="floating-icon bulb-2">💡</div>
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
                <p class="brand-subtitle">Reset your password</p>
            </div>

            <!-- Info Message -->
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i>
                <span>Enter your email address and we'll send you a password reset link.</span>
            </div>

            @if(session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <ul class="mb-0" style="list-style: none; padding-left: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Forgot Password Form -->
            <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input id="email"
                               type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="Enter your registered email"
                               required
                               autofocus>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submitButton">
                    Send Reset Link
                </button>

                <!-- Links -->
                <div class="auth-links">
                    <p class="mb-2">
                        <a href="{{ route('login') }}">
                            <i class="bi bi-arrow-left"></i> Back to Login
                        </a>
                    </p>

                    @if (Route::has('register'))
                        <p class="mb-0">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="fw-bold">
                                Sign Up
                            </a>
                        </p>
                    @endif
                </div>

                <!-- Footer -->
                <div class="footer-note">
                    <p class="copyright">© {{ date('Y') }} {{ setting('app_name', 'CBT System') }}. All rights reserved.</p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')

<script>
    // Loading state on form submit
    document.getElementById('forgotForm')?.addEventListener('submit', function(e) {
        const submitButton = document.getElementById('submitButton');
        if (submitButton) {
            submitButton.classList.add('loading');
            submitButton.disabled = true;
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
