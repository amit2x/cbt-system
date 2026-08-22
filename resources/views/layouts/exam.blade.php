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

        /* Topbar Styles - Fixed */
        .exam-topbar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 8px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .app-name {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .exam-name {
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .instructor-name {
            font-size: 0.7rem;
            opacity: 0.8;
        }

        .topbar-right {
            text-align: right;
        }

        .progress-stats {
            display: inline-block;
            margin-right: 20px;
            vertical-align: middle;
        }

        .progress-label {
            font-size: 0.65rem;
            text-transform: uppercase;
        }

        .progress-value {
            font-size: 1rem;
            font-weight: 700;
        }

        .progress-bar-container {
            width: 80px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin-top: 2px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #2ecc71;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .timer-box {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 4px 10px;
            display: inline-block;
            vertical-align: middle;
        }

        .timer-label {
            font-size: 0.65rem;
        }

        .timer-display {
            font-size: 1.1rem;
            font-weight: 700;
            font-family: monospace;
            line-height: 1.2;
        }

        .timer-warning {
            color: #ff6b6b;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

    
        .timer-warning {
            color: #f39c12 !important;
            animation: pulse 1s infinite;
        }
        
        .timer-danger {
            color: #e74c3c !important;
            animation: pulse 0.5s infinite;
        }
        
        @keyframes pulse {
        
            0% {
                transform: scale(1);
            }
        
            50% {
                transform: scale(1.08);
            }
        
            100% {
                transform: scale(1);
            }
        }
        
        /* Main Content - Adjusted for fixed topbar */
        .exam-main {
            margin-top: 65px;
            padding: 15px;
        }

        /* Left Panel - Student Info (Desktop) */
        .student-info-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 15px;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 10px;
        }

        .student-name {
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
        }

        .student-roll {
            font-size: 0.8rem;
            color: #7f8c8d;
            text-align: center;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.8rem;
        }

        .info-label {
            color: #7f8c8d;
        }

        .info-value {
            font-weight: 500;
        }

        /* Question Panel */
        .question-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .question-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .question-counter {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .question-badge {
            background: #e9ecef;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
        }

        .question-text {
            padding: 20px;
            font-size: 1rem;
            line-height: 1.5;
            /* min-height: 150px; */
        }

        .question-text img {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
        }

        /* Options */
        .options-container {
            padding: 15px 20px;
        }

        .option-item {
            display: flex;
            align-items: flex-start;
            padding: 10px 12px;
            margin-bottom: 10px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-item:hover {
            border-color: #3498db;
            background: #f0f7ff;
        }

        .option-item.selected {
            border-color: #2ecc71;
            background: #e8f8f0;
        }

        .option-marker {
            width: 28px;
            height: 28px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 12px;
            font-size: 0.9rem;
        }

        .option-item.selected .option-marker {
            background: #2ecc71;
            color: white;
        }

        .option-text {
            flex: 1;
            line-height: 1.4;
            font-size: 0.9rem;
        }

        .option-text img {
            max-width: 100%;
            height: auto;
            margin-top: 5px;
        }

        /* Action Buttons */
        .action-buttons {
            padding: 12px 15px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn-exam {
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-exam-primary {
            background: #3498db;
            color: white;
        }

        .btn-exam-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-exam-warning {
            background: #f39c12;
            color: white;
        }

        .btn-exam-warning:hover {
            background: #e67e22;
        }

        .btn-exam-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-exam-secondary:hover {
            background: #7f8c8d;
        }

        .btn-exam-outline {
            background: transparent;
            border: 1px solid #bdc3c7;
            color: #7f8c8d;
        }

        .btn-exam-outline:hover {
            background: #ecf0f1;
        }

        .btn-exam-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-exam-danger:hover {
            background: #c0392b;
        }

        /* Right Panel - Palette */
        .palette-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 80px;
        }

        .palette-header {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-badge {
            flex: 1;
            text-align: center;
            padding: 5px;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 500;
        }

        .stat-badge.not-visited {
            background: #ecf0f1;
            color: #7f8c8d;
        }

        .stat-badge.visited {
            background: #3498db;
            color: white;
        }

        .stat-badge.marked {
            background: #f39c12;
            color: white;
        }

        .stat-badge.answered {
            background: #2ecc71;
            color: white;
        }

        .stat-badge.answered-marked {
            background: #e67e22;
            color: white;
        }

        .question-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            padding: 12px;
            max-height: 300px;
            overflow-y: scroll;
        }

        .grid-btn {
            aspect-ratio: 1;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .grid-btn.not-visited {
            background: #ecf0f1;
            color: #7f8c8d;
        }

        .grid-btn.visited {
            background: #3498db;
            color: white;
        }

        .grid-btn.marked {
            background: #f39c12;
            color: white;
        }

        .grid-btn.answered {
            background: #2ecc71;
            color: white;
        }

        /*.grid-btn.answered-marked {*/
        /*    background: #e67e22;*/
        /*    color: white;*/
        /*}*/
        .grid-btn.answered-marked {
            background: #e67e22;
            color: white;
            position: relative;
        }
        
        .grid-btn.answered-marked::after {
            content: "✓";
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            font-size: 8px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .grid-btn.current {
            transform: scale(1.05);
            box-shadow: 0 0 0 2px #2c3e50;
        }

        .palette-actions {
            padding: 12px;
            border-top: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .palette-action-btn {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: none;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            cursor: pointer;
        }

        .mobile-menu-btn.right {
            left: auto;
            right: 20px;
            background: #2ecc71;
        }

        #mobilePalette.open {
            right: 0 !important;
        }
        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background: white;
            z-index: 1050;
            transition: left 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .mobile-sidebar.open {
            left: 0;
        }

        .mobile-sidebar-header {
            background: #1a1a2e;
            color: white;
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
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }

        .mobile-overlay.show {
            display: block;
        }

        /* Modal Styles */
        .modal-question-list {
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-question-item {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
        }

        .modal-question-item:hover {
            background: #f8f9fa;
        }

        /* Fullscreen Button */
        .fullscreen-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .col-lg-3 {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .exam-topbar {
                position: relative;
                padding: 8px 0;
            }

            .exam-main {
                margin-top: 0;
                padding: 10px;
            }

            .mobile-menu-btn {
                display: block;
            }

            .desktop-sidebar {
                display: none;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-exam {
                width: 100%;
                text-align: center;
            }

            .stats-grid {
                flex-wrap: wrap;
            }

            .stat-badge {
                min-width: calc(33% - 8px);
            }

            .question-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .topbar-right {
                text-align: center;
                margin-top: 8px;
            }

            .progress-stats,
            .timer-box {
                display: inline-block;
                margin: 0 5px;
            }
        }

        @media (max-width: 480px) {
            .question-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .btn-exam {
                font-size: 0.75rem;
                padding: 6px 10px;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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

    <!-- Fullscreen Button -->
    <button class="fullscreen-btn" onclick="toggleFullscreen()">
        <i class="fas fa-expand"></i>
    </button>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenus()"></div>

    <!-- Mobile Sidebar (Student Info) -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <i class="fas fa-user-graduate fa-2x mb-2"></i>
            <div class="student-name" id="mobileStudentName">{{ Auth::user()->name }}</div>
            <div class="student-roll">Roll No: {{ Auth::user()->roll_no ?? 'N/A' }}</div>
        </div>
        <div class="mobile-sidebar-content" id="mobileSidebarContent">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>

    <!-- Mobile Palette Sidebar -->
    <div class="mobile-sidebar" id="mobilePalette" style="left: auto; right: -280px;">
        <div class="mobile-sidebar-header">
            <i class="fas fa-th fa-2x mb-2"></i>
            <div>Question Palette</div>
        </div>
        <div class="mobile-sidebar-content" id="mobilePaletteContent">
            <!-- Dynamic content will be loaded here -->
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

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                $('.fullscreen-btn i').removeClass('fa-expand').addClass('fa-compress');
            } else {
                document.exitFullscreen();
                $('.fullscreen-btn i').removeClass('fa-compress').addClass('fa-expand');
            }
        }

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

        document.addEventListener('fullscreenchange', function() {
            const btn = $('.fullscreen-btn i');
            if (document.fullscreenElement) {
                btn.removeClass('fa-expand').addClass('fa-compress');
            } else {
                btn.removeClass('fa-compress').addClass('fa-expand');
            }
        });

        $(document).ready(function() {
            // Remove any jQuery conflicts
            if (typeof jQuery !== 'undefined') {
                console.log('jQuery loaded successfully');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>