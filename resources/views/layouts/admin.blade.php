<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ setting('theme_mode', 'light') }}"
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
            --primary-color: {
                    {
                    theme_color('primary_color', '#4e73df')
                }
            }

            ;

            --secondary-color: {
                    {
                    theme_color('secondary_color', '#858796')
                }
            }

            ;

            --success-color: {
                    {
                    theme_color('success_color', '#1cc88a')
                }
            }

            ;

            --info-color: {
                    {
                    theme_color('info_color', '#36b9cc')
                }
            }

            ;

            --warning-color: {
                    {
                    theme_color('warning_color', '#f6c23e')
                }
            }

            ;

            --danger-color: {
                    {
                    theme_color('danger_color', '#e74a3b')
                }
            }

            ;

            --sidebar-bg: {
                    {
                    setting('sidebar_bg', '#1a1e2b')
                }
            }

            ;

            --sidebar-text: {
                    {
                    setting('sidebar_text', '#ffffff')
                }
            }

            ;

            --header-bg: {
                    {
                    setting('header_bg', '#ffffff')
                }
            }

            ;

            --header-text: {
                    {
                    setting('header_text', '#3a3b45')
                }
            }

            ;
        }

        @if(is_dark_mode()===true) body {
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

        @endif #wrapper {
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 1.8rem;
        }

        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(90deg, var(--primary-color), transparent);
            border-left: 3px solid var(--primary-color);
        }

        #page-content-wrapper {
            /* min-width: 100vw; */
            min-width: 100%;
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
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            filter: brightness(90%);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .border-primary {
            border-color: var(--primary-color) !important;
        }

        /* Custom Scrollbar */
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
            background: var(--primary-color);
            filter: brightness(80%);
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-card {
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(66, 31, 31, 0.1);
        }

        .stats-card.border-left-primary {
            border-left-color: var(--primary-color);
        }

        .stats-card.border-left-success {
            border-left-color: var(--success-color);
        }

        .stats-card.border-left-info {
            border-left-color: var(--info-color);
        }

        .stats-card.border-left-warning {
            border-left-color: var(--warning-color);
        }

        .stats-card.border-left-danger {
            border-left-color: var(--danger-color);
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
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading d-flex align-items-center justify-content-center py-3">
                @if(setting('app_logo'))
                <img src="{{ setting('app_logo') }}" alt="{{ setting('app_name') }}" height="40" class="me-2">
                @endif
                <span class="fw-bold">{{ setting('app_name', 'CBT System') }}</span>
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
                    <i class="bi bi-people me-2"></i>
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

                <div class="list-group-item" style="background: transparent;">
                    <hr style="border-color: rgba(255,255,255,0.1);">
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
            <nav class="navbar navbar-expand-lg navbar-light border-bottom py-3">
                <div class="container-fluid">
                    <button class="btn btn-link" id="menu-toggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Quick Actions -->
                        <div class="dropdown me-3">
                            <button class="btn btn-link dropdown-toggle text-decoration-none" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-plus-circle fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.courses.create') }}">
                                        <i class="bi bi-book me-2"></i>New Course
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.batches.create') }}">
                                        <i class="bi bi-people me-2"></i>New Batch
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.questions.create') }}">
                                        <i class="bi bi-question-circle me-2"></i>New Question
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.exams.create') }}">
                                        <i class="bi bi-pencil-square me-2"></i>New Exam
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.questions.import') }}">
                                        <i class="bi bi-upload me-2"></i>Import Questions
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-link position-relative text-decoration-none" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    id="notification-badge" style="display: none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" style="width: 300px;"
                                id="notifications-dropdown">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <small><a href="#" class="text-decoration-none">Mark all read</a></small>
                                </div>
                                <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
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
                        <button class="btn btn-link me-3" onclick="toggleDarkMode()">
                            <i class="bi {{ is_dark_mode() ? 'bi-sun' : 'bi-moon' }} fs-5"></i>
                        </button>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-decoration-none" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}"
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
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
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

            <div class="container-fluid py-4">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">@yield('page-title')</h1>
                    @yield('page-actions')
                </div>
                @endif

                <!-- Alert Messages -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn"
                    role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn"
                    role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show animate__animated animate__fadeIn"
                    role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show animate__animated animate__fadeIn"
                    role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <!-- Main Content -->
                <div class="fade-in">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer border-top py-3 mt-auto">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 text-muted small">
                            &copy; {{ date('Y') }} {{ setting('app_name', 'CBT System') }}. All rights reserved.
                        </div>
                        <div class="col-md-6 text-end text-muted small">
                            <a href="{{ route('about') }}" class="text-decoration-none me-2">About</a> |
                            <a href="{{ route('contact') }}" class="text-decoration-none mx-2">Contact</a> |
                            <a href="{{ route('faq') }}" class="text-decoration-none mx-2">FAQ</a> |
                            <a href="{{ route('privacy-policy') }}" class="text-decoration-none mx-2">Privacy</a> |
                            <a href="{{ route('terms-of-service') }}" class="text-decoration-none mx-2">Terms</a> |
                            <a href="{{ route('cookie-policy') }}" class="text-decoration-none ms-2">Cookies</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        window.flashMessages = {
                success: "{{ session('success') }}",
                error: "{{ session('error') }}",
                warning: "{{ session('warning') }}",
                info: "{{ session('info') }}"
            };


        document.getElementById('menu-toggle').addEventListener('click', function(e) {
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

            // Update icon
            const icon = document.querySelector('[onclick="toggleDarkMode()"] i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun fs-5' : 'bi bi-moon fs-5';
            }

            // Reload to apply theme changes
            location.reload();
        }

        // Load notifications via AJAX
        function loadNotifications() {
            fetch('/admin/notifications')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (data.count > 0) {
                        badge.style.display = 'inline';
                        badge.textContent = data.count;
                    } else {
                        badge.style.display = 'none';
                    }

                    // Update dropdown content
                    const list = document.querySelector('.notification-list');
                    if (data.notifications.length > 0) {
                        list.innerHTML = data.notifications.map(n => `
                            <a href="${n.url}" class="dropdown-item text-wrap py-2">
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
                });
        }

        // Load notifications every 30 seconds
        setInterval(loadNotifications, 30000);
        loadNotifications();

        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>

</html>