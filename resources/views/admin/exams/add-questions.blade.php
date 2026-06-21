@extends('layouts.admin')

@section('title', 'Add Questions to Exam')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Questions: {{ $exam->title }}</h1>
        <div>
            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Exam
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if($exam->status == 'published' || $exam->status == 'in_progress')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Exam is already {{ ucfirst(str_replace('_', ' ', $exam->status)) }}!</strong>
            You cannot add or remove questions from a published exam. Please unpublish the exam first.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Question Bank -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Question Bank 
                        @if($exam->course_id)
                            <span class="badge bg-info">Course: {{ $exam->course->title }}</span>
                        @endif
                    </h6>
                    <span class="badge bg-secondary">Total: {{ $questions->total() }}</span>
                </div>
                <div class="card-body">
                    <!-- Search and Filters -->
                    <form method="GET" action="{{ route('admin.exams.add-questions', $exam) }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search questions..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="single_choice" {{ request('type') == 'single_choice' ? 'selected' : '' }}>Single Choice</option>
                                    <option value="multiple_choice" {{ request('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="numerical" {{ request('type') == 'numerical' ? 'selected' : '' }}>Numerical</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="difficulty" class="form-control">
                                    <option value="">All Difficulties</option>
                                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Show filter info -->
                    @if($exam->course_id)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Showing questions from <strong>{{ $exam->course->title }}</strong> course.
                            @if($exam->module_id)
                                <br>Module: <strong>{{ $exam->module->title ?? 'N/A' }}</strong>
                            @endif
                            @if($exam->modules && $exam->modules->count() > 0)
                                <br>Modules: 
                                @foreach($exam->modules as $module)
                                    <span class="badge bg-secondary">{{ $module->title }}</span>
                                @endforeach
                            @endif
                        </div>
                    @endif

                    <!-- Questions List -->
                    <form id="addQuestionsForm" method="POST" action="{{ route('admin.exams.store-questions', $exam) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all" 
                                                   {{ $exam->status == 'published' || $exam->status == 'in_progress' ? 'disabled' : '' }}>
                                        </th>
                                        <th>Question</th>
                                        <th>Type</th>
                                        <th>Difficulty</th>
                                        <th>Default Marks</th>
                                        <th width="80">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($questions as $question)
                                    @php
                                        $isSelected = $exam->questions->contains($question->id);
                                    @endphp
                                    <tr class="{{ $isSelected ? 'table-success' : '' }}">
                                        <td>
                                            <input type="checkbox" class="question-checkbox" 
                                                   name="questions[{{ $loop->index }}][question_id]" 
                                                   value="{{ $question->id }}"
                                                   {{ $isSelected ? 'checked' : '' }}
                                                   {{ $exam->status == 'published' || $exam->status == 'in_progress' ? 'disabled' : '' }}>
                                            <input type="hidden" name="questions[{{ $loop->index }}][question_id]" value="{{ $question->id }}">
                                        </td>
                                        <td>
                                            {{ Str::limit(strip_tags($question->question_text), 100) }}
                                            <br>
                                            <small class="text-muted">ID: #{{ $question->id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $question->type_badge }}">
                                                {{ $question->type_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $question->difficulty_badge }}">
                                                {{ $question->difficulty_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm marks-input" 
                                                   name="questions[{{ $loop->index }}][marks]" 
                                                   value="{{ $question->pivot->marks ?? $question->marks }}"
                                                   min="1" step="0.5"
                                                   style="width: 80px; display: inline-block;"
                                                   {{ $isSelected ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            @if($isSelected)
                                                <span class="badge bg-success">Added</span>
                                            @else
                                                <span class="badge bg-secondary">Available</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-question-circle fs-1 text-muted"></i>
                                            <p class="mt-2">No questions found.</p>
                                            @if($exam->course_id)
                                                <small class="text-muted">Try creating questions for this course first.</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $questions->withQueryString()->links() }}

                        <hr>

                        @if($exam->status != 'published' && $exam->status != 'in_progress')
                            <!-- Question Settings -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h6>Selected Questions: <span id="selected-count">{{ $exam->questions->count() }}</span></h6>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-success" onclick="bulkConfigure()">
                                        <i class="bi bi-gear"></i> Configure Selected
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="clearAllSelection()">
                                        <i class="bi bi-x-circle"></i> Clear All
                                    </button>
                                </div>
                            </div>

                            <!-- Configuration Section -->
                            <div id="config-section" style="display: none;" class="mt-4 p-3 bg-light rounded">
                                <h5 class="mb-3">Configure Selected Questions</h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="section_id" class="form-label">Section</label>
                                            <select class="form-control" id="section_id" name="section_id">
                                                <option value="">No Section</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}">{{ $section->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="marks" class="form-label">Marks (Override)</label>
                                            <input type="number" class="form-control" id="marks" name="marks" min="1" step="0.5">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="negative_marks" class="form-label">Negative Marks</label>
                                            <input type="number" class="form-control" id="negative_marks" name="negative_marks" min="0" step="0.25">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-primary d-block" onclick="applyConfig()">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="bi bi-save"></i> Save Questions to Exam
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-lock-fill me-2"></i>
                                This exam is already published. Questions cannot be modified.
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Selected Questions Sidebar -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Selected Questions 
                        <span class="badge bg-primary" id="selected-badge">{{ $exam->questions->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="selected-questions-list">
                        @if($exam->questions->count() > 0)
                            <div class="list-group">
                                @foreach($exam->questions as $index => $question)
                                    <div class="list-group-item selected-question-item" data-id="{{ $question->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="fw-bold">Q{{ $index + 1 }}</small>
                                                <small class="text-muted">{{ Str::limit(strip_tags($question->question_text), 40) }}</small>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary me-1">{{ $question->pivot->marks ?? $question->marks }} marks</span>
                                                @if($exam->status != 'published' && $exam->status != 'in_progress')
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="removeSelectedQuestion({{ $question->id }})">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No questions selected</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sections Management -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sections</h6>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSectionModal"
                            {{ $exam->status == 'published' || $exam->status == 'in_progress' ? 'disabled' : '' }}>
                        <i class="bi bi-plus-circle"></i> Add Section
                    </button>
                </div>
                <div class="card-body">
                    @if($exam->sections->count() > 0)
                        <div class="list-group">
                            @foreach($exam->sections as $section)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $section->title }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-question-circle"></i> {{ $section->questions_count ?? 0 }} questions
                                            @if($section->duration_minutes)
                                                | <i class="bi bi-clock"></i> {{ $section->duration_minutes }} min
                                            @endif
                                        </small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="editSection({{ $section->id }})"
                                                {{ $exam->status == 'published' || $exam->status == 'in_progress' ? 'disabled' : '' }}>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteSection({{ $section->id }})"
                                                {{ $exam->status == 'published' || $exam->status == 'in_progress' ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No sections created yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>





<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addSectionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSectionModalLabel">Add Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="section_title" class="form-label">Section Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="section_description" class="form-label">Description</label>
                        <textarea class="form-control" id="section_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="section_duration" class="form-label">Duration (minutes) - Optional</label>
                        <input type="number" class="form-control" id="section_duration" name="duration_minutes" min="1">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="randomize_section" name="randomize_questions" value="1">
                        <label class="form-check-label" for="randomize_section">
                            Randomize questions in this section
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSectionForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editSectionModalLabel">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                                <div class="modal-body">
                    <input type="hidden" id="edit_section_id" name="section_id">
                    <div class="mb-3">
                        <label for="edit_section_title" class="form-label">Section Title</label>
                        <input type="text" class="form-control" id="edit_section_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_section_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_section_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_section_duration" class="form-label">Duration (minutes) - Optional</label>
                        <input type="number" class="form-control" id="edit_section_duration" name="duration_minutes" min="1">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_randomize_section" name="randomize_questions" value="1">
                        <label class="form-check-label" for="edit_randomize_section">
                            Randomize questions in this section
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Confirmation Modal -->
<div class="modal fade" id="removeConfirmModal" tabindex="-1" aria-labelledby="removeConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeConfirmModalLabel">Remove Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this question from the exam?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('styles')
<style>
    .selected-question-item {
        transition: all 0.3s;
    }
    .selected-question-item:hover {
        background-color: #f8f9fa;
    }
    .table-success {
        background-color: #d4edda !important;
    }
    .marks-input:disabled {
        opacity: 0.5;
    }
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedQuestions = [];
    let removeQuestionId = null;

    // Helper function to safely get Bootstrap modal
    function getModalInstance(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return null;
        
        try {
            // Check if bootstrap is available globally
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                // Try to get existing instance or create new one
                let modal = bootstrap.Modal.getInstance(element);
                if (!modal) {
                    modal = new bootstrap.Modal(element);
                }
                return modal;
            } else {
                // Fallback: Use jQuery if bootstrap is not available
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    return {
                        show: function() {
                            $(element).modal('show');
                        },
                        hide: function() {
                            $(element).modal('hide');
                        }
                    };
                } else {
                    console.error('Bootstrap or jQuery modal not available');
                    return null;
                }
            }
        } catch (e) {
            console.error('Error getting modal instance:', e);
            return null;
        }
    }

    // Helper function to show modal
    function showModal(elementId) {
        const modal = getModalInstance(elementId);
        if (modal) {
            modal.show();
        } else {
            toastr.error('Modal not available');
            // Fallback: show alert
            console.log('Modal not available, showing alert instead');
            // You could also use a custom modal or alert here
        }
    }

    // Helper function to hide modal
    function hideModal(elementId) {
        const modal = getModalInstance(elementId);
        if (modal) {
            modal.hide();
        }else{
            toastr.error('Modal not available');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        
          // Check if bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap not loaded, waiting...');
            // Wait for bootstrap to load
            const checkBootstrap = setInterval(function() {
                if (typeof bootstrap !== 'undefined') {
                    clearInterval(checkBootstrap);
                    toastr.success('Bootstrap loaded:', bootstrap.Modal.VERSION);
                    console.log('Bootstrap loaded:', bootstrap.Modal.VERSION);
                    initPage();
                }
            }, 100);
            // Timeout after 5 seconds
            setTimeout(function() {
                clearInterval(checkBootstrap);
                if (typeof bootstrap === 'undefined') {
                    console.error('Bootstrap failed to load');
                }
            }, 5000);
        } else {
            console.log('Bootstrap already loaded:', bootstrap.Modal.VERSION);
            initPage();
        }
        
        // Initialize selected questions from existing exam questions
        @foreach($exam->questions as $question)
            selectedQuestions.push({
                id: {{ $question->id }},
                text: `{{ Str::limit(strip_tags($question->question_text), 50) }}`,
                marks: {{ $question->pivot->marks ?? $question->marks }}
            });
        @endforeach
        updateSelectedList();

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.question-checkbox:not(:disabled)').forEach(function(checkbox) {
                    checkbox.checked = this.checked;
                }, this);
                updateSelectedQuestions();
                updateMarksInputs();
            });
        }

        // Individual checkboxes
        document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelectAll();
                updateSelectedQuestions();
                updateMarksInputs();
                
                // Enable/disable marks inputs based on checkbox
                const row = this.closest('tr');
                const marksInput = row.querySelector('.marks-input');
                if (marksInput) {
                    marksInput.disabled = !this.checked;
                }
            });
        });

        // Add Section Form
        const addSectionForm = document.getElementById('addSectionForm');
        if (addSectionForm) {
            addSectionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Adding...';

                const formData = new FormData(this);
                const url = '{{ route("admin.exams.sections.store", $exam) }}';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideModal('addSectionModal');
                        addSectionForm.reset();
                        toastr.success(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || 'Error adding section');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Error adding section');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        }

        // Confirm remove question
        const confirmRemoveBtn = document.getElementById('confirmRemoveBtn');
        if (confirmRemoveBtn) {
            confirmRemoveBtn.addEventListener('click', function() {
                if (removeQuestionId) {
                    removeQuestionFromExam(removeQuestionId);
                    hideModal('removeConfirmModal');
                }
            });
        }

        // Form submit validation
        const addQuestionsForm = document.getElementById('addQuestionsForm');
        if (addQuestionsForm) {
            addQuestionsForm.addEventListener('submit', function(e) {
                const selected = document.querySelectorAll('.question-checkbox:checked').length;
                if (selected === 0) {
                    e.preventDefault();
                    toastr.warning('Please select at least one question to add');
                    return false;
                }
                
                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Saving...';
                }
            });
        }

        // Edit Section Form
        const editSectionForm = document.getElementById('editSectionForm');
        if (editSectionForm) {
            editSectionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const id = document.getElementById('edit_section_id').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner me-2"></span>Updating...';

                const formData = new FormData(this);
                formData.append('_method', 'PUT');
                const url = `/admin/exams/{{ $exam->id }}/sections/${id}`;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideModal('editSectionModal');
                        toastr.success(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || 'Error updating section');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Error updating section');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        }

        // Add Section button click handler
        const addSectionBtn = document.querySelector('[data-bs-target="#addSectionModal"]');
        if (addSectionBtn) {
            addSectionBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showModal('addSectionModal');
            });
        }
    });

    function updateSelectAll() {
        const total = document.querySelectorAll('.question-checkbox:not(:disabled)').length;
        const checked = document.querySelectorAll('.question-checkbox:not(:disabled):checked').length;
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.checked = total > 0 && total === checked;
        }
    }

    function updateSelectedQuestions() {
        selectedQuestions = [];
        document.querySelectorAll('.question-checkbox:checked').forEach(function(checkbox) {
            const row = checkbox.closest('tr');
            const questionId = checkbox.value;
            const questionText = row.querySelector('td:nth-child(2)')?.textContent?.trim() || '';
            const marksInput = row.querySelector('.marks-input');
            const marks = marksInput ? parseFloat(marksInput.value) || 0 : 0;
            selectedQuestions.push({
                id: parseInt(questionId),
                text: questionText.substring(0, 50),
                marks: marks
            });
        });
        updateSelectedList();
        updateSelectedBadge();
    }

    function updateSelectedList() {
        const container = document.getElementById('selected-questions-list');
        const count = selectedQuestions.length;
        const selectedCount = document.getElementById('selected-count');
        if (selectedCount) selectedCount.textContent = count;

        if (!container) return;

        if (count === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No questions selected</p>';
            return;
        }

        let html = '<div class="list-group">';
        selectedQuestions.forEach((q, index) => {
            html += `
                <div class="list-group-item selected-question-item" data-id="${q.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="fw-bold">Q${index + 1}</small>
                            <small class="text-muted">${q.text}...</small>
                        </div>
                        <div>
                            <span class="badge bg-primary me-1">${q.marks || 0} marks</span>
                            @if($exam->status != 'published' && $exam->status != 'in_progress')
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmRemoveQuestion(${q.id})">
                                    <i class="bi bi-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    }

    function updateSelectedBadge() {
        const badge = document.getElementById('selected-badge');
        if (badge) badge.textContent = selectedQuestions.length;
    }

    function updateMarksInputs() {
        document.querySelectorAll('.question-checkbox').forEach(function(checkbox) {
            const row = checkbox.closest('tr');
            const marksInput = row.querySelector('.marks-input');
            if (marksInput) {
                marksInput.disabled = !checkbox.checked;
            }
        });
    }

    function confirmRemoveQuestion(questionId) {
        removeQuestionId = questionId;
        showModal('removeConfirmModal');
    }

    function removeSelectedQuestion(questionId) {
        removeQuestionId = questionId;
        removeQuestionFromExam(questionId);
    }

    function removeQuestionFromExam(questionId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        fetch(`/admin/exams/{{ $exam->id }}/questions/${questionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Question removed from exam');
                // Uncheck the checkbox
                const checkbox = document.querySelector(`.question-checkbox[value="${questionId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.closest('tr')?.classList.remove('table-success');
                }
                // Remove from selected list
                selectedQuestions = selectedQuestions.filter(q => q.id !== questionId);
                updateSelectedList();
                updateSelectedBadge();
            } else {
                toastr.error(data.message || 'Error removing question');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error removing question');
        });
    }

    function bulkConfigure() {
        if (selectedQuestions.length === 0) {
            toastr.warning('Please select at least one question');
            return;
        }
        const configSection = document.getElementById('config-section');
        if (configSection) {
            if (configSection.style.display === 'none' || configSection.style.display === '') {
                configSection.style.display = 'block';
            } else {
                configSection.style.display = 'none';
            }
        }
    }

    function applyConfig() {
        const sectionId = document.getElementById('section_id')?.value;
        const marks = document.getElementById('marks')?.value;
        const negativeMarks = document.getElementById('negative_marks')?.value;

        // Apply to all selected checkboxes
        document.querySelectorAll('.question-checkbox:checked').forEach(function(checkbox) {
            const row = checkbox.closest('tr');
            const marksInput = row.querySelector('.marks-input');
            
            if (marks && marksInput) {
                marksInput.value = marks;
            }
            
            if (sectionId) {
                // Store section in a hidden input (you'd need to add this to the form)
                console.log('Assign section:', sectionId, 'to question:', checkbox.value);
            }
            
            if (negativeMarks) {
                // Store negative marks (you'd need to add this to the form)
                console.log('Set negative marks:', negativeMarks, 'for question:', checkbox.value);
            }
        });

        toastr.success('Configuration applied to selected questions');
        const configSection = document.getElementById('config-section');
        if (configSection) {
            configSection.style.display = 'none';
        }
    }

    function clearAllSelection() {
        if (selectedQuestions.length === 0) {
            toastr.warning('No questions to clear');
            return;
        }
        
        if (confirm('Are you sure you want to clear all selected questions?')) {
            document.querySelectorAll('.question-checkbox:checked').forEach(function(checkbox) {
                checkbox.checked = false;
                const row = checkbox.closest('tr');
                const marksInput = row.querySelector('.marks-input');
                if (marksInput) {
                    marksInput.disabled = true;
                }
                row?.classList.remove('table-success');
            });
            selectedQuestions = [];
            updateSelectedList();
            updateSelectedBadge();
            toastr.success('All selections cleared');
        }
    }

    function editSection(sectionId) {
        fetch(`/admin/exams/{{ $exam->id }}/sections/${sectionId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const section = data.section || data;
            document.getElementById('edit_section_id').value = section.id;
            document.getElementById('edit_section_title').value = section.title;
            document.getElementById('edit_section_description').value = section.description;
            document.getElementById('edit_section_duration').value = section.duration_minutes;
            document.getElementById('edit_randomize_section').checked = section.randomize_questions || false;
            
            showModal('editSectionModal');
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error loading section details');
        });
    }

    function deleteSection(id) {
        if (confirm('Are you sure you want to delete this section?')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            fetch(`/admin/exams/{{ $exam->id }}/sections/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(data.message || 'Error deleting section');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error deleting section');
            });
        }
    }

    // Modal cleanup
    document.addEventListener('hidden.bs.modal', function(e) {
        if (e.target.id === 'addSectionModal') {
            document.getElementById('addSectionForm')?.reset();
        }
        if (e.target.id === 'editSectionModal') {
            document.getElementById('editSectionForm')?.reset();
        }
    });

    // Fallback for modal close buttons
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const target = this.closest('.modal');
            if (target) {
                hideModal(target.id);
            }
        });
    });

    console.log('Add Questions page loaded successfully');
</script>
@endpush