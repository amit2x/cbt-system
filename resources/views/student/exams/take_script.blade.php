
@push('scripts')
<script>
     // ============================================
// DETECT BACK NAVIGATION
// ============================================
(function() {
    // Check if accessed via proper channel
    const accessToken = document.querySelector('meta[name="exam-access-token"]')?.content;
    
    if (accessToken === 'invalid') {
        // Page was loaded via back button or direct URL
        document.body.innerHTML = `
            <div style="display:flex;justify-content:center;align-items:center;height:100vh;background:#f5f5f5;font-family:Arial,sans-serif;">
                <div style="text-align:center;background:white;padding:40px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:500px;">
                    <h3 style="color:#dc3545;">⚠️ Invalid Access</h3>
                    <p style="color:#666;">This exam page cannot be accessed directly or via browser back button.</p>
                    <p style="color:#999;">Please resume your exam from the dashboard.</p>
                    <a href="{{ route('student.dashboard') }}" style="display:inline-block;margin-top:15px;padding:10px 25px;background:#1a237e;color:white;text-decoration:none;border-radius:5px;">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        `;
        
        // Also force redirect after 2 seconds
        setTimeout(() => {
            window.location.replace('{{ route("student.dashboard") }}');
        }, 3000);
        
        throw new Error('Invalid exam access - back navigation detected');
    }
})();


    
    // ============================================
    // EXAM DATA - Loaded from Server
    // ============================================
    // const studentExamId = {{ $studentExam->id }};
    // const questionOrder = @json($questionOrder);
    // let currentQuestionId = {{ $startQuestionId }};
    // let currentQuestionIndex = questionOrder.indexOf(currentQuestionId);
    // let autoSaveInterval;
    // let timerInterval;
    // let timeRemaining = {{ $remainingSeconds }};
    
    // // Load all questions data from server
    // let questionsData = @json($questionsData);
    
    // // Initialize local answers structure
    // let answersData = @json($answers);
    
    // // Queue for pending saves
    // let saveQueue = [];
    // let isProcessingQueue = false;
    
    // // Debounce timer for auto-save
    // let autoSaveDebounceTimer = null;
    
    // // Track last saved state to avoid duplicate saves
    // let lastSavedState = {};
    
    // let navigationButton = null;
    // let isSubmitting = false;
    // let isSaving = false;
    // let isAnswerSaving = false;
    
    // New optimized code:
const studentExamId = {{ $studentExam->id }};
const questionOrder = @json($questionOrder);
let currentQuestionId = {{ $startQuestionId }};
let currentQuestionIndex = questionOrder.indexOf(currentQuestionId);
let autoSaveInterval;
let timerInterval;
let timeRemaining = {{ $remainingSeconds }};

// Preloaded questions data from server
let questionsData = @json($questionsData);

// Preloaded answers data from server
let answersData = @json($answersData);

// Queue for pending saves
let saveQueue = [];
let isProcessingQueue = false;

// Debounce timer for auto-save
let autoSaveDebounceTimer = null;

// Track last saved state to avoid duplicate saves
let lastSavedState = {};

let currentQuestionData = null;
let navigationButton = null;
let isSubmitting = false;
let isSaving = false;
let isAnswerSaving = false;

let isPageRestoredFromBFCache = false;



    // Initialize answersData if not already structured
    if (!Array.isArray(answersData)) {
        const savedAnswers = Object.values(answersData);
        savedAnswers.forEach(answer => {
            if (answer.question_id) {
                answersData[answer.question_id] = {
                    is_visited: answer.is_visited || false,
                    selected_options: answer.selected_options || [],
                    is_marked_for_review: answer.is_marked_for_review || false
                };
            }
        });
    }

    // Initialize last saved state
    // questionOrder.forEach(qId => {
    //     const answer = answersData[qId] || {};
    //     lastSavedState[qId] = JSON.stringify({
    //         selected_options: answer.selected_options || [],
    //         is_marked_for_review: answer.is_marked_for_review || false
    //     });
    // });
    
    // Initialize last saved state
Object.keys(answersData).forEach(qId => {
    const answer = answersData[qId];
    lastSavedState[qId] = JSON.stringify({
        selected_options: answer.selected_options || [],
        is_marked_for_review: answer.is_marked_for_review || false
    });
});

