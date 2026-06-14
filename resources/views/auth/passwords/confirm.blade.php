@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Confirm Password')

@push('styles')
<style>
    .password-requirements {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .password-requirements li {
        margin-bottom: 5px;
    }

    .password-requirements li i {
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<!-- Animated Background with Educational Icons -->
<div class="background-elements">
    <!-- Books -->
    <div class="floating-icon book-1">📚</div>
    <div class="floating-icon book-2">📖</div>

    <!-- Security Icons -->
    <div class="floating-icon" style="top: 15%; left: 10%;">🔐</div>
    <div class="floating-icon" style="top: 35%; right: 15%;">🔒</div>
    <div class="floating-icon" style="bottom: 25%; left: 20%;">🔑</div>
    <div class="floating-icon" style="bottom: 45%; right: 25%;">🛡️</div>

    <!-- Verification Badges -->
    <div class="floating-icon" style="top: 50%; left: 30%;">✓</div>
    <div class="floating-icon" style="top: 70%; right: 35%;">✅</div>

    <!-- Pens -->
    <div class="floating-icon pen-1">✏️</div>
    <div class="floating-icon pen-2">🖊️</div>
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
                <p class="brand-subtitle">Confirm your password to continue</p>
            </div>

            <!-- Security Notice -->
            <div class="alert alert-warning mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-lock-fill fs-3 me-3"></i>
                    <div>
                        <strong>Secure Area</strong>
                        <p class="mb-0 small">Please confirm your password before accessing this area.</p>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <ul class="mb-0" style="list-style: none; padding-left: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Confirm Password Form -->
            <form method="POST" action="{{ route('password.confirm') }}" id="confirmForm">
                @csrf

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input id="password"
                               type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password"
                               placeholder="Enter your password"
                               required
                               autofocus>
                        <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                            👁️
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements mb-4 p-3 bg-light rounded-3">
                    <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Password Requirements:</h6>
                    <ul class="small text-muted mb-0" style="list-style: none; padding-left: 0;">
                        <li><i class="bi bi-check me-2 text-success"></i>Minimum 8 characters</li>
                        <li><i class="bi bi-check me-2 text-success"></i>Contains at least one uppercase letter</li>
                        <li><i class="bi bi-check me-2 text-success"></i>Contains at least one number</li>
                    </ul>
                </div>

                <!-- Remember Me -->
                <div class="checkbox-group">
                    <input type="checkbox"
                           id="remember"
                           name="remember">
                    <label for="remember">Keep me signed in on this device</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="confirmButton">
                    Confirm Password
                </button>

                <!-- Links -->
                <div class="auth-links">
                    @if (Route::has('password.request'))
                        <p class="mb-2">
                            <a href="{{ route('password.request') }}">
                                <i class="bi bi-question-circle me-1"></i>
                                Forgot Your Password?
                            </a>
                        </p>
                    @endif

                    <p class="mb-0">
                        <a href="{{ route('login') }}">
                            <i class="bi bi-arrow-left me-1"></i>
                            Back to Login
                        </a>
                    </p>
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
    // Password visibility toggle
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.querySelector('.password-toggle');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.textContent = '🔒';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = '👁️';
        }
    }

    // Loading state on form submit
    document.getElementById('confirmForm')?.addEventListener('submit', function(e) {
        const confirmButton = document.getElementById('confirmButton');
        if (confirmButton) {
            confirmButton.classList.add('loading');
            confirmButton.disabled = true;
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

    // Check password strength in real-time
    document.getElementById('password')?.addEventListener('input', function(e) {
        const password = e.target.value;
        const requirements = document.querySelectorAll('.password-requirements li');

        // Check length
        if (password.length >= 8) {
            requirements[0].innerHTML = '<i class="bi bi-check-circle-fill me-2 text-success"></i>Minimum 8 characters ✓';
        } else {
            requirements[0].innerHTML = '<i class="bi bi-check me-2 text-success"></i>Minimum 8 characters';
        }

        // Check uppercase
        if (/[A-Z]/.test(password)) {
            requirements[1].innerHTML = '<i class="bi bi-check-circle-fill me-2 text-success"></i>Contains at least one uppercase letter ✓';
        } else {
            requirements[1].innerHTML = '<i class="bi bi-check me-2 text-success"></i>Contains at least one uppercase letter';
        }

        // Check number
        if (/[0-9]/.test(password)) {
            requirements[2].innerHTML = '<i class="bi bi-check-circle-fill me-2 text-success"></i>Contains at least one number ✓';
        } else {
            requirements[2].innerHTML = '<i class="bi bi-check me-2 text-success"></i>Contains at least one number';
        }
    });
</script>
@endpush
