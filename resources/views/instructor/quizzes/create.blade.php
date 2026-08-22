@extends('layouts.instructor')

@section('title', 'Create Quick Quiz')

@push('styles')
<style>
.question-item {
    transition: background-color 0.15s ease;
    cursor: pointer;
    border-left: 3px solid transparent;
}
.question-item:hover { background-color: #f8f9fa; }
.question-item.selected {
    background-color: #e8f5e9 !important;
    border-left-color: #28a745 !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Quick Quiz</h1>
        <a href="{{ route('instructor.exams.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('instructor.quizzes.launch') }}" method="POST" id="quizForm">
        @csrf
        {{-- ✅ Hidden fields for question IDs --}}
        <input type="hidden" name="selected_question_ids" id="selectedQuestionIds">
        {{-- ✅ Also include individual hidden inputs as fallback --}}
        <div id="hiddenQuestionInputs"></div>

        <div class="row">
            <div class="col-md-8">
                <!-- Basic Details -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Step 1: Quiz Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   placeholder="e.g., Weekly Test - Algebra Basics" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_id" class="form-label">Select Batch <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="batch_id" name="batch_id" required>
                                        <option value="">Choose batch...</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}" data-course-id="{{ $batch->course_id }}">
                                                {{ $batch->name }} ({{ $batch->course->title ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Duration (min) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="duration_minutes"
                                           name="duration_minutes" min="1" max="180" value="15" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card shadow mb-4" id="filtersCard" style="display: none;">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">Step 2: Filters</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <select class="form-select" id="subject_filter">
                                    <option value="">All Subjects</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Difficulty</label>
                                <select class="form-select" id="difficulty_filter">
                                    <option value="">All</option>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" id="type_filter">
                                    <option value="">All</option>
                                    <option value="multiple_choice">MCQ</option>
                                    <option value="single_choice">Single</option>
                                    <option value="numerical">Numerical</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Questions to Load</label>
                                <input type="number" class="form-control" id="load_count" value="25" min="5" max="100">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="btnApplyFilters">
                                <i class="bi bi-funnel"></i> Apply Filters & Load Questions
                            </button>
                            <span id="filterStatus" class="ms-2 small text-muted"></span>
                        </div>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="card shadow mb-4" id="questionsCard" style="display: none;">
                    <div class="card-header py-3 d-flex justify-content-between bg-success text-white">
                        <h6 class="m-0">
                            Step 3: Select Questions 
                            <span class="badge bg-white text-dark ms-2" id="questionsCount">0</span>
                        </h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-light" id="btnSelectAll">Select All</button>
                            <button type="button" class="btn btn-sm btn-light" id="btnDeselectAll">Clear</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-2 border-bottom bg-light">
                            <input type="text" class="form-control form-control-sm" id="searchQuestions" 
                                   placeholder="Search loaded questions...">
                        </div>
                        <div id="questionsList" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center py-4 text-muted">
                                <p>Apply filters to load questions</p>
                            </div>
                        </div>
                        <div id="loadMoreContainer" class="text-center p-2 border-top" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnLoadMore">
                                Load More
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Step 4: Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="randomize_questions" name="randomize_questions" value="1" checked>
                                    <label class="form-check-label">Randomize questions</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="randomize_options" name="randomize_options" value="1" checked>
                                    <label class="form-check-label">Randomize options</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="show_results" name="show_result_immediately" value="1" checked>
                                    <label class="form-check-label">Show results immediately</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="allow_review" name="allow_review" value="1" checked>
                                    <label class="form-check-label">Allow review</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-success btn-lg" id="btnLaunchQuiz" disabled>
                        <i class="bi bi-rocket-takeoff"></i> Launch Quiz
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card shadow mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header py-3 bg-dark text-white">
                        <h6 class="m-0">Selected: <span id="sidebarCount">0</span></h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="selectedQuestionsList" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted text-center py-3 small">None selected</p>
                        </div>
                    </div>
                    <div class="card-footer bg-light small">
                        <div class="d-flex justify-content-between"><span>Questions:</span><strong id="sidebarTotal">0</strong></div>
                        <div class="d-flex justify-content-between"><span>Marks:</span><strong id="sidebarMarks">0</strong></div>
                        <div class="d-flex justify-content-between"><span>Time:</span><strong id="sidebarTime">0 min</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const state = {
    batchId: null,
    courseId: null,
    selectedQuestions: new Map(),
    currentPage: 1,
    hasMore: false,
    isLoading: false
};

document.addEventListener('DOMContentLoaded', (event) => {
    $('.select2').select2({ placeholder: 'Choose batch...', allowClear: true });

    // Batch selection
    $('#batch_id').on('change', function() {
        state.batchId = $(this).val();
        state.courseId = $(this).find(':selected').data('course-id');
        state.selectedQuestions.clear();
        state.currentPage = 1;
        
        if (state.batchId) {
            loadSubjects(state.courseId);
            $('#filtersCard').slideDown();
            $('#questionsCard').slideDown();
            resetQuestionsView();
        } else {
            $('#filtersCard').slideUp();
            $('#questionsCard').slideUp();
        }
        updateAllUI();
    });

    // Apply filters
    $('#btnApplyFilters').on('click', function() {
        state.currentPage = 1;
        state.selectedQuestions.clear();
        loadQuestions(true);
    });

    // Search
    let searchTimer;
    $('#searchQuestions').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const s = $(this).val().toLowerCase();
            $('#questionsList .question-item').each(function() {
                $(this).toggle($(this).text().toLowerCase().includes(s));
            });
        }, 300);
    });

    // Select/Clear
    $('#btnSelectAll').on('click', () => $('.question-checkbox:visible').not(':checked').trigger('click'));
    $('#btnDeselectAll').on('click', () => { $('.question-checkbox:checked').trigger('click'); state.selectedQuestions.clear(); updateAllUI(); });

    // Load more
    $('#btnLoadMore').on('click', () => { if (state.hasMore && !state.isLoading) { state.currentPage++; loadQuestions(false); } });

    // Checkbox
    $(document).on('change', '.question-checkbox', function() {
        const id = $(this).val();
        const data = $(this).data('question');
        if ($(this).is(':checked') && data) state.selectedQuestions.set(id.toString(), data);
        else state.selectedQuestions.delete(id.toString());
        updateAllUI();
    });

    // ✅ FORM SUBMISSION - Fixed
    $('#quizForm').on('submit', function(e) {
        const ids = Array.from(state.selectedQuestions.keys());
        
        if (ids.length === 0) {
            e.preventDefault();
            toastr.error('Please select at least one question');
            return false;
        }
        
        // ✅ Set the JSON string
        $('#selectedQuestionIds').val(JSON.stringify(ids));
        
        // ✅ ALSO create individual hidden inputs as backup
        let hiddenInputs = '';
        ids.forEach(function(id) {
            hiddenInputs += `<input type="hidden" name="question_ids[]" value="${id}">`;
        });
        $('#hiddenQuestionInputs').html(hiddenInputs);
        
        console.log('Submitting ' + ids.length + ' questions:', ids); // Debug
        return true;
    });
});