// ============================================
// ✅ CLEAR OLD FLAGS ON PROPER PAGE LOAD
// ============================================
(function() {
    // When page loads via proper channel (dashboard resume), clear old flags
    // The server already validated this is a legitimate access
    const flags = [
        'exam_submitted_' + studentExamId,
        'exam_saved_exit_' + studentExamId,
        'exam_paused_' + studentExamId
    ];
    
    flags.forEach(flag => {
        sessionStorage.removeItem(flag);
        localStorage.removeItem(flag);
    });
    
    // Set a fresh initialized flag
    sessionStorage.setItem('exam_initialized_' + studentExamId, 'true');
})();
    // ============================================
    // DOM READY
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        if (isExamEnded()) {
            destroyExamPage();
            return;
        }
        
        console.log('Exam initialized with preloaded questions:', Object.keys(questionsData).length);
        
        applySavedFontSize();
        applySavedImageSize();
        updateQuestionButtons();
        updateStats();
        loadQuestion(currentQuestionId); // Now uses local data
        startTimer();
        startAutoSave();
        setupSecurity();
        
        // Language selector
        const drpLang = document.getElementById('drpLanguage');
        if (drpLang) {
            drpLang.addEventListener('change', function() {
                toastr.info('Language changed to ' + this.options[this.selectedIndex].text);
            });
        }
    });
    // ✅ DETECT BACK/FORWARD CACHE RESTORATION
    window.addEventListener('pageshow', function(event) {
        // If page was restored from bfcache (back/forward cache)
        if (event.persisted) {
            console.log('Page restored from back/forward cache');
            isPageRestoredFromBFCache = true;
            
            // Check if exam was ended
            if (isExamEnded()) {
                destroyExamPage();
                return;
            }
            
            // If exam was saved & exited, force redirect
            if (sessionStorage.getItem('exam_saved_exit_' + studentExamId) === 'true' ||
                localStorage.getItem('exam_saved_exit_' + studentExamId)) {
                destroyExamPage('Your exam progress has been saved. Please resume from the dashboard.');
                return;
            }
        }
    });
    
    // ✅ ADD VISIBILITY CHECK
    document.addEventListener('visibilitychange', function() {
        // When page becomes visible again
        if (!document.hidden) {
            // Check if exam was ended while hidden
            if (isExamEnded()) {
                destroyExamPage();
                return;
            }
            
            // If saved & exited, force redirect
            if (sessionStorage.getItem('exam_saved_exit_' + studentExamId) === 'true' ||
                localStorage.getItem('exam_saved_exit_' + studentExamId)) {
                destroyExamPage('Your exam progress has been saved. Please resume from the dashboard.');
                return;
            }
        }
    });
    
    
    // ============================================
    // HELPER: Check if exam has ended
    // ============================================
    function isExamEnded() {
        // ✅ Only check for saved_exit flag if it was set IN THIS SESSION
        // The initialized flag tells us this is a fresh session
        const isInitialized = sessionStorage.getItem('exam_initialized_' + studentExamId) === 'true';
        
        // If exam was submitted, always redirect
        if (sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true' ||
            localStorage.getItem('exam_submitted_' + studentExamId) !== null) {
            return true;
        }
        
        // If exam was saved & exited AND this is NOT a fresh session, redirect
        if ((sessionStorage.getItem('exam_saved_exit_' + studentExamId) === 'true' ||
             sessionStorage.getItem('exam_paused_' + studentExamId) === 'true' ||
             localStorage.getItem('exam_saved_exit_' + studentExamId) !== null) && !isInitialized) {
            return true;
        }
        
        return false;
    }
    // ============================================
    // HELPER: Destroy exam page and redirect
    // ============================================
    function destroyExamPage(message = null) {
        // Clear all intervals
        if (timerInterval) clearInterval(timerInterval);
        if (autoSaveInterval) clearInterval(autoSaveInterval);
        if (window._statusCheckInterval) clearInterval(window._statusCheckInterval);
        
        // Clear all flags
        clearAllExamFlags(studentExamId);
        
        // Default message
        if (!message) {
            if (sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true') {
                message = 'This exam has already been submitted.';
            } else {
                message = 'Your exam session has ended. Please resume from the dashboard.';
            }
        }
        
        // Show message
        toastr.warning(message);
        
        // Replace page content immediately
        document.body.innerHTML = `
            <div style="text-align:center;padding:100px;font-family:Arial,sans-serif;background:#f5f5f5;min-height:100vh;">
                <div style="max-width:500px;margin:0 auto;background:white;padding:40px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color:#1a237e;margin-bottom:15px;">
                        <i class="fas fa-info-circle"></i> Exam Session Ended
                    </h3>
                    <p style="color:#666;margin-bottom:20px;">${message}</p>
                    <p style="color:#999;font-size:14px;">Redirecting to dashboard...</p>
                    <div style="margin-top:20px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Force redirect
        setTimeout(() => {
            window.location.replace('{{ route("student.dashboard") }}');
        }, 2000);
    }
    
    // ============================================
    // CLEAR ALL FLAGS
    // ============================================
    function clearAllExamFlags(examId) {
        const flags = [
            'exam_submitted_' + examId,
            'exam_saved_exit_' + examId,
            'exam_paused_' + examId,
            'exam_initialized_' + examId
        ];
        
        flags.forEach(flag => {
            sessionStorage.removeItem(flag);
            localStorage.removeItem(flag);
        });
    }

    // ============================================
    // LOAD QUESTION - Now uses preloaded data
    // ============================================
    // function loadQuestion(questionId) {
    //     // Update current question tracking
    //     currentQuestionId = questionId;
    //     currentQuestionIndex = questionOrder.indexOf(questionId);

    //     // Save current question to server (lighter call)
    //     fetch(`/student/exam/${studentExamId}/save-current-question`, {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //         },
    //         body: JSON.stringify({ question_id: questionId })
    //     }).catch(error => console.error('Error saving current question:', error));

    //     // Update current button highlight
    //     document.querySelectorAll('.grid-btn').forEach(btn => btn.classList.remove('current'));
    //     const currentBtn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
    //     if (currentBtn) currentBtn.classList.add('current');

    //     // Mark as visited locally
    //     if (!answersData[currentQuestionId]) {
    //         answersData[currentQuestionId] = { 
    //             is_visited: true,
    //             selected_options: [],
    //             is_marked_for_review: false
    //         };
    //     } else if (!answersData[currentQuestionId].is_visited) {
    //         answersData[currentQuestionId].is_visited = true;
    //     }

    //     // Get question data from preloaded cache
    //     const questionData = questionsData[questionId];
        
    //     if (questionData) {
    //         // Load from local cache instantly
    //         currentQuestionData = questionData;
            
    //         // Merge with saved answers
    //         if (answersData[questionId]) {
    //             currentQuestionData.is_marked_for_review = answersData[questionId].is_marked_for_review || false;
    //             currentQuestionData.answer_text = answersData[questionId].answer_text || null;
                
    //             // Update options selected state
    //             const selectedOptions = answersData[questionId].selected_options || [];
    //             if (currentQuestionData.options) {
    //                 currentQuestionData.options = currentQuestionData.options.map(option => ({
    //                     ...option,
    //                     selected: selectedOptions.includes(option.id)
    //                 }));
    //             }
    //         }
            
    //         displayQuestion(currentQuestionData);
    //         updateQuestionButtons();
    //         updateStats();
            
    //         if (navigationButton) {
    //             hideButtonSpinner(navigationButton);
    //             navigationButton = null;
    //         }
    //     } else {
    //         // Fallback: fetch from server if not in cache (shouldn't happen)
    //         console.warn('Question not found in cache, fetching from server:', questionId);
    //         fetchQuestionFromServer(questionId);
    //     }
    // }
    // ============================================
// LOAD QUESTION - Uses preloaded data (No AJAX needed)
// ============================================
// function loadQuestion(questionId) {
//     // Update current question tracking
//     currentQuestionId = questionId;
//     currentQuestionIndex = questionOrder.indexOf(questionId);

//     // Save current question to server (light API call)
//     // saveCurrentQuestionToServer(questionId);

//     // Update current button highlight
//     document.querySelectorAll('.grid-btn').forEach(btn => btn.classList.remove('current'));
//     const currentBtn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
//     if (currentBtn) currentBtn.classList.add('current');

//     // Mark as visited locally
//     if (!answersData[questionId]) {
//         answersData[questionId] = { 
//             is_visited: true,
//             selected_options: [],
//             is_marked_for_review: false
//         };
//     } else if (!answersData[questionId].is_visited) {
//         answersData[questionId].is_visited = true;
//     }

//     // Get question data from preloaded cache
//     const questionData = questionsData[questionId];
    
//     if (questionData) {
//         // Load from local cache instantly
//         currentQuestionData = JSON.parse(JSON.stringify(questionData)); // Deep copy
        
//         // Merge with saved answers
//         if (answersData[questionId]) {
//             currentQuestionData.is_marked_for_review = answersData[questionId].is_marked_for_review || false;
//             currentQuestionData.answer_text = answersData[questionId].answer_text || null;
            
//             // Update options selected state
//             const selectedOptions = answersData[questionId].selected_options || [];
//             if (currentQuestionData.options) {
//                 currentQuestionData.options = currentQuestionData.options.map(option => ({
//                     ...option,
//                     selected: selectedOptions.includes(option.id)
//                 }));
//             }
//         }
        
//         displayQuestion(currentQuestionData);
//         updateQuestionButtons();
//         updateStats();
        
//         if (navigationButton) {
//             hideButtonSpinner(navigationButton);
//             navigationButton = null;
//         }
//     } else {
//         // Fallback: fetch from server if not in cache (shouldn't happen)
//         console.warn('Question not found in cache, fetching from server:', questionId);
//         fetchQuestionFromServerFallback(questionId);
//     }
// }

// ============================================
// LOAD QUESTION - With proper answer loading
// ============================================
function loadQuestion(questionId) {
    // Update current question tracking
    currentQuestionId = questionId;
    currentQuestionIndex = questionOrder.indexOf(questionId);

    // Save current question to server
    saveCurrentQuestionToServer(questionId);

    // Update current button highlight
    document.querySelectorAll('.grid-btn').forEach(btn => btn.classList.remove('current'));
    const currentBtn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
    if (currentBtn) currentBtn.classList.add('current');

    // Mark as visited locally
    if (!answersData[questionId]) {
        answersData[questionId] = { 
            is_visited: true,
            selected_options: [],
            is_marked_for_review: false,
            answer_text: ''
        };
    } else if (!answersData[questionId].is_visited) {
        answersData[questionId].is_visited = true;
    }

    // Get question data from preloaded cache
    const questionData = questionsData[questionId];
    
    if (questionData) {
        // Deep copy the question data
        currentQuestionData = JSON.parse(JSON.stringify(questionData));
        
        // Merge with saved answers from answersData
        const savedAnswer = answersData[questionId] || {};
        
        // Set answer_text if it exists
        currentQuestionData.answer_text = savedAnswer.answer_text || '';
        
        // Set is_marked_for_review
        currentQuestionData.is_marked_for_review = savedAnswer.is_marked_for_review || false;
        
        // Update options selected state
        const selectedOptions = savedAnswer.selected_options || [];
        if (currentQuestionData.options) {
            currentQuestionData.options = currentQuestionData.options.map(option => ({
                ...option,
                selected: selectedOptions.includes(option.id)
            }));
        }
        
        // Display the question
        displayQuestion(currentQuestionData);
        updateQuestionButtons();
        updateStats();
        
        if (navigationButton) {
            hideButtonSpinner(navigationButton);
            navigationButton = null;
        }
    } else {
        // Fallback: fetch from server
        console.warn('Question not found in cache, fetching from server:', questionId);
        fetchQuestionFromServerFallback(questionId);
    }
}

// Light API call to save current question position
function saveCurrentQuestionToServer(questionId) {
    fetch(`/student/exam/${studentExamId}/save-current-question`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ question_id: questionId })
    }).catch(error => console.error('Error saving current question:', error));
}

// Fallback fetch function (only if data not in cache)
function fetchQuestionFromServerFallback(questionId) {
    const questionTextEl = document.getElementById('question-text');
    if (questionTextEl) {
        questionTextEl.innerHTML = `
            <div class="text-center py-5">
                <div class="loading-spinner"></div>
                <p class="mt-2 text-muted">Loading question...</p>
            </div>
        `;
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
            // Cache the data locally
            questionsData[questionId] = data.data;
            currentQuestionData = data.data;
            displayQuestion(data.data);
            updateQuestionButtons();
            updateStats();
            if (navigationButton) {
                hideButtonSpinner(navigationButton);
                navigationButton = null;
            }
        }
    })
    .catch(error => {
        console.error('Error loading question:', error);
        toastr.error('Error loading question');
        if (navigationButton) {
            hideButtonSpinner(navigationButton);
            navigationButton = null;
        }
    });
}

    // Fallback fetch function
    function fetchQuestionFromServer(questionId) {
        const questionTextEl = document.getElementById('question-text');
        if (questionTextEl) {
            questionTextEl.innerHTML = `
                <div class="text-center py-5">
                    <div class="loading-spinner"></div>
                    <p class="mt-2 text-muted">Loading question...</p>
                </div>
            `;
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
                questionsData[questionId] = data.data;
                currentQuestionData = data.data;
                displayQuestion(data.data);
                updateQuestionButtons();
                updateStats();
                if (navigationButton) {
                    hideButtonSpinner(navigationButton);
                    navigationButton = null;
                }
            }
        })
        .catch(error => {
            console.error('Error loading question:', error);
            toastr.error('Error loading question');
            if (navigationButton) {
                hideButtonSpinner(navigationButton);
                navigationButton = null;
            }
        });
    }

    // ============================================
    // DISPLAY QUESTION (Unchanged - uses currentQuestionData)
    // ============================================
    // function displayQuestion(data) {
    //     // Update question number
    //     const questionNumberEl = document.getElementById('current-question-number');
    //     if (questionNumberEl) {
    //         questionNumberEl.innerText = currentQuestionIndex + 1;
    //     }

    //     // Question Text
    //     let questionHtml = '';
    //     if (data.image) {
    //         questionHtml += `<img alt="Question" src="${data.image}" data-questionno="${currentQuestionIndex + 1}" class="img-responsive MQ">`;
    //     }
    //     if (data.text) {
    //         questionHtml += `<div class="question-content">${data.text}</div>`;
    //     }
    //     if (!data.image && !data.text) {
    //         questionHtml = `<div class="text-center py-3 text-muted">No question content available</div>`;
    //     }
        
    //     const questionTextEl = document.getElementById('question-text');
    //     if (questionTextEl) {
    //         questionTextEl.innerHTML = questionHtml;
    //     }

    //     // Options Display - Read-only view with numbers
    //     let optionsDisplayHtml = '<div class="options-display-grid options-inline">';
    //     if (data.options && data.options.length > 0) {
    //         data.options.forEach((option, idx) => {
    //             const number = idx + 1;
    //             let optionContent = '';
    //             if (option.image) {
    //                 optionContent = `<img src="${option.image}" alt="Option ${number}" class="option-display-image">`;
    //             } else if (option.text) {
    //                 optionContent = `<span class="option-display-text">${option.text}</span>`;
    //             }
    //             optionsDisplayHtml += `
    //                 <div class="option-display-item" data-option-id="${option.id}">
    //                     <span class="option-display-label">${number}.</span>
    //                     <div class="option-display-content">${optionContent}</div>
    //                 </div>
    //             `;
    //         });
    //     } else {
    //         optionsDisplayHtml = `<div class="text-center py-2 text-muted">No options available</div>`;
    //     }
    //     optionsDisplayHtml += '</div>';
        
    //     const optionsDisplayEl = document.getElementById('options-display');
    //     if (optionsDisplayEl) {
    //         optionsDisplayEl.innerHTML = optionsDisplayHtml;
    //     }

    //     // Option Selectors
    //     const isMultiple = data.type === 'multiple_choice';
    //     const isNumerical = data.type === 'numerical';
    //     let selectorsHtml = '';
        
    //     if (isNumerical) {
    //         selectorsHtml = `
    //             <td colspan="4">
    //                 <div class="numerical-answer-wrapper">
    //                     <label class="form-label fw-bold">Your Answer:</label>
    //                     <input type="number" class="form-control form-control-sm numerical-input" 
    //                           id="numerical-input" value="${data.answer || ''}" step="any"
    //                           placeholder="Enter your numerical answer..." 
    //                           onchange="window.saveNumericalAnswer(this.value)">
    //                 </div>
    //             </td>
    //         `;
    //     } else if (data.options && data.options.length > 0) {
    //         const totalOptions = data.options.length;
    //         const colWidth = Math.floor(12 / totalOptions);
            
    //         data.options.forEach((option, idx) => {
    //             const number = idx + 1;
    //             const isSelected = option.selected || false;
    //             selectorsHtml += `
    //                 <td style="text-align: center; width: ${colWidth}%;">
    //                     <label class="option-selector ${isSelected ? 'selected' : ''}" data-option-id="${option.id}">
    //                         <input type="${isMultiple ? 'checkbox' : 'radio'}" 
    //                               name="question_${data.id}" 
    //                               value="${option.id}"
    //                               ${isSelected ? 'checked' : ''}
    //                               onchange="window.selectOption(${option.id})">
    //                         <span class="selector-label">${number} )</span>
    //                     </label>
    //                 </td>
    //             `;
    //         });
    //     }
        
    //     const selectorsRowEl = document.getElementById('option-selectors-row');
    //     if (selectorsRowEl) {
    //         selectorsRowEl.innerHTML = selectorsHtml;
    //     }

    //     // Update mark for review button text
    //     const markReviewText = document.getElementById('mark-review-text');
    //     if (markReviewText) {
    //         if (data.is_marked_for_review) {
    //             markReviewText.innerText = 'Unmark Review';
    //         } else {
    //             markReviewText.innerText = 'Mark for Review';
    //         }
    //     }
        
    //     setTimeout(function() {
    //         applySavedFontSize();
    //     }, 50);
    // }

// ============================================
// DISPLAY QUESTION - With Image Support
// ============================================
function displayQuestion(data) {
    // Update question number
    const questionNumberEl = document.getElementById('current-question-number');
    if (questionNumberEl) {
        questionNumberEl.innerText = currentQuestionIndex + 1;
    }

    // Question Text & Image
    let questionHtml = '';
    
    // Add question image first (or text first based on preference)
    if (data.image) {
        questionHtml += `<img src="${data.image}" alt="${data.image_alt || 'Question image'}" class="img-fluid mb-3 question_image " >`;
    }
    if (data.text) {
        questionHtml += `<div class="question-content">${data.text}</div>`;
    }
    if (!data.image && !data.text) {
        questionHtml = `<div class="text-center py-3 text-muted">No question content available</div>`;
    }
    
    const questionTextEl = document.getElementById('question-text');
    if (questionTextEl) {
        questionTextEl.innerHTML = questionHtml;
    }
    
    
    
    // ============================================
    // DETERMINE QUESTION TYPE
    // ============================================
    const isNumerical = data.type === 'numerical';
    const isMultiple = data.type === 'multiple_choice';
    
    // ============================================
    // GET SAVED ANSWER FOR THIS QUESTION
    // ============================================
    const savedAnswer = answersData[currentQuestionId] || {};
    const savedSelectedOptions = savedAnswer.selected_options || [];
    const savedAnswerText = savedAnswer.answer_text || '';
    
    // ============================================
    // OPTIONS DISPLAY - Skip for numerical questions
    // ============================================
    let optionsDisplayHtml = '';
    const optionsDisplayEl = document.getElementById('options-display');
    
    if (isNumerical) {
        // ✅ Numerical questions - Show message with saved answer
        const displayAnswer = savedAnswerText || '';
        optionsDisplayHtml = `
            <div class="text-center py-2 text-muted">
                This is a numerical answer question. Enter your answer below.
            </div>
        `;
        if (optionsDisplayEl) {
            optionsDisplayEl.innerHTML = optionsDisplayHtml;
        }
    } else {
        // ✅ Choice questions - Show options
        optionsDisplayHtml = '<div class="options-display-grid">';
        if (data.options && data.options.length > 0) {
            data.options.forEach((option, idx) => {
                const number = idx + 1;
                let optionContent = '';
                const isSelected = savedSelectedOptions.includes(option.id);
                
                // Show option image if available
                if (option.image && option.image !== 'null' && option.image !== '') {
                    optionContent += `<div class="question-image-container"><img src="${option.image}" alt="${option.image_alt || 'Option ' + number}" class="option-display-image mb-1 option_image"> </div>`;
                }
                // Show option text if available
                if (option.text) {
                    if (optionContent) {
                        optionContent += `<br>`;
                    }
                    optionContent += `<span class="option-display-text">${option.text}</span>`;
                }
                if (!option.image && !option.text) {
                    optionContent = `<span class="text-muted">Option ${number}</span>`;
                }
                
                optionsDisplayHtml += `
                    <div class="option-display-item ${isSelected ? 'selected-option' : ''}" data-option-id="${option.id}">
                        <span class="option-display-label">${number}.)</span>
                        <div class="option-display-content">${optionContent}</div>
                    </div>
                `;
            });
        } else {
            optionsDisplayHtml += `<div class="text-center py-2 text-muted">No options available</div>`;
        }
        optionsDisplayHtml += '</div>';
        
        if (optionsDisplayEl) {
            optionsDisplayEl.innerHTML = optionsDisplayHtml;
        }
    }

    // ============================================
    // OPTION SELECTORS - Skip for numerical questions
    // ============================================
    let selectorsHtml = '';
    const selectorsRowEl = document.getElementById('option-selectors-row');
    
    if (isNumerical) {
        // ✅ Numerical question - Show number input with saved value
        const numericValue = savedAnswerText || '';
        selectorsHtml = `
            <td colspan="4">
                <div class="numerical-answer-wrapper">
                    <label class="form-label fw-bold">Your Answer:</label>
                    <input type="number" class="form-control form-control-sm numerical-input" 
                           id="numerical-input" value="${numericValue}" step="any"
                           placeholder="Enter your numerical answer..." 
                           onchange="window.saveNumericalAnswer(this.value)">
                    ${numericValue ? `<small class="text-success ms-2">✓ Saved: ${numericValue}</small>` : ''}
                </div>
            </td>
        `;
    } else if (data.options && data.options.length > 0) {
        // ✅ Choice questions - Show option selectors with saved state
        const totalOptions = data.options.length;
        const colWidth = Math.floor(12 / totalOptions);
        
        data.options.forEach((option, idx) => {
            const number = idx + 1;
            const isSelected = savedSelectedOptions.includes(option.id);
            const inputType = isMultiple ? 'checkbox' : 'radio';
            const inputName = isMultiple ? `question_${data.id}_${option.id}` : `question_${data.id}`;
            
            // Build option label with image and text
            let optionLabel = '';
            if (option.image && option.image !== 'null' && option.image !== '') {
                optionLabel += `<img src="${option.image}" alt="${option.image_alt || 'Option ' + number}" class="option-image-selector">`;
            }
            if (option.text) {
                if (optionLabel) {
                    optionLabel += `<br>`;
                }
                optionLabel += `<span class="option-text-selector">${option.text}</span>`;
            }
            if (!option.image && !option.text) {
                optionLabel = `Option ${number}`;
            }
            
            selectorsHtml += `

                
                <td style="text-align: center; width: ${colWidth}%;">
                    <label class="option-selector ${isSelected ? 'selected' : ''}" data-option-id="${option.id}">
                        <input type="${isMultiple ? 'checkbox' : 'radio'}" 
                               name="question_${data.id}" 
                               value="${option.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="window.selectOption(${option.id})">
                        <span class="selector-label">${number} )</span>
                    </label>
                </td>
            `;
        });
    }
    
    if (selectorsRowEl) {
        selectorsRowEl.innerHTML = selectorsHtml;
    }

    // Update mark for review button text
    const markReviewText = document.getElementById('mark-review-text');
    if (markReviewText) {
        if (data.is_marked_for_review) {
            markReviewText.innerText = 'Unmark Review';
        } else {
            markReviewText.innerText = 'Mark for Review';
        }
    }
    
    setTimeout(function() {
        applySavedFontSize();
        applySavedImageSize();
    }, 50);
}
    
    // ============================================
    // SELECT OPTION (UI Only - Updates local state)
    // ============================================
    // function selectOption(optionId) {
    //     const questionType = currentQuestionData?.type || '';
        
    //     if (questionType === 'single_choice') {
    //         document.querySelectorAll('.option-selector').forEach(el => {
    //             el.classList.remove('selected');
    //             const input = el.querySelector('input');
    //             if (input) input.checked = false;
    //         });
            
    //         const selector = document.querySelector(`.option-selector[data-option-id="${optionId}"]`);
    //         if (selector) {
    //             selector.classList.add('selected');
    //             const input = selector.querySelector('input');
    //             if (input) input.checked = true;
    //         }
    //     } else if (questionType === 'multiple_choice') {
    //         const selector = document.querySelector(`.option-selector[data-option-id="${optionId}"]`);
    //         if (selector) {
    //             selector.classList.toggle('selected');
    //             const input = selector.querySelector('input');
    //             if (input) input.checked = !input.checked;
    //         }
    //     }
        
    //     // Auto-save on option change (debounced) // no need this
    //     // scheduleAutoSave();
    // }
    
function selectOption(optionId) {
    const questionType = currentQuestionData?.type || '';
    
    // Find the input element directly using its value attribute
    const inputElement = document.querySelector(`input[value="${optionId}"]`);
    if (!inputElement) return;

    // Find the closest label element wrapping this input for CSS styles
    const selector = inputElement.closest('.option-selector');
    
    if (questionType === 'single_choice') {
        // Clear the active styles from all other labels in the row
        document.querySelectorAll('.option-selector').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Add active style to the clicked label
        if (selector) selector.classList.add('selected');
        
    } else if (questionType === 'multiple_choice') {
        // Toggle active style based on the checkbox's actual state
        if (selector) {
            selector.classList.toggle('selected', inputElement.checked);
        }
    }
    
    // Trigger your change handler to save the data to your server/state
    if (typeof handleOptionChange === 'function') {
        handleOptionChange(inputElement, optionId);
    }
}

    

    // ============================================
    // SCHEDULE AUTO-SAVE (Debounced)
    // ============================================
    function scheduleAutoSave() {
        if (autoSaveDebounceTimer) {
            clearTimeout(autoSaveDebounceTimer);
        }
        autoSaveDebounceTimer = setTimeout(() => {
            saveCurrentAnswer(true);
        }, 2000); // 2 second debounce
    }

    // ============================================
    // GET SELECTED OPTIONS
    // ============================================
    function getSelectedOptions() {
        const selectedSelectors = document.querySelectorAll('.option-selector.selected');
        const options = [];
        selectedSelectors.forEach(el => {
            const optionId = parseInt(el.dataset.optionId);
            if (!isNaN(optionId)) {
                options.push(optionId);
            }
        });
        return options;
    }

    // ============================================
    // CHECK IF OPTION IS SELECTED
    // ============================================
    function isOptionSelected() {
        const questionType = currentQuestionData?.type || '';
        
        if (questionType === 'numerical') {
            const numInput = document.getElementById('numerical-input');
            return numInput && numInput.value !== '';
        } else {
            const selected = document.querySelectorAll('.option-selector.selected');
            return selected.length > 0;
        }
    }

    // ============================================
    // SAVE ANSWER - Optimized with local state
    // ============================================
    // function saveAnswer(answer, isAutoSave = false) {
    //     const questionId = currentQuestionId;
    //     const markReviewText = document.getElementById('mark-review-text');
    //     const isMarkedForReview = markReviewText ? markReviewText.innerText === 'Unmark Review' : false;

    //     // Check if state actually changed
    //     const currentState = JSON.stringify({
    //         selected_options: answer,
    //         is_marked_for_review: isMarkedForReview
    //     });
        
    //     if (currentState === lastSavedState[questionId] && isAutoSave) {
    //         // No changes, skip save
    //         return;
    //     }

    //     // Update local state immediately
    //     if (!answersData[questionId]) {
    //         answersData[questionId] = {};
    //     }
    //     answersData[questionId].selected_options = answer;
    //     answersData[questionId].is_marked_for_review = isMarkedForReview;

    //     // Update UI immediately
    //     let status = 'not-answered';
    //     if (isMarkedForReview && answer && answer.length > 0) {
    //         status = 'answered-marked';
    //     } else if (isMarkedForReview) {
    //         status = 'marked';
    //     } else if (answer && answer.length > 0) {
    //         status = 'answered';
    //     }
    //     updateQuestionButtonStatus(questionId, status);
    //     updateStats();

    //     // Update last saved state
    //     lastSavedState[questionId] = currentState;

    //     // Add to save queue
    //     addToSaveQueue({
    //         question_id: questionId,
    //         answer: answer,
    //         is_marked_for_review: isMarkedForReview
    //     });
    // }
    
    // ============================================
// SAVE ANSWER - Optimized with local state and save queue
// ============================================
// function saveAnswer(answer, isAutoSave = false) {
//     const questionId = currentQuestionId;
//     const markReviewText = document.getElementById('mark-review-text');
//     const isMarkedForReview = markReviewText ? markReviewText.innerText === 'Unmark Review' : false;

//     // Check if state actually changed
//     const currentState = JSON.stringify({
//         selected_options: Array.isArray(answer) ? answer : [answer],
//         is_marked_for_review: isMarkedForReview
//     });
    
//     if (currentState === lastSavedState[questionId] && isAutoSave) {
//         // No changes, skip save
//         return;
//     }

//     // Normalize answer to array
//     const normalizedAnswer = Array.isArray(answer) ? answer : [answer];

//     // Update local state immediately
//     if (!answersData[questionId]) {
//         answersData[questionId] = {};
//     }
//     answersData[questionId].selected_options = normalizedAnswer;
//     answersData[questionId].is_marked_for_review = isMarkedForReview;

//     // Update UI immediately
//     let status = 'not-answered';
//     if (isMarkedForReview && normalizedAnswer.length > 0) {
//         status = 'answered-marked';
//     } else if (isMarkedForReview) {
//         status = 'marked';
//     } else if (normalizedAnswer.length > 0) {
//         status = 'answered';
//     }
//     updateQuestionButtonStatus(questionId, status);
//     updateStats();

//     // Update last saved state
//     lastSavedState[questionId] = currentState;

//     // Add to save queue
//     addToSaveQueue({
//         question_id: questionId,
//         answer: normalizedAnswer,
//         is_marked_for_review: isMarkedForReview
//     });
// }

// ============================================
// SAVE ANSWER - With answer_text support
// ============================================
function saveAnswer(answer, isAutoSave = false, answerText = null) {
    const questionId = currentQuestionId;
    const markReviewText = document.getElementById('mark-review-text');
    const isMarkedForReview = markReviewText ? markReviewText.innerText === 'Unmark Review' : false;

    // Normalize answer to array
    const normalizedAnswer = Array.isArray(answer) ? answer : (answer ? [answer] : []);
    
    // Get answer_text
    let finalAnswerText = answerText;
    if (finalAnswerText === null && currentQuestionData?.type === 'numerical') {
        const numInput = document.getElementById('numerical-input');
        if (numInput) {
            finalAnswerText = numInput.value.toString().trim();
        }
    }

    // Check if state actually changed
    const currentState = JSON.stringify({
        selected_options: normalizedAnswer,
        answer_text: finalAnswerText || '',
        is_marked_for_review: isMarkedForReview
    });
    
    if (currentState === lastSavedState[questionId] && isAutoSave) {
        return;
    }

    // Update local state immediately
    if (!answersData[questionId]) {
        answersData[questionId] = {
            is_visited: true,
            selected_options: [],
            is_marked_for_review: false,
            answer_text: ''
        };
    }
    answersData[questionId].selected_options = normalizedAnswer;
    answersData[questionId].answer_text = finalAnswerText || '';
    answersData[questionId].is_marked_for_review = isMarkedForReview;
    answersData[questionId].is_visited = true;

    // Update UI immediately
    let status = 'not-answered';
    const hasAnswer = normalizedAnswer.length > 0 && normalizedAnswer[0] !== '' && normalizedAnswer[0] !== null;
    if (isMarkedForReview && hasAnswer) {
        status = 'answered-marked';
    } else if (isMarkedForReview) {
        status = 'marked';
    } else if (hasAnswer) {
        status = 'answered';
    }
    updateQuestionButtonStatus(questionId, status);
    updateStats();

    // Update last saved state
    lastSavedState[questionId] = currentState;

    // Add to save queue with answer_text
    addToSaveQueue({
        question_id: questionId,
        answer: normalizedAnswer,
        is_marked_for_review: isMarkedForReview,
        answer_text: finalAnswerText || '' // ✅ Always include answer_text
    });
}
// ============================================
// SAVE QUEUE MANAGEMENT
// ============================================
// function addToSaveQueue(saveData) {
//     // Remove any existing entry for this question
//     saveQueue = saveQueue.filter(item => item.question_id !== saveData.question_id);
    
//     // Add new entry
//     saveQueue.push(saveData);
    
//     // Process queue
//     processSaveQueue();
// }

// function processSaveQueue() {
//     if (isProcessingQueue || saveQueue.length === 0) return;
    
//     isProcessingQueue = true;
//     const saveData = saveQueue.shift();
    
//     fetch(`/student/exam/${studentExamId}/save-answer`, {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/json',
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//             'Accept': 'application/json'
//         },
//         body: JSON.stringify(saveData)
//     })
//     .then(response => response.json())
//     .then(data => {
//         isProcessingQueue = false;
        
//         if (data.status === 'success') {
//             // Update stats with server data if needed
//             if (data.data) {
//                 updateStats(data.data);
//             }
//         } else {
//             // On failure, re-add to queue
//             saveQueue.unshift(saveData);
//             console.warn('Save failed, re-queued:', saveData);
//         }
        
//         // Process next item in queue
//         if (saveQueue.length > 0) {
//             setTimeout(() => processSaveQueue(), 100);
//         }
//     })
//     .catch(error => {
//         isProcessingQueue = false;
//         // On error, re-add to queue
//         saveQueue.unshift(saveData);
//         console.error('Error saving answer:', error);
        
//         // Retry after delay
//         if (saveQueue.length > 0) {
//             setTimeout(() => processSaveQueue(), 2000);
//         }
//     });
// }

// ============================================
// ADD TO SAVE QUEUE - With answer_text support
// ============================================
function addToSaveQueue(saveData) {
    // Remove any existing entry for this question
    saveQueue = saveQueue.filter(item => item.question_id !== saveData.question_id);
    
    // Add new entry
    saveQueue.push(saveData);
    
    // Process queue
    processSaveQueue();
}

// ============================================
// PROCESS SAVE QUEUE - With answer_text support
// ============================================
function processSaveQueue() {
    if (isProcessingQueue || saveQueue.length === 0) return;
    
    isProcessingQueue = true;
    const saveData = saveQueue.shift();
    
    // Build request data
    const requestData = {
        question_id: saveData.question_id,
        answer: saveData.answer || [],
        is_marked_for_review: saveData.is_marked_for_review || false,
    };
    
    // ✅ Add answer_text for numerical questions
    if (saveData.answer_text !== undefined) {
        requestData.answer_text = saveData.answer_text;
    } else if (currentQuestionData && currentQuestionData.type === 'numerical') {
        // If answer_text not provided but it's a numerical question, use the first answer
        const numAnswer = saveData.answer && saveData.answer.length > 0 ? saveData.answer[0] : '';
        requestData.answer_text = numAnswer;
    }
    
    fetch(`/student/exam/${studentExamId}/save-answer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        isProcessingQueue = false;
        
        if (data.status === 'success') {
            if (data.data) {
                updateStats(data.data);
            }
        } else {
            // On failure, re-add to queue
            saveQueue.unshift(saveData);
            console.warn('Save failed, re-queued:', saveData);
        }
        
        // Process next item in queue
        if (saveQueue.length > 0) {
            setTimeout(() => processSaveQueue(), 100);
        }
    })
    .catch(error => {
        isProcessingQueue = false;
        saveQueue.unshift(saveData);
        console.error('Error saving answer:', error);
        
        if (saveQueue.length > 0) {
            setTimeout(() => processSaveQueue(), 2000);
        }
    });
}

    // ============================================
    // SAVE QUEUE MANAGEMENT
    // ============================================
    // function addToSaveQueue(saveData) {
    //     // Remove any existing entry for this question
    //     saveQueue = saveQueue.filter(item => item.question_id !== saveData.question_id);
        
    //     // Add new entry
    //     saveQueue.push(saveData);
        
    //     // Process queue
    //     processSaveQueue();
    // }

    // function processSaveQueue() {
    //     if (isProcessingQueue || saveQueue.length === 0) return;
        
    //     isProcessingQueue = true;
    //     const saveData = saveQueue.shift();
        
    //     fetch(`/student/exam/${studentExamId}/save-answer`, {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    //             'Accept': 'application/json'
    //         },
    //         body: JSON.stringify(saveData)
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         isProcessingQueue = false;
            
    //         if (data.status === 'success') {
    //             // Update stats with server data if needed
    //             if (data.data) {
    //                 updateStats(data.data);
    //             }
    //         } else {
    //             // On failure, re-add to queue
    //             saveQueue.unshift(saveData);
    //         }
            
    //         // Process next item in queue
    //         if (saveQueue.length > 0) {
    //             setTimeout(() => processSaveQueue(), 100);
    //         }
    //     })
    //     .catch(error => {
    //         isProcessingQueue = false;
    //         // On error, re-add to queue
    //         saveQueue.unshift(saveData);
    //         console.error('Error saving answer:', error);
    //     });
    // }

    // ============================================
    // SAVE CURRENT ANSWER
    // ============================================
    function saveCurrentAnswer(isAutoSave = false) {
        const questionType = currentQuestionData?.type || '';
        let answer = null;
        
        if (questionType === 'single_choice' || questionType === 'multiple_choice') {
            answer = getSelectedOptions();
        } else if (questionType === 'numerical') {
            const numInput = document.getElementById('numerical-input');
            if (numInput) {
                answer = numInput.value;
            }
        }
        
        if (answer !== null && answer !== undefined) {
            saveAnswer(answer, isAutoSave);
            return true;
        }
        return false;
    }

    // ============================================
    // SAVE AND NEXT
    // ============================================
    function saveAndNext(button) {
        const hasAnswer = isOptionSelected();
        
        if (!hasAnswer) {
            toastr.warning('Please select an option first');
            return;
        }
        
        saveCurrentAnswer(false);
        setTimeout(() => {
            if (currentQuestionIndex < questionOrder.length - 1) {
                loadQuestion(questionOrder[currentQuestionIndex + 1]);
            } else {
                toastr.info('This is the last question');
            }
        }, 100);
    }

    // ============================================
    // SAVE AND MARK FOR REVIEW
    // ============================================
    function saveAndMarkForReview(button) {
        const hasAnswer = isOptionSelected();
        
        if (!hasAnswer) {
            toastr.warning('Please select an option first to save and mark for review');
            return;
        }
        
        const questionType = currentQuestionData?.type || '';
        let answer = null;
        
        if (questionType === 'single_choice' || questionType === 'multiple_choice') {
            answer = getSelectedOptions();
        } else if (questionType === 'numerical') {
            const numInput = document.getElementById('numerical-input');
            if (numInput) {
                answer = numInput.value;
            }
        }
        
        if (answer !== null && answer !== undefined && answer !== '') {
            saveAnswerWithMarkReview(answer, true);
        }
    }

    function saveAnswerWithMarkReview(answer, markForReview = true) {
        const questionId = currentQuestionId;
        
        // Update local state immediately
        if (!answersData[questionId]) {
            answersData[questionId] = {};
        }
        answersData[questionId].selected_options = answer;
        answersData[questionId].is_marked_for_review = markForReview;

        // Update UI
        const markReviewText = document.getElementById('mark-review-text');
        if (markReviewText) {
            markReviewText.innerText = 'Unmark Review';
        }

        let status = 'answered-marked';
        updateQuestionButtonStatus(questionId, status);
        updateStats();

        // Add to save queue
        addToSaveQueue({
            question_id: questionId,
            answer: answer,
            is_marked_for_review: markForReview
        });

        toastr.success('Answer saved and marked for review');
        
        // Navigate to next question
        setTimeout(() => {
            if (currentQuestionIndex < questionOrder.length - 1) {
                loadQuestion(questionOrder[currentQuestionIndex + 1]);
            } else {
                toastr.info('This is the last question');
            }
        }, 100);
    }

    // ============================================
    // MARK FOR REVIEW AND NEXT (No save)
    // ============================================
    function markForReviewAndNext(button) {
        const hasAnswer = isOptionSelected();
        
        if (hasAnswer) {
            clearAnswer();
            toastr.warning('⚠️ You have selected an answer, but it will NOT be saved. Mark for Review only marks the question for review without saving your answer.', 5000);
        }
        
        const questionId = currentQuestionId;
        
        // Update local state - mark for review only
        if (!answersData[questionId]) {
            answersData[questionId] = {
                is_visited: true,
                selected_options: [],
                is_marked_for_review: true
            };
        } else {
            answersData[questionId].is_marked_for_review = true;
        }

        // Update UI
        const markReviewText = document.getElementById('mark-review-text');
        if (markReviewText) {
            markReviewText.innerText = 'Unmark Review';
        }

        let status = 'marked';
        updateQuestionButtonStatus(questionId, status);
        updateStats();

        // Add to save queue - only mark for review
        addToSaveQueue({
            question_id: questionId,
            answer: answersData[questionId].selected_options || [],
            // answer: [],
            is_marked_for_review: true
        });

        toastr.info('Question marked for review');
        
        // Navigate to next question
        setTimeout(() => {
            if (currentQuestionIndex < questionOrder.length - 1) {
                loadQuestion(questionOrder[currentQuestionIndex + 1]);
            } else {
                toastr.info('This is the last question');
            }
        }, 100);
    }


    // ============================================
    // CLEAR ANSWER
    // ============================================
    function clearAnswer() {
        if (currentQuestionData) {
            if (currentQuestionData.type === 'single_choice' || currentQuestionData.type === 'multiple_choice') {
                document.querySelectorAll('.option-selector').forEach(el => {
                    el.classList.remove('selected');
                    const input = el.querySelector('input');
                    if (input) input.checked = false;
                });
                saveAnswer([]);
                toastr.info('Answer cleared');
            } else if (currentQuestionData.type === 'numerical') {
                const numInput = document.getElementById('numerical-input');
                if (numInput) {
                    numInput.value = '';
                    saveAnswer('');
                    toastr.info('Answer cleared');
                }
            }
        }
    }

    // ============================================
    // NAVIGATE TO QUESTION
    // ============================================
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
        
        setTimeout(() => {
            if (direction === 'next' && currentQuestionIndex < questionOrder.length - 1) {
                loadQuestion(questionOrder[currentQuestionIndex + 1]);
            } else if (direction === 'prev' && currentQuestionIndex > 0) {
                loadQuestion(questionOrder[currentQuestionIndex - 1]);
            }
        }, 50);
    }

    // ============================================
    // TOGGLE MARK FOR REVIEW (Standalone)
    // ============================================
    function toggleMarkForReview(button) {
        const markReviewText = document.getElementById('mark-review-text');
        const isMarked = markReviewText ? markReviewText.innerText === 'Unmark Review' : false;
        const newStatus = !isMarked;
        
        navigationButton = button;
        showButtonSpinner(button);

        // Update local state
        if (answersData[currentQuestionId]) {
            answersData[currentQuestionId].is_marked_for_review = newStatus;
        }

        if (markReviewText) {
            markReviewText.innerText = newStatus ? 'Unmark Review' : 'Mark for Review';
        }

        let status = 'not-answered';
        const hasAnswer = answersData[currentQuestionId]?.selected_options?.length > 0;
        if (newStatus && hasAnswer) {
            status = 'answered-marked';
        } else if (newStatus) {
            status = 'marked';
        } else if (hasAnswer) {
            status = 'answered';
        }
        updateQuestionButtonStatus(currentQuestionId, status);
        updateStats();

        // Add to save queue
        addToSaveQueue({
            question_id: currentQuestionId,
            answer: answersData[currentQuestionId]?.selected_options || [],
            is_marked_for_review: newStatus
        });

        hideButtonSpinner(navigationButton);
        navigationButton = null;
        toastr.info(newStatus ? 'Marked for review' : 'Unmarked for review');
    }

    // ============================================
    // UPDATE QUESTION BUTTON STATUS
    // ============================================
    function updateQuestionButtonStatus(questionId, status) {
        const btn = document.querySelector(`.grid-btn[data-question-id="${questionId}"]`);
        if (btn) {
            btn.className = 'grid-btn question-btn ' + status;
        }
    }

    // ============================================
    // UPDATE QUESTION BUTTONS
    // ============================================
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
                    status = 'not-answered';
                }
            }
            updateQuestionButtonStatus(qId, status);
        }
    }

    // ============================================
    // UPDATE STATS
    // ============================================
    function updateStats(data = null) {
        let answered = 0, marked = 0, answeredMarked = 0, notAnswered = 0, notVisited = 0;

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
                    notAnswered++;
                } else {
                    notVisited++;
                }
            } else {
                notVisited++;
            }
        }

        const totalQuestions = questionOrder.length;
        const totalAnswered = answered + answeredMarked;

        // Update stat elements
        const statElements = {
            'stat-answered-count': answered,
            'stat-marked-count': marked,
            'stat-answered-marked-count': answeredMarked,
            'stat-not-answered-count': notAnswered,
            'stat-not-visited-count': notVisited
        };
        
        Object.keys(statElements).forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = statElements[id];
        });

        // Progress
        const progressPercent = totalQuestions > 0 ? (totalAnswered / totalQuestions) * 100 : 0;
        const progressEl = document.getElementById('progress-percentage');
        const progressFillEl = document.getElementById('progress-bar-fill');
        if (progressEl) progressEl.innerText = Math.round(progressPercent) + '%';
        if (progressFillEl) progressFillEl.style.width = progressPercent + '%';

        // Update modals
        const modalElements = {
            'submit-answered': totalAnswered,
            'submit-answered-review': answeredMarked,
            'submit-marked': marked,
            'submit-not-visited': notVisited,
            'submit-unanswered': totalQuestions - totalAnswered,
            'exit-answered': totalAnswered,
            'exit-marked': marked,
            'exit-answered-review': answeredMarked,
            'exit-not-visited': notVisited,
            'exit-remaining': totalQuestions - totalAnswered
        };
        
        Object.keys(modalElements).forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = modalElements[id];
        });
    }

    // ============================================
    // TIMER (Unchanged)
    // ============================================
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
            if (timeRemaining === 1800) {
                toastr.warning('30 minutes remaining!');
            }
            if (timeRemaining === 300) {
                toastr.warning('5 minutes remaining!');
            }
            if (timeRemaining === 60) {
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
    
    function syncRemainingTime() {
        fetch(`/student/exam/${studentExamId}/sync-timer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                remaining_seconds: timeRemaining
            })
        });
    }
    
    setInterval(function() {
        syncRemainingTime();
    }, 30000);

    // ============================================
    // AUTO SAVE - Reduced frequency
    // ============================================
    // function startAutoSave() {
    //     autoSaveInterval = setInterval(function() {
    //         // Process any pending saves in queue
    //         if (saveQueue.length > 0) {
    //             processSaveQueue();
    //         }
            
    //         // Sync timer
    //         syncRemainingTime();
    //     }, 60000); // Every 60 seconds instead of 30
    // }
    
    // ============================================
