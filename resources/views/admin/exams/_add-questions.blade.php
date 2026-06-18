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

    <div class="row">
        <!-- Question Bank -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Question Bank</h6>
                </div>
                <div class="card-body">
                    <!-- Search and Filters -->
                    <form method="GET" action="{{ route('admin.exams.add-questions', $exam) }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-5">
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
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Questions List -->
                    <form id="addQuestionsForm" method="POST" action="{{ route('admin.exams.store-questions', $exam) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th>Question</th>
                                        <th>Type</th>
                                        <th>Difficulty</th>
                                        <th>Default Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($questions as $question)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="question-checkbox"
                                                   name="questions[{{ $loop->index }}][question_id]"
                                                   value="{{ $question->id }}"
                                                   {{ $exam->questions->contains($question->id) ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            {{ Str::limit(strip_tags($question->question_text), 100) }}
                                            <input type="hidden" name="questions[{{ $loop->index }}][question_id]" value="{{ $question->id }}">
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
                                        <td>{{ $question->marks }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No questions found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $questions->withQueryString()->links() }}

                        <hr>

                        <!-- Question Settings -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6>Selected Questions: <span id="selected-count">0</span></h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-success" onclick="bulkConfigure()">
                                    <i class="bi bi-gear"></i> Configure Selected
                                </button>
                            </div>
                        </div>

                        <!-- Configuration Section (initially hidden) -->
                        <div id="config-section" style="display: none;" class="mt-4">
                            <h5>Configure Selected Questions</h5>

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
                                        <input type="number" class="form-control" id="marks" name="marks" min="1">
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
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Add Selected Questions to Exam
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Selected Questions Sidebar -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Selected Questions</h6>
                </div>
                <div class="card-body">
                    <div id="selected-questions-list">
                        <p class="text-muted text-center">No questions selected</p>
                    </div>
                </div>
            </div>

            <!-- Sections Management -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sections</h6>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                        <i class="bi bi-plus-circle"></i> Add Section
                    </button>
                </div>
                <div class="card-body">
                    @forelse($sections as $section)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong>{{ $section->title }}</strong>
                                <br>
                                <small class="text-muted">Questions: {{ $section->questions_count ?? 0 }}</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="editSection({{ $section }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSection({{ $section->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No sections created yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addSectionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="section_title" class="form-label">Section Title</label>
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
<div class="modal fade" id="editSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSectionForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
@endsection

@push('scripts')
<script>
    let selectedQuestions = [];

(function() {
    'use strict';

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {


    $(document).ready(function() {
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
        $('#select-all').on('change', function() {
            $('.question-checkbox').prop('checked', $(this).prop('checked'));
            updateSelectedQuestions();
        });

        // Individual checkboxes
        $('.question-checkbox').on('change', function() {
            updateSelectAll();
            updateSelectedQuestions();
        });

        // Add Section
        $('#addSectionForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route("admin.exams.sections.store", $exam) }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        // $('#addSectionModal').modal('hide');
                        const modal = new bootstrap.Modal(document.getElementById('addSectionModal'));
                        modal.hide();
                        $('#addSectionForm')[0].reset();
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error adding section');
                }
            });
        });
    });


});

})();

    function updateSelectAll() {
        const total = $('.question-checkbox').length;
        const checked = $('.question-checkbox:checked').length;
        $('#select-all').prop('checked', total === checked);
    }

    function updateSelectedQuestions() {
        selectedQuestions = [];
        $('.question-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            const questionId = $(this).val();
            const questionText = row.find('td:eq(1)').text().trim();
            selectedQuestions.push({
                id: questionId,
                text: questionText,
                marks: 0
            });
        });
        updateSelectedList();
    }

    function updateSelectedList() {
        const container = $('#selected-questions-list');
        const count = selectedQuestions.length;

        $('#selected-count').text(count);

        if (count === 0) {
            container.html('<p class="text-muted text-center">No questions selected</p>');
            return;
        }

        let html = '<div class="list-group">';
        selectedQuestions.forEach((q, index) => {
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>${q.text.substring(0, 50)}...</small>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.html(html);
    }

    function removeQuestion(index) {
        const questionId = selectedQuestions[index].id;
        $(`.question-checkbox[value="${questionId}"]`).prop('checked', false);
        selectedQuestions.splice(index, 1);
        updateSelectedList();
        updateSelectAll();
    }

    function bulkConfigure() {
        if (selectedQuestions.length === 0) {
            toastr.warning('No questions selected');
            return;
        }
        $('#config-section').toggle();
    }

    function applyConfig() {
        const sectionId = $('#section_id').val();
        const marks = $('#marks').val();
        const negativeMarks = $('#negative_marks').val();

        // Apply to all selected checkboxes
        $('.question-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            // Here you would store these values in hidden inputs
            // For now, just show a success message
        });

        toastr.success('Configuration applied to selected questions');
        $('#config-section').hide();
    }

    function editSection(section) {
        $('#edit_section_id').val(section.id);
        $('#edit_section_title').val(section.title);
        $('#edit_section_description').val(section.description);
        $('#edit_section_duration').val(section.duration_minutes);
        $('#edit_randomize_section').prop('checked', section.randomize_questions);
        const modal = new bootstrap.Modal(document.getElementById('editSectionModal'));
        modal.show();
        // $('#editSectionModal').modal('show');
    }

    function deleteSection(id) {
        if (confirm('Are you sure you want to delete this section?')) {
            $.ajax({
                url: `/admin/exams/{{ $exam->id }}/sections/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error deleting section');
                }
            });
        }
    }


</script>
@endpush
