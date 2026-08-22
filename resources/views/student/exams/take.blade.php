@extends('layouts.base_exam')
@push('meta')
    <meta name="exam-access-token" content="{{ session('exam_access_' . $studentExam->id) ? 'valid' : 'invalid' }}">
@endpush

@section('title', $studentExam->exam->title)

@section('content')
<!-- Candidate Info Row -->
<div class="candidate-info-row topbar-row-2">
    <div class="bg-image"></div>
    <div class="container p-0">
        <div class="row align-items-center">
            <div class="col-md-9 col-sm-12">
                <div class="candidate-info-table">
                    <!-- Added line-height to make text rows tighter -->
                    <table style="line-height: 1.8;">
                        <tr>
                            <!-- Added overflow:hidden to make border-radius work with the image -->
                           <td style="border: 2px solid #666; border-radius: 8px; overflow: hidden; padding: 0; line-height: 0;">
                                <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.png') }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     class="img-thumbnail" 
                                     style="width: 100px; height: 100px; object-fit: cover; border: none; margin: 0; padding: 0;">
                            </td>

                            <!-- Reduced left padding slightly for better alignment -->
                            <td style="padding-left: 15px; vertical-align: middle;">
                                <table>
                                    <!-- Changed top/bottom padding to 0px for maximum compactness -->
                                    <tr>
                                        <td style="padding: 0px 8px; font-weight: 600; color: #1a237e;">Candidate Name</td>
                                        <td style="padding: 0px 5px;">:</td>
                                        <td style="padding: 0px 5px; color: #f7931e; font-weight: 700;">{{ Auth::user()->name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0px 8px; font-weight: 600; color: #1a237e;">Exam Name</td>
                                        <td style="padding: 0px 5px;">:</td>
                                        <td style="padding: 0px 5px; color: #f7931e; font-weight: 700;">{{ $studentExam->exam->title }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0px 8px; font-weight: 600; color: #1a237e;">Subject Name</td>
                                        <td style="padding: 0px 5px;">:</td>
                                        <td style="padding: 0px 5px; color: #f7931e; font-weight: 700;">{{ $studentExam->exam->module->title ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0px 8px; font-weight: 600; color: #1a237e;">Remaining Time</td>
                                        <td style="padding: 0px 5px;">:</td>
                                        <td style="padding: 0px 5px;">
                                            <span class="timer-display" id="time-remaining" style="font-weight: 700;">{{ $remainingSeconds ?? $studentExam->formatted_time_remaining }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-md-3 col-sm-12">
                <div class="topbar-controls">
                    <!-- Progress Bar Element -->
                    <div class="progress-indicator">
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-fill" id="progress-bar-fill" style="width: 0%;"></div>
                        </div>
                        <span id="progress-percentage">0%</span>
                    </div>
                    
                    <!-- Font Size Controls -->
                    <div class="font-size-controls">
                        <button class="font-size-btn" onclick="changeFontSize('decrease')" title="Smaller Font">
                            <span class="font-size-label">A</span>
                            <span class="font-size-symbol">-</span>
                        </button>
                        <button class="font-size-btn active" onclick="changeFontSize('reset')" title="Normal Font">
                            <span class="font-size-label">A</span>
                        </button>
                        <button class="font-size-btn" onclick="changeFontSize('increase')" title="Larger Font">
                            <span class="font-size-label">A</span>
                            <span class="font-size-symbol">+</span>
                        </button>
                        <button class="font-size-btn" onclick="changeFontSize('increase-more')" title="Largest Font">
                            <span class="font-size-label">A</span>
                            <span class="font-size-symbol">++</span>
                        </button>
                        
                        <div class="btn-group">
                            <button onclick="changeImageSize('decrease')"class="btn btn-sm btn-secondary" title ="Decrease Image Size"><i class="fa fa-search-minus"></i></button>
                            <button onclick="changeImageSize('reset')"class="btn btn-sm btn-secondary" title ="Reset Image Size"><span id="imageSize">100%</span></button>
                            <button onclick="changeImageSize('increase')" class="btn btn-sm btn-secondary" title ="Increase Image Size"> <i class="fa fa-search-plus"></i></button>
                        </div>
                        
      
                    </div>
 
                    <!-- Language Selector Dropdown -->
                    <select id="drpLanguage" class="form-control drpLanguage">
                        <option value="1">English</option>
                        <option value="2">Hindi</option>
                    </select>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- Main Content -->
<div class="exam-main">
    <div class="container">
        <div class="row">
            <!-- Left Panel - Question Area -->
            <!--<div class="col-md-8 g-1">-->
            <!--    <div class="question-panel" id="question-panel">-->
            <!--        <div class="question-header">-->
            <!--            <div class="d-flex justify-content-between align-items-center">-->
            <!--                <span class="question-counter">-->
            <!--                    Question <span id="current-question-number">1</span> of <span id="total-questions">{{ count($questionOrder) }}</span>-->
            <!--                </span>-->
            <!--                <div class="question-badges">-->
            <!--                    <span class="question-badge type" id="question-type">Loading...</span>-->
            <!--                    <span class="question-badge marks" id="question-marks">0 Marks</span>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->

            <!--        <div class="question-text" id="question-text">-->
            <!--            <div class="text-center py-5">-->
            <!--                <div class="loading-spinner"></div>-->
            <!--                <p class="mt-2 text-muted">Loading question...</p>-->
            <!--            </div>-->
            <!--        </div>-->

            <!--        <div class="options-container" id="options-container"></div>-->

            <!--        <div class="action-buttons">-->
            <!--            <div class="left-actions">-->
            <!--                <button type="button" class="btn-exam btn-exam-outline" onclick="clearAnswer()">-->
            <!--                    <i class="fas fa-eraser"></i> Clear-->
            <!--                </button>-->
            <!--                <button type="button" class="btn-exam btn-exam-warning" onclick="toggleMarkForReview(this)">-->
            <!--                    <i class="fas fa-flag"></i> <span id="mark-review-text">Mark for Review</span>-->
            <!--                </button>-->
            <!--            </div>-->
            <!--            <div class="right-actions">-->
            <!--                <button type="button" class="btn-exam btn-exam-secondary" onclick="navigateToQuestion('prev', this)">-->
            <!--                    <i class="fas fa-arrow-left"></i> Previous-->
            <!--                </button>-->
            <!--                <button type="button" class="btn-exam btn-exam-primary" onclick="navigateToQuestion('next', this)">-->
            <!--                    Save & Next <i class="fas fa-arrow-right"></i>-->
            <!--                </button>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
            
            <!-- Left Panel - Question Area -->
<!-- Left Panel - Question Area -->
<!-- Left Panel - Question Area -->
<!-- Left Panel - Question Area -->
<div class="col-md-8 g-1" id="questionArea">
    <div class="question-panel-wrapper">
        <!-- Hide/Show Palette Toggle Button -->
        <button class="palette-toggle-btn" id="paletteToggleBtn" onclick="togglePalette()">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="question-panel" id="question-panel">
            <!-- Question Header -->
            <!--<div class="question-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">-->
            <!--    <h4 class="question-title" style="margin: 0; flex-grow: 1; display: flex; justify-content: space-between; align-items: center; width: 100%;">-->
            <!--        <span>Question <span id="current-question-number">1</span>:</span>-->
            <!--        <button class="btndown" onclick="scrollToBottom()" style="background-color: #007bff; color: #ffffff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 20px; display: inline-flex; align-items: center; justify-content: center;">-->
            <!--            <i class="fa-solid fa-arrow-down" style="font-weight: 900;"></i>-->
            <!--        </button>-->
            <!--    </h4>-->
            <!--</div>-->
            
            <!-- Question Header with Down Arrow -->
            <div class="question-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h4 class="question-title" style="margin: 0; flex-grow: 1; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span>Question <span id="current-question-number">1</span>:</span>
                    <button class="btndown" onclick="scrollToBottom()" style="background-color: #007bff; color: #ffffff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 20px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-arrow-down" style="font-weight: 900;"></i>
                    </button>
                </h4>
            </div>




            <!-- Question Body -->
            <div class="question-body" id="questionBody">
                <!-- Question Text/Image -->
                <div class="question-text" id="question-text">
                    <div class="text-center py-5">
                        <div class="loading-spinner"></div>
                        <p class="mt-2 text-muted">Loading question...</p>
                    </div>
                </div>

                <!-- Options Display (Read-only view of options) -->
                <div class="options-display" id="options-display"></div>

                
            </div>

            <!-- Question Footer - Up Arrow -->
            <!--<div class="question-footer"> -->
            <!--  <button class="btn pull-right" onclick="scrollToTop()" style="background-color: #007bff; color: #ffffff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 20px; display: inline-flex; align-items: center; justify-content: center;">-->
            <!--    <i class="fa-solid fa-arrow-up" style="font-weight: 900;"></i>-->
            <!--  </button> -->
            <!--</div>-->

            <!-- Question Footer with Up Arrow -->
            <div class="question-footer"> 
                <button class="btn pull-right" onclick="scrollToTop()" style="background-color: #007bff; color: #ffffff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 20px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-arrow-up" style="font-weight: 900;"></i>
                </button> 
            </div>


            <!-- Action Buttons - Save, Clear, Mark for Review -->
            <div class="action-buttons-row" id="action-buttons-row">
                <button class="btn-exam btn-exam-success btn-save-answer" onclick="saveAndNext(this)">
                    <i class="fas fa-save"></i> SAVE &amp; NEXT
                </button>
                
                <!-- Option Selectors - A, B, C, D with Radio/Checkbox -->
                <div class="option-selectors-wrapper">
                    <table class="table table-borderless mb0 option-selectors-table">
                        <tbody>
                            <tr id="option-selectors-row">
                                <!-- Dynamically populated -->
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button class="btn-exam btn-exam-outline btn-reset-answer" onclick="clearAnswer()">
                    <i class="fas fa-eraser"></i> ERASE
                </button>
                <button class="btn-exam btn-exam-warning btn-save-mark-answer" onclick="saveAndMarkForReview(this)">
                    <i class="fas fa-flag"></i> SAVE &amp; MARK FOR REVIEW
                </button>
                <!--<button class="btn-exam btn-exam-primary btn-mark-answer" onclick="markForReviewAndNext(this)">-->
                <!--    <i class="fas fa-flag"></i> MARK FOR REVIEW &amp; NEXT-->
                <!--</button>-->
            </div>

            <!-- Panel Footer - Navigation and Submit -->
            <div class="panel-footer">
                <div class="row align-items-center">
                    <!-- Left Side: Navigation Buttons -->
                    <div class="col-md-8 col-8 text-start d-flex gap-2">
                        <button class="btn-exam btn-exam-secondary" id="btnPrevQue" onclick="navigateToQuestion('prev', this)">
                            <i class="fas fa-arrow-left"></i> PREVIOUS
                        </button>
                        <button class="btn-exam btn-exam-secondary" id="btnNextQue" onclick="navigateToQuestion('next', this)">
                            NEXT <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    
                    <!-- Right Side: Submit Button -->
                    <div class="col-md-4 col-4 text-end">
                        <button class="btn-exam btn-exam-danger btn-submit-all-answers" onclick="openSubmitModal()">
                            <i class="fas fa-check-circle"></i> SUBMIT
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Right Panel - Palette -->
            <div class="col-md-4 g-1" id="paletteArea">
                <div class="palette-card">
                    <div class="palette-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-th me-2"></i>Question Palette</h6>
                    </div>

                    

                    <!-- Question Grid -->
                    <div class="question-grid" id="question-grid">
                        @foreach($questionOrder as $index => $questionId)
                        @php
                        $answer = $answers[$questionId] ?? null;
                        $status = 'not-visited';
                        if ($answer) {
                            if ($answer->is_marked_for_review && ($answer->selected_options || $answer->answer_text)) {
                                $status = 'answered-marked';
                            } elseif ($answer->is_marked_for_review) {
                                $status = 'marked';
                            } elseif ($answer->selected_options || $answer->answer_text) {
                                $status = 'answered';
                            } else {
                                $status = 'not-answered';
                            }
                        }
                        @endphp
                        <button class="grid-btn {{ $status }} question-btn" 
                                data-question-id="{{ $questionId }}"
                                data-index="{{ $index }}" 
                                onclick="loadQuestion({{ $questionId }})">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </button>
                        @endforeach
                    </div>
                    
                    <!-- Stats with Polygon Shapes - 5 Boxes -->
                    <div class="stats-grid">
                        <!-- Not Visited -->
                        <div class="stat-badge not-visited">
                            <div class="stat-box">
                                <div class="stat-shape status-box-small"></div>
                                <div class="stat-number" id="stat-not-visited-count">{{ count($questionOrder) }}</div>
                            </div>
                            <span class="stat-label">Not Visited</span>
                        </div>

                        <!-- Not Answered (Red Polygon) -->
                        <div class="stat-badge not-answered">
                            <div class="stat-box">
                                <div class="stat-shape status-red-small"></div>
                                <div class="stat-number" id="stat-not-answered-count">0</div>
                            </div>
                            <span class="stat-label">Not Answered</span>
                        </div>

                        <!-- Answered (Green Polygon) -->
                        <div class="stat-badge answered">
                            <div class="stat-box">
                                <div class="stat-shape status-green-small"></div>
                                <div class="stat-number" id="stat-answered-count">0</div>
                            </div>
                            <span class="stat-label">Answered</span>
                        </div>

                        <!-- Marked for Review (Purple Circle) -->
                        <div class="stat-badge marked">
                            <div class="stat-box">
                                <div class="stat-shape status-purple-circle-small"></div>
                                <div class="stat-number" id="stat-marked-count">0</div>
                            </div>
                            <span class="stat-label">Marked for Review</span>
                        </div>

                        <!-- Answered & Marked for Review (Purple Circle + Green Tick) -->
                        <div class="stat-badge answered-marked" style="grid-column: span 2;">
                            <div class="stat-box">
                                <div class="stat-shape status-purple-review-small"></div>
                                <div class="stat-number" id="stat-answered-marked-count">0</div>
                            </div>
                            <span class="stat-label">Answered & Marked for Review</span>
                            <span class="stat-sub-label">(will be considered for evaluation)</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="palette-actions grid-layout"> 
                      <button class="palette-action-btn" onclick="showQuestionPaper()"> <i class="fas fa-file-alt"></i> Question Paper </button> 
                      <button class="palette-action-btn" onclick="showInstructions()"> <i class="fas fa-info-circle"></i> Instructions </button> 
                      <button class="palette-action-btn primary" onclick="saveAndExit()"> <i class="fas fa-save"></i> Save & Exit </button> 
                      <button class="palette-action-btn danger" onclick="openSubmitModal()"> <i class="fas fa-check-circle"></i> Submit </button> 
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Question Paper Modal -->
<div class="modal fade" id="questionPaperModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Question Paper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="modal-question-list" id="questionPaperList">
                    <div class="text-center py-4">
                        <div class="loading-spinner"></div>
                        <p class="mt-2">Loading questions...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Instructions Modal -->
<div class="modal fade" id="instructionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Exam Instructions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                $exam = $studentExam->exam;
                @endphp
            <div class="row justify-content-center">
    <div class="col-12">
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
            </div>
        </div>
    </div>
</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Save & Exit Modal -->
<div class="modal fade" id="saveExitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-save me-2"></i>Save & Exit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Your progress has been saved. Are you sure you want to exit?</p>
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex justify-content-between align-items-center bg-success text-white p-2 rounded">
                        <span>Total Answered:</span>
                        <span class="fw-bold" id="exit-answered">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-warning text-dark p-2 rounded">
                        <span>Answered & Mark for Review:</span>
                        <span class="fw-bold" id="exit-answered-review">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-warning text-dark p-2 rounded">
                        <span>Marked for Review but not Answered:</span>
                        <span class="fw-bold" id="exit-marked">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-info text-white p-2 rounded">
                        <span>Not Visited:</span>
                        <span class="fw-bold" id="exit-not-visited">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-danger text-white p-2 rounded">
                        <span>Remaining:</span>
                        <span class="fw-bold" id="exit-remaining">0</span>
                    </div>
                </div>
                <p class="text-muted small mb-0">You can resume this exam later from your dashboard.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmSaveAndExit()">Save & Exit</button>
            </div>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Submit Exam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to submit the exam?</p>
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex justify-content-between align-items-center bg-success text-white p-2 rounded">
                        <span>Total Answered Questions:</span>
                        <span class="fw-bold" id="submit-answered">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-warning text-dark p-2 rounded">
                        <span>Answered & Mark for Review:</span>
                        <span class="fw-bold" id="submit-answered-review">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-warning text-dark p-2 rounded">
                        <span>Marked for Review but not Answered:</span>
                        <span class="fw-bold" id="submit-marked">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-info text-white p-2 rounded">
                        <span>Not visited:</span>
                        <span class="fw-bold" id="submit-not-visited">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-danger text-white p-2 rounded">
                        <span>Unanswered:</span>
                        <span class="fw-bold" id="submit-unanswered">0</span>
                    </div>
                </div>
                <p class="text-muted small mb-0">Once submitted, you cannot change your answers.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitExam()">Submit Exam</button>
            </div>
        </div>
    </div>
</div>

@php
    $startQuestionId = $studentExam->current_question_id ?? $questionOrder[0];
@endphp

@push('styles')
<style>
/* Hide Right Profile icon*/

.topbar-right{
    display: none !important;
}
    /* ================================================
   FONT SIZE CONTROLS
   ================================================ */
.font-size-controls {
    display: flex;
    align-items: center;
    gap: 2px;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.font-size-btn {
    display: flex;
    align-items: center;
    gap: 1px;
    padding: 3px 8px;
    border: none;
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #555;
}

.font-size-btn:hover {
    background: #e9ecef;
    color: #1a237e;
}

.font-size-btn.active {
    background: #1a237e;
    color: #ffffff;
}

.font-size-btn .font-size-label {
    font-weight: 700;
    font-size: 0.9rem;
}

.font-size-btn .font-size-symbol {
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 1px;
}

/* Font size levels */
.font-size-btn[data-size="1"] .font-size-label { font-size: 0.75rem; }
.font-size-btn[data-size="2"] .font-size-label { font-size: 0.9rem; }
.font-size-btn[data-size="3"] .font-size-label { font-size: 1.05rem; }
.font-size-btn[data-size="4"] .font-size-label { font-size: 1.2rem; }

/* Responsive */
@media (max-width: 768px) {
    .font-size-controls {
        padding: 2px 4px;
    }
    
    .font-size-btn {
        padding: 2px 6px;
    }
    
    .font-size-btn .font-size-label {
        font-size: 0.75rem;
    }
}

/* Font Size Classes */
.font-size-small .question-text,
.font-size-small .question-content,
.font-size-small .option-display-item,
.font-size-small .option-display-text,
.font-size-small .option-display-content,
.font-size-small .option-text-content,
.font-size-small .question-number {
    font-size: 0.85rem !important;
}

.font-size-small .option-selector {
    font-size: 0.8rem !important;
}

.font-size-normal .question-text,
.font-size-normal .question-content,
.font-size-normal .option-display-item,
.font-size-normal .option-display-text,
.font-size-normal .option-display-content,
.font-size-normal .option-text-content,
.font-size-normal .question-number {
    font-size: 1rem !important;
}

.font-size-normal .option-selector {
    font-size: 0.9rem !important;
}

.font-size-large .question-text,
.font-size-large .question-content,
.font-size-large .option-display-item,
.font-size-large .option-display-text,
.font-size-large .option-display-content,
.font-size-large .option-text-content,
.font-size-large .question-number {
    font-size: 1.2rem !important;
}

.font-size-large .option-selector {
    font-size: 1.05rem !important;
}

.font-size-xlarge .question-text,
.font-size-xlarge .question-content,
.font-size-xlarge .option-display-item,
.font-size-xlarge .option-display-text,
.font-size-xlarge .option-display-content,
.font-size-xlarge .option-text-content,
.font-size-xlarge .question-number {
    font-size: 1.4rem !important;
}

.font-size-xlarge .option-selector {
    font-size: 1.2rem !important;
}

/***
** Instructions
*/

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









    /* ================================================
       CANDIDATE INFO ROW
       ================================================ */
    .candidate-info-row {
        background: #ffffff;
        /*padding: 12px 0;*/
        border-bottom: 2px solid #e9ecef;
        /*margin-top: 5px;*/
    }

    .candidate-info-table table {
        border-collapse: collapse;
    }

    .candidate-info-table td {
        /*padding: 2px 5px;*/
        font-size: 0.9rem;
    }
    
    .timer-display {
            background: #0098DA none repeat scroll 0 0 !Important;
            border-radius: 20px !important;
            color: #fff !important;
            padding: 0px 10px !important;
        }
    
    .topbar-controls {
        display: flex;
        flex-direction: column; /* Stacks elements vertically */
        align-items: flex-end;  /* Aligns elements to the right side */
        gap: 8px;              /* Shrunk gap for a tighter vertical layout */
        width: 100%;
    }
    
    .topbar-controls .drpLanguage {
        padding: 5px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #fff;
        color: #333;
        cursor: pointer;
        max-width: 150px;           /* Prevents bootstrap form-control from forcing 100% width */
    }
    
    /* Progress Bar Styles */
    .progress-indicator {
        display: flex;
        flex-direction: column;
        align-items: flex-end; /* Align internal text percentage to the right */
        gap: 3px;
        width: 100%;           /* Allows the bar wrapper to span full width */
        max-width: 150px;      /* Set a max boundary so the bar isn't excessively stretched */
    }
    
    .progress-bar-wrapper {
        width: 100%;
        height: 10px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #1a237e);
        border-radius: 4px;
        transition: width 0.5s ease;
    }
    
    .progress-indicator span {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1a237e;
    }



    /* ================================================
       QUESTION PANEL
       ================================================ */
    .question-panel {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
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
        font-size: 0.95rem;
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
        width: 28px;
        height: 28px;
        min-width: 28px;
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

    /* ================================================
       ACTION BUTTONS
       ================================================ */
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

    .action-buttons .left-actions,
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

    /* ================================================
       PALETTE CARD - WITH COMPLETE NTA STATS
       ================================================ */
    .palette-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        position: sticky;
        top: 10px;
        overflow: hidden;
    }

    .palette-header {
        padding: 10px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        font-size: 0.9rem;
        color: #1a237e;
    }

    /* ===== LEGEND ===== */
    .palette-legend {
        padding: 10px 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .palette-legend .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 2px 0;
        font-size: 0.75rem;
        color: #555;
    }

    .palette-legend .legend-icon {
        width: 22px;
        height: 22px;
        min-width: 22px;
        flex-shrink: 0;
    }

    /* Legend Shapes */
    .status-box {
        background-color: #f0f0f0;
        border: 2px solid #b5b5b5;
        border-radius: 4px;
    }

    .status-red {
        background-color: #e04006;
        clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
    }

    .status-green {
        background-color: #24bd04;
        clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
    }

    .status-purple-circle {
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        border-radius: 50%;
    }

    .status-purple-review {
        position: relative;
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        border-radius: 50%;
    }

    .status-purple-review::after {
        content: "✓";
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 11px;
        height: 11px;
        background-color: #24bd04;
        color: white;
        font-size: 7px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
        border: 1px solid white;
    }

    .palette-legend .legend-label {
        font-size: 0.75rem;
        color: #555;
    }

    /* ===== STATS GRID - 5 BOXES WITH POLYGON SHAPES ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
    }

    .stat-badge {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 6px 4px;
        border-radius: 6px;
        border: 1.5px dashed #b5b5b5;
        background: #fafafa;
        min-height: 65px;
        justify-content: center;
    }

    .stat-badge .stat-box {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat-badge .stat-shape {
        width: 24px;
        height: 24px;
        min-width: 24px;
        flex-shrink: 0;
    }

    /* Small versions of polygon shapes for stats */
    .status-box-small {
        background-color: #f0f0f0;
        border: 1.5px solid #b5b5b5;
        border-radius: 3px;
    }

    .status-red-small {
        background-color: #e04006;
        clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
    }

    .status-green-small {
        background-color: #24bd04;
        clip-path: polygon(0% 0%, 0% 0%, 100% 20%, 100% 80%, 0% 100%);
    }

    .status-purple-circle-small {
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        border-radius: 50%;
    }

    .status-purple-review-small {
        position: relative;
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        border-radius: 50%;
    }

    .status-purple-review-small::after {
        content: "✓";
        position: absolute;
        bottom: -1px;
        right: -1px;
        width: 9px;
        height: 9px;
        background-color: #24bd04;
        color: white;
        font-size: 6px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
        border: 1px solid white;
    }

    .stat-badge .stat-number {
        font-size: 1rem;
        font-weight: 700;
        color: #1a237e;
        min-width: 20px;
        text-align: center;
    }

    .stat-badge .stat-label {
        font-size: 0.55rem;
        color: #555;
        text-align: center;
        margin-top: 2px;
        line-height: 1.2;
    }

    .stat-badge .stat-sub-label {
        font-size: 0.5rem;
        color: #999;
        text-align: center;
        line-height: 1.2;
        margin-top: 1px;
    }

    .stat-badge.not-visited {
        background: #f5f5f5;
        border-color: #ccc;
    }

    .stat-badge.not-answered {
        background: #fde8e0;
        border-color: #e04006;
    }

    .stat-badge.answered {
        background: #e8f5e9;
        border-color: #24bd04;
    }

    .stat-badge.marked {
        background: #ede7f6;
        border-color: #632cdb;
    }

    .stat-badge.answered-marked {
        background: #fce4ec;
        border-color: #dc3545;
        grid-column: span 3;
    }

    /* ===== QUESTION GRID ===== */
    .question-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        padding: 10px 12px;
        max-height: 250px;
        overflow-y: auto;
    }

    .grid-btn {
        aspect-ratio: 1;
        border: none;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        color: #333;
        font-family: monospace;
    }

    .grid-btn:hover {
        transform: scale(1.05);
        z-index: 1;
    }

    /* Not Visited - White box with border */
    .grid-btn.not-visited {
        background: #f0f0f0;
        color: #666;
        border: 1.5px solid #b5b5b5;
        border-radius: 4px;
    }

    /* Not Answered - Red Polygon */
    .grid-btn.not-answered {
        background: #e04006;
        color: #ffffff;
        border: none;
        clip-path: polygon(0% 0%, 0% 0%, 100% 15%, 100% 85%, 0% 100%);
        border-radius: 0;
    }

    /* Answered - Green Polygon */
    .grid-btn.answered {
        background: #24bd04;
        color: #ffffff;
        border: none;
        clip-path: polygon(0% 0%, 0% 0%, 100% 15%, 100% 85%, 0% 100%);
        border-radius: 0;
    }

    /* Marked for Review - Purple Circle */
    .grid-btn.marked {
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        color: #ffffff;
        border: none;
        border-radius: 50%;
    }

    /* Answered & Marked for Review - Purple Circle with Green Tick */
    .grid-btn.answered-marked {
        background: radial-gradient(circle at 30% 30%, #632cdb, #310794);
        color: #ffffff;
        border: none;
        border-radius: 50%;
    }

    .grid-btn.answered-marked::after {
        content: "✓";
        position: absolute;
        bottom: 0px;
        right: 0px;
        width: 13px;
        height: 13px;
        background-color: #24bd04;
        color: white;
        font-size: 8px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
        border: 1.5px solid white;
    }

    .grid-btn.current {
        outline: 3px solid #1a237e;
        outline-offset: 2px;
        z-index: 2;
    }

    /* ===== PALETTE ACTIONS ===== */
    .palette-actions {
        margin-top: 25px;
        padding: 10px 12px;
        border-top: 1px solid #e9ecef;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .palette-actions {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      /*gap: 10px; */
    }

    .palette-action-btn {
        width: 100%;
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        background: #ffffff;
        font-size: 0.7rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #555;
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
    
    
 


    /* ================================================
       RESPONSIVE
       ================================================ */
    @media (max-width: 992px) {
        .col-lg-3 {
            margin-top: 15px;
        }

        .palette-card {
            position: static;
        }

        .question-grid {
            grid-template-columns: repeat(7, 1fr);
        }
    }

    @media (max-width: 768px) {
        .candidate-info-row .row {
            flex-direction: column;
        }

        .candidate-info-table table {
            width: 100%;
        }

        .candidate-info-table td {
            font-size: 0.8rem;
            padding: 1px 4px;
        }

        .topbar-controls {
            justify-content: center;
            margin-top: 10px;
        }

        .question-grid {
            grid-template-columns: repeat(7, 1fr);
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons .left-actions,
        .action-buttons .right-actions {
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .stat-badge.answered-marked {
            grid-column: span 2;
        }
    }

    @media (max-width: 480px) {
        .candidate-info-table td {
            font-size: 0.7rem;
        }

        .candidate-info-table img {
            width: 60px;
            height: 60px;
        }

        .question-grid {
            grid-template-columns: repeat(7, 1fr);
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .stat-badge.answered-marked {
            grid-column: span 2;
        }

        .stat-badge .stat-shape {
            width: 18px;
            height: 18px;
            min-width: 18px;
        }

        .stat-badge .stat-number {
            font-size: 0.8rem;
        }

        .stat-badge .stat-label {
            font-size: 0.5rem;
        }

        .status-purple-review-small::after {
            width: 7px;
            height: 7px;
            font-size: 5px;
        }

        .grid-btn.answered-marked::after {
            width: 10px;
            height: 10px;
            font-size: 6px;
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
    
  /* ================================================
   QUESTION PANEL - NTA STYLE
   ================================================ */
.question-panel-wrapper {
    position: relative;
}

/* ================================================
   PALETTE TOGGLE BUTTON
   ================================================ */
.question-panel-wrapper {
    position: relative;
    width: 100%;
}

.palette-toggle-btn {
    position: absolute;
    right: -18px;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 60px;
    background: #1a1a1a;
    color: #ffffff;
    border: none;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 2px 0 8px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.palette-toggle-btn:hover {
    background: #333;
    transform: translateY(-50%) scale(1.05);
}

.palette-toggle-btn .fa-chevron-right,
.palette-toggle-btn .fa-chevron-left {
    transition: transform 0.3s ease;
}

.palette-toggle-btn.collapsed {
    right: -18px;
    background: #1a237e;
}

.palette-toggle-btn.collapsed:hover {
    background: #0d1445;
}

/* Palette hidden state */
#paletteArea.hidden {
    display: none !important;
}

#questionArea {
    transition: all 0.3s ease;
}

/* Responsive - Hide toggle button on mobile */
@media (max-width: 992px) {
    .palette-toggle-btn {
        display: none !important;
    }
    
    #questionArea.col-lg-9,
    #questionArea.col-lg-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}


/* Question Panel */
.question-panel {
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
}

/* Question Header */
.question-header {
    padding: 12px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.question-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a237e;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.question-title .btndown {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
    width: 22px;
    height: 22px;
}

.question-title .btndown:hover {
    opacity: 1;
}

/* Question Body */


/* Question Body - Scrollable */
.question-body {
    max-height: 400px;
    overflow-y: auto; /* Changed from scroll to auto for better UX */
    padding: 15px 20px 5px;
    scroll-behavior: smooth; /* Smooth scrolling */
}

/* Custom scrollbar styling (optional) */
.question-body::-webkit-scrollbar {
    width: 6px;
}

.question-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.question-body::-webkit-scrollbar-thumb {
    background: #1a237e;
    border-radius: 4px;
}

.question-body::-webkit-scrollbar-thumb:hover {
    background: #0d1445;
}

/* For Firefox */
.question-body {
    scrollbar-width: thin;
    scrollbar-color: #1a237e #f1f1f1;
}

/* Question Text */
.question-text .img-responsive {
    max-width: 100%;
    height: auto;
    margin: 5px 0 10px;
}

.question-text .question-content {
    font-size: 1rem;
    line-height: 1.7;
    color: #333;
}

/* Options Display - Read-only view */
.options-display-grid {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin: 8px 0 10px;
}

.option-display-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 6px 10px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #fafafa;
}

.option-display-label {
    font-weight: 600;
    color: #1a237e;
    min-width: 25px;
    font-size: 0.9rem;
}

.option-display-content {
    flex: 1;
    font-size: 0.9rem;
    color: #333;
}

.option-display-image {
    max-width: 100%;
    max-height: 120px;
    object-fit: contain;
}

/* Option Selectors - A, B, C, D */
.option-selectors-wrapper {
    padding: 8px 0 5px;
    border-top: 1px solid #e9ecef;
    margin-top: 5px;
}

.option-selectors-table {
    margin: 0;
    width: auto;
}

.option-selectors-table td {
    padding: 5px 20px 5px 0;
    border: none !important;
}

.option-selector {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    padding: 4px 12px;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 0.9rem;
    color: #555;
}

.option-selector:hover {
    background: #f5f5f5;
}

.option-selector input[type="radio"],
.option-selector input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #1a237e;
    margin: 0;
}

.option-selector .selector-label {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.option-selector.selected {
    background: #e8f5e9;
    border-radius: 4px;
}

/* Question Footer - Up Arrow */
.question-footer {
    padding: 5px 20px 8px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
}

.question-footer .btnup {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
    width: 22px;
    height: 22px;
}

.question-footer .btnup:hover {
    opacity: 1;
}

/* Action Buttons Row */
.action-buttons-row {
    padding: 10px 15px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.btn-exam {
    padding: 6px 16px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.btn-exam-success {
    background: #28a745;
    color: #ffffff;
}
.btn-exam-success:hover {
    background: #1e7e34;
    color: #ffffff;
}

.btn-exam-primary {
    background: #1a237e;
    color: #ffffff;
}
.btn-exam-primary:hover {
    background: #0d1445;
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

/* Panel Footer - Back, Submit, Next */
.panel-footer {
    padding: 10px 15px;
    background: #ffffff;
    border-top: 2px solid #e9ecef;
}

.panel-footer .row {
    display: flex;
    align-items: center;
}

.panel-footer .col-4 {
    display: flex;
    align-items: center;
}

.panel-footer .text-start {
    justify-content: flex-start;
}

.panel-footer .text-center {
    justify-content: center;
}

.panel-footer .text-end {
    justify-content: flex-end;
}

.panel-footer .btn-exam {
    padding: 6px 20px;
    font-size: 0.75rem;
    min-width: 80px;
}

/* Responsive */
@media (max-width: 768px) {
    .question-body {
        padding: 10px 12px 5px;
    }
    
    .action-buttons-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-exam {
        justify-content: center;
        padding: 8px 12px;
        font-size: 0.7rem;
    }
    
    .option-selectors-table td {
        padding: 5px 10px 5px 0;
    }
    
    .panel-footer .col-4 {
        flex: 0 0 33.33%;
        max-width: 33.33%;
    }
    
    .panel-footer .btn-exam {
        padding: 5px 10px;
        font-size: 0.65rem;
        min-width: 60px;
    }
}

@media (max-width: 480px) {
    .option-selectors-table td {
        padding: 5px 6px 5px 0;
    }
    
    .option-selector {
        padding: 3px 6px;
        font-size: 0.8rem;
    }
    
    .option-selector .selector-label {
        font-size: 0.8rem;
    }
    
    .panel-footer .btn-exam {
        padding: 4px 8px;
        font-size: 0.6rem;
        min-width: 50px;
    }
}
  
  
  
  /* Option Selector - Number Labels */
.option-selector .selector-label {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
    min-width: 20px;
    text-align: center;
}

/* Option Display - Number Labels */
.option-display-label {
    font-weight: 600;
    color: #1a237e;
    min-width: 25px;
    font-size: 0.9rem;
}

/* Selected state */
.option-selector.selected {
    background: #e8f5e9;
    border-color: #28a745;
    border-radius: 4px;
}

.option-selector.selected .selector-label {
    color: #1a237e;
}


/* ================================================
   IMAGE SUPPORT STYLES
   ================================================ */
.option-selector .selector-content {
    display: block;
    font-size: 0.85rem;
    margin-top: 2px;
    line-height: 1.4;
}

.option-selector .selector-content img {
    display: block;
    margin: 0 auto 3px;
    max-height: 60px;
    border: 1px solid #ddd; 
    padding: 3px;
    border-radius: 4px;
}

.option-display-content img {
    max-height: 100px;
    margin-bottom: 5px;
    border: 1px solid #ddd;
    padding: 5px;
    border-radius: 5px;
}

.question-text img {
    /*max-height: 300px;*/
    margin-bottom: 15px;
    border: 1px solid #ddd;
    padding: 5px;
    border-radius: 5px;
}

/* Option selector with image - selected state */
.option-selector.selected .selector-content img {
    border-color: #28a745;
}




/*Image Size*/
.question-text img {
    transform-origin: center;
    transition: transform 0.25s ease;
}

.image-size-small img {
    transform: scale(0.75);
}

.image-size-normal img {
    transform: scale(1);
}

.image-size-large img {
    transform: scale(1.25);
}

.image-size-xlarge img {
    transform: scale(1.5);
}

.question_image{
        min-height: 400px; width:auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;
    }
    
    .option_image{
        min-height: 200px; width:auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;
    }
    
    
.question-image-container{
    width:100%;
    overflow-x:auto;
    overflow-y:visible;
    text-align:center;
    padding:10px;
    margin-bottom:20px;
    border:1px solid #ddd;
    border-radius:8px;
    background:#fff;
}

.question-image-container::-webkit-scrollbar{
    height:8px;
}

.question-image-container::-webkit-scrollbar-thumb{
    background:#999;
    border-radius:10px;
}

.question_image{

    display:inline-block;
    width:800px;
    max-width:none;
    height:auto;
    transform-origin:center center;
    cursor:zoom-in;
    transition:width .2s ease;
}
</style>
@endpush

@push('scripts')
<script>
  

</script>
@endpush

@include('student.exams.take_script')


@endsection