// AUTO SAVE - Reduced frequency since we save on change
// ============================================
function startAutoSave() {
    autoSaveInterval = setInterval(function() {
        // Process any pending saves in queue
        if (saveQueue.length > 0) {
            processSaveQueue();
        }
        
        // Save current question answer if changed
        saveCurrentAnswer(true);
        
        // Sync timer with server
        syncRemainingTime();
    }, 60000); // Every 60 seconds
}

    // ============================================
    // SAVE NUMERICAL ANSWER
    // ============================================
    // function saveNumericalAnswer(value) {
    //     saveAnswer(value);
    // }
    
    // ============================================
// SAVE NUMERICAL ANSWER - Fixed
// ============================================
// ============================================
// SAVE NUMERICAL ANSWER - Fixed
// ============================================
function saveNumericalAnswer(value) {
    const questionId = currentQuestionId;
    
    if (!currentQuestionData || currentQuestionData.type !== 'numerical') {
        return;
    }
    
    // Initialize answersData for this question if not exists
    if (!answersData[questionId]) {
        answersData[questionId] = {
            is_visited: true,
            selected_options: [],
            is_marked_for_review: false,
            answer_text: ''
        };
    }
    
    // Update the answer
    const trimmedValue = value !== null && value !== undefined ? value.toString().trim() : '';
    answersData[questionId].selected_options = trimmedValue ? [trimmedValue] : [];
    answersData[questionId].answer_text = trimmedValue;
    answersData[questionId].is_visited = true;
    
    // Update question button status
    let status = 'not-answered';
    if (answersData[questionId].is_marked_for_review && trimmedValue) {
        status = 'answered-marked';
    } else if (answersData[questionId].is_marked_for_review) {
        status = 'marked';
    } else if (trimmedValue) {
        status = 'answered';
    }
    updateQuestionButtonStatus(questionId, status);
    updateStats();
    
    // ✅ Add to save queue with answer_text
    addToSaveQueue({
        question_id: questionId,
        answer: trimmedValue ? [trimmedValue] : [],
        is_marked_for_review: answersData[questionId].is_marked_for_review || false,
        answer_text: trimmedValue // ✅ Explicitly set answer_text
    });
    
    // Update the display to show saved indicator
    const numInput = document.getElementById('numerical-input');
    if (numInput) {
        numInput.value = trimmedValue;
    }
    
    // Show saved indicator
    const wrapper = document.querySelector('.numerical-answer-wrapper');
    if (wrapper) {
        // Remove existing indicator
        const existingIndicator = wrapper.querySelector('.text-success');
        if (existingIndicator) {
            existingIndicator.remove();
        }
        
        if (trimmedValue) {
            const indicator = document.createElement('small');
            indicator.className = 'text-success ms-2';
            indicator.textContent = `✓ Saved: ${trimmedValue}`;
            wrapper.appendChild(indicator);
        }
    }
}
    // ============================================
    // SCROLL FUNCTIONS
    // ============================================
    function scrollToTop() {
        const questionBody = document.getElementById('questionBody');
        if (questionBody) {
            questionBody.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    }

    function scrollToBottom() {
        const questionBody = document.getElementById('questionBody');
        if (questionBody) {
            questionBody.scrollTo({
                top: questionBody.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    // ============================================
    // TOGGLE PALETTE
    // ============================================
    function togglePalette() {
        const palette = document.getElementById('paletteArea');
        const questionArea = document.getElementById('questionArea');
        const toggleBtn = document.getElementById('paletteToggleBtn');
        
        if (!palette || !questionArea || !toggleBtn) {
            console.error('Elements not found');
            return;
        }
        
        const isHidden = palette.style.display === 'none';
        
        if (isHidden) {
            palette.style.display = 'block';
            questionArea.className = 'col-lg-8';
            toggleBtn.classList.remove('collapsed');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            palette.classList.remove('palette-hidden');
        } else {
            palette.style.display = 'none';
            questionArea.className = 'col-lg-12';
            toggleBtn.classList.add('collapsed');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            palette.classList.add('palette-hidden');
        }
    }

    // ============================================
    // SHOW QUESTION PAPER (Now uses local data)
    // ============================================
    function showQuestionPaper() {
        renderQuestionPaper();
        const modal = new bootstrap.Modal(document.getElementById('questionPaperModal'));
        modal.show();
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
                    status = 'marked';
                    statusText = 'Marked for Review';
                    statusClass = 'warning';
                } else if (answer.selected_options && answer.selected_options.length > 0) {
                    status = 'answered';
                    statusText = 'Answered';
                    statusClass = 'success';
                } else if (answer.is_visited) {
                    status = 'not-answered';
                    statusText = 'Not Answered';
                    statusClass = 'danger';
                }
            }

            html += `
                <div class="modal-question-item d-flex align-items-center justify-content-between p-2"
                     onclick="window.loadQuestion(${qId}); bootstrap.Modal.getInstance(document.getElementById('questionPaperModal')).hide();"
                     style="cursor: pointer;">
                    <div class="d-flex align-items-center me-3" style="max-width: 75%;">
                        <span class="badge bg-${statusClass} me-3">${String(i + 1).padStart(2, '0')}</span>
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

    // ============================================
    // INSTRUCTIONS
    // ============================================
    function showInstructions() {
        const modal = new bootstrap.Modal(document.getElementById('instructionsModal'));
        modal.show();
    }

    // ============================================
    // SAVE & EXIT
    // ============================================
    function saveAndExit() {
        // Process all pending saves before exit
        processSaveQueue();
        
        const modal = new bootstrap.Modal(document.getElementById('saveExitModal'));
        modal.show();
    }

    // ============================================
    // SUBMIT EXAM
    // ============================================
    function openSubmitModal() {
        const modal = new bootstrap.Modal(document.getElementById('submitModal'));
        modal.show();
    }


function submitExam(closeModal = true) {
    if (closeModal) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('submitModal'));
        if (modal) modal.hide();
    }
    if (isSubmitting) return;
    isSubmitting = true;
    
    // Disable the question panel immediately
    document.querySelector('.question-panel').style.opacity = '0.5';
    document.querySelector('.question-panel').style.pointerEvents = 'none';
    
    // Clear intervals
    clearInterval(timerInterval);
    if (autoSaveInterval) clearInterval(autoSaveInterval);
    if (window._statusCheckInterval) clearInterval(window._statusCheckInterval);
    
    // ✅ SET FLAGS IMMEDIATELY
    sessionStorage.setItem('exam_submitted_' + studentExamId, 'true');
    localStorage.setItem('exam_submitted_' + studentExamId, Date.now().toString());
    
    // Show overlay
    showSubmitOverlay();
    
    // Process all pending saves first
    flushSaveQueue().then(() => {
        fetch(`/student/exam/${studentExamId}/submit`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                save_queue: saveQueue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.replace(data.data.redirect);
            } else {
                isSubmitting = false;
                sessionStorage.removeItem('exam_submitted_' + studentExamId);
                localStorage.removeItem('exam_submitted_' + studentExamId);
                toastr.error('Error submitting exam. Please try again.');
            }
        })
        .catch(error => {
            isSubmitting = false;
            sessionStorage.removeItem('exam_submitted_' + studentExamId);
            localStorage.removeItem('exam_submitted_' + studentExamId);
            toastr.error('Network error. Please try again.');
        });
    });
}

// ============================================
// SHOW SUBMIT OVERLAY
// ============================================
function showSubmitOverlay() {
    const existingOverlay = document.getElementById('submitOverlay');
    if (existingOverlay) existingOverlay.remove();
    
    const overlay = document.createElement('div');
    overlay.id = 'submitOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.95);
        z-index: 99999;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
    `;
    
    overlay.innerHTML = `
        <div style="text-align:center;">
            <h3 style="color:#1a237e;">Submitting Your Exam...</h3>
            <p style="color:#666;">Please wait while we submit your answers.</p>
            <div class="spinner-border text-primary mt-3" role="status">
                <span class="visually-hidden">Submitting...</span>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}
// Flush all pending saves
function flushSaveQueue() {
    return new Promise((resolve) => {
        if (saveQueue.length === 0) {
            resolve();
            return;
        }
        
        const checkQueue = setInterval(() => {
            if (saveQueue.length === 0 && !isProcessingQueue) {
                clearInterval(checkQueue);
                resolve();
            }
        }, 100);
        
        // Process queue
        processSaveQueue();
        
        // Timeout after 5 seconds
        setTimeout(() => {
            clearInterval(checkQueue);
            resolve();
        }, 5000);
    });
}

// function confirmSaveAndExit() {
//     if (isSaving) return;
//     isSaving = true;
    
//     // Process all pending saves first
//     flushSaveQueue().then(() => {
//         fetch(`/student/exam/${studentExamId}/save-exit`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//             },
//             body: JSON.stringify({
//                 current_question_id: currentQuestionId,
//                 remaining_seconds: timeRemaining
//             })
//         })
//         .then(response => response.json())
//         .then(data => {
//             toastr.success(data.message);
//             setTimeout(() => {
//                 // window.location.href = '{{ route("student.dashboard") }}';
//                 window.location.replace('{{ route("student.dashboard") }}');

//             }, 1000);
//         })
//         .catch(() => {
//             isSaving = false;
//             toastr.error('Error saving progress');
//         });
//     });
// }

// ============================================
// SAVE & EXIT - With back button prevention
// ============================================
// function confirmSaveAndExit() {
//     if (isSaving) return;
//     isSaving = true;
    
//     // Clear intervals
//     clearInterval(timerInterval);
//     if (autoSaveInterval) clearInterval(autoSaveInterval);
//     if (window._submissionCheckInterval) clearInterval(window._submissionCheckInterval);
    
//     // ✅ SET FLAGS for save & exit (different from submit)
//     sessionStorage.setItem('exam_saved_exit_' + studentExamId, 'true');
//     sessionStorage.setItem('exam_paused_' + studentExamId, 'true');
//     localStorage.setItem('exam_saved_exit_' + studentExamId, Date.now().toString());
    
//     // Process all pending saves first
//     flushSaveQueue().then(() => {
//         fetch(`/student/exam/${studentExamId}/save-exit`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//             },
//             body: JSON.stringify({
//                 current_question_id: currentQuestionId,
//                 remaining_seconds: timeRemaining
//             })
//         })
//         .then(response => response.json())
//         .then(data => {
//             toastr.success(data.message || 'Progress saved successfully');
            
//             // ✅ Use replace() to prevent back navigation
//             setTimeout(() => {
//                 window.location.replace('{{ route("student.dashboard") }}');
//             }, 1000);
//         })
//         .catch(() => {
//             // ❌ Clear flags on failure
//             isSaving = false;
//             sessionStorage.removeItem('exam_saved_exit_' + studentExamId);
//             sessionStorage.removeItem('exam_paused_' + studentExamId);
//             localStorage.removeItem('exam_saved_exit_' + studentExamId);
//             toastr.error('Error saving progress');
//         });
//     });
// }

function confirmSaveAndExit() {
    if (isSaving) return;
    isSaving = true;
    
    // ✅ IMMEDIATELY: Destroy the exam UI
    // This prevents any interaction after clicking save & exit
    document.querySelector('.question-panel').style.opacity = '0.5';
    document.querySelector('.question-panel').style.pointerEvents = 'none';
    
    // Clear intervals
    clearInterval(timerInterval);
    if (autoSaveInterval) clearInterval(autoSaveInterval);
    if (window._statusCheckInterval) clearInterval(window._statusCheckInterval);
    
    // ✅ SET FLAGS IMMEDIATELY (before API call)
    sessionStorage.setItem('exam_saved_exit_' + studentExamId, 'true');
    sessionStorage.setItem('exam_paused_' + studentExamId, 'true');
    localStorage.setItem('exam_saved_exit_' + studentExamId, Date.now().toString());
    
    // ✅ Immediately show overlay
    showSaveExitOverlay();
    
    // Process all pending saves first
    flushSaveQueue().then(() => {
        fetch(`/student/exam/${studentExamId}/save-exit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                current_question_id: currentQuestionId,
                remaining_seconds: timeRemaining
            })
        })
        .then(response => response.json())
        .then(data => {
            // ✅ Force redirect immediately
            window.location.replace('{{ route("student.dashboard") }}');
        })
        .catch(() => {
            // Even on error, redirect (server already has the last save)
            window.location.replace('{{ route("student.dashboard") }}');
        });
    });
}

