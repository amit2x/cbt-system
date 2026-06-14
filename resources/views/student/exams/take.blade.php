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
<div class="exam-topbar py-2">
    <!-- Added padding utility to control height -->
    <div class="container-fluid">
        <div class="row align-items-center">

            <!-- Left Side: App Name -->
            <div class="col-md-3">
                <div class="app-name font-weight-bold">{{ config('app.name', 'CBT System') }}</div>
            </div>

            <!-- Center Side: Exam & Instructor Name -->
            <div class="col-md-5 text-center">
                <div class="exam-name font-weight-bold">{{ Str::limit($studentExam->exam->title, 40) }}</div>
                <div class="instructor-name small text-muted">Instructor: {{ $studentExam->exam->batch->instructor->name
                    ?? 'N/A' }}</div>
            </div>

            <!-- Right Side: Progress and Time (Stacked vertically) -->
            <div class="col-md-4">
                <div class="topbar-right d-flex flex-column align-items-end justify-content-center">

                    <!-- Progress Bar Row -->
                    <div class="progress-stats w-100 d-flex align-items-center justify-content-end mb-1">
                        <div class="progress-label small mr-2">Progress: <span id="progress-percentage">0%</span></div>
                        <div class="progress-bar-container"
                            style="width: 100px; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                            <div class="progress-bar-fill" id="progress-bar-fill"
                                style="width: 0%; height: 100%; background: #007bff;"></div>
                        </div>
                    </div>

                    <!-- Timer Row -->
                    <div class="timer-box d-flex align-items-center justify-content-end">
                        <div class="timer-label small mr-2">Time Remaining:</div>
                        <div class="timer-display font-weight-bold" id="time-remaining">{{
                            $studentExam->formatted_time_remaining }}</div>
                    </div>

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
                    <div class="student-roll">Roll No: {{ Auth::user()->roll_no ?? 'N/A' }}</div>

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
                                onclick="navigateToQuestion('prev')">
                                <i class="fas fa-arrow-left me-1"></i> Previous
                            </button>
                            <button type="button" class="btn-exam btn-exam-warning me-1"
                                onclick="toggleMarkForReview()">
                                <i class="fas fa-flag me-1"></i> <span id="mark-review-text">Mark for Review</span>
                            </button>
                            <button type="button" class="btn-exam btn-exam-primary"
                                onclick="navigateToQuestion('next')">
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
                <p>Your progress has been saved. Are you sure you want to exit?</p>
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Answered:</span>
                        <span class="fw-bold" id="exit-answered">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Marked for Review:</span>
                        <span class="fw-bold" id="exit-marked">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Remaining:</span>
                        <span class="fw-bold" id="exit-remaining">0</span>
                    </div>
                </div>
                <p class="text-muted">You can resume this exam later from your dashboard.</p>
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
                <p>Are you sure you want to submit the exam?</p>
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Answered Questions:</span>
                        <span class="fw-bold" id="submit-answered">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Marked for Review:</span>
                        <span class="fw-bold" id="submit-marked">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Unanswered:</span>
                        <span class="fw-bold" id="submit-unanswered">0</span>
                    </div>
                </div>
                <p class="text-muted small">Once submitted, you cannot change your answers.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitExam()">Submit Exam</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Exam data
        const studentExamId = {{ $studentExam->id }};
        const questionOrder = @json($questionOrder);
        let currentQuestionId = questionOrder[0];
        let currentQuestionIndex = 0;
        let autoSaveInterval;
        let timerInterval;
        let timeRemaining = {{ $studentExam->remaining_seconds }};
        let answersData = @json($answers);
        let currentQuestionData = null;
        let questionsData = {};

        // Initialize exam
        document.addEventListener('DOMContentLoaded', function() {
            loadQuestion(currentQuestionId);
            startTimer();
            startAutoSave();
            setupSecurity();
            updateStats();

            // Copy desktop stats to mobile
            updateMobileSidebar();
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
            currentQuestionId = questionId;
            currentQuestionIndex = questionOrder.indexOf(questionId);

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
                    questionsData[questionId] = data.data;
                    displayQuestion(data.data);
                    updateQuestionButtons();
                    updateStats();
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
            const isMarkedForReview = document.getElementById('mark-review-text').innerText === 'Unmark Review';

            fetch(`/student/exam/${studentExamId}/save-answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: currentQuestionId,
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
                    updateStats(data.data);

                    if (!answersData[currentQuestionId]) {
                        answersData[currentQuestionId] = {};
                    }
                    answersData[currentQuestionId].selected_options = answer;
                    answersData[currentQuestionId].is_marked_for_review = isMarkedForReview;

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
                    updateQuestionButtonStatus(currentQuestionId, status);
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
            const submitMarked = document.getElementById('submit-marked');
            const submitUnanswered = document.getElementById('submit-unanswered');
            if (submitAnswered) submitAnswered.innerText = totalAnswered;
            if (submitMarked) submitMarked.innerText = marked;
            if (submitUnanswered) submitUnanswered.innerText = questionOrder.length - totalAnswered;

            const exitAnswered = document.getElementById('exit-answered');
            const exitMarked = document.getElementById('exit-marked');
            const exitRemaining = document.getElementById('exit-remaining');
            if (exitAnswered) exitAnswered.innerText = totalAnswered;
            if (exitMarked) exitMarked.innerText = marked;
            if (exitRemaining) exitRemaining.innerText = questionOrder.length - totalAnswered;
        }

        function startTimer() {
            timerInterval = setInterval(function() {
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    toastr.warning('Time is up! Submitting exam...');
                    submitExam();
                    return;
                }

                timeRemaining--;
                updateTimerDisplay();

                if (timeRemaining === 300) {
                    toastr.warning('5 minutes remaining!');
                    document.querySelector('.timer-display')?.classList.add('timer-warning');
                } else if (timeRemaining === 60) {
                    toastr.warning('1 minute remaining!');
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
            }, 30000);
        }

        function navigateToQuestion(direction) {
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

        function toggleMarkForReview() {
            const isMarked = document.getElementById('mark-review-text').innerText === 'Unmark Review';
            const newStatus = !isMarked;

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
                    if (newStatus) {
                        document.getElementById('mark-review-text').innerText = 'Unmark Review';
                        toastr.info('Marked for review');
                    } else {
                        document.getElementById('mark-review-text').innerText = 'Mark for Review';
                        toastr.info('Unmarked for review');
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

        function showQuestionPaper() {
            const modalBody = document.getElementById('questionPaperList');
            modalBody.innerHTML = '<div class="text-center py-4"><div class="loading-spinner"></div><p class="mt-2">Loading questions...</p></div>';

            const questionPaperModal = new bootstrap.Modal(document.getElementById('questionPaperModal'));
            questionPaperModal.show();

            const pendingIds = questionOrder.filter(id => !questionsData[id]);
            if (pendingIds.length > 0) {
                const promises = pendingIds.map(questionId => {
                    return fetch(`/student/exam/${studentExamId}/question/${questionId}`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    }).then(res => res.json());
                });

                Promise.all(promises).then(responses => {
                    responses.forEach(response => {
                        if (response.status === 'success') {
                            questionsData[response.data.id] = response.data;
                        }
                    });
                    renderQuestionPaper();
                });
            } else {
                renderQuestionPaper();
            }
        }

        function renderQuestionPaper() {
            let html = '<div class="list-group">';
            for (let i = 0; i < questionOrder.length; i++) {
                const qId = questionOrder[i];
                const answer = answersData[qId];

                let status = 'not-visited';
                let statusText = 'Not Visited';
                let statusClass = 'secondary';

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
                    <div class="modal-question-item d-flex align-items-center justify-content-between" onclick="loadQuestion(${qId}); bootstrap.Modal.getInstance(document.getElementById('questionPaperModal')).hide();">
                        <div>
                            <span class="badge bg-${statusClass} me-3">${i + 1}</span>
                            <span>Question ${i + 1}</span>
                        </div>
                        <div>
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

        function confirmSaveAndExit() {
            bootstrap.Modal.getInstance(document.getElementById('saveExitModal')).hide();
            saveCurrentAnswer(false);
            toastr.success('Progress saved. You can resume later.');
            setTimeout(() => {
                window.location.href = '{{ route("student.dashboard") }}';
            }, 1500);
        }

        function openSubmitModal() {
            const submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
            submitModal.show();
        }

        function submitExam() {
            bootstrap.Modal.getInstance(document.getElementById('submitModal')).hide();

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
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    fetch(`/student/exam/${studentExamId}/tab-switch`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                }
            });

            window.addEventListener('beforeunload', function(e) {
                if (!window.location.href.includes('result')) {
                    e.preventDefault();
                    e.returnValue = 'Are you sure you want to leave? Your answers will be saved but exam may be considered incomplete.';
                }
            });
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