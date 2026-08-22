@extends('layouts.auth')

@section('title', config('app.name') . ' - Login')

@section('content')
<div class="auth-card">
    <!-- Brand Section -->
    <div class="brand-section">
        <div class="logo-wrapper">
            @if(setting('app_logo'))
                <img src="{{ setting('app_logo') }}" alt="{{ setting('app_name') }}">
            @else
                <h1 class="brand-title">{{ config('app.name') }}</h1>
            @endif
        </div>
        <p class="brand-subtitle">Welcome back! Please login to your account</p>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
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

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" id="loginForm">
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
                       placeholder="Enter your email address"
                       required
                       autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

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
                       required>
                <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                    👁️
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="checkbox-group">
            <input type="checkbox"
                   id="remember"
                   name="remember"
                   {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">Keep me signed in on this device</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-submit" id="loginButton">
            Access Dashboard
        </button>

        <!-- Links -->
        <!--<div class="auth-links">-->
        <!--    @if (Route::has('password.request'))-->
        <!--        <p class="mb-2">-->
        <!--            <a href="{{ route('password.request') }}">-->
        <!--                Forgot your password?-->
        <!--            </a>-->
        <!--        </p>-->
        <!--    @endif-->

        <!--    @if (Route::has('register'))-->
        <!--        <p class="mb-0">-->
        <!--            Don't have an account?-->
        <!--            <a href="{{ route('register') }}" class="fw-bold">-->
        <!--                Create Account-->
        <!--            </a>-->
        <!--        </p>-->
        <!--    @endif-->
        <!--</div>-->

        <!-- Footer -->
        <!--<div class="footer-note">-->
        <!--    <p class="copyright">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>-->
        <!--    <p class="version">Version 1.0 • 256-bit SSL Encrypted</p>-->
        <!--</div>-->
    </form>
</div>

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
    document.getElementById('loginForm')?.addEventListener('submit', function(e) {
        const loginButton = document.getElementById('loginButton');
        if (loginButton) {
            loginButton.classList.add('loading');
            loginButton.disabled = true;
        }
    });

    // Dynamic animation for floating elements based on mouse position
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
@endsection
