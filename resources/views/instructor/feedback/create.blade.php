@extends('layouts.instructor')

@section('title', 'Get Class Feedback')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Get Class Feedback</h1>
        <a href="{{ route('instructor.feedback.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Feedback
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Feedback Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('instructor.feedback.launch') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="batch_id" class="form-label">Select Batch <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2" id="batch_id" name="batch_id" required>
                                <option value="">Choose batch...</option>
                                @foreach($batches as $batch)
                                <option value="{{ $batch->id }}">
                                    {{ $batch->name }} ({{ $batch->course->title }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feedback Type <span class="text-danger">*</span></label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="type" id="typeQuick" value="quick"
                                    checked>
                                <label class="form-check-label" for="typeQuick">
                                    <strong>Quick Feedback</strong><br>
                                    <small class="text-muted">Simple rating with comment option. Students respond
                                        quickly.</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="typeDetailed"
                                    value="detailed">
                                <label class="form-check-label" for="typeDetailed">
                                    <strong>Detailed Feedback</strong><br>
                                    <small class="text-muted">Create custom questions to get detailed insights.</small>
                                </label>
                            </div>
                        </div>

                        <!-- Quick Feedback Options -->
                        <div id="quickOptions" class="mb-3">
                            <label class="form-label">Additional Comment Field</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_comment" name="allow_comment"
                                    value="1" checked>
                                <label class="form-check-label" for="allow_comment">
                                    Allow students to add comments
                                </label>
                            </div>
                        </div>

                        <!-- Detailed Feedback Questions -->
                        <div id="detailedOptions" style="display: none;">
                            <label class="form-label">Custom Questions</label>
                            <div id="questionsContainer">
                                <div class="question-item mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="questions[]"
                                            placeholder="Enter your question...">
                                        <button type="button" class="btn btn-danger"
                                            onclick="removeQuestion(this)">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="addQuestion()">
                                <i class="bi bi-plus-circle"></i> Add Question
                            </button>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Launch Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tips for Good Feedback</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">✓ Keep questions clear and specific</li>
                        <li class="mb-2">✓ Ask about what went well and what could improve</li>
                        <li class="mb-2">✓ Use quick feedback for daily classes</li>
                        <li class="mb-2">✓ Use detailed feedback for course evaluation</li>
                        <li>✓ Feedback is anonymous to encourage honest responses</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sample Questions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action small"
                            onclick="addSampleQuestion('How would you rate today\'s class?');">
                            How would you rate today's class?
                        </button>
                        <button type="button" class="list-group-item list-group-item-action small"
                            onclick="addSampleQuestion('What topics were most helpful?');">
                            What topics were most helpful?
                        </button>
                        <button type="button" class="list-group-item list-group-item-action small"
                            onclick="addSampleQuestion('Any suggestions for improvement?');">
                            Any suggestions for improvement?
                        </button>
                        <button type="button" class="list-group-item list-group-item-action small"
                            onclick="addSampleQuestion('Was the pace appropriate?');">
                            Was the pace appropriate?
                        </button>
                        <button type="button" class="list-group-item list-group-item-action small"
                            onclick="addSampleQuestion('Did you face any technical issues?');">
                            Did you face any technical issues?
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this feedback session?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    let questionCount = 1;

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Initialize the Bootstrap 5 Modal instance natively
        const deleteModalEl = document.getElementById('deleteModal');
        let deleteModalInstance = null;
        if (deleteModalEl) {
            deleteModalInstance = new bootstrap.Modal(deleteModalEl);
        }

        // jQuery Initialization
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select a batch',
                allowClear: true
            });

            // Toggle between quick and detailed feedback
            $('input[name="type"]').on('change', function() {
                if ($(this).val() === 'quick') {
                    $('#quickOptions').show();
                    $('#detailedOptions').hide();
                } else {
                    $('#quickOptions').hide();
                    $('#detailedOptions').show();
                }
            });
        });

        // 2. Attach functions to the window object so HTML onclick attributes can reach them
        window.addQuestion = function() {
            const html = `
                <div class="question-item mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="questions[]" placeholder="Enter your question...">
                        <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
                    </div>
                </div>
            `;
            $('#questionsContainer').append(html);
            questionCount++;
        };

        window.removeQuestion = function(btn) {
            $(btn).closest('.question-item').remove();
            questionCount--;
        };

        window.addSampleQuestion = function(question) {
            // Switch to detailed feedback
            $('#detailedOptions').show();
            $('#quickOptions').hide();
            $('input[name="type"][value="detailed"]').prop('checked', true).trigger('change');

            // Add the question
            const html = `
                <div class="question-item mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="questions[]" value="${question.replace(/'/g, "\\'")}">
                        <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove</button>
                    </div>
                </div>
            `;
            $('#questionsContainer').append(html);
            questionCount++;
        };

        window.deleteFeedback = function(id) {
            // Safely update the form target action using jQuery
            $('#deleteForm').attr('action', '/instructor/feedback/' + id);
            
            // Trigger the modal open via native Bootstrap 5 instance
            if (deleteModalInstance) {
                deleteModalInstance.show();
            } else {
                console.error("Modal element #deleteModal not found in DOM.");
            }
        };
    });    
</script>

@endpush