// ============================================
// SHOW SAVE & EXIT OVERLAY (prevents interaction)
// ============================================
function showSaveExitOverlay() {
    // Remove existing overlay if any
    const existingOverlay = document.getElementById('saveExitOverlay');
    if (existingOverlay) existingOverlay.remove();
    
    const overlay = document.createElement('div');
    overlay.id = 'saveExitOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.95);
        z-index: 99999;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
    `;
    
    overlay.innerHTML = `
        <div style="text-align:center;">
            <h3 style="color:#1a237e;">Saving Your Progress...</h3>
            <p style="color:#666;">Please wait while we save your exam progress.</p>
            <div class="spinner-border text-primary mt-3" role="status">
                <span class="visually-hidden">Saving...</span>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}
    // ============================================
    // SECURITY 
    // ============================================
    // function setupSecurity() {
    //     window.history.pushState(null, null, window.location.href);
    //     window.addEventListener('popstate', function(event) {
    //          // Check if exam was submitted
    //         const isSubmitted = sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true';
            
    //         if (isSubmitted) {
    //             // Force redirect to dashboard
    //             window.location.replace('{{ route("student.dashboard") }}');
    //             return;
    //         }
    //         window.history.pushState(null, null, window.location.href);
    //         alert('Back navigation is disabled during the examination.');
    //     });

    //     document.addEventListener('visibilitychange', function() {
    //         // Check if exam was submitted
    //         const isSubmitted = sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true';
            
    //         if (isSubmitted) {
    //             window.location.replace('{{ route("student.dashboard") }}');
    //             return;
    //         }
    //         if (document.hidden && !isSubmitting && !isSaving) {
    //             fetch(`/student/exam/${studentExamId}/tab-switch`, {
    //                 method: 'POST',
    //                 headers: {
    //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    //                     'Accept': 'application/json'
    //                 }
    //             });
    //             toastr.warning('Please do not switch tabs during the exam');
    //         }
    //     });

    //     window.addEventListener('beforeunload', function(e) {
    //         const isSubmitted = sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true';
    //         if (isSubmitted || isSubmitting || isSaving) return;
            
    //         // Check for unsaved answers
    //         const hasUnsaved = Object.keys(unsavedSelections).some(qId => 
    //             unsavedSelections[qId]?.selected_options?.length > 0
    //         );
            
    //         if (hasUnsaved) {
    //             e.preventDefault();
    //             e.returnValue = 'You have unsaved answers. Leave anyway?';
    //             return e.returnValue;
    //         }
            
    //         e.preventDefault();
    //         e.returnValue = 'You are currently taking an exam. Leaving will forfeit your progress.';
    //         return e.returnValue;
    //     });

    //     document.addEventListener('contextmenu', function(e) { 

    //         const isSubmitted = sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true';
    //         if (isSubmitted) {
    //             window.location.replace('{{ route("student.dashboard") }}');
    //             return;
    //         }
    //         e.preventDefault()
    //     });
    //     document.addEventListener('selectstart', function(e) { e.preventDefault(); });

    //     document.addEventListener('keydown', function(e) {
    //         const isSubmitted = sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true';
            
    //         if (isSubmitted) {
    //             window.location.replace('{{ route("student.dashboard") }}');
    //             return false;
    //         }
    //         if (e.key === 'F12') { e.preventDefault(); return false; }
    //         if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) {
    //             e.preventDefault();
    //             return false;
    //         }
    //         if ((e.ctrlKey || e.metaKey) && (e.key === 'U' || e.key === 'u')) {
    //             e.preventDefault();
    //             return false;
    //         }
    //         if ((e.ctrlKey || e.metaKey) && (e.key === 'C' || e.key === 'c' || e.key === 'V' || e.key === 'v' || e.key === 'X' || e.key === 'x')) {
    //             e.preventDefault();
    //             alert('Copying and pasting is disabled during the exam.');
    //             return false;
    //         }
    //         if ((e.ctrlKey || e.metaKey) && (e.key === 'P' || e.key === 'p')) {
    //             e.preventDefault();
    //             return false;
    //         }
    //         if ((e.ctrlKey || e.metaKey) && (e.key === 'S' || e.key === 's')) {
    //             e.preventDefault();
    //             return false;
    //         }
    //     });
    //      // Additional: Check page visibility periodically
    //     setInterval(() => {
    //         const isSubmitted = sessionStorage.getItem('exam_submitted_' + studentExamId) === 'true';
    //         if (isSubmitted && !isSubmitting) {
    //             window.location.replace('{{ route("student.dashboard") }}');
    //         }
    //     }, 5000);
    // }

