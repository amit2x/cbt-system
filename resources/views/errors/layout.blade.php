<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Error') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(145deg, #f8f9fc 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-bubbles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bubble {
            position: absolute;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 50%;
            animation: float 8s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Error Container */
        .error-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 800px;
            padding: 2rem;
            margin: 0 auto;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 48px;
            padding: 3rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(78, 115, 223, 0.03), transparent);
            transform: rotate(45deg);
            animation: shimmer 6s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        /* Error Code */
        .error-code {
            font-size: 12rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .error-code::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: blur(10px);
            opacity: 0.3;
            z-index: -1;
        }

        /* Error Title */
        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Illustration */
        .error-illustration {
            margin: 2rem 0;
            position: relative;
            min-height: 200px;
        }

        .illustration-svg {
            max-width: 100%;
            height: auto;
        }

        /* Animated Elements */
        .floating-element {
            animation: float-element 3s infinite ease-in-out;
        }

        @keyframes float-element {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .rotate-element {
            animation: rotate 10s infinite linear;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Buttons */
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-error {
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(78, 115, 223, 0.5);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #4e73df;
            color: #4e73df;
        }

        .btn-outline:hover {
            background: #4e73df;
            color: white;
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-code {
                font-size: 8rem;
            }

            .error-title {
                font-size: 1.8rem;
            }

            .error-card {
                padding: 2rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn-error {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Bubbles -->
    <div class="bg-bubbles">
        @for($i = 1; $i <= 10; $i++)
            @php
                $size = rand(50, 200);
                $left = rand(0, 100);
                $top = rand(0, 100);
                $delay = rand(0, 5);
                $duration = rand(5, 15);
            @endphp
            <div class="bubble" style="width: {{ $size }}px; height: {{ $size }}px; left: {{ $left }}%; top: {{ $top }}%; animation-delay: -{{ $delay }}s; animation-duration: {{ $duration }}s;"></div>
        @endfor
    </div>

    <div class="error-container">
        <div class="error-card">
            @yield('content')
        </div>
    </div>
</body>
</html>
