<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @stack('styles')

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', 'Nunito', Roboto, sans-serif;
            overflow-x: hidden;
        }

        /* Fixed Topbar - NTA Style */
        .exam-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Top Bar - Branding */
        .topbar-branding {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            padding: 6px 0;
        }

        .topbar-branding .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo img {
            height: 38px;
            width: auto;
        }

        .brand-text {
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .brand-text small {
            font-weight: 300;
            font-size: 0.7rem;
            opacity: 0.8;
            display: block;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,0.3);
            font-size: 0.8rem;
        }

        /* Bottom Bar - Exam Info & Timer */
        .topbar-exam-info {
            background: #f8f9fa;
            padding: 4px 0;
            border-bottom: 2px solid #1a237e;
        }

        .exam-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1a237e;
        }

        .exam-meta {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .timer-box {
            background: #dc3545;
            color: #ffffff;
            padding: 4px 14px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .timer-box .timer-label {
            font-size: 0.7rem;
            font-weight: 500;
        }

        .timer-box .timer-display {
            font-size: 1.1rem;
            font-weight: 700;
            font-family: monospace;
        }

        /* Main Content */
        .exam-main {
            margin-top: 105px;
            padding: 12px 0 20px;
        }

        /* Section Tabs */
        .section-tabs {
            background: #ffffff;
            border-radius: 8px 8px 0 0;
            padding: 8px 15px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .section-tab {
            padding: 6px 18px;
            border-radius: 6px 6px 0 0;
            font-size: 0.8rem;
            font-weight: 500;
            color: #6c757d;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .section-tab:hover {
            background: #f8f9fa;
            color: #1a237e;
        }

        .section-tab.active {
            color: #1a237e;
            background: #f8f9fa;
            border-bottom: 3px solid #1a237e;
        }

        /* Question Area */
        .question-panel {
            background: #ffffff;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            min-height: 400px;
        }

        .question-header {
            padding: 12px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .question-counter {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1a237e;
        }

        .question-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .question-badge {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            background: #e9ecef;
            color: #495057;
        }

        .question-badge.type {
            background: #e3f2fd;
            color: #1565c0;
        }

        .question-badge.marks {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .question-text {
            padding: 20px 25px;
            font-size: 1rem;
            line-height: 1.7;
            min-height: 100px;
        }

        .question-text img {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
        }

        /* Options */
        .options-container {
            padding: 10px 25px 20px;
        }

        .option-item {
            display: flex;
            align-items: flex-start;
            padding: 10px 14px;
            margin-bottom: 8px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-item:hover {
            border-color: #90caf9;
            background: #f5f9ff;
        }

        .option-item.selected {
            border-color: #28a745;
            background: #e8f5e9;
        }

        .option-marker {
            width: 26px;
            height: 26px;
            min-width: 26px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 12px;
            font-size: 0.8rem;
            border: 1px solid #dee2e6;
        }

        .option-item.selected .option-marker {
            background: #28a745;
            color: #ffffff;
            border-color: #28a745;
        }

        .option-text {
            flex: 1;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .option-text img {
            max-width: 100%;
            height: auto;
            margin-top: 5px;
        }

        /* Action Buttons */
        .action-buttons {
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .action-buttons .left-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-buttons .right-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-exam {
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-exam-primary {
            background: #1a237e;
            color: #ffffff;
        }

        .btn-exam-primary:hover {
            background: #0d1445;
            color: #ffffff;
        }

        .btn-exam-success {
            background: #28a745;
            color: #ffffff;
        }

        .btn-exam-success:hover {
            background: #1e7e34;
            color: #ffffff;
        }

        .btn-exam-warning {
            background: #fd7e14;
            color: #ffffff;
        }

        .btn-exam-warning:hover {
            background: #e06b0a;
            color: #ffffff;
        }

        .btn-exam-secondary {
            background: #6c757d;
            color: #ffffff;
        }

        .btn-exam-secondary:hover {
            background: #5a6268;
            color: #ffffff;
        }

        .btn-exam-outline {
            background: transparent;
            border: 1px solid #ced4da;
            color: #495057;
        }

        .btn-exam-outline:hover {
            background: #f8f9fa;
        }

        .btn-exam-danger {
            background: #dc3545;
            color: #ffffff;
        }

        .btn-exam-danger:hover {
            background: #bd2130;
            color: #ffffff;
        }

        /* Right Panel - Palette (NTA Style) */
        .palette-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: sticky;
            top: 115px;
        }

        .palette-header {
            padding: 10px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            font-size: 0.9rem;
            color: #1a237e;
        }

        .palette-legend {
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.7rem;
        }

        .palette-legend .legend-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 3px;
        }

        .palette-legend .legend-dot {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            display: inline-block;
            flex-shrink: 0;
        }

        .legend-dot.not-visited { background: #e9ecef; border: 1px solid #ced4da; }
        .legend-dot.answered { background: #28a745; }
        .legend-dot.marked-review { background: #fd7e14; }
        .legend-dot.answered-marked { background: #dc3545; }

        .question-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
            padding: 12px 15px;
            max-height: 280px;
            overflow-y: auto;
        }

        .grid-btn {
            aspect-ratio: 1;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .grid-btn:hover {
            transform: scale(1.05);
        }

        .grid-btn.not-visited {
            background: #e9ecef;
            color: #6c757d;
        }

        .grid-btn.answered {
            background: #28a745;
            color: #ffffff;
        }

        .grid-btn.marked-review {
            background: #fd7e14;
            color: #ffffff;
        }

        .grid-btn.answered-marked {
            background: #dc3545;
            color: #ffffff;
        }

        .grid-btn.current {
            outline: 3px solid #1a237e;
            outline-offset: 2px;
            z-index: 1;
        }

        /* Stats Summary */
        .stats-summary {
            padding: 8px 15px;
            border-top: 1px solid #e9ecef;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.7rem;
        }

        .stats-summary .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stats-summary .stat-dot {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
        }

        .stat-dot.s-answered { background: #28a745; }
        .stat-dot.s-marked { background: #fd7e14; }
        .stat-dot.s-answered-marked { background: #dc3545; }
        .stat-dot.s-not-visited { background: #e9ecef; border: 1px solid #ced4da; }

        .palette-actions {
            padding: 10px 15px;
            border-top: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .palette-action-btn {
            width: 100%;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            background: #ffffff;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .palette-action-btn:hover {
            background: #f8f9fa;
        }

        .palette-action-btn.primary {
            background: #1a237e;
            color: #ffffff;
            border-color: #1a237e;
        }

        .palette-action-btn.primary:hover {
            background: #0d1445;
        }

        .palette-action-btn.danger {
            background: #dc3545;
            color: #ffffff;
            border-color: #dc3545;
        }

        .palette-action-btn.danger:hover {
            background: #bd2130;
        }

        /* Mobile Responsive */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #1a237e;
            color: #ffffff;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1001;
            cursor: pointer;
        }

        .mobile-menu-btn.right {
            left: auto;
            right: 20px;
            background: #28a745;
        }

        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 290px;
            height: 100%;
            background: #ffffff;
            z-index: 1050;
            transition: left 0.3s ease;
            box-shadow: 2px 0 15px rgba(0,0,0,0.15);
            overflow-y: auto;
        }

        .mobile-sidebar.open {
            left: 0;
        }

        .mobile-sidebar.right {
            left: auto;
            right: -300px;
        }

        .mobile-sidebar.right.open {
            right: 0;
            left: auto;
        }

        .mobile-sidebar-header {
            background: #1a237e;
            color: #ffffff;
            padding: 15px;
            text-align: center;
        }

        .mobile-sidebar-content {
            padding: 15px;
        }

        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }

        .mobile-overlay.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .exam-main {
                margin-top: 95px;
            }

            .palette-card {
                margin-top: 15px;
            }
        }

        @media (max-width: 768px) {
            .exam-main {
                margin-top: 85px;
                padding: 10px;
            }

            .mobile-menu-btn {
                display: block;
            }

            .desktop-sidebar {
                display: none !important;
            }

            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .action-buttons .left-actions,
            .action-buttons .right-actions {
                justify-content: center;
            }

            .btn-exam {
                font-size: 0.75rem;
                padding: 5px 12px;
            }

            .question-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .topbar-branding .container-fluid {
                flex-wrap: wrap;
                gap: 4px;
            }

            .brand-logo img {
                height: 28px;
            }

            .brand-text {
                font-size: 0.8rem;
            }

            .brand-text small {
                font-size: 0.6rem;
            }

            .timer-box {
                padding: 2px 10px;
            }

            .timer-box .timer-display {
                font-size: 0.9rem;
            }

            .section-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding: 6px 10px;
            }

            .section-tab {
                font-size: 0.7rem;
                padding: 4px 12px;
                white-space: nowrap;
            }
        }

        @media (max-width: 480px) {
            .exam-main {
                margin-top: 75px;
            }

            .question-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .question-text {
                padding: 15px;
                font-size: 0.9rem;
            }

            .options-container {
                padding: 10px 15px 15px;
            }

            .question-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(26, 35, 126, 0.1);
            border-top-color: #1a237e;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .timer-warning {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
    </style>
</head>
<body>
    @yield('content')

    <!-- Mobile Menu Buttons -->
    <button class="mobile-menu-btn" id="menuToggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    <button class="mobile-menu-btn right" id="paletteToggle" onclick="toggleMobilePalette()">
        <i class="fas fa-th"></i>
    </button>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenus()"></div>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <i class="fas fa-user-graduate fa-2x mb-2"></i>
            <div class="student-name" id="mobileStudentName">{{ Auth::user()->name }}</div>
            <div class="student-roll">Roll No: {{ Auth::user()->roll_no ?? 'N/A' }}</div>
        </div>
        <div class="mobile-sidebar-content" id="mobileSidebarContent">
            <!-- Dynamic content -->
        </div>
    </div>

    <!-- Mobile Palette -->
    <div class="mobile-sidebar right" id="mobilePalette">
        <div class="mobile-sidebar-header">
            <i class="fas fa-th fa-2x mb-2"></i>
            <div>Question Palette</div>
        </div>
        <div class="mobile-sidebar-content" id="mobilePaletteContent">
            <!-- Dynamic content -->
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 3000
        };

        function toggleMobileMenu() {
            $('#mobileSidebar').toggleClass('open');
            $('#mobileOverlay').toggleClass('show');
            $('#mobilePalette').removeClass('open');
        }

        function toggleMobilePalette() {
            $('#mobilePalette').toggleClass('open');
            $('#mobileOverlay').toggleClass('show');
            $('#mobileSidebar').removeClass('open');
        }

        function closeMobileMenus() {
            $('#mobileSidebar').removeClass('open');
            $('#mobilePalette').removeClass('open');
            $('#mobileOverlay').removeClass('show');
        }
    </script>

    @stack('scripts')
</body>
</html>