// Load subjects
// function loadSubjects(courseId) {
//     if (!courseId) return;
//     console.log('Loading modules for course:', courseId);
    
//     $.ajax({
//         url: `/instructor/courses/${courseId}/modules`,
//         method: 'GET',
//         success: function(response) {
//             console.log('Modules response:', response);
//             let html = '<option value="">All Subjects</option>';
//             if (response.status === 'success' && Array.isArray(response.data)) {
//                 response.data.forEach(function(m) {
//                     html += `<option value="${m.id}">${m.title || m.name || 'Module ' + m.id}</option>`;
//                 });
//             }
//             $('#subject_filter').html(html);
//         },
//         error: function(xhr) {
//             console.error('Error loading modules:', xhr);
//             $('#subject_filter').html('<option value="">All Subjects</option>');
//         }
//     });
// }

// Load subjects
function loadSubjects(courseId) {
    if (!courseId) {
        console.warn('No courseId provided to loadSubjects');
        return;
    }
    
    // ✅ First, destroy any existing Select2 on subject_filter
    if ($('#subject_filter').hasClass('select2-hidden-accessible')) {
        $('#subject_filter').select2('destroy');
    }
    
    // Show loading state
    $('#subject_filter').html('<option value="">Loading subjects...</option>');
    
    $.ajax({
        url: `/instructor/courses/${courseId}/modules`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Modules response:', response);
            
            // Handle direct array response
            let modules = [];
            if (Array.isArray(response)) {
                modules = response;
            } else if (response.data && Array.isArray(response.data)) {
                modules = response.data;
            }
            
            // ✅ Build HTML
            let html = '<option value="">All Subjects</option>';
            
            if (modules.length > 0) {
                modules.forEach(function(m) {
                    const id = m.id || m.ID || '';
                    const title = m.title || m.name || 'Module ' + id;
                    html += `<option value="${id}">${title}</option>`;
                });
            } else {
                html += '<option value="" disabled>No subjects found</option>';
            }
            
            // ✅ Direct DOM manipulation (bypass Select2)
            const selectEl = document.getElementById('subject_filter');
            if (selectEl) {
                selectEl.innerHTML = html;
            }
            
            $('#subject_filter').html(html);
            
            $('#subject_filter').select2({
                placeholder: 'All Subjects',
                allowClear: true,
                width: '100%'
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading modules:', error);
            $('#subject_filter').html('<option value="">All Subjects</option><option value="" disabled>Error loading</option>');
        }
    });
}
// Load questions
function loadQuestions(reset = true) {
    if (state.isLoading || !state.batchId) return;
    state.isLoading = true;
    
    if (reset) {
        $('#questionsList').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        $('#filterStatus').text('Loading...');
    }
    
    const params = {
        page: state.currentPage,
        batch_id: state.batchId,
        total_questions: $('#load_count').val() || 25,
        search: '',
        type: $('#type_filter').val() || '',
        difficulty: $('#difficulty_filter').val() || '',
        subject_id: $('#subject_filter').val() || ''
    };
    
    console.log('Loading questions with params:', params);
    
    $.get(`/instructor/quizzes/questions-by-batch/${state.batchId}`, params, function(response) {
        state.isLoading = false;
        console.log('Questions response:', response);
        
        if (response.status === 'success') {
            const d = response.data;
            state.hasMore = d.has_more;
            
            const html = d.questions.map(q => `
                <div class="question-item p-2 border-bottom ${state.selectedQuestions.has(q.id.toString()) ? 'selected' : ''}">
                    <div class="form-check">
                        <input class="form-check-input question-checkbox" type="checkbox" 
                               value="${q.id}" id="q${q.id}"
                               data-question='${JSON.stringify(q).replace(/'/g, "&#39;")}'
                               ${state.selectedQuestions.has(q.id.toString()) ? 'checked' : ''}>
                        <label class="form-check-label w-100" for="q${q.id}">
                            <div class="d-flex justify-content-between">
                                <span class="small">${escapeHtml(q.full_text || q.text || 'Question ' + q.id)}</span>
                                <span class="text-nowrap ms-2">
                                    <span class="badge bg-info">${q.type_name || q.type || 'MCQ'}</span>
                                    <span class="badge bg-warning">${q.marks || 0}M</span>
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
            `).join('');
            
            if (reset) $('#questionsList').html(html);
            else $('#questionsList').append(html);
            
            $('#questionsCount').text(d.total || d.count || 0);
            $('#loadMoreContainer').toggle(d.has_more || false);
            $('#filterStatus').text(`Loaded ${d.count || 0} of ${d.total || 0} questions`);
            updateAllUI();
        } else {
            $('#questionsList').html('<div class="text-center py-4 text-muted">No questions found</div>');
            $('#filterStatus').text('No questions found');
        }
    }).fail(function(xhr) {
        state.isLoading = false;
        console.error('Error loading questions:', xhr);
        $('#questionsList').html('<div class="text-center py-4 text-danger">Error loading questions</div>');
        $('#filterStatus').text('Error');
    });
}

function resetQuestionsView() {
    $('#questionsList').html('<div class="text-center py-4 text-muted"><p>Click "Apply Filters" to load questions</p></div>');
    $('#questionsCount').text('0');
    $('#loadMoreContainer').hide();
    $('#filterStatus').text('');
}

function updateAllUI() {
    const count = state.selectedQuestions.size;
    let marks = 0;
    state.selectedQuestions.forEach(q => marks += parseInt(q.marks) || 0);
    
    $('#sidebarCount, #sidebarTotal').text(count);
    $('#sidebarMarks').text(marks);
    $('#sidebarTime').text(count > 0 ? Math.ceil(count * 1.5) + ' min' : '0 min');
    $('#btnLaunchQuiz').prop('disabled', count === 0);
    
    // Sidebar list
    let html = '';
    state.selectedQuestions.forEach((q, id) => {
        html += `<div class="d-flex justify-content-between py-1 border-bottom small">
            <span class="text-truncate me-1" style="max-width:180px;">${escapeHtml((q.full_text || q.text || '').substring(0, 40))}</span>
            <span><span class="badge bg-warning">${q.marks}M</span> <button class="btn btn-sm text-danger p-0 remove-q" data-id="${id}">&times;</button></span>
        </div>`;
    });
    $('#selectedQuestionsList').html(html || '<p class="text-muted text-center py-3 small">None selected</p>');
    
    $('.remove-q').off('click').on('click', function() {
        const id = $(this).data('id').toString();
        state.selectedQuestions.delete(id);
        $(`#q${id}`).prop('checked', false).closest('.question-item').removeClass('selected');
        updateAllUI();
    });
    
    if (count > 0) $('#duration_minutes').val(Math.max(Math.ceil(count * 1.5), 5));
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush