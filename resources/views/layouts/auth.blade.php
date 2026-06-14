<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ setting('meta_description', 'CBT System - Online Exam Platform') }}">

    <title>@yield('title', 'Authentication') - {{ setting('app_name', 'CBT System') }}</title>

    <!-- Favicon -->
    @if(setting('app_favicon'))
        <link rel="icon" href="{{ setting('app_favicon') }}" type="image/x-icon">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @endif

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(145deg, #0A2540 0%, #1A3A5F 50%, #234B7A 100%);
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Animated Background Pattern */
        .background-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        /* Educational Icons Animation */
        .floating-icon {
            position: absolute;
            font-size: clamp(24px, 4vw, 32px);
            color: rgba(255, 255, 255, 0.1);
            pointer-events: none;
            z-index: 1;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));
        }

        /* Book Icons */
        .book-1 { top: 5%; left: 3%; animation: floatBook1 25s infinite ease-in-out; }
        .book-2 { top: 15%; right: 8%; animation: floatBook2 22s infinite ease-in-out; }
        .book-3 { bottom: 20%; left: 12%; animation: floatBook3 28s infinite ease-in-out; }
        .book-4 { bottom: 25%; right: 15%; animation: floatBook4 30s infinite ease-in-out; }

        /* Pen Icons */
        .pen-1 { top: 30%; left: 10%; animation: floatPen1 20s infinite ease-in-out; }
        .pen-2 { top: 45%; right: 5%; animation: floatPen2 26s infinite ease-in-out; }
        .pen-3 { bottom: 10%; left: 20%; animation: floatPen3 24s infinite ease-in-out; }
        .pen-4 { bottom: 40%; right: 20%; animation: floatPen4 32s infinite ease-in-out; }

        /* Exam/Question Icons */
        .exam-1 { top: 60%; left: 5%; animation: floatExam1 27s infinite ease-in-out; }
        .exam-2 { top: 70%; right: 10%; animation: floatExam2 23s infinite ease-in-out; }
        .exam-3 { bottom: 5%; left: 30%; animation: floatExam3 29s infinite ease-in-out; }
        .exam-4 { bottom: 15%; right: 25%; animation: floatExam4 31s infinite ease-in-out; }

        /* Graduation Caps */
        .grad-1 { top: 8%; right: 15%; animation: floatGrad1 26s infinite ease-in-out; }
        .grad-2 { bottom: 30%; left: 25%; animation: floatGrad2 28s infinite ease-in-out; }

        /* Light Bulbs (Ideas) */
        .bulb-1 { top: 40%; left: 15%; animation: floatBulb1 21s infinite ease-in-out; }
        .bulb-2 { bottom: 45%; right: 12%; animation: floatBulb2 25s infinite ease-in-out; }

        /* Calculators */
        .calc-1 { top: 75%; left: 18%; animation: floatCalc1 33s infinite ease-in-out; }
        .calc-2 { bottom: 50%; right: 30%; animation: floatCalc2 27s infinite ease-in-out; }

        /* Keyframes for floating animations */
        @keyframes floatBook1 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            33% { transform: translate(120px, 80px) rotate(15deg); opacity: 0.15; }
            66% { transform: translate(60px, 140px) rotate(-10deg); opacity: 0.12; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatBook2 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            25% { transform: translate(-100px, 70px) rotate(-12deg); opacity: 0.14; }
            50% { transform: translate(-180px, -40px) rotate(8deg); opacity: 0.12; }
            75% { transform: translate(-70px, -100px) rotate(-5deg); opacity: 0.13; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatBook3 {
            0% { transform: translate(0, 0) scale(1); opacity: 0.1; }
            50% { transform: translate(90px, -80px) scale(1.1); opacity: 0.16; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.1; }
        }

        @keyframes floatBook4 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            40% { transform: translate(-70px, -100px) rotate(20deg); opacity: 0.15; }
            80% { transform: translate(60px, -40px) rotate(-15deg); opacity: 0.13; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatPen1 {
            0% { transform: translate(0, 0) rotate(-10deg); opacity: 0.1; }
            30% { transform: translate(150px, 50px) rotate(20deg); opacity: 0.14; }
            60% { transform: translate(100px, 120px) rotate(-5deg); opacity: 0.12; }
            100% { transform: translate(0, 0) rotate(-10deg); opacity: 0.1; }
        }

        @keyframes floatPen2 {
            0% { transform: translate(0, 0) rotate(5deg); opacity: 0.1; }
            35% { transform: translate(-120px, -60px) rotate(-15deg); opacity: 0.15; }
            70% { transform: translate(-50px, 80px) rotate(10deg); opacity: 0.13; }
            100% { transform: translate(0, 0) rotate(5deg); opacity: 0.1; }
        }

        @keyframes floatPen3 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 0.1; }
            45% { transform: translate(80px, -90px) scale(1.2) rotate(25deg); opacity: 0.16; }
            90% { transform: translate(-40px, -30px) scale(0.9) rotate(-10deg); opacity: 0.12; }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatPen4 {
            0% { transform: translate(0, 0); opacity: 0.1; }
            40% { transform: translate(-140px, 70px); opacity: 0.15; }
            80% { transform: translate(-30px, 110px); opacity: 0.13; }
            100% { transform: translate(0, 0); opacity: 0.1; }
        }

        @keyframes floatExam1 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            30% { transform: translate(100px, -50px) rotate(-8deg); opacity: 0.14; }
            60% { transform: translate(150px, 40px) rotate(12deg); opacity: 0.12; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatExam2 {
            0% { transform: translate(0, 0) scale(1); opacity: 0.1; }
            40% { transform: translate(-90px, 110px) scale(0.9); opacity: 0.15; }
            80% { transform: translate(50px, 60px) scale(1.1); opacity: 0.13; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.1; }
        }

        @keyframes floatExam3 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            35% { transform: translate(120px, -80px) rotate(15deg); opacity: 0.16; }
            70% { transform: translate(30px, -120px) rotate(-10deg); opacity: 0.14; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatExam4 {
            0% { transform: translate(0, 0); opacity: 0.1; }
            45% { transform: translate(-110px, -70px); opacity: 0.15; }
            90% { transform: translate(-70px, 50px); opacity: 0.12; }
            100% { transform: translate(0, 0); opacity: 0.1; }
        }

        @keyframes floatGrad1 {
            0% { transform: translate(0, 0) rotate(-5deg); opacity: 0.1; }
            33% { transform: translate(80px, -90px) rotate(10deg); opacity: 0.14; }
            66% { transform: translate(130px, -30px) rotate(-8deg); opacity: 0.12; }
            100% { transform: translate(0, 0) rotate(-5deg); opacity: 0.1; }
        }

        @keyframes floatGrad2 {
            0% { transform: translate(0, 0) scale(1); opacity: 0.1; }
            50% { transform: translate(-150px, 100px) scale(1.2); opacity: 0.16; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.1; }
        }

        @keyframes floatBulb1 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            40% { transform: translate(90px, 130px) rotate(15deg); opacity: 0.15; }
            80% { transform: translate(-40px, 80px) rotate(-10deg); opacity: 0.13; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatBulb2 {
            0% { transform: translate(0, 0); opacity: 0.1; }
            30% { transform: translate(-100px, -80px); opacity: 0.14; }
            60% { transform: translate(-180px, 20px); opacity: 0.12; }
            100% { transform: translate(0, 0); opacity: 0.1; }
        }

        @keyframes floatCalc1 {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
            35% { transform: translate(110px, -100px) rotate(-12deg); opacity: 0.15; }
            70% { transform: translate(60px, -160px) rotate(8deg); opacity: 0.13; }
            100% { transform: translate(0, 0) rotate(0deg); opacity: 0.1; }
        }

        @keyframes floatCalc2 {
            0% { transform: translate(0, 0) scale(1); opacity: 0.1; }
            45% { transform: translate(-130px, 80px) scale(0.9); opacity: 0.16; }
            90% { transform: translate(-50px, 140px) scale(1.1); opacity: 0.14; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.1; }
        }

        /* Gradient overlay for better readability */
        .overlay-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, transparent 0%, rgba(10, 37, 64, 0.8) 100%);
            z-index: 2;
            pointer-events: none;
        }

        /* Main Container - Improved for desktop */
        .auth-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            padding: 30px 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 860px; /* Increased from 680px for better desktop view */
            margin: 0 auto;
            position: relative;
            z-index: 11;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 48px;
            padding: 50px 70px; /* Increased padding for desktop */
            box-shadow: 0 30px 70px -20px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        /* Shine effect */
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.8s ease;
            pointer-events: none;
        }

        .auth-card:hover::before {
            left: 100%;
        }

        /* Brand Section */
        .brand-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-wrapper {
            display: inline-block;
            margin-bottom: 20px;
        }

        .logo-wrapper img {
            height: auto;
            width: 300px; /* Increased logo size */
            max-width: 100%;
            object-fit: contain;
        }

        .brand-title {
            font-size: 32px;
            font-weight: 800;
            color: #0A2540;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 18px;
            color: #546E7A;
            font-weight: 500;
        }

        /* Form Elements - Enlarged for desktop */
        .form-group {
            margin-bottom: 28px;
        }

        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #1E2F40;
            margin-bottom: 10px;
            letter-spacing: -0.2px;
        }

        .input-wrapper {
            position: relative;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: #8A9AA8;
            font-size: 24px;
            line-height: 1;
            z-index: 1;
        }

        .form-control {
            width: 100%;
            height: 70px; /* Taller inputs */
            padding: 0 30px 0 64px;
            border: 2px solid #E5EAF0;
            border-radius: 24px; /* More rounded */
            font-size: 18px;
            color: #1E2F40;
            background: #FFFFFF;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .form-control:focus {
            outline: none;
            border-color: #0A2540;
            box-shadow: 0 0 0 4px rgba(10, 37, 64, 0.1);
        }

        .form-control::placeholder {
            color: #9AABBA;
            font-weight: 400;
            font-size: 17px;
        }

        .form-control.is-invalid {
            border-color: #DC3545;
            background: #FFF8F8;
        }

        .invalid-feedback {
            color: #DC3545;
            font-size: 14px;
            margin-top: 8px;
            font-weight: 500;
            padding-left: 20px;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #8A9AA8;
            font-size: 24px;
            line-height: 1;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #0A2540;
        }

        /* Checkbox Group */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 32px 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 22px;
            height: 22px;
            border: 2px solid #D0DAE4;
            border-radius: 6px;
            cursor: pointer;
            accent-color: #0A2540;
        }

        .checkbox-group label {
            font-size: 17px;
            color: #405B6A;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 70px;
            background: linear-gradient(145deg, #0A2540, #1A3A5F);
            border: none;
            border-radius: 24px;
            color: white;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            20%, 100% { transform: translateX(100%) rotate(45deg); }
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 35px -10px rgba(10, 37, 64, 0.5);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Links */
        .auth-links {
            text-align: center;
            margin-top: 28px;
        }

        .auth-links a {
            color: #546E7A;
            text-decoration: none;
            font-size: 17px;
            font-weight: 500;
            transition: color 0.2s ease;
            position: relative;
            padding-bottom: 2px;
        }

        .auth-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #0A2540, #1A3A5F);
            transition: width 0.3s ease;
        }

        .auth-links a:hover {
            color: #0A2540;
        }

        .auth-links a:hover::after {
            width: 100%;
        }

        /* Alert Messages */
        .alert {
            padding: 18px 24px;
            border-radius: 20px;
            margin-bottom: 28px;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 14px;
            border: none;
        }

        .alert-success {
            background: linear-gradient(145deg, #E8F5E9, #C8E6C9);
            color: #2E7D32;
        }

        .alert-danger {
            background: linear-gradient(145deg, #FFEBEE, #FFCDD2);
            color: #C62828;
        }

        .alert-warning {
            background: linear-gradient(145deg, #FFF3E0, #FFE0B2);
            color: #EF6C00;
        }

        .alert-info {
            background: linear-gradient(145deg, #E3F2FD, #BBDEFB);
            color: #1565C0;
        }

        /* Footer */
        .footer-note {
            text-align: center;
            margin-top: 36px;
            padding-top: 28px;
            border-top: 2px solid #EDF2F7;
        }

        .copyright {
            font-size: 15px;
            color: #6E8B9B;
            margin: 0 0 6px;
            font-weight: 500;
        }

        .version {
            font-size: 14px;
            color: #8FAABC;
            font-weight: 400;
        }

        /* Loading State */
        .btn-submit.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn-submit.loading::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            margin: -15px 0 0 -15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Tablet Styles */
        @media (min-width: 769px) and (max-width: 1024px) {
            .auth-container {
                max-width: 720px;
            }

            .auth-card {
                padding: 45px 55px;
            }

            .form-control {
                height: 65px;
                font-size: 17px;
            }

            .btn-submit {
                height: 65px;
                font-size: 19px;
            }
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .auth-wrapper {
                padding: 15px;
            }

            .auth-container {
                max-width: 100%;
            }

            .auth-card {
                padding: 35px 25px;
                border-radius: 36px;
            }

            .logo-wrapper img {
                width: 220px;
            }

            .brand-title {
                font-size: 26px;
            }

            .brand-subtitle {
                font-size: 16px;
            }

            .form-control {
                height: 60px;
                font-size: 16px;
                padding: 0 20px 0 52px;
                border-radius: 20px;
            }

            .btn-submit {
                height: 60px;
                font-size: 18px;
                border-radius: 20px;
            }

            .input-icon {
                font-size: 20px;
                left: 18px;
            }

            .password-toggle {
                font-size: 20px;
                right: 18px;
            }

            .checkbox-group label {
                font-size: 15px;
            }

            .auth-links a {
                font-size: 15px;
                padding: 8px 0;
                display: inline-block;
            }

            /* Hide some floating icons on mobile */
            .floating-icon {
                opacity: 0.05;
            }
        }

        /* Small phones */
        @media (max-width: 380px) {
            .auth-card {
                padding: 25px 20px;
                border-radius: 28px;
            }

            .logo-wrapper img {
                width: 160px;
            }

            .brand-title {
                font-size: 22px;
            }

            .form-control {
                height: 55px;
                font-size: 15px;
            }

            .btn-submit {
                height: 55px;
                font-size: 16px;
            }
        }

        /* Landscape mode */
        @media (max-height: 700px) and (orientation: landscape) {
            body {
                align-items: flex-start;
                padding: 20px;
            }

            .auth-wrapper {
                min-height: auto;
                padding: 20px;
            }

            .auth-card {
                padding: 30px 40px;
            }

            .brand-section {
                margin-bottom: 25px;
            }

            .logo-wrapper img {
                width: 180px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-control {
                height: 55px;
            }

            .checkbox-group {
                margin: 20px 0;
            }

            .btn-submit {
                height: 55px;
                margin-bottom: 15px;
            }

            .footer-note {
                margin-top: 20px;
                padding-top: 15px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Animated Background Elements -->
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

        <!-- Graduation Caps -->
        <div class="floating-icon grad-1">🎓</div>
        <div class="floating-icon grad-2">🎓</div>

        <!-- Light Bulbs (Ideas) -->
        <div class="floating-icon bulb-1">💡</div>
        <div class="floating-icon bulb-2">💡</div>

        <!-- Calculators -->
        <div class="floating-icon calc-1">🧮</div>
        <div class="floating-icon calc-2">🧮</div>

        <!-- Additional Educational Icons -->
        <div class="floating-icon" style="top: 12%; left: 25%;">🔬</div>
        <div class="floating-icon" style="top: 55%; right: 35%;">🧪</div>
        <div class="floating-icon" style="bottom: 35%; left: 40%;">📊</div>
        <div class="floating-icon" style="top: 80%; right: 45%;">📈</div>
        <div class="floating-icon" style="top: 20%; left: 45%;">🌐</div>
        <div class="floating-icon" style="bottom: 15%; right: 5%;">💻</div>
    </div>

    <!-- Gradient Overlay -->
    <div class="overlay-gradient"></div>

    <!-- Main Content -->
    <div class="auth-wrapper">
        <div class="auth-container">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
