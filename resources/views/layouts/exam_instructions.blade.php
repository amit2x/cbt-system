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
        }

        /* Fixed Topbar with NTA Branding */
        .exam-topbar {
            background: #ffffff;
            border-bottom: 3px solid #1a237e;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .topbar-branding {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            padding: 8px 0;
        }

        .topbar-branding .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo img {
            height: 45px;
            width: auto;
        }

        .brand-text {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .brand-text small {
            font-weight: 300;
            font-size: 0.75rem;
            opacity: 0.8;
            display: block;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .language-selector {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            color: #ffffff;
            padding: 4px 12px;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .language-selector:focus {
            outline: none;
            border-color: rgba(255,255,255,0.6);
        }

        .language-selector option {
            color: #333;
        }

        .profile-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.3s;
        }

        .profile-icon:hover {
            background: rgba(255,255,255,0.3);
            border-color: #ffffff;
        }

        /* Topbar Bottom - Exam Info */
        .topbar-info {
            background: #f8f9fa;
            padding: 6px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .topbar-info .exam-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a237e;
        }

        .topbar-info .exam-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .topbar-info .timer-display {
            font-size: 1rem;
            font-weight: 700;
            color: #dc3545;
            font-family: monospace;
        }

        /* Main Content */
        .exam-main {
            margin-top: 120px;
            padding: 20px 0 40px;
        }

        /* Instructions Card */
        .instructions-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 30px 35px;
            margin-bottom: 25px;
        }

        .instructions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .instructions-header h4 {
            color: #1a237e;
            font-weight: 700;
            font-size: 1.3rem;
            margin: 0;
        }

        .instructions-header .language-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .instructions-header .language-dropdown label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #495057;
            margin: 0;
        }

        .instructions-header .language-dropdown select {
            padding: 4px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.85rem;
            background: #fff;
        }

        .instructions-content h5 {
            color: #1a237e;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .instructions-content .instruction-section {
            margin-bottom: 20px;
        }

        .instructions-content .instruction-section h6 {
            font-weight: 600;
            color: #343a40;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .instructions-content .instruction-section p,
        .instructions-content .instruction-section li {
            font-size: 0.9rem;
            color: #495057;
            line-height: 1.7;
        }

        .instructions-content .instruction-section ul {
            padding-left: 20px;
        }

        .instructions-content .instruction-section ul li {
            margin-bottom: 5px;
        }

        .status-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 15px 0;
        }

        .status-legend .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #495057;
        }

        .status-legend .legend-dot {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-block;
        }

        .legend-dot.not-visited { background: #e9ecef; border: 1px solid #ced4da; }
        .legend-dot.not-answered { background: #f39c12; }
        .legend-dot.answered { background: #28a745; }
        .legend-dot.marked-review { background: #fd7e14; }
        .legend-dot.answered-marked { background: #dc3545; }

        /* Agreement Section */
        .agreement-section {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 10px;
        }

        .agreement-section .form-check {
            margin-bottom: 15px;
        }

        .agreement-section .form-check-label {
            font-size: 0.9rem;
            color: #495057;
            line-height: 1.6;
        }

        .agreement-section .form-check-input {
            margin-top: 3px;
        }

        .btn-start-exam {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: #ffffff;
            padding: 12px 50px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-start-exam:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(26, 35, 126, 0.4);
            color: #ffffff;
        }

        .btn-start-exam:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .footer-copyright {
            text-align: center;
            padding: 15px 0;
            font-size: 0.75rem;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            margin-top: 30px;
        }

        .footer-copyright span {
            color: #1a237e;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .exam-main {
                margin-top: 110px;
                padding: 15px;
            }

            .instructions-card {
                padding: 20px;
            }

            .instructions-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .instructions-header .language-dropdown {
                width: 100%;
            }

            .instructions-header .language-dropdown select {
                flex: 1;
            }

            .topbar-branding .container-fluid {
                flex-wrap: wrap;
                gap: 5px;
            }

            .brand-logo img {
                height: 32px;
            }

            .brand-text {
                font-size: 0.9rem;
            }

            .brand-text small {
                font-size: 0.65rem;
            }

            .topbar-info .exam-title {
                font-size: 0.75rem;
            }

            .status-legend {
                gap: 10px;
            }

            .status-legend .legend-item {
                font-size: 0.7rem;
            }

            .btn-start-exam {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .exam-main {
                margin-top: 100px;
            }

            .instructions-card {
                padding: 15px;
            }

            .brand-logo {
                gap: 8px;
            }

            .brand-logo img {
                height: 28px;
            }

            .brand-text {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Topbar -->
    <div class="exam-topbar">
        <!-- Top Bar - Branding -->
        <div class="topbar-branding">
            <div class="container-fluid">
                <div class="brand-logo">
                    <!-- Replace with your NTA logo path -->
                    <img src="{{ asset('images/nta-logo.png') }}" alt="NTA" onerror="this.style.display='none'">
                    <div class="brand-text">
                        National Testing Agency
                        <small>Ministry of Education, Government of India</small>
                    </div>
                </div>
                <div class="topbar-right">
                    <select class="language-selector" id="languageSelector">
                        <option value="en">English</option>
                        <option value="hi">हिंदी</option>
                    </select>
                    <div class="profile-icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar - Exam Info -->
        <div class="topbar-info">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="exam-title">@yield('exam_title', 'Exam')</div>
                        <div class="exam-meta">@yield('exam_meta', '')</div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="timer-display">
                            <i class="far fa-clock me-2"></i>
                            <span id="time-remaining">@yield('exam_duration', '--:--')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="exam-main">
        <div class="container">
            @yield('content')
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
    </script>

    @stack('scripts')
</body>
</html>