// ============================================
// SECURITY - Enhanced with proper back button prevention
// ============================================
// ============================================
// SECURITY - Handles both Submit and Save & Exit
// ============================================
// ============================================
// SECURITY - With BFCache detection
// ============================================
// function setupSecurity() {
//     // Set flag for fresh page load
//     if (!sessionStorage.getItem('exam_initialized_' + studentExamId)) {
//         sessionStorage.setItem('exam_initialized_' + studentExamId, 'true');
//         window.history.replaceState({exam_id: studentExamId, page: 'exam'}, '', window.location.href);
//     }
    
//     window.history.pushState({exam_id: studentExamId, page: 'exam'}, '', window.location.href);
    
//     // Handle back/forward button
//     window.addEventListener('popstate', function(event) {
//         // ✅ IMMEDIATE CHECK
//         if (isExamEnded()) {
//             destroyExamPage();
//             return;
//         }
        
//         // Prevent going back during active exam
//         window.history.pushState({exam_id: studentExamId, page: 'exam'}, '', window.location.href);
//         toastr.warning('Back navigation is disabled during the examination.');
//     });

//     // Tab visibility
//     document.addEventListener('visibilitychange', function() {
//         if (document.hidden) {
//             if (!isSubmitting && !isSaving) {
//                 fetch(`/student/exam/${studentExamId}/tab-switch`, {
//                     method: 'POST',
//                     headers: {
//                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//                         'Accept': 'application/json'
//                     }
//                 }).catch(() => {});
//                 toastr.warning('Please do not switch tabs during the exam');
//             }
//         } else {
//             // ✅ When tab becomes visible, check if exam ended
//             if (isExamEnded()) {
//                 destroyExamPage();
//             }
//         }
//     });

