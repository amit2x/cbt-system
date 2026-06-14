<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ setting('theme_mode', 'light') }}"
      data-primary="{{ setting('primary_color', '#4e73df') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ setting('meta_description', 'CBT System') }}">
    <meta name="keywords" content="{{ setting('meta_keywords', 'cbt, exam') }}">

    <title>@yield('title', 'Dashboard') - {{ setting('app_name', 'CBT System') }}</title>

    <!-- Favicon -->
    @if(setting('app_favicon'))
        <link rel="icon" href="{{ setting('app_favicon') }}" type="image/x-icon">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @endif

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: {{ theme_color('primary_color') }};
            --secondary-color: {{ theme_color('secondary_color', '#858796') }};
            --success-color: {{ theme_color('success_color', '#1cc88a') }};
            --info-color: {{ theme_color('info_color', '#36b9cc') }};
            --warning-color: {{ theme_color('warning_color', '#f6c23e') }};
            --danger-color: {{ theme_color('danger_color', '#e74a3b') }};
            --sidebar-bg: {{ setting('sidebar_bg', '#1a1e2b') }};
            --sidebar-text: {{ setting('sidebar_text', '#ffffff') }};
            --header-bg: {{ setting('header_bg', '#ffffff') }};
            --header-text: {{ setting('header_text', '#3a3b45') }};
        }

        @if(is_dark_mode())
            body {
                background-color: #1a1e2b;
                color: #e0e0e0;
            }
            .card {
                background-color: #2d323f;
                border-color: #404654;
            }
            .table {
                color: #e0e0e0;
            }
            .table-light {
                background-color: #404654;
                color: #ffffff;
            }
            .modal-content {
                background-color: #2d323f;
                color: #e0e0e0;
            }
        @endif

        #wrapper {
            overflow-x: hidden;
        }

        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: -15rem;
            transition: margin 0.25s ease-out;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        #sidebar-wrapper .list-group {
            width: 15rem;
        }

        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            border: none;
            color: var(--sidebar-text);
            padding: 0.75rem 1.25rem;
            transition: all 0.3s;
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255,255,255,0.1);
            padding-left: 1.8rem;
        }

        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(90deg, var(--primary-color), transparent);
            border-left: 3px solid var(--primary-color);
        }

        #page-content-wrapper {
            min-width: 100vw;
        }

        body.sb-sidenav-toggled #wrapper #sidebar-wrapper {
            margin-left: 0;
        }

        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                min-width: 0;
                width: 100%;
            }

            body.sb-sidenav-toggled #wrapper #sidebar-wrapper {
                margin-left: -15rem;
            }
        }

        .navbar {
            background-color: var(--header-bg) !important;
            color: var(--header-text);
        }

        .navbar .nav-link {
            color: var(--header-text) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            filter: brightness(90%);
        }

        .stats-card {
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .stats-card.border-left-primary { border-left-color: var(--primary-color); }
        .stats-card.border-left-success { border-left-color: var(--success-color); }
        .stats-card.border-left-info { border-left-color: var(--info-color); }
        .stats-card.border-left-warning { border-left-color: var(--warning-color); }
        .stats-card.border-left-danger { border-left-color: var(--danger-color); }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            filter: brightness(80%);
        }
    </style>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')

    @if(setting('google_analytics_id'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ setting("google_analytics_id") }}');
        </script>
    @endif
</head>
<body class="{{ is_dark_mode() ? 'dark-mode' : '' }} sb-nav-fixed">
    @yield('layout-content')

    <!-- Global Scripts -->
    <script>
        // Sidebar Toggle
        document.getElementById('menu-toggle')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sb-sidenav-toggled'));
        });

        // Restore sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.body.classList.add('sb-sidenav-toggled');
        }

        // Dark mode toggle
        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            document.cookie = `dark_mode=${isDark}; path=/; max-age=31536000`;

            const icon = document.querySelector('[onclick="toggleDarkMode()"] i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun fs-5' : 'bi bi-moon fs-5';
            }

            location.reload();
        }

        // Auto-hide alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                bootstrap.Alert.getOrCreateInstance(alert)?.close();
            });
        }, 5000);

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
