<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ setting('theme_mode', 'light') }}"
      data-primary="{{ setting('primary_color', '#4e73df') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
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
        /* ===== ROOT VARIABLES ===== */
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
            --bottom-nav-bg: #ffffff;
            --bottom-nav-active: var(--primary-color);
            --safe-area-bottom: env(safe-area-inset-bottom, 0px);
        }

        /* ===== DARK MODE ===== */
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

        [data-theme="dark"] .bottom-nav,
        .dark-mode .bottom-nav {
            background-color: #1a1e2b !important;
            border-top-color: #404654 !important;
        }

        [data-theme="dark"] .bottom-nav .nav-item,
        .dark-mode .bottom-nav .nav-item {
            color: #7f8c8d;
        }

        [data-theme="dark"] .bottom-nav .nav-item.active,
        .dark-mode .bottom-nav .nav-item.active {
            color: var(--primary-color);
        }

        /* ===== GENERAL RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            background-color: #f8f9fc;
            padding-bottom: 0;
        }

       /* ===== SIDEBAR ===== */
        #wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        #sidebar-wrapper {
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            width: 280px;
            min-width: 280px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: transform 0.3s ease, margin-left 0.3s ease;
            z-index: 1050;
            flex-shrink: 0;
            position: sticky;
            top: 0;
        }

        /* Desktop: Sidebar visible by default */
        @media (min-width: 992px) {
            #sidebar-wrapper {
                position: sticky;
                transform: translateX(0);
                margin-left: 0;
            }
            
            /* When toggled, move sidebar off screen */
            body.sb-sidenav-toggled #sidebar-wrapper {
                margin-left: -280px;
            }
            
            /* Page content adjusts with sidebar */
            #page-content-wrapper {
                flex: 1;
                min-width: 0;
                transition: margin-left 0.3s ease;
            }
            
        }

        /* Mobile: Sidebar hidden by default */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                position: fixed;
                top: 0;
                left: 0;
                transform: translateX(-280px);
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            #sidebar-wrapper.show {
                transform: translateX(0);
            }
            
            #page-content-wrapper {
                flex: 1;
                min-width: 0;
                transition: margin-left 0.3s ease;
            }
        }

        

        /* ===== SIDEBAR OVERLAY (Mobile) ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1045;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }
       
       
        
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
            padding-bottom: 80px;
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
        /*#page-content-wrapper {*/
        /*    flex: 1;*/
        /*    display: flex;*/
        /*    flex-direction: column;*/
        /*    height: 100vh;*/
        /*    overflow: hidden;*/
        /*    background-color: #f8f9fc;*/
        /*    transition: all 0.3s ease;*/
        /*    padding-bottom: 0;*/
        /*}*/
        
        /* ===== PAGE CONTENT ===== */
        #page-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fc;
            transition: margin-left 0.3s ease;
        }

        [data-theme="dark"] #page-content-wrapper,
        .dark-mode #page-content-wrapper {
            background-color: #1a1e2b;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background-color: var(--header-bg) !important;
            color: var(--header-text);
            padding: 0.6rem 1rem;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1040;
            min-height: 60px;
        }

        [data-theme="dark"] .navbar,
        .dark-mode .navbar {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }

        .navbar .btn-link {
            color: var(--header-text) !important;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem 1.5rem 80px;
            padding-bottom: 90px;
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

        /* ===== BOTTOM NAVIGATION (Mobile Only) ===== */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bottom-nav-bg);
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            z-index: 1030;
            padding: 6px 0 calc(6px + var(--safe-area-bottom));
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .bottom-nav .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4px 12px;
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.65rem;
            transition: all 0.2s;
            position: relative;
            min-width: 50px;
        }

        .bottom-nav .nav-item i {
            font-size: 1.4rem;
            margin-bottom: 2px;
            transition: all 0.2s;
        }

        .bottom-nav .nav-item span {
            font-weight: 500;
            white-space: nowrap;
        }

        .bottom-nav .nav-item.active {
            color: var(--bottom-nav-active);
        }

        .bottom-nav .nav-item.active i {
            transform: scale(1.1);
        }

        .bottom-nav .nav-item .badge {
            position: absolute;
            top: -2px;
            right: 4px;
            font-size: 0.6rem;
            padding: 2px 5px;
            min-width: 18px;
        }

        .bottom-nav .nav-item:hover {
            color: var(--bottom-nav-active);
        }

        /* ===== RESPONSIVE - Mobile Bottom Nav ===== */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                position: fixed;
                top: 0;
                left: -280px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
                transition: transform 0.3s ease-in-out;

                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            #sidebar-wrapper.show {
                /*left: 0;*/
                transform: translateX(280px); 
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

            /* Show bottom nav on mobile */
            .bottom-nav {
                display: block;
            }

            .main-content-area {
                padding: 1rem 1rem 80px;
                padding-bottom: 90px;
            }

            .desktop-only {
                display: none !important;
            }
        }

        @media (min-width: 992px) {
            .navbar .btn-link {
                display: inline-block !important;
            }

            .sidebar-overlay {
                display: none !important;
            }

            .bottom-nav {
                display: none !important;
            }

            .mobile-only {
                display: none !important;
            }
        }

        @media (max-width: 576px) {
            .main-content-area {
                padding: 0.75rem 0.75rem 80px;
                padding-bottom: 85px;
            }

            .bottom-nav .nav-item i {
                font-size: 1.2rem;
            }

            .bottom-nav .nav-item span {
                font-size: 0.55rem;
            }

            .bottom-nav .nav-item {
                padding: 2px 8px;
                min-width: 40px;
            }
        }

        /* ===== FOOTER ===== */
        .footer {
            background: transparent;
            padding: 0.75rem 0;
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

        /* ===== UTILITY ===== */
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

        /* ===== NOTIFICATIONS ===== */
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

        /* ===== RESPONSIVE FIXES ===== */
        @media (max-width: 576px) {
            .footer {
                margin: 0 0.75rem;
                font-size: 0.75rem;
            }
        }

        /* ===== PRINT ===== */
        @media print {
            #sidebar-wrapper,
            .navbar,
            .footer,
            .bottom-nav,
            .fullscreen-btn,
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

<body class="{{ is_dark_mode() ? 'dark-mode' : '' }}"       data-sidebar-collapsed="{{ session('sidebar_collapsed', false) }}">
    
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    @yield('layout-content')

    {{-- Global Loader --}}
    @include('layouts.global-loader')
    <!-- Global Scripts -->
    <script src="{{ asset('js/loader.js') }}"></script>
    <script>
         // ===== SIDEBAR TOGGLE =====
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-wrapper');
            const overlay = document.getElementById('sidebarOverlay');
            const isDesktop = window.innerWidth >= 992;
            
            if (!sidebar) return;
            
            if (isDesktop) {
                // Desktop: Toggle body class
                document.body.classList.toggle('sb-sidenav-toggled');
                localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sb-sidenav-toggled'));
            } else {
                // Mobile: Toggle show class
                sidebar.classList.toggle('show');
                if (overlay) {
                    overlay.classList.toggle('show');
                }
                // Prevent body scroll when sidebar is open
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
            }
        }

        // ===== CLOSE SIDEBAR =====
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar-wrapper');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth < 992) {
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
                // document.body.style.overflow = '';
                setTimeout(() => {
                  if (sidebar && !sidebar.classList.contains('show')) {
                    document.body.style.overflow = '';
                  }
                }, 300);
            }
        }

        // ===== SIDEBAR TOGGLE BUTTON =====
        document.addEventListener('DOMContentLoaded', function() {
            // Menu toggle button
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }
            
            // Close button in sidebar (mobile)
            const closeBtn = document.querySelector('#sidebar-wrapper .btn-close-sidebar');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }
            
            // Overlay click to close
            const overlay = document.getElementById('sidebarOverlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }
            
            // Restore sidebar state on desktop
            if (window.innerWidth >= 992) {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    document.body.classList.add('sb-sidenav-toggled');
                }
            }
            
            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const sidebar = document.getElementById('sidebar-wrapper');
                    const overlay = document.getElementById('sidebarOverlay');
                    
                    if (window.innerWidth >= 992) {
                        // On desktop, remove mobile classes
                        if (sidebar) {
                            sidebar.classList.remove('show');
                        }
                        if (overlay) {
                            overlay.classList.remove('show');
                        }
                        document.body.style.overflow = '';
                    } else {
                        // On mobile, ensure overlay is hidden if sidebar is hidden
                        if (sidebar && !sidebar.classList.contains('show')) {
                            if (overlay) overlay.classList.remove('show');
                            document.body.style.overflow = '';
                        }
                    }
                }, 150);
            });
        });

        // ===== KEYBOARD SHORTCUTS =====
        document.addEventListener('keydown', function(e) {
            // Alt + S = Toggle Sidebar
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                toggleSidebar();
            }
            // Escape = Close Sidebar (mobile)
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // ===== PREVENT SIDEBAR CLOSE ON CONTENT CLICK =====
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar-wrapper');
            const menuToggle = document.getElementById('menu-toggle');
            
            if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('show')) {
                // If click is outside sidebar and not on menu toggle
                if (!sidebar.contains(e.target) && !menuToggle?.contains(e.target)) {
                    closeSidebar();
                }
            }
        });

        // ===== DARK MODE TOGGLE =====
        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            
            document.cookie = `dark_mode=${isDark}; path=/; max-age=31536000`;
            
            const icon = document.querySelector('[onclick="toggleDarkMode()"] i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun fs-5' : 'bi bi-moon fs-5';
            }
        }

        // ===== NOTIFICATIONS =====
        function loadNotifications() {
            fetch('/notifications', {
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
        // setInterval(loadNotifications, 30000);
        loadNotifications();

        // ===== AUTO-HIDE ALERTS =====
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 1000);

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

        

    </script>

    @stack('scripts')
</body>
</html>