//     // Before unload
//     window.addEventListener('beforeunload', function(e) {
//         if (isExamEnded() || isSubmitting || isSaving) return;
        
//         e.preventDefault();
//         e.returnValue = 'You are currently taking an exam. Use Save & Exit to save your progress.';
//         return e.returnValue;
//     });

//     // Right-click
//     document.addEventListener('contextmenu', function(e) { 
//         if (isExamEnded()) {
//             destroyExamPage();
//             return;
//         }
//         e.preventDefault();
//         return false;
//     });
    
//     // Text selection
//     document.addEventListener('selectstart', function(e) { 
//         e.preventDefault(); 
//         return false;
//     });

//     // Keyboard shortcuts
//     document.addEventListener('keydown', function(e) {
//         if (isExamEnded()) {
//             destroyExamPage();
//             return false;
//         }
        
//         if (e.key === 'F12' || e.keyCode === 123) { e.preventDefault(); return false; }
//         if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) {
//             e.preventDefault(); return false;
//         }
//         if ((e.ctrlKey || e.metaKey) && (e.key === 'U' || e.key === 'u')) {
//             e.preventDefault(); return false;
//         }
//         if ((e.ctrlKey || e.metaKey) && (e.key === 'C' || e.key === 'c' || e.key === 'V' || e.key === 'v' || e.key === 'X' || e.key === 'x')) {
//             e.preventDefault(); return false;
//         }
//         if ((e.ctrlKey || e.metaKey) && (e.key === 'P' || e.key === 'p')) {
//             e.preventDefault(); return false;
//         }
//     });
    
