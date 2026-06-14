@extends('layouts.instructor')

@section('title', 'Edit Quiz')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Quiz: {{ $quiz->title }}</h1>
        <div>
            <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Quiz
            </a>
            <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quiz Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('instructor.quizzes.update', $quiz) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="{{ old('title', $quiz->title) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_id" class="form-label">Batch</label>
                                    <select class="form-control select2" id="batch_id" name="batch_id" required>
                                        <option value="">Select Batch</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}"
                                                {{ old('batch_id', $quiz->batch_id) == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->name }} ({{ $batch->course->title }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control" id="duration_minutes"
                                           name="duration_minutes" value="{{ old('duration_minutes', $quiz->duration_minutes) }}"
                                           min="1" max="60" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attempts_allowed" class="form-label">Attempts Allowed</label>
                                    <input type="number" class="form-control" id="attempts_allowed"
                                           name="attempts_allowed" value="{{ old('attempts_allowed', $quiz->attempts_allowed) }}"
                                           min="1" max="5">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="draft" {{ old('status', $quiz->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status', $quiz->status) == 'published' ? 'selected' : '' }}>Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3">{{ old('instructions', $quiz->instructions) }}</textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="randomize" name="randomize_questions" value="1"
                                   {{ old('randomize_questions', $quiz->randomize_questions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="randomize">
                                Randomize question order
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="randomize_options" name="randomize_options" value="1"
                                   {{ old('randomize_options', $quiz->randomize_options) ? 'checked' : '' }}>
                            <label class="form-check-label" for="randomize_options">
                                Randomize options order
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="show_result" name="show_result_immediately" value="1"
                                   {{ old('show_result_immediately', $quiz->show_result_immediately) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_result">
                                Show result immediately after completion
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Quiz
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Manage Questions</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">Current Questions: {{ $quiz->questions_count }}</p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('instructor.quizzes.questions', $quiz) }}" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Edit Questions
                        </a>
                        <button type="button" class="btn btn-success" onclick="addMoreQuestions()">
                            <i class="bi bi-plus-circle"></i> Add More Questions
                        </button>
                    </div>

                    <hr>

                    <h6 class="mb-3">Quick Stats</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Marks:</td>
                            <td class="fw-bold">{{ $quiz->total_marks }}</td>
                        </tr>
                        <tr>
                            <td>Questions:</td>
                            <td class="fw-bold">{{ $quiz->questions_count }}</td>
                        </tr>
                        <tr>
                            <td>Avg Marks/Question:</td>
                            <td class="fw-bold">
                                @if($quiz->questions_count > 0)
                                    {{ round($quiz->total_marks / $quiz->questions_count, 1) }}
                                @else
                                    0
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Questions Modal -->
<div class="modal fade" id="addQuestionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Questions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchQuestions" placeholder="Search questions...">
                </div>
                <div id="availableQuestions" style="max-height: 400px; overflow-y: auto;">
                    <!-- Questions will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addSelectedQuestions()">Add Selected</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });

    function addMoreQuestions() {
        $('#addQuestionsModal').modal('show');
        loadAvailableQuestions();
    }

    function loadAvailableQuestions() {
        $.ajax({
            url: '{{ route("instructor.quizzes.available-questions", $quiz) }}',
            method: 'GET',
            success: function(response) {
                let html = '';
                response.questions.forEach(question => {
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input question-checkbox"
                                   type="checkbox" value="${question.id}" id="q${question.id}">
                            <label class="form-check-label" for="q${question.id}">
                                ${question.text.substring(0, 100)}...
                                <span class="badge bg-info">${question.type}</span>
                                <span class="badge bg-warning">${question.marks} marks</span>
                            </label>
                        </div>
                    `;
                });
                $('#availableQuestions').html(html);
            }
        });
    }

    function addSelectedQuestions() {
        const selectedIds = [];
        $('.question-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one question');
            return;
        }

        $.ajax({
            url: '{{ route("instructor.quizzes.add-questions", $quiz) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Questions added successfully');
                    $('#addQuestionsModal').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function() {
                toastr.error('Error adding questions');
            }
        });
    }
</script>
@endpush
