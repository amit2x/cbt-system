@extends('layouts.admin')

@section('title', $exam->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $exam->title }}</h1>
        <div>
            @if($exam->status == 'draft')
                <button type="button" class="btn btn-success" onclick="publishExam()">
                    <i class="bi bi-check-circle"></i> Publish Exam
                </button>
            @endif
            <a href="{{ route('admin.exams.add-questions', $exam) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Questions
            </a>
            <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-info">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <span class="badge bg-{{ $exam->status_badge }} fs-6 p-2">
                                {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="progress" style="height: 10px;">
                                @php
                                    $progress = 0;
                                    if($exam->total_students > 0) {
                                        $progress = ($exam->attempted_count / $exam->total_students) * 100;
                                    }
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $progress }}%"></div>
                            </div>
                            <small class="text-muted">{{ $exam->attempted_count }} of {{ $exam->total_students }} students attempted</small>
                        </div>
                        @if($exam->is_scheduled)
                            <div class="ms-4">
                                @if($exam->is_available)
                                    <span class="badge bg-success">Available Now</span>
                                @elseif($exam->start_time > now())
                                    <span class="badge bg-warning">Starts {{ $exam->start_time->diffForHumans() }}</span>
                                @else
                                    <span class="badge bg-secondary">Expired</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Exam Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Exam Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Type:</th>
                                    <td>
                                        <span class="badge bg-{{ $exam->type_badge }}">
                                            {{ $exam->type_name }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ ucfirst($exam->category) }}</td>
                                </tr>
                                <tr>
                                    <th>Course:</th>
                                    <td>{{ $exam->course->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Batch:</th>
                                    <td>
                                        @if($exam->batch)
                                            {{ $exam->batch->name }}
                                        @elseif($exam->batches->count() > 0)
                                            @foreach($exam->batches as $batch)
                                                <span class="badge bg-info">{{ $batch->name }}</span>
                                            @endforeach
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Duration:</th>
                                    <td>{{ $exam->formatted_duration }}</td>
                                </tr>
                                <tr>
                                    <th>Total Marks:</th>
                                    <td>{{ $exam->total_marks }}</td>
                                </tr>
                                <tr>
                                    <th>Passing %:</th>
                                    <td>{{ $exam->passing_percentage }}%</td>
                                </tr>
                                <tr>
                                    <th>Negative Marking:</th>
                                    <td>{{ $exam->negative_marking }}</td>
                                </tr>
                                <tr>
                                    <th>Attempts Allowed:</th>
                                    <td>{{ $exam->attempts_allowed }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($exam->description)
                        <hr>
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $exam->description }}</p>
                    @endif

                    @if($exam->instructions)
                        <hr>
                        <h6>Instructions:</h6>
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($exam->instructions)) !!}
                        </div>
                    @endif

                    @if($exam->is_scheduled)
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Start Time:</strong><br>
                                {{ $exam->start_time->format('d M Y, h:i A') }}
                            </div>
                            <div class="col-md-6">
                                <strong>End Time:</strong><br>
                                {{ $exam->end_time->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Questions List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Questions ({{ $exam->questions_count }})</h6>
                    <a href="{{ route('admin.exams.add-questions', $exam) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Manage Questions
                    </a>
                </div>
                <div class="card-body">
                    @if($exam->questions_count > 0)
                        @if($exam->sections->count() > 0)
                            <!-- Grouped by sections -->
                            @foreach($exam->sections as $section)
                                <div class="section-block mb-4">
                                    <h6 class="bg-light p-2 rounded">
                                        <strong>{{ $section->title }}</strong>
                                        @if($section->duration_minutes)
                                            <span class="badge bg-info ms-2">{{ $section->formatted_duration }}</span>
                                        @endif
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Question</th>
                                                    <th>Type</th>
                                                    <th>Marks</th>
                                                    <th>Negative</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($questions[$section->id] ?? [] as $question)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ Str::limit(strip_tags($question->question_text), 50) }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $question->type_badge }}">
                                                                {{ $question->type_name }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $question->pivot->marks ?? $question->marks }}</td>
                                                        <td>{{ $question->pivot->negative_marks ?? $question->negative_marks }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- No sections - flat list -->
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Question</th>
                                            <th>Type</th>
                                            <th>Difficulty</th>
                                            <th>Marks</th>
                                            <th>Negative</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($exam->questions as $question)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ Str::limit(strip_tags($question->question_text), 50) }}</td>
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
                                                <td>{{ $question->pivot->marks ?? $question->marks }}</td>
                                                <td>{{ $question->pivot->negative_marks ?? $question->negative_marks }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-question-circle fs-1 text-muted"></i>
                            <p class="mt-2">No questions added yet.</p>
                            <a href="{{ route('admin.exams.add-questions', $exam) }}" class="btn btn-primary">
                                Add Questions Now
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Students:</span>
                            <span class="fw-bold">{{ $stats['total_students'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Attempted:</span>
                            <span class="fw-bold text-primary">{{ $stats['attempted'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Average Score:</span>
                            <span class="fw-bold text-success">{{ $stats['average_score'] }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Highest Score:</span>
                            <span class="fw-bold text-info">{{ $stats['highest_score'] }}%</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Passed:</span>
                            <span class="fw-bold text-success">{{ $stats['passing_count'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Failed:</span>
                            <span class="fw-bold text-danger">{{ $stats['failing_count'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Pass Rate:</span>
                            <span class="fw-bold">
                                @if($stats['attempted'] > 0)
                                    {{ round(($stats['passing_count'] / $stats['attempted']) * 100, 2) }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Exam Settings</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-{{ $exam->randomize_questions ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-2"></i>
                            Randomize Questions
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-{{ $exam->randomize_options ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-2"></i>
                            Randomize Options
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-{{ $exam->show_result_immediately ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-2"></i>
                            Show Result Immediately
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-{{ $exam->allow_review ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-2"></i>
                            Allow Review
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="previewExam()">
                            <i class="bi bi-eye"></i> Preview Exam
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="duplicateExam()">
                            <i class="bi bi-files"></i> Duplicate Exam
                        </button>
                        <a href="{{ route('admin.exams.results', $exam) }}" class="btn btn-outline-info">
                            <i class="bi bi-bar-chart"></i> View Results
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteExam()">
                            <i class="bi bi-trash"></i> Delete Exam
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exam Preview: {{ $exam->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" method="POST" action="{{ route('admin.exams.destroy', $exam) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function publishExam() {
        if (confirm('Are you sure you want to publish this exam?')) {
            $.ajax({
                url: '{{ route("admin.exams.publish", $exam) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Exam published successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error publishing exam');
                }
            });
        }
    }

    function previewExam() {
        let preview = `
            <div class="exam-preview">
                <div class="alert alert-info">
                    <h6>Exam Details:</h6>
                    <ul class="mb-0">
                        <li><strong>Duration:</strong> {{ $exam->formatted_duration }}</li>
                        <li><strong>Total Questions:</strong> {{ $exam->questions_count }}</li>
                        <li><strong>Total Marks:</strong> {{ $exam->total_marks }}</li>
                        <li><strong>Negative Marking:</strong> {{ $exam->negative_marking }}</li>
                    </ul>
                </div>
                <hr>
                <h6>Questions Preview:</h6>
        `;

        @foreach($exam->questions->take(5) as $question)
            preview += `
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Question {{ $loop->iteration }}:</h6>
                        <p>{{ $question->question_text }}</p>
                        <div class="text-muted small">
                            <span class="badge bg-info">{{ $question->type_name }}</span>
                            <span class="badge bg-warning">{{ $question->difficulty_name }}</span>
                            <span class="badge bg-primary">{{ $question->pivot->marks ?? $question->marks }} marks</span>
                        </div>
                    </div>
                </div>
            `;
        @endforeach

        @if($exam->questions_count > 5)
            preview += `<p class="text-center text-muted">... and {{ $exam->questions_count - 5 }} more questions</p>`;
        @endif

        preview += '</div>';

        $('#previewContent').html(preview);
        // $('#previewModal').modal('show');
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    }

    function duplicateExam() {
        if (confirm('Create a duplicate of this exam?')) {
            $.ajax({
                url: '{{ route("admin.exams.duplicate", $exam) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Exam duplicated successfully');
                    setTimeout(() => {
                        window.location.href = response.redirect || '{{ route("admin.exams.index") }}';
                    }, 1500);
                },
                error: function(xhr) {
                    toastr.error('Error duplicating exam');
                }
            });
        }
    }

    function deleteExam() {
        if (confirm('Are you sure you want to delete this exam?')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endpush
