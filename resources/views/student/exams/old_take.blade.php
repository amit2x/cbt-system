@extends('layouts.exam')

@section('title', $studentExam->exam->title)

@section('content')
{{--
<!-- Topbar -->
<div class="exam-topbar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="app-name">{{ config('app.name', 'CBT System') }}</div>
                <div class="exam-name">{{ Str::limit($studentExam->exam->title, 40) }}</div>
                <div class="instructor-name">Instructor: {{ $studentExam->exam->batch->instructor->name ?? 'N/A' }}
                </div>
            </div>
            <div class="col-md-8">
                <div class="topbar-right">
                    <div class="progress-stats">
                        <div class="progress-label">Progress</div>
                        <div class="progress-value" id="progress-percentage">0%</div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" id="progress-bar-fill" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="timer-box">
                        <div class="timer-label">Time Remaining</div>
                        <div class="timer-display" id="time-remaining">{{ $studentExam->formatted_time_remaining }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<!-- Topbar -->
<div class="exam-topbar py-2 bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <!-- Combined row with mobile adjustments -->
        <div class="row align-items-center justify-content-between g-2">
            
            <!-- Left Side: App Name (Hidden or minified on very small screens if needed) -->
            <div class="col-6 col-md-3 order-1">
                <div class="app-name font-weight-bold text-truncate text-md-left">
                    {{ config('app.name', 'CBT System') }}
                </div>
            </div>

            <!-- Right Side: Progress and Time (Moved up on mobile next to App Name) -->
            <div class="col-6 col-md-4 order-2 order-md-3">
                <div class="topbar-right d-flex flex-column align-items-end justify-content-center">
                    <!-- Progress Bar Row -->
                    <div class="progress-stats w-100 d-flex align-items-center justify-content-end mb-1 me-0">
                        <div class="progress-label small mr-2">Prog: <span id="progress-percentage">0%</span></div>
                        <div class="progress-bar-container" style="width: 100px; max-width: 150px; height: 10px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                            <div class="progress-bar-fill" id="progress-bar-fill" style="width: 0%; height: 100%; background: #007bff;"></div>
                        </div>
                    </div>
                    <!-- Timer Row -->
                    <div class="timer-box d-flex align-items-center justify-content-end">
                        <span class="timer-display font-weight-bold text-danger">
                            Time: 
                        </span>
                        <div class="timer-display font-weight-bold text-danger" id="time-remaining">
                            {{ $studentExam->formatted_time_remaining }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center Side: Exam & Instructor Name (Spans full width on mobile, centered below) -->
            <div class="col-12 col-md-5 text-center order-3 order-md-2 my-2 my-md-0 border-sm-top pt-2 pt-md-0 border-md-0">
                <div class="exam-name font-weight-bold text-truncate px-2">
                    {{ Str::limit($studentExam->exam->title, 30) }}
                </div>
                <div class="instructor-name small text-muted text-truncate">
                    Inst: {{ $studentExam->exam->batch->instructor->name ?? 'N/A' }}
                </div>
            </div>

        </div>
    </div>
</div>


<!-- Main Content -->
<div class="exam-main">
    <div class="container-fluid p-0 m-0">
        <div class="row">
            <!-- Left Panel - Student Info (Desktop) -->
            <div class="col-lg-2 desktop-sidebar g-1">
                <div class="student-info-card">
                    <div class="student-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="student-name">{{ Auth::user()->name }}</div>
                    <div class="student-roll">Roll No: {{ Auth::user()->roll_no ?? Auth::user()->id }}</div>

                    <div class="info-divider mt-3 mb-2" style="border-top: 1px solid #e9ecef;"></div>

                    <div class="info-item">
                        <span class="info-label">Course:</span>
                        <span class="info-value">{{ $studentExam->exam->batch->course->title ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Batch:</span>
                        <span class="info-value">{{ $studentExam->exam->batch->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Questions:</span>
                        <span class="info-value">{{ count($questionOrder) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Marks:</span>
                        <span class="info-value">{{ $studentExam->exam->total_marks }}</span>
                    </div>
                </div>
            </div>

            <!-- Center Panel - Question Area -->
            <div class="col-lg-7 g-1">
                <div class="question-panel" id="question-panel">
                    <div class="question-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="question-counter">
                                Question <span id="current-question-number">1</span> of <span id="total-questions">{{
                                    count($questionOrder) }}</span>
                            </span>
                            <div>
                                <span class="question-badge me-2" id="question-type"></span>
                                <span class="question-badge" id="question-marks"></span>
                            </div>
                        </div>
                    </div>
                    <div class="question-text" id="question-text">
                        <div class="text-center py-5">
                            <div class="loading-spinner"></div>
                            <p class="mt-2">Loading question...</p>
                        </div>
                    </div>
                    <div class="options-container" id="options-container"></div>
                    <div class="action-buttons">
                        <button type="button" class="btn-exam btn-exam-outline" onclick="clearAnswer()">
                            <i class="fas fa-eraser me-1"></i> Clear
                        </button>
                        <div>
                            <button type="button" class="btn-exam btn-exam-secondary me-1"
                                onclick="navigateToQuestion('prev', this)">
                                <i class="fas fa-arrow-left me-1"></i> Previous
                            </button>
                            <button type="button" class="btn-exam btn-exam-warning me-1"
                                onclick="toggleMarkForReview(this)">
                                <i class="fas fa-flag me-1"></i> <span id="mark-review-text">Mark for Review</span>
                            </button>
                            <button type="button" class="btn-exam btn-exam-primary"
                                onclick="navigateToQuestion('next', this)">
                                Save & Next <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Palette (Desktop) -->
            <div class="col-lg-3 desktop-sidebar g-1">
                <div class="palette-card">
                    <div class="palette-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-th me-2"></i>Question Palette</h6>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-badge not-visited" id="stat-not-visited">Not Visited: <span
                                id="stat-not-visited-count">{{ count($questionOrder) }}</span></div>
                        <div class="stat-badge visited" id="stat-visited">Visited: <span
                                id="stat-visited-count">0</span></div>
                        <div class="stat-badge marked" id="stat-marked">Marked: <span id="stat-marked-count">0</span>
                        </div>
                        <div class="stat-badge answered" id="stat-answered">Answered: <span
                                id="stat-answered-count">0</span></div>
                        <div class="stat-badge answered-marked" id="stat-answered-marked">Ans & Marked: <span
                                id="stat-answered-marked-count">0</span></div>
                    </div>
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
                        $status = 'visited';
                        }
                        }
                        @endphp
                        <button class="grid-btn {{ $status }} question-btn" data-question-id="{{ $questionId }}"
                            data-index="{{ $index }}" onclick="loadQuestion({{ $questionId }})">
                            {{ $index + 1 }}
                        </button>
                        @endforeach
                    </div>
                    <div class="palette-actions">
                        <button class="palette-action-btn btn-exam-outline" onclick="showQuestionPaper()">
                            <i class="fas fa-file-alt me-2"></i>Question Paper
                        </button>
                        <button class="palette-action-btn btn-exam-outline" onclick="showInstructions()">
                            <i class="fas fa-info-circle me-2"></i>Instruction View
                        </button>
                        <button class="palette-action-btn btn-exam-warning" onclick="saveAndExit()">
                            <i class="fas fa-save me-2"></i>Save & Exit
                        </button>
                        <button class="palette-action-btn btn-exam-danger" onclick="openSubmitModal()">
                            <i class="fas fa-check-circle me-2"></i>Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Exam Instructions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="instructions-content" id="instructionsContent">
                    <h6>General Instructions:</h6>
                    <ul>
                        <li>The timer will start as soon as you click "Start Exam"</li>
                        <li>You can navigate between questions using the question palette</li>
                        <li>Answers are auto-saved periodically</li>
                        <li>You can mark questions for review</li>
                        <li>Do not refresh the page during the exam</li>
                        <li>The exam will auto-submit when time expires</li>
                        <li>Do not switch tabs/windows during the exam</li>
                        <li>Ensure stable internet connection</li>
                    </ul>
                    @if($studentExam->exam->instructions)
                    <hr>
                    <h6>Specific Instructions:</h6>
                    <div>{!! nl2br(e($studentExam->exam->instructions)) !!}</div>
                    @endif
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
                    <!-- Answered: Success (Green) -->
                    <div class="d-flex justify-content-between align-items-center bg-success text-white p-2 rounded">
                        <span>Total Answered:</span>
                        <span class="fw-bold" id="exit-answered">0</span>
                    </div>
                    
                    <!-- Answered & Mark for Review: Warning with White Text -->
                    <div class="d-flex justify-content-between align-items-center bg-warning text-white p-2 rounded">
                        <span>Answered & Mark for Review:</span>
                        <span class="fw-bold" id="exit-answered-review">0</span>
                    </div>
                    
                    <!-- Marked for Review but not Answered: Warning with White Text -->
                    <div class="d-flex justify-content-between align-items-center bg-warning p-2 rounded">
                        <span>Marked for Review but not Answered:</span>
                        <span class="fw-bold" id="exit-marked">0</span>
                    </div>
                    
                    <!-- Not Visited: Info (Cyan/Blue) -->
                    <div class="d-flex justify-content-between align-items-center bg-info text-white p-2 rounded">
                        <span>Not Visited:</span>
                        <span class="fw-bold" id="exit-not-visited">0</span>
                    </div>
                    
                    <!-- Remaining: Danger (Red) -->
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
                    <!-- Answered: Success (Green) -->
                    <div class="d-flex justify-content-between align-items-center bg-success text-white p-2 rounded"> 
                        <span>Total Answered Questions:</span> 
                        <span class="fw-bold" id="submit-answered">0</span> 
                    </div> 
                    
                    <!-- Answered & Mark for Review: Warning with White Text -->
                    <div class="d-flex justify-content-between align-items-center bg-warning text-white p-2 rounded"> 
                        <span>Answered & Mark for Review:</span> 
                        <span class="fw-bold" id="submit-answered-review">0</span> 
                    </div> 
                    
                    <!-- Marked for Review but not Answered: Warning with White Text -->
                    <div class="d-flex justify-content-between align-items-center bg-warning p-2 rounded"> 
                        <span>Marked for Review but not Answered:</span> 
                        <span class="fw-bold" id="submit-marked">0</span> 
                    </div> 
                    
                    <!-- Not Visited: Info (Cyan/Blue) -->
                    <div class="d-flex justify-content-between align-items-center bg-info text-white p-2 rounded"> 
                        <span>Not visited:</span> 
                        <span class="fw-bold" id="submit-not-visited">0</span> 
                    </div> 
                    
                    <!-- Unanswered: Danger (Red) -->
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

<script>
    (function() {
        // Exam data
        const studentExamId = {{ $studentExam->id }};
        const questionOrder = @json($questionOrder);
        // let currentQuestionId = questionOrder[0];
        let currentQuestionId = {{ $startQuestionId }};
        let currentQuestionIndex = questionOrder.indexOf(currentQuestionId);
        // let currentQuestionIndex = 0;
        let autoSaveInterval;
        let timerInterval;
        // let timeRemaining = {{ $studentExam->remaining_seconds }};
        let timeRemaining = {{ $remainingSeconds }};
        let answersData = @json($answers);
        let currentQuestionData = null;
        let questionsData = {};
        let navigationButton = null;
        //exam-submit
        let isSubmitting = false;
        let isSaving = false;
        
        //saved stats
        savedAnswers = Object.values(answersData);
        savedAnswers.forEach(answer => {
            answersData[answer.question_id] = {
                is_visited: answer.is_visited || false,
                selected_options: answer.selected_options || [],
                is_marked_for_review: answer.is_marked_for_review || false
            };
        });
        

        // Initialize exam
        document.addEventListener('DOMContentLoaded', function() {
            updateQuestionButtons();
            updateStats();
            loadQuestion(currentQuestionId);
            startTimer();
            startAutoSave();
            setupSecurity();

            // Copy desktop stats to mobile
            updateMobileSidebar();
            
            //preload the questionsData
            preloadQuestionData();
            
        });

        function updateMobileSidebar() {
            
            const studentInfo = document.querySelector('.student-info-card');
            if (studentInfo) {
                const mobileContent = document.getElementById('mobileSidebarContent');
                if (mobileContent) {
                    mobileContent.innerHTML = studentInfo.innerHTML;
                }
            }

            const paletteContent = document.querySelector('.palette-card');
            if (paletteContent) {
                const mobilePaletteContent = document.getElementById('mobilePaletteContent');
                if (mobilePaletteContent) {
                    mobilePaletteContent.innerHTML = paletteContent.innerHTML;
                }
            }
        }

        function loadQuestion(questionId) {
            document.getElementById('question-text').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading question...</p>
                </div>
            `;
            
            document.getElementById('options-container').innerHTML = '';
            
            const buttons = document.querySelectorAll('.btn-exam,.grid-btn');
            buttons.forEach(btn => btn.disabled = true);

            currentQuestionId = questionId;
            currentQuestionIndex = questionOrder.indexOf(questionId);
            
            //save current question id first

            fetch(`/student/exam/${studentExamId}/save-current-question`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    question_id: questionId
                })
            });

            // Update current button styling
            document.querySelectorAll('.grid-btn').forEach(btn => btn.classList.remove('current'));
            const currentBtn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
            if (currentBtn) currentBtn.classList.add('current');

            // Mark as visited
            if (!answersData[currentQuestionId]) {
                answersData[currentQuestionId] = { is_visited: true };
                updateQuestionButtonStatus(currentQuestionId, 'visited');
            } else if (!answersData[currentQuestionId].is_visited) {
                answersData[currentQuestionId].is_visited = true;
                updateQuestionButtonStatus(currentQuestionId, 'visited');
            }

            fetch(`/student/exam/${studentExamId}/question/${questionId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentQuestionData = data.data;
                    // console.log(
                    //     'Server Response',
                    //     data.data
                    // );

                    questionsData[questionId] = data.data;
                    displayQuestion(data.data);
                    updateQuestionButtons();
                    updateStats();
                    if (navigationButton) {
                        hideButtonSpinner(navigationButton);
                        navigationButton = null;
                    }
                    buttons.forEach(btn => btn.disabled = false);
                }
            })
            .catch(error => {
                console.error('Error loading question:', error);
                toastr.error('Error loading question');
            });
        }

        function displayQuestion(data) {
            document.getElementById('current-question-number').innerText = currentQuestionIndex + 1;

            let typeText = '';
            let typeIcon = '';
            switch(data.type) {
                case 'single_choice':
                    typeText = 'Single Choice';
                    typeIcon = '<i class="fas fa-dot-circle me-1"></i>';
                    break;
                case 'multiple_choice':
                    typeText = 'Multiple Choice';
                    typeIcon = '<i class="fas fa-check-square me-1"></i>';
                    break;
                case 'numerical':
                    typeText = 'Numerical';
                    typeIcon = '<i class="fas fa-calculator me-1"></i>';
                    break;
                default:
                    typeText = data.type;
            }

            document.getElementById('question-type').innerHTML = `${typeIcon}${typeText} / ${data.difficulty || 'Medium'}`;
            document.getElementById('question-marks').innerHTML = `<i class="fas fa-star me-1"></i>${data.marks} Marks | <i class="fas fa-minus-circle me-1"></i>${data.negative_marks || 0}`;

            let questionHtml = data.text;
            if (data.image) {
                questionHtml += `<div class="mt-2"><img src="${data.image}" alt="Question Image" class="img-fluid rounded"></div>`;
            }
            document.getElementById('question-text').innerHTML = questionHtml;

            let optionsHtml = '';
            if (data.type === 'single_choice') {
                optionsHtml = '<div class="options-list">';
                data.options.forEach((option, idx) => {
                    const letter = String.fromCharCode(65 + idx);
                    let optionHtml = `<div class="option-text">${option.text}</div>`;
                    if (option.image) {
                        optionHtml += `<div class="mt-1"><img src="${option.image}" alt="Option Image" class="img-fluid rounded" style="max-height: 100px;"></div>`;
                    }
                    optionsHtml += `
                        <div class="option-item ${option.selected ? 'selected' : ''}" data-option-id="${option.id}">
                            <div class="option-marker">${letter}</div>
                            ${optionHtml}
                        </div>
                    `;
                });
                optionsHtml += '</div>';
            } else if (data.type === 'multiple_choice') {
                optionsHtml = '<div class="options-list">';
                data.options.forEach((option, idx) => {
                    const letter = String.fromCharCode(65 + idx);
                    let optionHtml = `<div class="option-text">${option.text}</div>`;
                    if (option.image) {
                        optionHtml += `<div class="mt-1"><img src="${option.image}" alt="Option Image" class="img-fluid rounded" style="max-height: 100px;"></div>`;
                    }
                    optionsHtml += `
                        <div class="option-item ${option.selected ? 'selected' : ''}" data-option-id="${option.id}">
                            <div class="option-marker">
                                <i class="fas ${option.selected ? 'fa-check-square' : 'fa-square'}"></i>
                            </div>
                            ${optionHtml}
                        </div>
                    `;
                });
                optionsHtml += '</div>';
            } else if (data.type === 'numerical') {
                optionsHtml = `
                    <div class="numerical-answer">
                        <label class="form-label fw-bold">Your Answer:</label>
                        <input type="number" class="form-control form-control-lg" id="numerical-input"
                               value="${data.answer || ''}" step="any"
                               placeholder="Enter your numerical answer...">
                    </div>
                `;
            }

            document.getElementById('options-container').innerHTML = optionsHtml;

            // Add event listeners to options
            if (data.type === 'single_choice') {
                document.querySelectorAll('.option-item').forEach(opt => {
                    opt.addEventListener('click', function() {
                        const optionId = parseInt(this.dataset.optionId);
                        selectSingleOption(optionId);
                    });
                });
            } else if (data.type === 'multiple_choice') {
                document.querySelectorAll('.option-item').forEach(opt => {
                    opt.addEventListener('click', function() {
                        const optionId = parseInt(this.dataset.optionId);
                        toggleMultipleOption(optionId);
                    });
                });
            } else if (data.type === 'numerical') {
                const numInput = document.getElementById('numerical-input');
                if (numInput) {
                    numInput.addEventListener('change', function() {
                        saveNumericalAnswer(this.value);
                    });
                }
            }

            // Update mark for review button
            const markBtn = document.getElementById('mark-review-text');
            if (data.is_marked_for_review) {
                markBtn.innerText = 'Unmark Review';
            } else {
                markBtn.innerText = 'Mark for Review';
            }
        }

        function selectSingleOption(optionId) {
            document.querySelectorAll('.option-item').forEach(opt => opt.classList.remove('selected'));
            const selectedOpt = document.querySelector(`.option-item[data-option-id="${optionId}"]`);
            if (selectedOpt) selectedOpt.classList.add('selected');
            saveAnswer([optionId]);
        }

        function toggleMultipleOption(optionId) {
            const optionItem = document.querySelector(`.option-item[data-option-id="${optionId}"]`);
            const icon = optionItem.querySelector('.option-marker i');

            if (icon.classList.contains('fa-square')) {
                icon.classList.remove('fa-square');
                icon.classList.add('fa-check-square');
                optionItem.classList.add('selected');
            } else {
                icon.classList.remove('fa-check-square');
                icon.classList.add('fa-square');
                optionItem.classList.remove('selected');
            }

            const selectedOptions = [];
            document.querySelectorAll('.option-item.selected').forEach(opt => {
                const optId = parseInt(opt.dataset.optionId);
                selectedOptions.push(optId);
            });

            saveAnswer(selectedOptions);
        }

        function saveNumericalAnswer(value) {
            saveAnswer(value);
        }

        function saveAnswer(answer, isAutoSave = false) {
            const questionId = currentQuestionId;
            const isMarkedForReview = document.getElementById('mark-review-text').innerText === 'Unmark Review';

            fetch(`/student/exam/${studentExamId}/save-answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer: answer,
                    is_marked_for_review: isMarkedForReview
                })
            })
            .then(response => response.json())
            .then(data => {
            
                if (data.status === 'success') {
                    if (!isAutoSave) {
                        toastr.success('Answer saved');
                    }
                    
                    if (!answersData[questionId]) {
                        answersData[questionId] = {};
                    }
            
                    answersData[questionId].selected_options = answer;
                    answersData[questionId].is_marked_for_review = isMarkedForReview;


                    let status = 'answered';
                    if (isMarkedForReview && answer && answer.length > 0) {
                        status = 'answered-marked';
                    } else if (isMarkedForReview) {
                        status = 'marked';
                    } else if (answer && answer.length > 0) {
                        status = 'answered';
                    } else {
                        status = 'visited';
                    }
                    // updateQuestionButtonStatus(currentQuestionId, status);
                    updateQuestionButtonStatus(questionId, status);
                    updateStats(data.data);
                }
            })
            .catch(error => {
                if (!isAutoSave) {
                    toastr.error('Error saving answer');
                }
            });
        }

        function updateQuestionButtonStatus(questionId, status) {
            // console.log(questionId);
            // console.log(status);
            const btn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
            if (btn) {
                btn.classList.remove('not-visited', 'visited', 'marked', 'answered', 'answered-marked');
                btn.classList.add(status);
            }
        }

        function updateStats(data = null) {
            let answered = 0, marked = 0, answeredMarked = 0, visited = 0, notVisited = 0;

            for (let i = 0; i < questionOrder.length; i++) {
                const qId = questionOrder[i];
                const answer = answersData[qId];

                if (answer) {
                    if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
                        answeredMarked++;
                    } else if (answer.is_marked_for_review) {
                        marked++;
                    } else if (answer.selected_options && answer.selected_options.length > 0) {
                        answered++;
                    } else if (answer.is_visited) {
                        visited++;
                    } else {
                        notVisited++;
                    }
                } else {
                    notVisited++;
                }
            }

            document.getElementById('stat-answered-count').innerText = answered;
            document.getElementById('stat-marked-count').innerText = marked;
            document.getElementById('stat-answered-marked-count').innerText = answeredMarked;
            document.getElementById('stat-visited-count').innerText = visited;
            document.getElementById('stat-not-visited-count').innerText = notVisited;

            const totalAnswered = answered + answeredMarked;
            const progressPercent = (totalAnswered / questionOrder.length) * 100;
            document.getElementById('progress-percentage').innerText = Math.round(progressPercent) + '%';
            document.getElementById('progress-bar-fill').style.width = progressPercent + '%';

            const submitAnswered = document.getElementById('submit-answered');
            const submitAnsweredReview = document.getElementById('submit-answered-review');
            const submitMarked = document.getElementById('submit-marked');
            const submitUnanswered = document.getElementById('submit-unanswered');
            const submitNotVisited = document.getElementById('submit-not-visited');
            if (submitAnswered) submitAnswered.innerText = totalAnswered;
            if (submitAnsweredReview) submitAnsweredReview.innerText = answeredMarked;
            if (submitMarked) submitMarked.innerText = marked;
            if (submitNotVisited) submitNotVisited.innerText = notVisited;
            if (submitUnanswered) submitUnanswered.innerText = questionOrder.length - totalAnswered;

            const exitAnswered = document.getElementById('exit-answered');
            const exitMarked = document.getElementById('exit-marked');
            const exitRemaining = document.getElementById('exit-remaining');
            const exitAnsweredReview = document.getElementById('exit-answered-review');
            const exitNotVisited = document.getElementById('exit-not-visited');
            
            if (exitAnswered) exitAnswered.innerText = totalAnswered;
            if (exitMarked) exitMarked.innerText = marked;
            if (exitAnsweredReview) exitAnsweredReview.innerText = answeredMarked;
            if (exitNotVisited) exitNotVisited.innerText = notVisited;
            if (exitRemaining) exitRemaining.innerText = questionOrder.length - totalAnswered;
        }

        // function startTimer() {
        //     timerInterval = setInterval(function() {
        //         if (timeRemaining <= 0) {
        //             clearInterval(timerInterval);
        //             toastr.warning('Time is up! Submitting exam...');
        //             submitExam();
        //             return;
        //         }

        //         timeRemaining--;
        //         updateTimerDisplay();

        //         if (timeRemaining === 300) {
        //             toastr.warning('5 minutes remaining!');
        //             document.querySelector('.timer-display')?.classList.add('timer-warning');
        //         } else if (timeRemaining === 60) {
        //             toastr.warning('1 minute remaining!');
        //         }
        //     }, 1000);
        // }

        function startTimer()
        {
            updateTimerDisplay();
            timerInterval = setInterval(function() {
                timeRemaining--;
        
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    updateTimerDisplay();
                    toastr.warning('Time is up! Submitting exam...');
                    submitExam(false);
                    return;
                }
        
                updateTimerDisplay();
        
                if (timeRemaining === 300) {
                    toastr.warning('5 minutes remaining!');
                    document.querySelector('.timer-display')?.classList.add('timer-warning');
                }
        
                if (timeRemaining === 60) {
                    toastr.warning('1 minute remaining!');
                }
        
            }, 1000);
        }
        
        function syncRemainingTime()
        {
            fetch(`/student/exam/${studentExamId}/sync-timer`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        remaining_seconds:timeRemaining
                    })
                }
            );
        }
        
        setInterval(function() {
            syncRemainingTime();
        }, 30000);
        
        function updateTimerDisplay() {
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;

            const display = hours > 0
                ? `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                : `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            const timerEl = document.getElementById('time-remaining');
            if (timerEl) timerEl.innerText = display;
        }

        function startAutoSave() {
            autoSaveInterval = setInterval(function() {
                const questionType = document.getElementById('question-type')?.innerText || '';
                let answer = null;

                if (questionType.includes('Single Choice')) {
                    const selected = document.querySelector('.option-item.selected');
                    if (selected) {
                        const optionId = selected.dataset.optionId;
                        if (optionId) answer = [parseInt(optionId)];
                    }
                } else if (questionType.includes('Multiple Choice')) {
                    answer = [];
                    document.querySelectorAll('.option-item.selected').forEach(opt => {
                        const optionId = opt.dataset.optionId;
                        if (optionId) answer.push(parseInt(optionId));
                    });
                } else if (questionType.includes('Numerical')) {
                    const numInput = document.getElementById('numerical-input');
                    if (numInput) answer = numInput.value;
                }

                if (answer !== null && answer.length !== 0) {
                    saveAnswer(answer, true);
                }
            }, 60000);
        }

        function navigateToQuestion(direction, button) {
            if (direction === 'prev' && currentQuestionIndex === 0) {
                toastr.info('This is the first question');
                return;
            }
        
            if (direction === 'next' && currentQuestionIndex === questionOrder.length - 1) {
                toastr.info('This is the last question');
                return;
            }
            navigationButton = button;
            showButtonSpinner(button);
            saveCurrentAnswer(true);

            if (direction === 'next' && currentQuestionIndex < questionOrder.length - 1) {
                loadQuestion(questionOrder[currentQuestionIndex + 1]);
            } else if (direction === 'prev' && currentQuestionIndex > 0) {
                loadQuestion(questionOrder[currentQuestionIndex - 1]);
            }
        }
        
        

        function saveCurrentAnswer(isAutoSave = false) {
            const questionType = document.getElementById('question-type')?.innerText || '';
            let answer = null;

            if (questionType.includes('Single Choice')) {
                const selected = document.querySelector('.option-item.selected');
                if (selected) {
                    const optionId = selected.dataset.optionId;
                    if (optionId) answer = [parseInt(optionId)];
                }
            } else if (questionType.includes('Multiple Choice')) {
                answer = [];
                document.querySelectorAll('.option-item.selected').forEach(opt => {
                    const optionId = opt.dataset.optionId;
                    if (optionId) answer.push(parseInt(optionId));
                });
            } else if (questionType.includes('Numerical')) {
                const numInput = document.getElementById('numerical-input');
                if (numInput) answer = numInput.value;
            }

            if (answer !== null && answer.length !== 0) {
                saveAnswer(answer, isAutoSave);
            }
        }

        function clearAnswer() {
            if (currentQuestionData && currentQuestionData.type === 'single_choice') {
                document.querySelectorAll('.option-item').forEach(opt => opt.classList.remove('selected'));
                saveAnswer([]);
            } else if (currentQuestionData && currentQuestionData.type === 'multiple_choice') {
                document.querySelectorAll('.option-item').forEach(opt => {
                    opt.classList.remove('selected');
                    const icon = opt.querySelector('.option-marker i');
                    if (icon) {
                        icon.classList.remove('fa-check-square');
                        icon.classList.add('fa-square');
                    }
                });
                saveAnswer([]);
            } else if (currentQuestionData && currentQuestionData.type === 'numerical') {
                const numInput = document.getElementById('numerical-input');
                if (numInput) {
                    numInput.value = '';
                    saveAnswer('');
                }
            }
        }

        function toggleMarkForReview(button) {
            
            const isMarked = document.getElementById('mark-review-text').innerText === 'Unmark Review';
            const newStatus = !isMarked;
            
            navigationButton = button;
            showButtonSpinner(button);

            fetch(`/student/exam/${studentExamId}/mark-review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: currentQuestionId,
                    mark: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (navigationButton) {
                        hideButtonSpinner(navigationButton);
                        navigationButton = null;
                    }
                    if (newStatus) {
                        document.getElementById('mark-review-text').innerText = 'Unmark Review';
                        toastr.warning('Marked for review');
                    } else {
                        document.getElementById('mark-review-text').innerText = 'Mark for Review';
                        toastr.warning('Unmarked for review');
                    }

                    if (answersData[currentQuestionId]) {
                        answersData[currentQuestionId].is_marked_for_review = newStatus;
                    }

                    let status = 'visited';
                    const hasAnswer = answersData[currentQuestionId]?.selected_options?.length > 0;
                    if (newStatus && hasAnswer) {
                        status = 'answered-marked';
                    } else if (newStatus) {
                        status = 'marked';
                    } else if (hasAnswer) {
                        status = 'answered';
                    }
                    updateQuestionButtonStatus(currentQuestionId, status);
                    updateStats(data.data);
                    
                }
            })
            .catch(error => {
                toastr.error('Error updating review status');
            });
        }

        function updateQuestionButtons() {
            for (let i = 0; i < questionOrder.length; i++) {
                const qId = questionOrder[i];
                const answer = answersData[qId];
                let status = 'not-visited';
                // console.log('QID:- '+ qId + ' answer ' + answersData[qId]);
                if (answer) {
                    if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
                        status = 'answered-marked';
                    } else if (answer.is_marked_for_review) {
                        status = 'marked';
                    } else if (answer.selected_options && answer.selected_options.length > 0) {
                        status = 'answered';
                    } else if (answer.is_visited) {
                        status = 'visited';
                    }
                }
                updateQuestionButtonStatus(qId, status);
            }
        }

        // function showQuestionPaper() {
        //     const modalBody = document.getElementById('questionPaperList');
        //     modalBody.innerHTML = '<div class="text-center py-4"><div class="loading-spinner"></div><p class="mt-2">Loading questions...</p></div>';

        //     const questionPaperModal = new bootstrap.Modal(document.getElementById('questionPaperModal'));
        //     questionPaperModal.show();

        //     const pendingIds = questionOrder.filter(id => !questionsData[id]);
        //     if (pendingIds.length > 0) {
        //         const promises = pendingIds.map(questionId => {
        //             return fetch(`/student/exam/${studentExamId}/question/${questionId}`, {
        //                 method: 'GET',
        //                 headers: { 'Accept': 'application/json' }
        //             }).then(res => res.json());
        //         });

        //         Promise.all(promises).then(responses => {
        //             responses.forEach(response => {
        //                 if (response.status === 'success') {
        //                     questionsData[response.data.id] = response.data;
        //                 }
        //             });
        //             renderQuestionPaper();
        //         });
        //     } else {
        //         renderQuestionPaper();
        //     }
        // }

        // function renderQuestionPaper() {
        //     let html = '<div class="list-group">';
        //     for (let i = 0; i < questionOrder.length; i++) {
        //         const qId = questionOrder[i];
        //         const answer = answersData[qId];

        //         let status = 'not-visited';
        //         let statusText = 'Not Visited';
        //         let statusClass = 'secondary';

        //         if (answer) {
        //             if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
        //                 status = 'answered-marked';
        //                 statusText = 'Answered & Marked';
        //                 statusClass = 'warning';
        //             } else if (answer.is_marked_for_review) {
        //                 status = 'marked';
        //                 statusText = 'Marked for Review';
        //                 statusClass = 'warning';
        //             } else if (answer.selected_options && answer.selected_options.length > 0) {
        //                 status = 'answered';
        //                 statusText = 'Answered';
        //                 statusClass = 'success';
        //             } else if (answer.is_visited) {
        //                 status = 'visited';
        //                 statusText = 'Visited';
        //                 statusClass = 'info';
        //             }
        //         }

        //         html += `
        //             <div class="modal-question-item d-flex align-items-center justify-content-between" onclick="loadQuestion(${qId}); bootstrap.Modal.getInstance(document.getElementById('questionPaperModal')).hide();">
        //                 <div>
        //                     <span class="badge bg-${statusClass} me-3">${i + 1}</span>
        //                     <span>Question ${i + 1}</span>
        //                 </div>
        //                 <div>
        //                     <span class="badge bg-${statusClass}">${statusText}</span>
        //                 </div>
        //             </div>
        //         `;
        //     }
        //     html += '</div>';
        //     document.getElementById('questionPaperList').innerHTML = html;
        // }
//         function renderQuestionPaper() {
//     let html = '<div class="list-group">';
    
//     for (let i = 0; i < questionOrder.length; i++) {
//         const qId = questionOrder[i];
//         const answer = answersData[qId];
        
//         // 1. Fetch question text and handle fallback if data is still loading
//         const questionObj = questionsData[qId];
//         let questionText = `Question ${i + 1}`; // Default fallback text
        
//         if (questionObj && questionObj.text) {
//             // Adjust 'question_text' to match your actual API property name (e.g., body, text, title)
//             const rawText = questionObj.text; 
//             questionText = rawText.length > 200 ? rawText.substring(0, 200) + '...' : rawText;
//         }

//         let status = 'not-visited';
//         let statusText = 'Not Visited';
//         let statusClass = 'secondary';
        
//         if (answer) {
//             if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
//                 status = 'answered-marked';
//                 statusText = 'Answered & Marked';
//                 statusClass = 'warning';
//             } else if (answer.is_marked_for_review) {
//                 status = 'marked';
//                 statusText = 'Marked for Review';
//                 statusClass = 'warning';
//             } else if (answer.selected_options && answer.selected_options.length > 0) {
//                 status = 'answered';
//                 statusText = 'Answered';
//                 statusClass = 'success';
//             } else if (answer.is_visited) {
//                 status = 'visited';
//                 statusText = 'Visited';
//                 statusClass = 'info';
//             }
//         }
        
//         // 2. Updated HTML layout to fit the question text cleanly
//         html += `
//             <div class="modal-question-item d-flex align-items-center justify-content-between p-2" 
//                  onclick="loadQuestion(${qId}); bootstrap.Modal.getInstance(document.getElementById('questionPaperModal')).hide();" 
//                  style="cursor: pointer;">
//                 <div class="d-flex align-items-center me-3" style="max-width: 75%;">
//                     <span class="badge bg-${statusClass} me-3">${i + 1}</span>
//                     <span class="text-wrap small text-secondary">${questionText}</span>
//                 </div>
//                 <div class="text-end" style="min-width: 120px;">
//                     <span class="badge bg-${statusClass}">${statusText}</span>
//                 </div>
//             </div>
//         `;
//     }
    
//     html += '</div>';
//     document.getElementById('questionPaperList').innerHTML = html;
// }



async function preloadQuestionData() {
    const pendingIds = questionOrder.filter(id => !questionsData[id]);
    if (pendingIds.length === 0) return;

    // Split pendingIds into smaller batches of 5 to avoid overloading the server/browser
    const batchSize = 1; 
    
    for (let i = 0; i < pendingIds.length; i += batchSize) {
        const batch = pendingIds.slice(i, i + batchSize);
        
        // Map the current batch into fetch promises
        const promises = batch.map(questionId => {
            return fetch(`/student/exam/${studentExamId}/question/${questionId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .catch(err => {
                console.error(`Failed to fetch question ${questionId}:`, err);
                return null; // Return null so Promise.all doesn't fail entirely
            });
        });

        // Wait for this specific batch of 5 to finish completely before moving to the next 5
        const responses = await Promise.all(promises);

        responses.forEach(response => {
            if (response && response.status === 'success' && response.data) {
                questionsData[response.data.id] = response.data;
            }
        });

        // Dynamically refresh the layout as batches load in the background
        const modalEl = document.getElementById('questionPaperModal');
        if (modalEl && modalEl.classList.contains('show')) {
            renderQuestionPaper();
        }
    }
}


// 2. Triggered when clicking the button. Reads fresh answer data instantly.
function showQuestionPaper() {
    // Generates the UI mapping the cached texts against the LIVE answersData object
    renderQuestionPaper();
    
    const questionPaperModal = new bootstrap.Modal(document.getElementById('questionPaperModal'));
    questionPaperModal.show();
}

function renderQuestionPaper() {
    let html = '<div class="list-group">';
    
    for (let i = 0; i < questionOrder.length; i++) {
        const qId = questionOrder[i];
        
        // CRITICAL: Pulls the absolute latest live answer state right now
        const answer = answersData[qId]; 
        const questionObj = questionsData[qId];
        
        let questionText = `Question ${i + 1}`; 
        
        if (questionObj && questionObj.text) {
            const cleanText = questionObj.text.replace(/<\/?[^>]+(>|$)/g, "");
            questionText = cleanText.length > 200 ? cleanText.substring(0, 200) + '...' : cleanText;
        }

        let status = 'not-visited';
        let statusText = 'Not Visited';
        let statusClass = 'secondary';
        
        // This evaluations block runs freshly on every single modal open trigger
        if (answer) {
            if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
                status = 'answered-marked';
                statusText = 'Answered & Marked';
                statusClass = 'warning';
            } else if (answer.is_marked_for_review) {
                status = 'marked';
                statusText = 'Marked for Review';
                statusClass = 'warning';
            } else if (answer.selected_options && answer.selected_options.length > 0) {
                status = 'answered';
                statusText = 'Answered';
                statusClass = 'success';
            } else if (answer.is_visited) {
                status = 'visited';
                statusText = 'Visited';
                statusClass = 'info';
            }
        }
        
        html += `
            <div class="modal-question-item d-flex align-items-center justify-content-between p-2" 
                 onclick="loadQuestion(${qId}); bootstrap.Modal.getInstance(document.getElementById('questionPaperModal')).hide();" 
                 style="cursor: pointer;">
                <div class="d-flex align-items-center me-3" style="max-width: 75%;">
                    <span class="badge bg-${statusClass} me-3">${i + 1}</span>
                    <span class="text-wrap small text-secondary">${questionText}</span>
                </div>
                <div class="text-end" style="min-width: 120px;">
                    <span class="badge bg-${statusClass}">${statusText}</span>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    
    const container = document.getElementById('questionPaperList');
    if (container) {
        container.innerHTML = html;
    }
}



        function showInstructions() {
            const instructionsModal = new bootstrap.Modal(document.getElementById('instructionsModal'));
            instructionsModal.show();
        }

        function saveAndExit() {
            const saveExitModal = new bootstrap.Modal(document.getElementById('saveExitModal'));
            saveExitModal.show();
        }

        // function confirmSaveAndExit() {
        //     bootstrap.Modal.getInstance(document.getElementById('saveExitModal')).hide();
        //     saveCurrentAnswer(false);
        //     toastr.success('Progress saved. You can resume later.');
        //     setTimeout(() => {
        //         window.location.href = '{{ route("student.dashboard") }}';
        //     }, 1500);
        // }
        
        function confirmSaveAndExit(closeModal = true) {
            
            if (closeModal) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('submitModal'));
        
                if (modal) {
                    modal.hide();
                }
            }

            if (isSaving) {
                return;
            }
            isSaving = true;
            
            fetch(`/student/exam/${studentExamId}/save-exit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content
                },
                body: JSON.stringify({
                    current_question_id: currentQuestionId,
                    remaining_seconds: timeRemaining,
                })
            })
            .then(response => response.json())
            .then(data => {
        
                toastr.success(data.message);
        
                setTimeout(() => {
                    window.location.href ='{{ route("student.dashboard") }}';
                }, 1000);
            });
        }

        function openSubmitModal() {
            const submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
            submitModal.show();
        }

        
        function submitExam(closeModal = true) {
            if (closeModal) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('submitModal'));
        
                if (modal) {
                    modal.hide();
                }
            }
            // bootstrap.Modal.getInstance(document.getElementById('submitModal')).hide();
            
            if (isSubmitting) {
                return;
            }
        
            isSubmitting = true;
        
            clearInterval(timerInterval);
        
            if (autoSaveInterval) {
                clearInterval(autoSaveInterval);
            }

            fetch(`/student/exam/${studentExamId}/submit`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    clearInterval(timerInterval);
                    clearInterval(autoSaveInterval);
                    toastr.success('Exam submitted successfully');
                    setTimeout(() => {
                        window.location.href = data.data.redirect;
                    }, 2000);
                }
            })
            .catch(error => {
                toastr.error('Error submitting exam');
            });
        }

        // function setupSecurity() {
            
          
           
        //     // 1. Force the history trap immediately on page load
        //     window.history.pushState(null, null, window.location.href);
            
        //     // 2. Continuous trap: Keep flooding the history stack if they click back quickly
        //     window.addEventListener('popstate', function (event) {
        //         // Re-lock the position instantly
        //         window.history.pushState(null, null, window.location.href);
                
        //         // Custom non-blocking alert/modal instead of standard alert (which pauses script execution)
        //         alert('Back navigation is disabled during the examination.');
        //     });

        //     document.addEventListener('visibilitychange', function() {
        //         if (document.hidden) {
        //             fetch(`/student/exam/${studentExamId}/tab-switch`, {
        //                 method: 'POST',
        //                 headers: {
        //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        //                     'Accept': 'application/json'
        //                 }
        //             });
        //         }
        //     });

        //     window.addEventListener('beforeunload', function(e) {
        //         if (isSubmitting || isSaving ) {
        //             return;
        //         }
        //         // Standard required browser implementation to trigger a confirmation dialog
        //         event.preventDefault();
                
        //         // Custom message (Note: Modern browsers will show their own generic text anyway)
        //         event.returnValue = 'You are currently taking an exam. Leaving will forfeit your progress.';
        //         return event.returnValue;
        //         if (!window.location.href.includes('result')) {
        //             e.preventDefault();
        //             e.returnValue = 'Are you sure you want to leave? Your answers will be saved but exam may be considered incomplete.';
        //         }
        //     });
        // }
        
        function setupSecurity() {
            // ==========================================
            // 0. NEW: MULTI-TAB DETECTOR (BroadcastChannel)
            // ==========================================
            const examChannel = new BroadcastChannel(`exam_session_guard_${studentExamId}`);
            const currentTabId = Date.now(); // Unique ID for this specific tab instance
            let isPrimaryTab = true;
        
            // Ping existing tabs immediately to find duplicates
            examChannel.postMessage({ type: 'PING_EXISTING_TABS', tabId: currentTabId });
        
            // Listen for tab communication
            examChannel.onmessage = function(event) {
                const message = event.data;
        
                if (message.type === 'PING_EXISTING_TABS') {
                    // If this tab was already open first, tell the new tab to lock itself
                    if (currentTabId < message.tabId) {
                        examChannel.postMessage({ type: 'PONG_ACTIVE_FOUND', targetTabId: message.tabId });
                    }
                } 
                
                if (message.type === 'PONG_ACTIVE_FOUND' && message.targetTabId === currentTabId) {
                    isPrimaryTab = false;
                    blockDuplicateTab();
                }
            };
        
            function blockDuplicateTab() {
                // Freeze the page immediately by overlaying a full-screen blocker
                document.body.innerHTML = `
                    <div style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:#fff; z-index:999999; display:flex; flex-direction:column; justify-content:center; align-items:center; font-family:sans-serif; padding:20px; text-align:center;">
                        <h1 style="color:#dc3545; font-size: 2.5rem; margin-bottom: 10px;">⚠️ Access Denied</h1>
                        <p style="font-size: 1.2rem; color: #495057; max-width: 500px;">
                            This exam is already open in another browser tab. Multi-tab access is strictly prohibited.
                        </p>
                        <button onclick="window.location.href='/student/dashboard'" style="margin-top: 20px; padding: 12px 24px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                            Return to Dashboard
                        </button>
                    </div>
                `;
                // Send a direct violation log to Laravel backend
                reportViolation('duplicate-tab-attempt');
                setTimeout(() => { window.close(); }, 5000);
            }
        
            // ==========================================
            // 1. ORIGINAL HISTORY TRAP
            // ==========================================
            window.history.pushState(null, null, window.location.href);
            window.addEventListener('popstate', function (event) {
                window.history.pushState(null, null, window.location.href);
                alert('Back navigation is disabled during the examination.');
            });
        
            // ==========================================
            // 2. VISIBILITY & FOCUS TRAPS (Tab/Window Switches)
            // ==========================================
            document.addEventListener('visibilitychange', function() {
                // Only report if this is actually the primary valid test tab
                if (document.hidden && isPrimaryTab) {
                    reportViolation('tab-switch');
                }
                if (isSubmitting || isSaving || !isPrimaryTab) {
                    return;
                }else{
                    alert('Do not switch tabs during exam');
                }
            });
        
            window.addEventListener('blur', function() {
                if (isPrimaryTab) {
                    reportViolation('loss-of-focus');
                }
                // alert('You are loosing focus from exam');
            });
        
            // ==========================================
            // 3. FULLSCREEN ENFORCEMENT & RESIZE DETECTION
            // ==========================================
            window.addEventListener('resize', function() {
                if (isPrimaryTab) {
                    reportViolation('window-resized');
                }
            });
        
            // ==========================================
            // 4. DISABLE RIGHT-CLICK & TEXT SELECTION (Anti-Copy)
            // ==========================================
            document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
            document.addEventListener('selectstart', function(e) { e.preventDefault(); });
        
            // ==========================================
            // 5. DISABLE SHORTCUTS (Copy, Paste, DevTools)
            // ==========================================
            document.addEventListener('keydown', function(e) {
                if (!isPrimaryTab) return;
        
                // Disable F12 (Developer Tools)
                if (e.key === 'F12') { e.preventDefault(); return false; }
        
                // Disable Ctrl+Shift+I / J / C (DevTools)
                if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C' || e.key === 'i' || e.key === 'j' || e.key === 'c')) {
                    e.preventDefault();
                    return false;
                }
        
                // Disable Ctrl+U / Cmd+U (View Source)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'U' || e.key === 'u')) { e.preventDefault(); return false; }
                
                // Disable Ctrl+s / Cmd+S (for saving the exam)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'S' || e.key === 's')) { e.preventDefault(); return false; }
        
                // Disable Ctrl+C, Ctrl+V, Ctrl+X (Copy, Paste, Cut)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'C' || e.key === 'c' || e.key === 'V' || e.key === 'v' || e.key === 'X' || e.key === 'x')) {
                    e.preventDefault();
                    alert('Copying and pasting is disabled during the exam.');
                    return false;
                }
        
                // Disable Ctrl+P (Print Screen / PDF Save)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'P' || e.key === 'p')) { e.preventDefault(); return false; }
            });
        
            document.addEventListener('paste', function(e) { e.preventDefault(); });
        
            // ==========================================
            // 6. CENTRALIZED VIOLATION LOGGING
            // ==========================================
            function reportViolation(type) {
                fetch(`/student/exam/${studentExamId}/violation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ violation_type: type })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.action === 'terminate') {
                        alert('Exam terminated due to multiple security violations.');
                        window.location.href = '/student/dashboard';
                    }
                });
            }
        
            // ==========================================
            // 7. ORIGINAL BEFOREUNLOAD CLOSURE TRAP
            // ==========================================
            window.addEventListener('beforeunload', function(e) {
                if (isSubmitting || isSaving || !isPrimaryTab) {
                    return;
                }
                e.preventDefault();
                e.returnValue = 'You are currently taking an exam. Leaving will forfeit your progress.';
                return e.returnValue;
            });
        }

        
        function showButtonSpinner(button, text = 'Loading...')
        {
            button.dataset.originalHtml = button.innerHTML;
        
            button.disabled = true;
        
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                ${text}
            `;
        }
        
        function hideButtonSpinner(button)
        {
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
        
            button.disabled = false;
        }

        // Expose functions globally for onclick handlers
        window.loadQuestion = loadQuestion;
        window.clearAnswer = clearAnswer;
        window.navigateToQuestion = navigateToQuestion;
        window.toggleMarkForReview = toggleMarkForReview;
        window.showQuestionPaper = showQuestionPaper;
        window.showInstructions = showInstructions;
        window.saveAndExit = saveAndExit;
        window.confirmSaveAndExit = confirmSaveAndExit;
        window.openSubmitModal = openSubmitModal;
        window.submitExam = submitExam;
        window.selectSingleOption = selectSingleOption;
        window.toggleMultipleOption = toggleMultipleOption;
        window.saveNumericalAnswer = saveNumericalAnswer;
    })();
</script>
@endsection