@extends('layouts.base')

@section('layout-content')
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
            <a href="{{ route('student.dashboard') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('student.exams.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('student.exams.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square me-2"></i>
                <span>My Exams</span>
            </a>

            <a href="{{ route('student.results.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart me-2"></i>
                <span>My Results</span>
            </a>

            <a href="{{ route('student.feedback.pending') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('student.feedback.*') ? 'active' : '' }}">
                <i class="bi bi-chat me-2"></i>
                <span>Feedback</span>
                @if($pendingFeedbackCount ?? 0 > 0)
                <span class="badge bg-danger ms-2">{{ $pendingFeedbackCount }}</span>
                @endif
            </a>

            <div class="list-group-item" style="background: transparent;">
                <hr style="border-color: rgba(255,255,255,0.1);">
            </div>

            <a href="{{ route('student.analytics.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('student.analytics') ? 'active' : '' }}">
                <i class="bi bi-graph-up me-2"></i>
                <span>My Analytics</span>
            </a>

            <a href="{{ route('student.study-materials') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('student.study-materials') ? 'active' : '' }}">
                <i class="bi bi-journal me-2"></i>
                <span>Study Materials</span>
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
                    <!-- Notifications -->
                    <div class="dropdown me-3">
                        <button class="btn btn-link position-relative text-decoration-none" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="notification-badge" style="display: none;">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 300px;" id="notifications-dropdown">
                            <div class="dropdown-header">
                                <span>Notifications</span>
                            </div>
                            <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                                <div class="text-center py-3 text-muted">
                                    <i class="bi bi-check-circle"></i>
                                    <p class="mb-0">No new notifications</p>
                                </div>
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
                                    <i class="bi bi-gear me-2"></i>Settings
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

        {{-- <div class="container-fluid py-4">
            @yield('content')
        </div> --}}
        <div class="container-fluid py-4">
            <!-- Global Alert Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Success!</strong><br>
                        {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Error!</strong><br>
                        {{ session('error') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Warning!</strong><br>
                        {{ session('warning') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Information!</strong><br>
                        {{ session('info') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Validation Errors!</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')
        </div>



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
@endsection