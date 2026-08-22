@extends('layouts.exam')

@section('title', $studentExam->exam->title)

@section('content')
<!-- Topbar -->
<div class="exam-topbar">
    <!-- Top Bar - Branding -->
    <div class="topbar-branding">
        <div class="container-fluid">
            <div class="brand-logo">
                <img src="{{ asset('images/nta-logo.png') }}" alt="NTA" onerror="this.style.display='none'">
                <div class="brand-text">
                    National Testing Agency
                    <small>Ministry of Education, Government of India</small>
                </div>
            </div>
            <div class="topbar-right">
                <div class="profile-icon" onclick="toggleProfileMenu()">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar - Exam Info & Timer -->
    <div class="topbar-exam-info">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="exam-title">{{ $studentExam->exam->title }}</div>
                    <div class="exam-meta">
                        Instructor: {{ $studentExam->exam->batch->instructor->name ?? 'N/A' }} | 
                        Total Questions: {{ count($questionOrder) }} | 
                        Total Marks: {{ $studentExam->exam->total_marks }}
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-1 mt-md-0">
                    <div class="timer-box">
                        <span class="timer-label"><i class="far fa-clock me-1"></i>Time Left:</span>
                        <span class="timer-display" id="time-remaining">{{ $remainingSeconds ?? $studentExam->formatted_time_remaining }}</span>
                    </div>
                    <div class="d-inline-block ms-2" style="font-size:0.7rem; color:#6c757d;">
                        <span id="progress-percentage">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="exam-main">
    <div class="container-fluid">
        <div class="row">
            <!-- Left Panel - Student Info (Desktop) -->
            <div class="col-lg-2 desktop-sidebar">
                <div class="palette-card mb-3">
                    <div class="palette-header">
                        <i class="fas fa-user-graduate me-2"></i>Candidate
                    </div>
                    <div class="p-3">
                        <div class="text-center mb-2">
                            <div style="width:60px;height:60px;border-radius:50%;background:#1a237e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto;">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <div class="fw-bold mt-1">{{ Auth::user()->name }}</div>
                            <div class="small text-muted">Roll: {{ Auth::user()->roll_no ?? Auth::user()->id }}</div>
                        </div>
                        <div class="small">
                            <div class="d-flex justify-content-between"><span class="text-muted">Course:</span> <span>{{ $studentExam->exam->batch->course->title ?? 'N/A' }}</span></div>
                            <div class="d-flex justify-content-between"><span class="text-muted">Batch:</span> <span>{{ $studentExam->exam->batch->name ?? 'N/A' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center Panel - Question Area -->
            <div class="col-lg-7">
                <!-- Section Tabs -->
                <div class="section-tabs">
                    <button class="section-tab active">Section A</button>
                    <button class="section-tab">Section B</button>
                </div>

                <div class="question-panel" id="question-panel">
                    <div class="question-header">
                        <span class="question-counter">
                            Question <span id="current-question-number">1</span> of <span id="total-questions">{{ count($questionOrder) }}</span>
                        </span>
                        <div class="question-badges">
                            <span class="question-badge type" id="question-type">Loading...</span>
                            <span class="question-badge marks" id="question-marks">0 Marks</span>
                        </div>
                    </div>

                    <div class="question-text" id="question-text">
                        <div class="text-center py-5">
                            <div class="loading-spinner"></div>
                            <p class="mt-2 text-muted">Loading question...</p>
                        </div>
                    </div>

                    <div class="options-container" id="options-container"></div>

                    <div class="action-buttons">
                        <div class="left-actions">
                            <button type="button" class="btn-exam btn-exam-outline" onclick="clearAnswer()">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                            <button type="button" class="btn-exam btn-exam-warning" onclick="toggleMarkForReview(this)">
                                <i class="fas fa-flag"></i> <span id="mark-review-text">Mark for Review</span>
                            </button>
                        </div>
                        <div class="right-actions">
                            <button type="button" class="btn-exam btn-exam-secondary" onclick="navigateToQuestion('prev', this)">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn-exam btn-exam-primary" onclick="navigateToQuestion('next', this)">
                                Save & Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Palette (Desktop) -->
            <div class="col-lg-3 desktop-sidebar">
                <div class="palette-card">
                    <div class="palette-header">
                        <i class="fas fa-th me-2"></i>Question Palette
                    </div>

                    <div class="palette-legend">
                        <div class="legend-row"><span class="legend-dot not-visited"></span> Not Visited</div>
                        <div class="legend-row"><span class="legend-dot answered"></span> Answered</div>
                        <div class="legend-row"><span class="legend-dot marked-review"></span> Marked for Review</div>
                        <div class="legend-row"><span class="legend-dot answered-marked"></span> Answered & Marked</div>
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
                                $status = 'marked-review';
                            } elseif ($answer->selected_options || $answer->answer_text) {
                                $status = 'answered';
                            }
                        }
                        @endphp
                        <button class="grid-btn {{ $status }} question-btn" data-question-id="{{ $questionId }}"
                            data-index="{{ $index }}" onclick="loadQuestion({{ $questionId }})">
                            {{ $index + 1 }}
                        </button>
                        @endforeach
                    </div>

                    <div class="stats-summary">
                        <span class="stat-item"><span class="stat-dot s-answered"></span> <span id="stat-answered-count">0</span></span>
                        <span class="stat-item"><span class="stat-dot s-marked"></span> <span id="stat-marked-count">0</span></span>
                        <span class="stat-item"><span class="stat-dot s-answered-marked"></span> <span id="stat-answered-marked-count">0</span></span>
                        <span class="stat-item"><span class="stat-dot s-not-visited"></span> <span id="stat-not-visited-count">{{ count($questionOrder) }}</span></span>
                    </div>

                    <div class="palette-actions">
                        <button class="palette-action-btn" onclick="showQuestionPaper()">
                            <i class="fas fa-file-alt"></i> Question Paper
                        </button>
                        <button class="palette-action-btn" onclick="showInstructions()">
                            <i class="fas fa-info-circle"></i> Instructions
                        </button>
                        <button class="palette-action-btn primary" onclick="saveAndExit()">
                            <i class="fas fa-save"></i> Save & Exit
                        </button>
                        <button class="palette-action-btn danger" onclick="openSubmitModal()">
                            <i class="fas fa-check-circle"></i> Submit
                        </button>
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
            <div class="modal-header" style="background:#1a237e;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Question Paper</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="modal-header" style="background:#fd7e14;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Exam Instructions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="modal-header" style="background:#fd7e14;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-save me-2"></i>Save & Exit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="modal-header" style="background:#dc3545;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Submit Exam</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<script>
    (function() {
        // Exam data
        const studentExamId = {{ $studentExam->id }};
        const questionOrder = @json($questionOrder);
        let currentQuestionId = {{ $startQuestionId }};
        let currentQuestionIndex = questionOrder.indexOf(currentQuestionId);
        let autoSaveInterval;
        let timerInterval;
        let timeRemaining = {{ $remainingSeconds ?? $studentExam->remaining_seconds }};
        let answersData = @json($answers);
        let currentQuestionData = null;
        let questionsData = {};
        let navigationButton = null;
        let isSubmitting = false;
        let isSaving = false;

        // Initialize answersData
        const savedAnswers = Object.values(answersData);
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
            updateMobileSidebar();
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
                    <div class="loading-spinner"></div>
                    <p class="mt-2 text-muted">Loading question...</p>
                </div>
            `;
            document.getElementById('options-container').innerHTML = '';

            const buttons = document.querySelectorAll('.btn-exam,.grid-btn');
            buttons.forEach(btn => btn.disabled = true);

            currentQuestionId = questionId;
            currentQuestionIndex = questionOrder.indexOf(questionId);

            // Save current question
            fetch(`/student/exam/${studentExamId}/save-current-question`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ question_id: questionId })
            });

            // Update current button
            document.querySelectorAll('.grid-btn').forEach(btn => btn.classList.remove('current'));
            const currentBtn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
            if (currentBtn) currentBtn.classList.add('current');

            // Mark as visited
            if (!answersData[currentQuestionId]) {
                answersData[currentQuestionId] = { is_visited: true };
                updateQuestionButtonStatus(currentQuestionId, 'not-visited');
            } else if (!answersData[currentQuestionId].is_visited) {
                answersData[currentQuestionId].is_visited = true;
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
            switch(data.type) {
                case 'single_choice': typeText = 'Single Choice'; break;
                case 'multiple_choice': typeText = 'Multiple Choice'; break;
                case 'numerical': typeText = 'Numerical'; break;
                default: typeText = data.type;
            }

            document.getElementById('question-type').innerHTML = typeText;
            document.getElementById('question-marks').innerHTML = data.marks + ' Marks';

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
                        optionHtml += `<div class="mt-1"><img src="${option.image}" alt="Option Image" class="img-fluid rounded" style="max-height:100px;"></div>`;
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
                        optionHtml += `<div class="mt-1"><img src="${option.image}" alt="Option Image" class="img-fluid rounded" style="max-height:100px;"></div>`;
                    }
                    optionsHtml += `
                        <div class="option-item ${option.selected ? 'selected' : ''}" data-option-id="${option.id}">
                            <div class="option-marker">
                                <i class="fas ${option.selected ? 'fa-check-circle' : 'fa-circle'}"></i>
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

            // Event listeners
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

            // Mark for review button
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

            if (icon.classList.contains('fa-circle')) {
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
                optionItem.classList.add('selected');
            } else {
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
                optionItem.classList.remove('selected');
            }

            const selectedOptions = [];
            document.querySelectorAll('.option-item.selected').forEach(opt => {
                selectedOptions.push(parseInt(opt.dataset.optionId));
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
                        status = 'marked-review';
                    } else if (answer && answer.length > 0) {
                        status = 'answered';
                    } else {
                        status = 'not-visited';
                    }
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
            const btn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
            if (btn) {
                btn.classList.remove('not-visited', 'answered', 'marked-review', 'answered-marked');
                btn.classList.add(status);
            }
        }

        function updateStats(data = null) {
            let answered = 0, marked = 0, answeredMarked = 0, notVisited = 0;

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
                    } else {
                        notVisited++;
                    }
                } else {
                    notVisited++;
                }
            }

            const totalAnswered = answered + answeredMarked;
            const totalQuestions = questionOrder.length;

            document.getElementById('stat-answered-count').innerText = answered;
            document.getElementById('stat-marked-count').innerText = marked;
            document.getElementById('stat-answered-marked-count').innerText = answeredMarked;
            document.getElementById('stat-not-visited-count').innerText = notVisited;

            const progressPercent = (totalAnswered / totalQuestions) * 100;
            document.getElementById('progress-percentage').innerText = Math.round(progressPercent) + '%';

            // Update modals
            document.getElementById('submit-answered').innerText = totalAnswered;
            document.getElementById('submit-answered-review').innerText = answeredMarked;
            document.getElementById('submit-marked').innerText = marked;
            document.getElementById('submit-not-visited').innerText = notVisited;
            document.getElementById('submit-unanswered').innerText = totalQuestions - totalAnswered;

            document.getElementById('exit-answered').innerText = totalAnswered;
            document.getElementById('exit-marked').innerText = marked;
            document.getElementById('exit-answered-review').innerText = answeredMarked;
            document.getElementById('exit-not-visited').innerText = notVisited;
            document.getElementById('exit-remaining').innerText = totalQuestions - totalAnswered;
        }

        function startTimer() {
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
                    document.querySelector('.timer-display')?.classList.add('timer-danger');
                }
            }, 1000);
        }

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
                        icon.classList.remove('fa-check-circle');
                        icon.classList.add('fa-circle');
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

                    let status = 'not-visited';
                    const hasAnswer = answersData[currentQuestionId]?.selected_options?.length > 0;
                    if (newStatus && hasAnswer) {
                        status = 'answered-marked';
                    } else if (newStatus) {
                        status = 'marked-review';
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

                if (answer) {
                    if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
                        status = 'answered-marked';
                    } else if (answer.is_marked_for_review) {
                        status = 'marked-review';
                    } else if (answer.selected_options && answer.selected_options.length > 0) {
                        status = 'answered';
                    }
                }
                updateQuestionButtonStatus(qId, status);
            }
        }

        // Question Paper functions
        function preloadQuestionData() {
            const pendingIds = questionOrder.filter(id => !questionsData[id]);
            if (pendingIds.length === 0) return;

            const batchSize = 5;
            for (let i = 0; i < pendingIds.length; i += batchSize) {
                const batch = pendingIds.slice(i, i + batchSize);
                const promises = batch.map(questionId => {
                    return fetch(`/student/exam/${studentExamId}/question/${questionId}`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .catch(err => {
                        console.error(`Failed to fetch question ${questionId}:`, err);
                        return null;
                    });
                });

                Promise.all(promises).then(responses => {
                    responses.forEach(response => {
                        if (response && response.status === 'success' && response.data) {
                            questionsData[response.data.id] = response.data;
                        }
                    });
                    const modalEl = document.getElementById('questionPaperModal');
                    if (modalEl && modalEl.classList.contains('show')) {
                        renderQuestionPaper();
                    }
                });
            }
        }

        function showQuestionPaper() {
            renderQuestionPaper();
            const questionPaperModal = new bootstrap.Modal(document.getElementById('questionPaperModal'));
            questionPaperModal.show();
        }

        function renderQuestionPaper() {
            let html = '<div class="list-group">';

            for (let i = 0; i < questionOrder.length; i++) {
                const qId = questionOrder[i];
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

                if (answer) {
                    if (answer.is_marked_for_review && answer.selected_options && answer.selected_options.length > 0) {
                        status = 'answered-marked';
                        statusText = 'Answered & Marked';
                        statusClass = 'danger';
                    } else if (answer.is_marked_for_review) {
                        status = 'marked-review';
                        statusText = 'Marked for Review';
                        statusClass = 'warning';
                    } else if (answer.selected_options && answer.selected_options.length > 0) {
                        status = 'answered';
                        statusText = 'Answered';
                        statusClass = 'success';
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
            document.getElementById('questionPaperList').innerHTML = html;
        }

        function showInstructions() {
            const instructionsModal = new bootstrap.Modal(document.getElementById('instructionsModal'));
            instructionsModal.show();
        }

        function saveAndExit() {
            const saveExitModal = new bootstrap.Modal(document.getElementById('saveExitModal'));
            saveExitModal.show();
        }

        function confirmSaveAndExit(closeModal = true) {
            if (closeModal) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('submitModal'));
                if (modal) modal.hide();
            }

            if (isSaving) return;
            isSaving = true;

            fetch(`/student/exam/${studentExamId}/save-exit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
                    window.location.href = '{{ route("student.dashboard") }}';
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
                if (modal) modal.hide();
            }

            if (isSubmitting) return;
            isSubmitting = true;

            clearInterval(timerInterval);
            if (autoSaveInterval) clearInterval(autoSaveInterval);

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

        function setupSecurity() {
            // History trap
            window.history.pushState(null, null, window.location.href);
            window.addEventListener('popstate', function(event) {
                window.history.pushState(null, null, window.location.href);
                alert('Back navigation is disabled during the examination.');
            });

            // Visibility change
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && !isSubmitting && !isSaving) {
                    fetch(`/student/exam/${studentExamId}/tab-switch`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    alert('Please do not switch tabs during the exam.');
                }
            });

            // Before unload
            window.addEventListener('beforeunload', function(e) {
                if (isSubmitting || isSaving) return;
                e.preventDefault();
                e.returnValue = 'You are currently taking an exam. Leaving will forfeit your progress.';
                return e.returnValue;
            });

            // Right click disable
            document.addEventListener('contextmenu', function(e) { e.preventDefault(); });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12') { e.preventDefault(); return false; }
                if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) {
                    e.preventDefault();
                    return false;
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'U' || e.key === 'u')) {
                    e.preventDefault();
                    return false;
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'C' || e.key === 'c' || e.key === 'V' || e.key === 'v' || e.key === 'X' || e.key === 'x')) {
                    e.preventDefault();
                    alert('Copying and pasting is disabled during the exam.');
                    return false;
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'P' || e.key === 'p')) {
                    e.preventDefault();
                    return false;
                }
            });
        }

        function showButtonSpinner(button, text = 'Loading...') {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
        }

        function hideButtonSpinner(button) {
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
            button.disabled = false;
        }

        // Expose functions globally
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
        window.toggleProfileMenu = function() {
            toastr.info('Profile menu would open here');
        };
    })();
</script>
@endsection