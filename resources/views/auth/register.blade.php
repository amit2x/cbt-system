@extends('layouts.auth')

@section('title', setting('app_name', 'CBT System') . ' - Register')

@push('styles')
<style>
    /* Add specific styles for registration form */
    .name-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    @media (max-width: 576px) {
        .name-fields {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }
</style>
@endpush

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
    <div class="floating-icon pen-3">🖍️</div>
    <div class="floating-icon pen-4">✒️</div>

    <!-- Exam Related -->
    <div class="floating-icon exam-1">📝</div>
    <div class="floating-icon exam-2">📋</div>
    <div class="floating-icon exam-3">📄</div>
    <div class="floating-icon exam-4">📃</div>

    <!-- Additional Icons -->
    <div class="floating-icon" style="top: 15%; left: 30%;">🔬</div>
    <div class="floating-icon" style="top: 60%; right: 25%;">🧪</div>
    <div class="floating-icon" style="bottom: 25%; left: 45%;">📊</div>
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
                <p class="brand-subtitle">Create your account to get started</p>
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

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                <!-- Name Fields -->
                <div class="name-fields">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input id="first_name"
                                   type="text"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   placeholder="John"
                                   required>
                        </div>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input id="last_name"
                                   type="text"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   placeholder="Doe"
                                   required>
                        </div>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

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
                               placeholder="john.doe@example.com"
                               required>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Phone Field -->
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📱</span>
                        <input id="phone"
                               type="tel"
                               class="form-control @error('phone') is-invalid @enderror"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="+1234567890">
                    </div>
                    @error('phone')
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
                               placeholder="Create a strong password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')" tabindex="-1">
                            👁️
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                               placeholder="Re-enter your password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')" tabindex="-1">
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="checkbox-group">
                    <input type="checkbox"
                           id="terms"
                           name="terms"
                           required>
                    <label for="terms">
                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and
                        <a href="#" class="text-decoration-none">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="registerButton">
                    Create Account
                </button>

                <!-- Links -->
                <div class="auth-links">
                    <p class="mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="fw-bold">
                            Sign In
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

    // Loading state on form submit
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        const registerButton = document.getElementById('registerButton');
        if (registerButton) {
            registerButton.classList.add('loading');
            registerButton.disabled = true;
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
