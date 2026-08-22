<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Palette Legend</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://jsdelivr.net" rel="stylesheet">
    <style>
        .legend-text {
            color: #555;
            font-size: 0.95rem;
        }
        
        /* 1. White/Gray Square with Border */
        .status-box {
            width: 35px;
            height: 35px;
            background-color: #f0f0f0;
            border: 2px solid #b5b5b5;
            border-radius: 6px;
        }

        /* 2. Red Trapezo-pentagon shape (simulated with clip-path) */
        .status-red {
            width: 35px;
            height: 32px;
            background-color: #e04006;
            clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
        }

        /* 3. Green Arrow/Trapezoid shape */
        .status-green {
            width: 35px;
            height: 32px;
            background-color: #24bd04;
            clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
        }

        /* 4. Purple Circle */
        .status-purple-circle {
            width: 35px;
            height: 35px;
            background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
            border-radius: 50%;
        }

        /* 5. Purple Circle with Green Check Tag */
        .status-purple-review {
            position: relative;
            width: 35px;
            height: 35px;
            background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
            border-radius: 50%;
        }
        .status-purple-review::after {
            content: "✓";
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 12px;
            height: 12px;
            background-color: #24bd04;
            color: white;
            font-size: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            border: 1px solid white;
        }
    </style>
</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow-sm" style="max-width: 900px;">
    <h6 class="mb-4 text-secondary">
        3. The Questions Palette displayed on the right side of screen will show the status of each question using one of the following symbols:
    </h6>

    <!-- List Items -->
    <div class="d-flex flex-column gap-3">
        
        <!-- Item 1 -->
        <div class="d-flex align-items-center">
            <span class="me-3 fw-bold text-secondary style="width: 20px;">1.</span>
            <div class="me-3 d-flex justify-content-center align-items-center" style="width: 50px;">
                <div class="status-box"></div>
            </div>
            <span class="legend-text">You have not visited the question yet.</span>
        </div>

        <!-- Item 2 -->
        <div class="d-flex align-items-center">
            <span class="me-3 fw-bold text-secondary" style="width: 20px;">2.</span>
            <div class="me-3 d-flex justify-content-center align-items-center" style="width: 50px;">
                <div class="status-red"></div>
            </div>
            <span class="legend-text">You have not answered the question.</span>
        </div>

        <!-- Item 3 -->
        <div class="d-flex align-items-center">
            <span class="me-3 fw-bold text-secondary" style="width: 20px;">3.</span>
            <div class="me-3 d-flex justify-content-center align-items-center" style="width: 50px;">
                <div class="status-green"></div>
            </div>
            <span class="legend-text">You have answered the question.</span>
        </div>

        <!-- Item 4 -->
        <div class="d-flex align-items-center">
            <span class="me-3 fw-bold text-secondary" style="width: 20px;">4.</span>
            <div class="me-3 d-flex justify-content-center align-items-center" style="width: 50px;">
                <div class="status-purple-circle"></div>
            </div>
            <span class="legend-text">You have NOT answered the question, but have marked the question for review.</span>
        </div>

        <!-- Item 5 -->
        <div class="d-flex align-items-center">
            <span class="me-3 fw-bold text-secondary" style="width: 20px;">5.</span>
            <div class="me-3 d-flex justify-content-center align-items-center" style="width: 50px;">
                <div class="status-purple-review"></div>
            </div>
            <span class="legend-text">The question(s) "Answered and Marked for Review" will be considered for evaluation.</span>
        </div>

    </div>
</div>

</body>
</html>
