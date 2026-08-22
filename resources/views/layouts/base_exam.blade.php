<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
        @stack('meta')

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
            min-height: 100vh;
        }

        /* ================================================
           TOPBAR - NTA STANDARD
           ================================================ */

        /* Topbar Row 1 - Logo & Branding */
        .topbar-row-1 {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            padding: 8px 0;
            border-bottom: 2px solid #0d1445;
        }

        .topbar-row-1 .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-left .logo-placeholder {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-left .logo-placeholder img {
            height: 45px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .brand-left .logo-placeholder .logo-text {
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .brand-left .logo-placeholder .logo-text small {
            font-weight: 300;
            font-size: 0.65rem;
            opacity: 0.85;
            display: block;
        }

        .brand-left .divider-line {
            width: 2px;
            height: 35px;
            background: rgba(255,255,255,0.3);
        }

        .brand-left .institute-name {
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .brand-left .institute-name small {
            font-weight: 300;
            font-size: 0.65rem;
            opacity: 0.8;
            display: block;
        }

        .topbar-row-1 .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar-row-1 .profile-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
        }

        .topbar-row-1 .profile-icon:hover {
            background: rgba(255,255,255,0.25);
            border-color: #ffffff;
        }
        
        .fixed-topbar {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 1030; 
                background-color: #ffffff; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            }
            
            /* Crucial: Add padding to your main body/content wrapper so it doesn't hide under the topbar */
            body {
                padding-top: 70px; 
            }

        .topbar-row-2{
    position: relative;
    overflow: hidden;
    min-height: 120px;
    background: #fff;
}

.topbar-row-2 .bg-image{
    position: absolute;
    inset: 0;

    background-image: url('/images/top.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    opacity: 0.99;

    z-index: 0;
}

.topbar-row-2 .container{
    position: relative;
    z-index: 1;
}


        .topbar-row-2 .container-fluid {
            position: relative;
            z-index: 1;
            padding: 12px 15px;
        }

        .topbar-row-2 .row {
            align-items: center;
        }

        /* Left Side - Title/Exam Info */
        .topbar-row-2 .left-content {
            display: flex;
            flex-direction: column;
        }

        .topbar-row-2 .left-content .main-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a237e;
            letter-spacing: 0.5px;
        }

        .topbar-row-2 .left-content .sub-title {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 400;
        }

        /* Right Side - Language & Info */
        .topbar-row-2 .right-content {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }

        .topbar-row-2 .language-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            padding: 4px 14px 4px 10px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .topbar-row-2 .language-selector label {
            font-size: 0.75rem;
            color: #6c757d;
            margin: 0;
            font-weight: 500;
        }

        .topbar-row-2 .language-selector select {
            border: none;
            background: transparent;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1a237e;
            padding: 2px 0;
            cursor: pointer;
            outline: none;
        }

        .topbar-row-2 .language-selector select option {
            color: #333;
        }

        /* Timer */
        .topbar-row-2 .timer-display {
            font-size: 1.1rem;
            font-weight: 700;
            color: #dc3545;
            font-family: 'Courier New', monospace;
            background: #fff5f5;
            padding: 4px 14px;
            border-radius: 6px;
            border: 1px solid #fcc;
        }

        .topbar-row-2 .timer-display i {
            color: #dc3545;
            margin-right: 6px;
        }

        /* ================================================
           MAIN CONTENT
           ================================================ */
        .exam-main {
            /*padding: 20px 0 30px;*/
            min-height: calc(100vh - 130px);
        }

        /* ================================================
           RESPONSIVE
           ================================================ */
        @media (max-width: 992px) {
            .brand-left .logo-placeholder img {
                height: 35px;
            }

            .brand-left .logo-placeholder .logo-text {
                font-size: 0.75rem;
            }

            .brand-left .institute-name {
                font-size: 0.75rem;
            }

            .topbar-row-2 .left-content .main-title {
                font-size: 1rem;
            }

            .topbar-row-2 .timer-display {
                font-size: 0.95rem;
                padding: 3px 10px;
            }
        }

        @media (max-width: 768px) {
            .topbar-row-1 .container-fluid {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .topbar-row-1 .topbar-right {
                width: 100%;
                justify-content: flex-end;
            }
            

            .brand-left {
                flex-wrap: wrap;
                gap: 8px;
            }

            .brand-left .divider-line {
                display: none;
            }

            .brand-left .logo-placeholder img {
                height: 30px;
            }

            .brand-left .logo-placeholder .logo-text {
                font-size: 0.7rem;
            }

            .brand-left .institute-name {
                font-size: 0.7rem;
            }

            .topbar-row-2 .right-content {
                justify-content: flex-start;
                margin-top: 8px;
                gap: 12px;
            }

            .topbar-row-2 .left-content .main-title {
                font-size: 0.9rem;
            }

            .topbar-row-2 .left-content .sub-title {
                font-size: 0.7rem;
            }

            .topbar-row-2 .timer-display {
                font-size: 0.85rem;
            }

            .topbar-row-2 .language-selector {
                padding: 3px 10px 3px 8px;
            }

            .topbar-row-2 .language-selector label {
                font-size: 0.65rem;
            }

            .topbar-row-2 .language-selector select {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .brand-left .logo-placeholder img {
                height: 25px;
            }

            .brand-left .logo-placeholder .logo-text {
                font-size: 0.6rem;
            }

            .brand-left .institute-name {
                font-size: 0.6rem;
            }

            .topbar-row-2 .container-fluid {
                padding: 8px 10px;
            }

            .topbar-row-2 .left-content .main-title {
                font-size: 0.8rem;
            }

            .topbar-row-2 .timer-display {
                font-size: 0.75rem;
                padding: 2px 8px;
            }

            .topbar-row-2 .language-selector {
                padding: 2px 8px 2px 6px;
            }

            .topbar-row-2 .language-selector label {
                font-size: 0.6rem;
            }

            .topbar-row-2 .language-selector select {
                font-size: 0.65rem;
            }
        }

        /* Utility Classes */
        .text-nta-blue {
            color: #1a237e;
        }

        .bg-nta-blue {
            background: #1a237e;
        }

        .border-nta-blue {
            border-color: #1a237e;
        }
        
        @stack('styles')
    </style>
</head>
<body>
    <!-- ============================================
         TOPBAR - ROW 1: Logo & Branding
         ============================================ -->
    <div class="topbar-row-1 fixed-topbar">
        <div class="container-fluid">
            <div class="brand-left">
                <div class="logo-placeholder">
                    <!-- AAI Logo -->
                    <img src="{{ asset('images/aai-logo-bg.png') }}" alt="AAI" onerror="this.style.display='none'">
                    <div class="logo-text">
                        Aviation Security Training Institute
                        <small>Ministry of Civil Aviation, Government of India</small>
                    </div>
                </div>
                <div class="divider-line"></div>
                <div class="institute-name">
                    {{ config('app.name', 'CBT System') }}
                    <small>Exam Platform</small>
                </div>
            </div>
            <div class="topbar-right">
                <div class="profile-icon" title=" {{ Auth::user()->name }}  ">
                    <img src="{{ Auth::user()->avatar_url }}" 
                        alt="{{ Auth::user()->name }}" 
                        class="rounded-circle" width="32" height="32">
                </div>
            </div>
        </div>
    </div>

    @yield('topbar-2')

    <!-- ============================================
         MAIN CONTENT
         ============================================ -->
    <div class="exam-main">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

    <!-- ============================================
         SCRIPTS
         ============================================ -->
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

    function toggleProfileMenu() {
        toastr.info('Profile menu would open here');
    }

    // Language selector - safe check for both possible IDs
    document.addEventListener('DOMContentLoaded', function() {
        // Try topbarLanguage first (from base layout)
        const topbarLang = document.getElementById('topbarLanguage');
        if (topbarLang) {
            topbarLang.addEventListener('change', function() {
                toastr.info('Language changed to ' + this.options[this.selectedIndex].text);
            });
        }
        
        // Try drpLanguage (from exam page)
        const drpLang = document.getElementById('drpLanguage');
        if (drpLang) {
            drpLang.addEventListener('change', function() {
                toastr.info('Language changed to ' + this.options[this.selectedIndex].text);
            });
        }
    });

    // Timer update function (to be overridden by child pages)
    // window.updateTimerDisplay = function(timeRemaining) {
    //     const hours = Math.floor(timeRemaining / 3600);
    //     const minutes = Math.floor((timeRemaining % 3600) / 60);
    //     const seconds = timeRemaining % 60;
    //     const display = hours > 0
    //         ? `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
    //         : `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    //     const timerEl = document.getElementById('time-remaining');
    //     if (timerEl) timerEl.innerText = display;
    // };
    
 
</script>

    @stack('scripts')
</body>
</html>