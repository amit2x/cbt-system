@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Reset Password')

@section('content')
<!-- Animated Background with Educational Icons -->
<div class="background-elements">
    <!-- Books -->
    <div class="floating-icon book-1">📚</div>
    <div class="floating-icon book-2">📖</div>

    <!-- Pens -->
    <div class="floating-icon pen-1">✏️</div>
    <div class="floating-icon pen-2">🖊️</div>

    <!-- Lock Icons for Security -->
    <div class="floating-icon" style="top: 30%; left: 15%;">🔐</div>
    <div class="floating-icon" style="bottom: 40%; right: 20%;">🔒</div>
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
                <p class="brand-subtitle">Create a new password</p>
            </div>

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

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input id="email"
                               type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email"
                               value="{{ $email ?? old('email') }}"
                               placeholder="Enter your email"
                               required
                               readonly>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input id="password"
                               type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password"
                               placeholder="Enter new password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')" tabindex="-1">
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input id="password_confirmation"
                               type="password"
                               class="form-control"
                               name="password_confirmation"
                               placeholder="Re-enter new password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')" tabindex="-1">
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Password Strength Indicator -->
                <div class="password-strength mb-3">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar" id="strength-bar" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <small class="text-muted" id="strength-text">Enter a password</small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="resetButton">
                    Reset Password
                </button>

                <!-- Links -->
                <div class="auth-links">
                    <p class="mb-0">
                        <a href="{{ route('login') }}">
                            <i class="bi bi-arrow-left"></i> Back to Login
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
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleBtn = passwordInput.nextElementSibling;

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.textContent = '🔒';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = '👁️';
        }
    }

    // Password strength checker
    document.getElementById('password')?.addEventListener('input', function(e) {
        const password = e.target.value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        let strength = 0;
        let feedback = '';

        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/)) strength += 15;
        if (password.match(/[$@#&!]+/)) strength += 10;

        strengthBar.style.width = strength + '%';

        if (strength < 30) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Weak password';
        } else if (strength < 60) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Medium password';
        } else if (strength < 80) {
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Good password';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Strong password';
        }
    });

    // Loading state on form submit
    document.getElementById('resetForm')?.addEventListener('submit', function(e) {
        const resetButton = document.getElementById('resetButton');
        if (resetButton) {
            resetButton.classList.add('loading');
            resetButton.disabled = true;
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
