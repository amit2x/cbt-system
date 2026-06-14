@extends('layouts.instructor')

@section('title', 'Create Quick Quiz')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Quick Quiz</h1>
        <a href="{{ route('instructor.exams.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Exams
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quiz Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('instructor.quizzes.launch') }}" method="POST" id="quizForm">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_id" class="form-label">Select Batch</label>
                                    <select class="form-control select2" id="batch_id" name="batch_id" required>
                                        <option value="">Choose batch...</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}">
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
                                           name="duration_minutes" min="1" max="60" value="10" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Questions</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" id="searchQuestions"
                                               placeholder="Search questions...">
                                    </div>
                                    <div id="questionsList" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($recentQuestions as $question)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input question-checkbox"
                                                       type="checkbox" name="question_ids[]"
                                                       value="{{ $question->id }}" id="q{{ $question->id }}">
                                                <label class="form-check-label" for="q{{ $question->id }}">
                                                    {{ Str::limit(strip_tags($question->question_text), 100) }}
                                                    <span class="badge bg-info">{{ $question->type_name }}</span>
                                                    <span class="badge bg-warning">{{ $question->marks }} marks</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Selected: <span id="selectedCount">0</span> questions</small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="randomize" name="randomize" value="1" checked>
                            <label class="form-check-label" for="randomize">
                                Randomize question order
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-lightning"></i> Launch Quiz
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div id="statsBox">
                        <p class="mb-2">Total selected: <span id="totalSelected">0</span></p>
                        <p class="mb-2">Total marks: <span id="totalMarks">0</span></p>
                        <p class="mb-0">Average marks: <span id="avgMarks">0</span></p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Choose 5-10 questions for quick quiz</li>
                        <li>Keep duration short (5-15 minutes)</li>
                        <li>Mix easy and medium difficulty</li>
                        <li>Results shown immediately to students</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instant Quiz Modal -->
<div class="modal fade" id="instantQuizModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Launch Instant Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="instantQuizForm">
                    @csrf
                    <div class="mb-3">
                        <label for="instant_batch_id" class="form-label">Batch</label>
                        <select class="form-control" id="instant_batch_id" name="batch_id" required>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="instant_duration" class="form-label">Duration (minutes)</label>
                        <input type="number" class="form-control" id="instant_duration"
                               name="duration_minutes" min="1" max="30" value="5">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="launchInstantQuiz()">
                    Launch Now
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // Update selected count
        $('.question-checkbox').on('change', function() {
            updateSelectedStats();
        });

        // Search questions
        $('#searchQuestions').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            $('#questionsList .form-check').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(search) > -1);
            });
        });
    });

    function updateSelectedStats() {
        const selected = $('.question-checkbox:checked');
        const count = selected.length;
        let totalMarks = 0;

        selected.each(function() {
            const label = $(this).next('label');
            const marks = parseInt(label.find('.badge.bg-warning').text());
            totalMarks += marks;
        });

        $('#selectedCount').text(count);
        $('#totalSelected').text(count);
        $('#totalMarks').text(totalMarks);
        $('#avgMarks').text(count > 0 ? (totalMarks / count).toFixed(1) : 0);
    }

    function launchInstantQuiz() {
        const batchId = $('#instant_batch_id').val();
        const duration = $('#instant_duration').val();

        // Get selected question IDs
        const questionIds = [];
        $('.question-checkbox:checked').each(function() {
            questionIds.push($(this).val());
        });

        if (questionIds.length === 0) {
            toastr.error('Please select at least one question');
            return;
        }

        $.ajax({
            url: '{{ route("instructor.quizzes.launch-instant") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                batch_id: batchId,
                duration_minutes: duration,
                question_ids: questionIds
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success('Instant quiz launched!');
                    $('#instantQuizModal').modal('hide');
                    setTimeout(() => {
                        window.location.href = '/instructor/exams/' + response.data.exam_id;
                    }, 1500);
                }
            },
            error: function() {
                toastr.error('Error launching quiz');
            }
        });
    }
</script>
@endpush