//     // Periodic check
//     const statusCheckInterval = setInterval(() => {
//         if (isExamEnded() && !isSubmitting && !isSaving) {
//             clearInterval(statusCheckInterval);
//             destroyExamPage();
//         }
//     }, 2000); // Check every 2 seconds
    
//     window._statusCheckInterval = statusCheckInterval;
// }

function setupSecurity() 
// {}
{
    // Unique channel name per exam
    const channelName = 'exam_security_channel_' + studentExamId;
    const channel = new BroadcastChannel(channelName);

    // Broadcast to other tabs that this new tab is now the active one
    channel.postMessage({ type: 'NEW_TAB_OPENED', timestamp: Date.now() });

    // Listen for messages from other tabs
    channel.onmessage = function(event) {
        if (event.data.type === 'NEW_TAB_OPENED') {
            // An older tab receives this: kick the user out of this old tab
            clearInterval(window._statusCheckInterval);
            channel.close();
            
            // Show alert and redirect/destroy page
            alert('This exam was opened in another tab. This tab will now close.');
            destroyExamPage(); 
        }
    };

    // Set flag for fresh page load
    if (!sessionStorage.getItem('exam_initialized_' + studentExamId)) {
        sessionStorage.setItem('exam_initialized_' + studentExamId, 'true');
        window.history.replaceState({exam_id: studentExamId, page: 'exam'}, '', window.location.href);
    }
    
    window.history.pushState({exam_id: studentExamId, page: 'exam'}, '', window.location.href);
    
    // Handle back/forward button
    window.addEventListener('popstate', function(event) {
        if (isExamEnded()) {
            destroyExamPage();
            return;
        }
        window.history.pushState({exam_id: studentExamId, page: 'exam'}, '', window.location.href);
        toastr.warning('Back navigation is disabled during the examination.');
    });

    // Tab visibility
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (!isSubmitting && !isSaving) {
                fetch(`/student/exam/${studentExamId}/tab-switch`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).catch(() => {});
                toastr.warning('Please do not switch tabs during the exam');
            }
        } else {
            if (isExamEnded()) {
                destroyExamPage();
            }
        }
    });

    // Before unload
    window.addEventListener('beforeunload', function(e) {
        if (isExamEnded() || isSubmitting || isSaving) return;
        
        e.preventDefault();
        e.returnValue = 'You are currently taking an exam. Use Save & Exit to save your progress.';
        return e.returnValue;
    });

    // Right-click
    document.addEventListener('contextmenu', function(e) { 
        if (isExamEnded()) {
            destroyExamPage();
            return;
        }
        e.preventDefault();
        return false;
    });
    
    // Text selection
    document.addEventListener('selectstart', function(e) { 
        e.preventDefault(); 
        return false;
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (isExamEnded()) {
            destroyExamPage();
            return false;
        }
        
        if (e.key === 'F12' || e.keyCode === 123) { e.preventDefault(); return false; }
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) {
            e.preventDefault(); return false;
        }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'U' || e.key === 'u')) {
            e.preventDefault(); return false;
        }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'C' || e.key === 'c' || e.key === 'V' || e.key === 'v' || e.key === 'X' || e.key === 'x')) {
            e.preventDefault(); return false;
        }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'P' || e.key === 'p')) {
            e.preventDefault(); return false;
        }
    });
    
    // Periodic check
    const statusCheckInterval = setInterval(() => {
        if (isExamEnded() && !isSubmitting && !isSaving) {
            clearInterval(statusCheckInterval);
            destroyExamPage();
        }
    }, 2000); 
    
    window._statusCheckInterval = statusCheckInterval;
}

