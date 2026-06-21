<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      data-theme="{{ setting('theme_mode', 'light') }}"
      data-primary="{{ setting('primary_color', '#4e73df') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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

    <style>
        :root {
            --primary-color: {{ theme_color('primary_color', '#4e73df') }};
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

        /* ===== DARK MODE SUPPORT ===== */
        [data-theme="dark"] body,
        .dark-mode {
            background-color: #1a1e2b;
            color: #e0e0e0;
        }

        [data-theme="dark"] .card,
        .dark-mode .card {
            background-color: #2d323f;
            border-color: #404654;
        }

        [data-theme="dark"] .table,
        .dark-mode .table {
            color: #e0e0e0;
        }

        [data-theme="dark"] .table-light,
        .dark-mode .table-light {
            background-color: #404654;
            color: #ffffff;
        }

        [data-theme="dark"] .modal-content,
        .dark-mode .modal-content {
            background-color: #2d323f;
            color: #e0e0e0;
        }

        [data-theme="dark"] .bg-light,
        .dark-mode .bg-light {
            background-color: #2d323f !important;
        }

        [data-theme="dark"] .text-muted,
        .dark-mode .text-muted {
            color: #a0aec0 !important;
        }

        [data-theme="dark"] .border,
        .dark-mode .border {
            border-color: #404654 !important;
        }

        [data-theme="dark"] .navbar,
        .dark-mode .navbar {
            background-color: #1a1e2b !important;
        }

        [data-theme="dark"] .dropdown-menu,
        .dark-mode .dropdown-menu {
            background-color: #2d323f;
            border-color: #404654;
        }

        [data-theme="dark"] .dropdown-item,
        .dark-mode .dropdown-item {
            color: #e0e0e0;
        }

        [data-theme="dark"] .dropdown-item:hover,
        .dark-mode .dropdown-item:hover {
            background-color: #404654;
            color: #ffffff;
        }

        [data-theme="dark"] .list-group-item,
        .dark-mode .list-group-item {
            background-color: transparent;
            color: #e0e0e0;
        }

        [data-theme="dark"] .list-group-item:hover,
        .dark-mode .list-group-item:hover {
            background-color: #404654;
        }

        /* ===== LAYOUT ===== */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        body {
            font-family: 'Nunito', 'Segoe UI', Roboto, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        #wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        #sidebar-wrapper {
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            width: 280px;
            min-width: 280px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s ease;
            z-index: 1050;
            flex-shrink: 0;
            position: sticky;
            top: 0;
        }

        /* Custom scrollbar for sidebar */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1.2rem 1.25rem;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: sticky;
            top: 0;
            background-color: var(--sidebar-bg);
            z-index: 10;
        }

        #sidebar-wrapper .list-group {
            padding-bottom: 2rem;
        }

        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            border: none;
            color: var(--sidebar-text);
            padding: 0.75rem 1.25rem;
            transition: all 0.3s ease;
            opacity: 0.85;
            position: relative;
            border-left: 3px solid transparent;
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.08);
            padding-left: 1.8rem;
            opacity: 1;
        }

        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(90deg, var(--primary-color), transparent);
            border-left: 3px solid var(--primary-color);
            opacity: 1;
            color: #ffffff;
        }

        #sidebar-wrapper .list-group-item i {
            width: 24px;
            text-align: center;
        }

        /* ===== PAGE CONTENT ===== */
        #page-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background-color: #f8f9fc;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] #page-content-wrapper,
        .dark-mode #page-content-wrapper {
            background-color: #1a1e2b;
        }

        /* ===== HEADER / NAVBAR ===== */
        .navbar {
            background-color: var(--header-bg) !important;
            color: var(--header-text);
            padding: 0.75rem 1rem;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1040;
        }

        [data-theme="dark"] .navbar,
        .dark-mode .navbar {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }

        .navbar .btn-link {
            color: var(--header-text) !important;
        }

        /* ===== MAIN CONTENT AREA ===== */
        .main-content-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem 1.5rem 0;
        }

        .main-content-area::-webkit-scrollbar {
            width: 6px;
        }

        .main-content-area::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .main-content-area::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        [data-theme="dark"] .main-content-area::-webkit-scrollbar-track,
        .dark-mode .main-content-area::-webkit-scrollbar-track {
            background: #2d323f;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: transparent;
            padding: 1rem 0;
            flex-shrink: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin: 0 1.5rem;
        }

        [data-theme="dark"] .footer,
        .dark-mode .footer {
            border-top-color: rgba(255, 255, 255, 0.05);
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        /* ===== MOBILE SIDEBAR ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1045;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                position: fixed;
                top: 0;
                left: -280px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            #sidebar-wrapper.show {
                left: 0;
            }

            .sidebar-overlay.show {
                display: block;
            }

            #page-content-wrapper {
                margin-left: 0;
            }

            .navbar .btn-link {
                display: inline-block !important;
            }
        }

        @media (min-width: 992px) {
            .navbar .btn-link {
                display: inline-block !important;
            }

            .sidebar-overlay {
                display: none !important;
            }
        }

        /* ===== UTILITY CLASSES ===== */
        .text-primary { color: var(--primary-color) !important; }
        .bg-primary { background-color: var(--primary-color) !important; }
        .border-primary { border-color: var(--primary-color) !important; }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            filter: brightness(90%);
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        /* ===== ANIMATIONS ===== */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== STATS CARDS ===== */
        .stats-card {
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-card.border-left-primary { border-left-color: var(--primary-color); }
        .stats-card.border-left-success { border-left-color: var(--success-color); }
        .stats-card.border-left-info { border-left-color: var(--info-color); }
        .stats-card.border-left-warning { border-left-color: var(--warning-color); }
        .stats-card.border-left-danger { border-left-color: var(--danger-color); }

        /* ===== ALERTS ===== */
        .alert {
            border: none;
            border-radius: 12px;
        }

        .alert-success {
            background-color: rgba(28, 200, 138, 0.15);
            color: #1cc88a;
        }

        .alert-danger {
            background-color: rgba(231, 74, 59, 0.15);
            color: #e74a3b;
        }

        .alert-warning {
            background-color: rgba(246, 194, 62, 0.15);
            color: #f6c23e;
        }

        .alert-info {
            background-color: rgba(54, 185, 204, 0.15);
            color: #36b9cc;
        }

        [data-theme="dark"] .alert-success,
        .dark-mode .alert-success {
            background-color: rgba(28, 200, 138, 0.25);
        }

        [data-theme="dark"] .alert-danger,
        .dark-mode .alert-danger {
            background-color: rgba(231, 74, 59, 0.25);
        }

        /* ===== DROPDOWN ===== */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.6rem 1.5rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: rgba(78, 115, 223, 0.08);
        }

        .dropdown-header {
            padding: 0.6rem 1.5rem;
            font-weight: 700;
        }

        /* ===== NOTIFICATIONS DROPDOWN ===== */
        #notifications-dropdown {
            width: 340px;
            max-width: 90vw;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .notification-item:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        /* ===== RESPONSIVE TABLES ===== */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        /* ===== RESPONSIVE CARDS ===== */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
        }

        [data-theme="dark"] .card-header,
        .dark-mode .card-header {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }

        /* ===== BREADCRUMBS ===== */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        /* ===== RESPONSIVE FIXES ===== */
        @media (max-width: 576px) {
            .main-content-area {
                padding: 1rem 1rem 0;
            }

            .footer {
                margin: 0 1rem;
            }

            #notifications-dropdown {
                width: 300px;
                max-width: 95vw;
                position: fixed;
                right: 10px;
                left: auto;
            }
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            #sidebar-wrapper,
            .navbar,
            .footer,
            .btn,
            .no-print {
                display: none !important;
            }

            #page-content-wrapper {
                margin-left: 0 !important;
            }

            .main-content-area {
                padding: 0 !important;
            }
        }
        
        /* Dark Mode Styles (Activates when .dark class is added upstream) */
        body.dark table {
            background-color: #1a1d20 !important; /* Forces overwrite if framework styles interfere */
            color: #f8f9fa;
        }
        
        body.dark input {
            background-color: #2b3035 !important;
            color: #f8f9fa;
            border-color: #495057;
        }
    </style>
