
@extends('layouts.base')

@section('layout-content')
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="border-end" id="sidebar-wrapper">
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
            <a href="{{ route('instructor.dashboard') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('instructor.courses.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}">
                <i class="bi bi-book me-2"></i>
                <span>My Courses</span>
            </a>

            <a href="{{ route('instructor.quizzes.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.quizzes.*') ? 'active' : '' }}">
                <i class="bi bi-lightning me-2"></i>
                <span>Quizzes</span>
            </a>

            <a href="{{ route('instructor.exams.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.exams.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square me-2"></i>
                <span>Exams</span>
            </a>

            <a href="{{ route('instructor.live-monitoring.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.live-monitoring.*') ? 'active' : '' }}">
                <i class="bi bi-camera-video me-2"></i>
                <span>Live Monitoring</span>
            </a>
            
            
            
            <a href="{{ route('instructor.monitoring.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.monitoring.*') ? 'active' : '' }}">
                <i class="bi bi-camera-video me-2"></i>
                <span>Monitoring</span>
            </a>

            <a href="{{ route('instructor.feedback.create') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.feedback.*') ? 'active' : '' }}">
                <i class="bi bi-chat me-2"></i>
                <span>Feedback</span>
            </a>

            <div class="list-group-item" style="background: transparent; padding: 0.5rem 1.25rem;">
                <hr style="border-color: rgba(255,255,255,0.08); margin: 0;">
            </div>

            <a href="{{ route('instructor.analytics.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.analytics') ? 'active' : '' }}">
                <i class="bi bi-graph-up me-2"></i>
                <span>Analytics</span>
            </a>

            <a href="{{ route('instructor.reports.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.reports') ? 'active' : '' }}">
                <i class="bi bi-file-text me-2"></i>
                <span>Reports</span>
            </a>

            <a href="{{ route('instructor.study-materials.index') }}"
               class="list-group-item list-group-item-action {{ request()->routeIs('instructor.study-materials.index') ? 'active' : '' }}">
                <i class="bi bi-journal me-2"></i>
                <span>Study Materials</span>
            </a>
        </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <button class="btn btn-link" id="menu-toggle">
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
                            <li><a class="dropdown-item" href="{{ route('instructor.quizzes.create') }}">
                                <i class="bi bi-lightning me-2"></i>Launch Quick Quiz</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('instructor.feedback.create') }}">
                                <i class="bi bi-chat me-2"></i>Get Feedback</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('instructor.live-monitoring.index') }}">
                                <i class="bi bi-camera-video me-2"></i>Start Monitoring</a>
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
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="bi bi-person me-2"></i>Profile</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('profile.settings') }}">
                                <i class="bi bi-gear me-2"></i>Settings</a>
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

        <!-- Main Content -->
        <div class="main-content-area mb-5" id="mainContent">
            <!-- Breadcrumbs -->
            @if(isset($breadcrumbs) || View::hasSection('breadcrumbs'))
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Home</a></li>
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
        <footer class="footer desktop-only">
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
</div>

<!-- ===== BOTTOM NAVIGATION (Mobile) ===== -->
<nav class="bottom-nav mobile-only">
    <div class="nav-items">
        <a href="{{ route('instructor.dashboard') }}" 
           class="nav-item {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('instructor.courses.index') }}" 
           class="nav-item {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}">
            <i class="bi bi-book"></i>
            <span>Courses</span>
        </a>
        <a href="{{ route('instructor.quizzes.index') }}" 
           class="nav-item {{ request()->routeIs('instructor.quizzes.*') ? 'active' : '' }}">
            <i class="bi bi-lightning"></i>
            <span>Quizzes</span>
        </a>
        <a href="{{ route('instructor.exams.index') }}" 
           class="nav-item {{ request()->routeIs('instructor.exams.*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i>
            <span>Exams</span>
        </a>
        <a href="{{ route('instructor.live-monitoring.index') }}" 
           class="nav-item {{ request()->routeIs('instructor.live-monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-camera-video"></i>
            <span>Monitor</span>
        </a>
    </div>
</nav>
@endsection