// Helper function to clear all exam flags
function clearAllExamFlags(examId) {
    sessionStorage.removeItem('exam_submitted_' + examId);
    sessionStorage.removeItem('exam_saved_exit_' + examId);
    sessionStorage.removeItem('exam_paused_' + examId);
    sessionStorage.removeItem('exam_initialized_' + examId);
    localStorage.removeItem('exam_submitted_' + examId);
    localStorage.removeItem('exam_saved_exit_' + examId);
}    // ============================================
    // BUTTON SPINNER
    // ============================================
    function showButtonSpinner(button, text = 'Loading...') {
        if (!button) return;
        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
    }

    function hideButtonSpinner(button) {
        if (!button) return;
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
        }
        button.disabled = false;
    }
    
    // ============================================
    // FONT SIZE CONTROL
    // ============================================
    let currentFontSize = 2;
    const maxFontSize = 4;
    const minFontSize = 1;

    function changeFontSize(direction) {
        if (direction === 'increase') {
            if (currentFontSize < maxFontSize) currentFontSize++;
        } else if (direction === 'decrease') {
            if (currentFontSize > minFontSize) currentFontSize--;
        } else if (direction === 'reset') {
            currentFontSize = 2;
        } else if (direction === 'increase-more') {
            if (currentFontSize < maxFontSize) currentFontSize = Math.min(currentFontSize + 2, maxFontSize);
        }
        
        const container = document.querySelector('.question-panel') || document.body;
        container.classList.remove('font-size-small', 'font-size-normal', 'font-size-large', 'font-size-xlarge');
        
        const sizeClasses = {
            1: 'font-size-small',
            2: 'font-size-normal',
            3: 'font-size-large',
            4: 'font-size-xlarge'
        };
        
        container.classList.add(sizeClasses[currentFontSize] || 'font-size-normal');
        
        document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
        const btns = document.querySelectorAll('.font-size-btn');
        if (currentFontSize >= 1 && currentFontSize <= 4) {
            if (btns[currentFontSize - 1]) btns[currentFontSize - 1].classList.add('active');
        }
        
        localStorage.setItem('examFontSize', currentFontSize);
    }

    function applySavedFontSize() {
        const savedSize = localStorage.getItem('examFontSize');
        if (savedSize) {
            currentFontSize = parseInt(savedSize);
            const container = document.querySelector('.question-panel') || document.body;
            
            const sizeClasses = {
                1: 'font-size-small',
                2: 'font-size-normal',
                3: 'font-size-large',
                4: 'font-size-xlarge'
            };
            
            container.classList.remove('font-size-small', 'font-size-normal', 'font-size-large', 'font-size-xlarge');
            container.classList.add(sizeClasses[currentFontSize] || 'font-size-normal');
            
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
            const btns = document.querySelectorAll('.font-size-btn');
            if (currentFontSize >= 1 && currentFontSize <= 4) {
                if (btns[currentFontSize - 1]) btns[currentFontSize - 1].classList.add('active');
            }
        }
    }
    
    //Image Size Controll
    
    let currentImageSize = 2;
    let imageSize = document.getElementById('imageSize');
    const minImageSize = 1;
    const maxImageSize = 4;
    const imageSizeConfig = {
        1: { class: 'image-size-small',  label: '75%' },
        2: { class: 'image-size-normal', label: '100%' },
        3: { class: 'image-size-large',  label: '125%' },
        4: { class: 'image-size-xlarge', label: '150%' }
    };
    
    function changeImageSize(direction) {
    
        if (direction === 'increase') {
            if (currentImageSize < maxImageSize) currentImageSize++;
        } else if (direction === 'decrease') {
            if (currentImageSize > minImageSize) currentImageSize--;
        } else if (direction === 'reset') {
            currentImageSize = 2;
        }
    
        const container = document.querySelector('.question-panel') || document.body;
        
    
        container.classList.remove(
            'image-size-small',
            'image-size-normal',
            'image-size-large',
            'image-size-xlarge'
        );
    
        const classes = {
            1: 'image-size-small',
            2: 'image-size-normal',
            3: 'image-size-large',
            4: 'image-size-xlarge'
        };
    
        // container.classList.add(classes[currentImageSize]);
        // updateImageSizeLabel();
        container.classList.add(imageSizeConfig[currentImageSize].class);
        imageSize.textContent =    imageSizeConfig[currentImageSize].label;
        localStorage.setItem('examImageSize', currentImageSize);
    }
    
    function applySavedImageSize() {

        const saved = localStorage.getItem('examImageSize');
    
        if (!saved) return;
    
        currentImageSize = parseInt(saved);
    
        const container = document.querySelector('.question-panel') || document.body;
    
        container.classList.remove(
            'image-size-small',
            'image-size-normal',
            'image-size-large',
            'image-size-xlarge'
        );
    
        const classes = {
            1: 'image-size-small',
            2: 'image-size-normal',
            3: 'image-size-large',
            4: 'image-size-xlarge'
        };
    
        // container.classList.add(classes[currentImageSize]);
        container.classList.add(imageSizeConfig[currentImageSize].class);
        imageSize.textContent =    imageSizeConfig[currentImageSize].label;
    }


    document.addEventListener("wheel",function(e){
    
        if(!e.ctrlKey)
            return;
    
        const img=document.querySelector(".question_image");
    
        if(!img)
            return;
    
        e.preventDefault();
    
        if(e.deltaY<0){
            zoomImage(100);
        }else{
            zoomImage(-100);
    
        }
    
    },{passive:false});
    
    document.addEventListener("mousedown",function(e){

        const container=e.target.closest(".question-image-container");
        if(!container) return;
        let startX=e.pageX;
        let scrollLeft=container.scrollLeft;
    
        function move(ev){
            container.scrollLeft=scrollLeft-(ev.pageX-startX);
        }
    
        function up(){
            document.removeEventListener("mousemove",move);
            document.removeEventListener("mouseup",up);
        }
    
        document.addEventListener("mousemove",move);
        document.addEventListener("mouseup",up);
    
    });
    
    
    function switchLanguage(locale) {
        // Show loading state
        toastr.info('Switching language...');
        
        fetch('{{ route("language.switch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ locale: locale })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update active button
                document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelector(`.lang-btn[onclick*="${locale}"]`)?.classList.add('active');
                
                // Reload current question with new language
                loadQuestion(currentQuestionId);
                
                toastr.success(data.message);
            }
        })
        .catch(error => {
            console.error('Language switch error:', error);
            toastr.error('Failed to switch language');
        });
    }
    // ============================================
    // EXPOSE FUNCTIONS GLOBALLY
    // ============================================
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
    window.scrollToTop = scrollToTop;
    window.scrollToBottom = scrollToBottom;
    window.saveAndNext = saveAndNext;
    window.saveAndMarkForReview = saveAndMarkForReview;
    window.markForReviewAndNext = markForReviewAndNext;
    window.selectOption = selectOption;
    window.togglePalette = togglePalette;
    window.getSelectedOptions = getSelectedOptions;
    window.saveCurrentAnswer = saveCurrentAnswer;
    window.saveAnswer = saveAnswer;
    window.saveNumericalAnswer = saveNumericalAnswer;
    window.saveAnswerWithMarkReview = saveAnswerWithMarkReview;
    window.isOptionSelected = isOptionSelected;
    window.updateStats = updateStats;
    window.updateQuestionButtons = updateQuestionButtons;
    window.updateQuestionButtonStatus = updateQuestionButtonStatus;
    window.showButtonSpinner = showButtonSpinner;
    window.hideButtonSpinner = hideButtonSpinner;
    window.changeFontSize = changeFontSize;
    window.applySavedFontSize = applySavedFontSize;
    window.changeImageSize = changeImageSize;
    window.applySavedImageSize = applySavedImageSize;
    window.destroyExamPage = destroyExamPage;
    window.isExamEnded = isExamEnded;
    window.switchLanguage = switchLanguage;

</script>
@endpush