</head>

<body class="{{ is_dark_mode() ? 'dark-mode' : '' }}" 
      data-sidebar-collapsed="{{ session('sidebar_collapsed', false) }}">
    
    <div id="wrapper">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <div id="sidebar-wrapper" class="border-end">
            <div class="sidebar-heading d-flex align-items-center justify-content-between py-3 px-4">
                <div class="d-flex align-items-center">
                    @if(setting('app_logo'))
                        <img src="{{ setting('app_logo') }}" alt="{{ setting('app_name') }}" height="35" class="me-2">
                    @endif
                    <span class="fw-bold">{{ setting('app_name', 'CBT System') }}</span>
                </div>
                <button class="btn btn-link text-white d-lg-none p-0" onclick="toggleSidebar()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="list-group list-group-flush">
                <a href="{{ route('admin.dashboard') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.courses.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                    <i class="bi bi-book me-2"></i>
                    <span>Courses</span>
                </a>

                <a href="{{ route('admin.batches.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.batches.*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i>
                    <span>Batches</span>
                </a>

                <a href="{{ route('admin.students.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge me-2"></i>
                    <span>Student Management</span>
                </a>

                <a href="{{ route('admin.questions.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                    <i class="bi bi-question-circle me-2"></i>
                    <span>Question Bank</span>
                </a>

                <a href="{{ route('admin.exams.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square me-2"></i>
                    <span>Exams</span>
                </a>

                <a href="{{ route('admin.analytics.dashboard') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up me-2"></i>
                    <span>Analytics</span>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-text me-2"></i>
                    <span>Reports</span>
                </a>

                <a href="{{ route('admin.study-materials.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.study-materials.*') ? 'active' : '' }}">
                    <i class="bi bi-journal me-2"></i>
                    <span>Study Materials</span>
                </a>

                <div class="list-group-item" style="background: transparent; padding: 0.5rem 1.25rem;">
                    <hr style="border-color: rgba(255,255,255,0.08); margin: 0;">
                </div>

                <a href="{{ route('admin.settings.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i>
                    <span>Settings</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill me-2"></i>
                    <span>User Management</span>
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button class="btn btn-link" id="menu-toggle" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-4"></i>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Quick Actions -->
                        <div class="dropdown me-2 me-md-3">
                            <button class="btn btn-link dropdown-toggle text-decoration-none p-0" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-plus-circle fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.courses.create') }}">
                                    <i class="bi bi-book me-2"></i>New Course</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.batches.create') }}">
                                    <i class="bi bi-people me-2"></i>New Batch</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.questions.create') }}">
                                    <i class="bi bi-question-circle me-2"></i>New Question</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.exams.create') }}">
                                    <i class="bi bi-pencil-square me-2"></i>New Exam</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.questions.import') }}">
                                    <i class="bi bi-upload me-2"></i>Import Questions</a>
                                </li>
                            </ul>
                        </div>

                        <!-- Notifications -->
                        <div class="dropdown me-2 me-md-3">
                            <button class="btn btn-link position-relative text-decoration-none p-0" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                      id="notification-badge" style="display: none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" id="notifications-dropdown">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <small><a href="#" class="text-decoration-none">Mark all read</a></small>
                                </div>
                                <div class="notification-list">
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-check-circle"></i>
                                        <p class="mb-0">No new notifications</p>
                                    </div>
                                </div>
                                <div class="dropdown-footer text-center border-top py-2">
                                    <a href="#" class="text-decoration-none small">View all notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Toggle -->
                        <button class="btn btn-link p-0 me-2 me-md-3" onclick="toggleDarkMode()" title="Toggle Theme">
                            <i class="bi {{ is_dark_mode() ? 'bi-sun' : 'bi-moon' }} fs-5"></i>
                        </button>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-decoration-none p-0" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->avatar_url }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="rounded-circle" width="32" height="32">
                                <span class="ms-2 d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="bi bi-person me-2"></i>Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.settings') }}">
                                        <i class="bi bi-gear me-2"></i>Account Settings
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.activity') }}">
                                        <i class="bi bi-clock-history me-2"></i>Activity Log
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="main-content-area" id="mainContent">
                <!-- Breadcrumbs -->
                @if(isset($breadcrumbs) || View::hasSection('breadcrumbs'))
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>
                @endif

                <!-- Page Title -->
                @hasSection('page-title')
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <h1 class="h3 mb-0">@yield('page-title')</h1>
                    @yield('page-actions')
                </div>
                @endif

                <!-- Alerts -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Main Content -->
                <div class="fade-in">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-muted small">
                            &copy; {{ date('Y') }} {{ setting('app_name', 'CBT System') }}. All rights reserved.
                        </div>
                        <div class="col-md-6 text-md-end text-muted small">
                            <a href="{{ route('about') }}" class="me-2">About</a> |
                            <a href="{{ route('contact') }}" class="mx-2">Contact</a> |
                            <a href="{{ route('faq') }}" class="mx-2">FAQ</a> |
                            <a href="{{ route('privacy-policy') }}" class="mx-2">Privacy</a> |
                            <a href="{{ route('terms-of-service') }}" class="mx-2">Terms</a> |
                            <a href="{{ route('cookie-policy') }}" class="ms-2">Cookies</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
            @include('layouts.global-loader')

    </div>

    <script src="{{ asset('js/global-loaders.js') }}"></script>

    <script>
        // ===== SIDEBAR TOGGLE =====
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-wrapper');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            
            // Save state in session
            const isOpen = sidebar.classList.contains('show');
            fetch('{{ route("admin.sidebar.toggle") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ open: isOpen })
            }).catch(() => {});
        }

        // Close sidebar on resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                document.getElementById('sidebar-wrapper').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        });

        // ===== DARK MODE TOGGLE =====
        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            
            // Save preference
            document.cookie = `dark_mode=${isDark}; path=/; max-age=31536000`;
            
            // Update icon
            const icon = document.querySelector('[onclick="toggleDarkMode()"] i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun fs-5' : 'bi bi-moon fs-5';
            }
        }

        // ===== NOTIFICATIONS =====
        function loadNotifications() {
            fetch('/admin/notifications',{
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Silent-Request': 'true'
        
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (data.count > 0) {
                        badge.style.display = 'inline';
                        badge.textContent = data.count;
                    } else {
                        badge.style.display = 'none';
                    }
                    
                    const list = document.querySelector('.notification-list');
                    if (data.notifications && data.notifications.length > 0) {
                        list.innerHTML = data.notifications.map(n => `
                            <a href="${n.url}" class="notification-item">
                                <small class="text-muted">${n.time}</small>
                                <p class="mb-0">${n.message}</p>
                            </a>
                        `).join('');
                    } else {
                        list.innerHTML = `
                            <div class="text-center py-3 text-muted">
                                <i class="bi bi-check-circle"></i>
                                <p class="mb-0">No new notifications</p>
                            </div>
                        `;
                    }
                })
                .catch(() => {});
        }

        // Load notifications every 30 seconds
        setInterval(loadNotifications, 30000);
        loadNotifications();

        // ===== AUTO-HIDE ALERTS =====
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 1000);

        // ===== SIDEBAR STATE ON LOAD =====
        document.addEventListener('DOMContentLoaded', function() {
            // If sidebar was open on mobile, show it
            const sidebarCollapsed = document.body.dataset.sidebarCollapsed === 'true';
            if (window.innerWidth < 992 && sidebarCollapsed) {
                document.getElementById('sidebar-wrapper').classList.add('show');
                document.getElementById('sidebarOverlay').classList.add('show');
            }
        });

        // ===== TOOLTIPS =====
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
        });

        // ===== POPOVERS =====
        document.addEventListener('DOMContentLoaded', function() {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(el => new bootstrap.Popover(el));
        });

        // ===== KEYBOARD SHORTCUTS =====
        document.addEventListener('keydown', function(e) {
            // Alt + S = Toggle Sidebar
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                toggleSidebar();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>