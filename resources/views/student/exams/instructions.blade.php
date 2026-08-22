@extends('layouts.base_exam')

@section('title', $exam->title . ' - Instructions')


@section('topbar-2')
        <!-- ============================================
         TOPBAR - ROW 2: Exam Info & Background
         ============================================ -->
    <div class="topbar-row-2">
        <!-- Background Image (Pen & Paper - Blurred) -->
        <div class="bg-image"></div>

        <div class="container py-4">
            <div class="row">
                <!-- Left: Title -->
                <div class="col-md-7 col-12">
                    <div class="left-content">
                        <div class="main-title fs-3 fw-medium text-uppercase">
                            @yield('page_title', 'General Instructions')
                        </div>
                        <!--<div class="sub-title">-->
                        <!--    @yield('page_subtitle', '')-->
                        <!--</div>-->
                    </div>
                </div>

                <!-- Right: Language & Timer -->
                <div class="col-md-5 col-12">
                    <div class="right-content">
                        <!-- Language Selector -->
                        <div class="language-selector">
                            <label for="topbarLanguage"><i class="fas fa-globe me-1"></i>Language</label>
                            <select id="topbarLanguage">
                                <option value="en">English</option>
                                <option value="hi">हिंदी</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="instructions-card">
            <div class="instructions-content">
                <h5>Please read the instructions carefully</h5>

                <!-- General Instructions -->
                <div class="instruction-section">
                    <h6 class="text-decoration-underline">General Instructions:</h6>
                    <ol class="instruction-list">
                        <li>Total duration of <strong>{{ $exam->title }}</strong> is <strong>{{ $exam->formatted_duration }}</strong>.</li>
                        <li>The clock will be set at the server. The countdown timer in the top right corner of screen will display the remaining time available for you to complete the examination. When the timer reaches zero, the examination will end by itself. You will not be required to end or submit your examination.</li>
                        <li>
                            The Questions Palette displayed on the right side of screen will show the status of each question using one of the following symbols:
                            
                            <!-- Palette Legend - Exact NTA Style -->
                            <div class="palette-legend-box">
                                <!-- Item 1: Not Visited -->
                                <div class="legend-item">
                                    <span class="legend-number">1.</span>
                                    <div class="legend-icon-wrapper">
                                        <div class="status-box"></div>
                                    </div>
                                    <span class="legend-text">You have not visited the question yet.</span>
                                </div>

                                <!-- Item 2: Not Answered -->
                                <div class="legend-item">
                                    <span class="legend-number">2.</span>
                                    <div class="legend-icon-wrapper">
                                        <div class="status-red"></div>
                                    </div>
                                    <span class="legend-text">You have not answered the question.</span>
                                </div>

                                <!-- Item 3: Answered -->
                                <div class="legend-item">
                                    <span class="legend-number">3.</span>
                                    <div class="legend-icon-wrapper">
                                        <div class="status-green"></div>
                                    </div>
                                    <span class="legend-text">You have answered the question.</span>
                                </div>

                                <!-- Item 4: Marked for Review (Not Answered) -->
                                <div class="legend-item">
                                    <span class="legend-number">4.</span>
                                    <div class="legend-icon-wrapper">
                                        <div class="status-purple-circle"></div>
                                    </div>
                                    <span class="legend-text">You have NOT answered the question, but have marked the question for review.</span>
                                </div>

                                <!-- Item 5: Answered & Marked for Review -->
                                <div class="legend-item">
                                    <span class="legend-number">5.</span>
                                    <div class="legend-icon-wrapper">
                                        <div class="status-purple-review"></div>
                                    </div>
                                    <span class="legend-text">The question(s) "Answered and Marked for Review" will be considered for evaluation.</span>
                                </div>
                            </div>
                        </li>
                        <li>You can click on the "&gt;" arrow which appears to the left of question palette to collapse the question palette thereby maximizing the question window. To view the question palette again, you can click on "&lt;" which appears on the right side of question window.</li>
                        <li>You can click on your "Profile" image on top right corner of your screen to change the language during the exam for entire question paper. On clicking of Profile image you will get a drop-down to change the question content to the desired language.</li>
                        <li>You can click on <i class="fas fa-arrow-up"></i> to navigate to the bottom and <i class="fas fa-arrow-down"></i> to navigate to top of the question area, without scrolling.</li>
                    </ol>
                </div>

                <!-- EXAM Information-->
                <div class="row mb-4">
                    <h6 class="text-decoration-underline">Instruction for this Exam:</h6>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">📋 Exam Details</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Type: </td>
                                            <td><strong>{{ $exam->type_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Duration: </td>
                                            <td><strong>{{ $exam->formatted_duration }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Questions: </td>
                                            <td><strong>{{ $exam->question_count }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Marks: </td>
                                            <td><strong>{{ $exam->total_marks }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Passing Percentage: </td>
                                            <td><strong>{{ $exam->passing_percentage }}%</strong></td>
                                        </tr>
                                        @if($exam->negative_marking > 0)
                                        <tr>
                                            <td>Negative Marking: </td>
                                            <td><strong class="text-danger">{{ $exam->negative_marking }}</strong></td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">⏰ Important Dates</h6>
                                    @if($exam->is_scheduled)
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Start Time: </td>
                                            <td><strong>{{ $exam->start_time->format('d M Y, h:i A') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>End Time: </td>
                                            <td><strong>{{ $exam->end_time->format('d M Y, h:i A') }}</strong></td>
                                        </tr>
                                    </table>
                                    @else
                                    <p class="mb-0">This exam is always available. You can start anytime.</p>
                                    @endif
                                    <hr>
                                    <p class="mb-0">
                                        <strong>Attempts Allowed:</strong>
                                        <span class="badge bg-info">{{ $exam->attempts_allowed }}</span>
                                        @if($attempts > 0)
                                        <br><small class="text-warning">You have used {{ $attempts }} attempt(s)</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($exam->instructions)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">📝 Specific Instructions</h6>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($exam->instructions)) !!}
                        </div>
                    </div>
                    @endif
                
                <!-- Navigating to a Question -->
                <div class="instruction-section">
                    <h6 class="text-decoration-underline">Navigating to a Question:</h6>
                    <ol class="instruction-list" start="7">
                        <li>
                            To answer a question, <strong>do the following:</strong>
                            <ul class="sub-list">
                                <li>Click on the question number in the Question Palette at the right of your screen to go to that numbered question directly. Note that using this option does NOT save your answer to the current question.</li>
                                <li>Click on <strong>Save &amp; Next</strong> to save your answer for the current question and then go to the next question.</li>
                                <li>Click on <strong>Mark for Review &amp; Next</strong> to save your answer for the current question, mark it for review, and then go to the next question.</li>
                            </ul>
                        </li>
                    </ol>
                </div>

                <!-- Answering a Question -->
                <div class="instruction-section">
                    <h6 class="text-decoration-underline">Answering a Question:</h6>
                    <p><strong>Procedure for answering a multiple choice type question:</strong></p>
                    <ul class="sub-list">
                        <li>To select your answer, click on the button of one of the options.</li>
                        <li>To deselect your chosen answer, click on the button of the chosen option again or click on the <strong>Clear Response</strong> button.</li>
                        <li>To change your chosen answer, click on the button of another option.</li>
                        <li>To save your answer, you MUST click on the <strong>Save &amp; Next</strong> button.</li>
                        <li>To mark the question for review, click on the <strong>Mark for Review &amp; Next</strong> button.</li>
                        <li>To change your answer to a question that has already been answered, first select that question for answering and then follow the procedure for answering that type of question.</li>
                    </ul>
                </div>

                <!-- Navigating through sections -->
                <div class="instruction-section">
                    <h6 class="text-decoration-underline">Navigating through sections:</h6>
                    <ul class="sub-list">
                        <li>Sections in this question paper are displayed on the top bar of the screen. Questions in a section can be viewed by clicking on the section name. The section you are currently viewing is highlighted.</li>
                        <li>After clicking the <strong>Save &amp; Next</strong> button on the last question for a section, you will automatically be taken to the first question of the next section.</li>
                        <li>You can shuffle between sections and questions anytime during the examination as per your convenience only during the time stipulated.</li>
                        <li>Candidate can view the corresponding section summary as part of the legend that appears in every section above the question palette.</li>
                    </ul>
                </div>

                <div class="instruction-section">
                    <p class="text-danger small">Please note all questions will appear in default language. This language can be changed for a particular question later on.</p>
                </div>

                <!-- Agreement -->
                <div class="agreement-section">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agreeCheck">
                        <label class="form-check-label" for="agreeCheck">
                            I have read and understood the instructions. All computer hardware allotted to me are in proper working condition. I declare that I am not in possession of / not wearing / not carrying any prohibited gadget like mobile phone, bluetooth devices etc. / any prohibited material with me into the Examination Hall. I agree that in case of not adhering to the instructions, I shall be liable to be debarred from this Test and/or to disciplinary action, which may include ban from future Tests / Examinations.
                        </label>
                    </div>

                    <div class="text-center mt-4">
                        <form action="{{ route('student.exams.start', $exam) }}" method="POST" id="startExamForm">
                            @csrf
                            @if(isset($is_saved) && $is_saved)
                                <button type="submit" class="btn-start-exam" id="startBtn" disabled>
                                    <i class="fas fa-play-circle me-2"></i>Resume Exam
                                </button>
                            @else
                                <button type="submit" class="btn-start-exam" id="startBtn" disabled>
                                    <i class="fas fa-play-circle me-2"></i>Start Exam
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            &copy; All Rights Reserved - <span>Aviation Security Training Institute, NSCBI Airport Kolkata</span>
        </div>
    </div>
</div>

@push('styles')
<style>
    .instructions-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 35px 40px;
        margin-bottom: 25px;
    }

    .instructions-content h5 {
        color: #1a237e;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    .instruction-section {
        margin-bottom: 22px;
    }

    .instruction-section h6 {
        font-weight: 600;
        color: #1a237e;
        font-size: 0.95rem;
        margin-bottom: 10px;
    }

    .instruction-list {
        padding-left: 25px;
        margin-bottom: 0;
    }

    .instruction-list li {
        font-size: 0.92rem;
        color: #333;
        line-height: 1.8;
        margin-bottom: 6px;
    }

    .instruction-list li strong {
        color: #1a237e;
    }

    .sub-list {
        padding-left: 25px;
        margin-top: 5px;
    }

    .sub-list li {
        font-size: 0.9rem;
        color: #444;
        line-height: 1.7;
        margin-bottom: 4px;
        list-style-type: disc;
    }

    .sub-list li strong {
        color: #1a237e;
    }

    /* Palette Legend */
    .palette-legend-box {
        background: #ffffff;
        border: 1px solid #dde1e6;
        border-radius: 6px;
        padding: 16px 20px;
        margin: 12px 0 8px 0;
        max-width: 700px;
    }

    .palette-legend-box .legend-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 6px 0;
    }

    .palette-legend-box .legend-number {
        font-weight: 600;
        color: #555;
        min-width: 22px;
        font-size: 0.9rem;
    }

    .legend-icon-wrapper {
        width: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
    }

    .status-box {
        width: 35px;
        height: 35px;
        background-color: #f0f0f0;
        border: 2px solid #b5b5b5;
        border-radius: 6px;
    }

    .status-red {
        width: 35px;
        height: 32px;
        background-color: #e04006;
        clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
    }

    .status-green {
        width: 35px;
        height: 32px;
        background-color: #24bd04;
        clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
    }

    .status-purple-circle {
        width: 35px;
        height: 35px;
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        border-radius: 50%;
    }

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
        bottom: -2px;
        right: -2px;
        width: 16px;
        height: 16px;
        background-color: #24bd04;
        color: white;
        font-size: 11px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        border: 1.5px solid white;
    }

    .palette-legend-box .legend-text {
        flex: 1;
        font-size: 0.88rem;
        color: #555;
        line-height: 1.4;
    }

    .agreement-section {
        border-top: 2px solid #e9ecef;
        padding-top: 22px;
        margin-top: 10px;
    }

    .agreement-section .form-check {
        margin-bottom: 18px;
        padding-left: 35px;
    }

    .agreement-section .form-check-input {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        margin-left: -35px;
        cursor: pointer;
        border-radius: 3px;
    }

    .agreement-section .form-check-input:checked {
        background-color: #1a237e;
        border-color: #1a237e;
    }

    .agreement-section .form-check-label {
        font-size: 0.92rem;
        color: #333;
        line-height: 1.7;
        cursor: pointer;
    }

    .btn-start-exam {
        background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
        color: #ffffff;
        padding: 14px 55px;
        font-size: 1.1rem;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }

    .btn-start-exam:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(26, 35, 126, 0.4);
        color: #ffffff;
    }

    .btn-start-exam:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .footer-copyright {
        text-align: center;
        padding: 18px 0;
        font-size: 0.8rem;
        color: #6c757d;
        border-top: 1px solid #e9ecef;
        margin-top: 10px;
    }

    .footer-copyright span {
        color: #1a237e;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .instructions-card {
            padding: 20px;
        }

        .palette-legend-box {
            padding: 12px 15px;
        }

        .palette-legend-box .legend-item {
            gap: 8px;
            padding: 5px 0;
        }

        .legend-icon-wrapper {
            width: 40px;
        }

        .status-box,
        .status-red,
        .status-green,
        .status-purple-circle,
        .status-purple-review {
            width: 28px;
            height: 28px;
        }

        .status-red,
        .status-green {
            height: 26px;
        }

        .status-purple-review::after {
            width: 14px;
            height: 14px;
            font-size: 9px;
        }

        .palette-legend-box .legend-text {
            font-size: 0.8rem;
        }

        .btn-start-exam {
            width: 100%;
            padding: 12px 20px;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .instructions-card {
            padding: 15px;
        }

        .palette-legend-box {
            padding: 10px 12px;
        }

        .legend-icon-wrapper {
            width: 35px;
        }

        .status-box,
        .status-red,
        .status-green,
        .status-purple-circle,
        .status-purple-review {
            width: 24px;
            height: 24px;
        }

        .status-red,
        .status-green {
            height: 22px;
        }

        .status-purple-review::after {
            width: 12px;
            height: 12px;
            font-size: 8px;
        }

        .palette-legend-box .legend-text {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('agreeCheck').addEventListener('change', function() {
        document.getElementById('startBtn').disabled = !this.checked;
    });

    document.getElementById('startExamForm').addEventListener('submit', function(e) {
        const startBtn = document.getElementById('startBtn');
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Starting Exam...';
    });
</script>
@endpush
@endsection