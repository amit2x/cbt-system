@extends('layouts.instructor')

@section('title', $quiz->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $quiz->title }}</h1>
        <div>
            <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Quiz
            </a>
            <a href="{{ route('instructor.quizzes.results', $quiz) }}" class="btn btn-success">
                <i class="bi bi-bar-chart"></i> View Results
            </a>
            <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Quiz Details -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quiz Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Batch:</th>
                                    <td>{{ $quiz->batch->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Course:</th>
                                    <td>{{ $quiz->batch->course->title ?? $quiz->course->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $quiz->duration_minutes }} minutes</td>
                                </tr>
                                <tr>
                                    <th>Total Questions:</th>
                                    <td>{{ $quiz->questions_count }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Total Marks:</th>
                                    <td>{{ $quiz->total_marks }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($quiz->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $quiz->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Attempts Allowed:</th>
                                    <td>{{ $quiz->attempts_allowed }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($quiz->instructions)
                        <hr>
                        <h6>Instructions:</h6>
                        <p>{{ $quiz->instructions }}</p>
                    @endif
                </div>
            </div>

            <!-- Questions List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Questions ({{ $quiz->questions_count }})</h6>
                </div>
                <div class="card-body">
                    @if($quiz->questions->count() > 0)
                        <div class="list-group">
                            @foreach($quiz->questions as $index => $question)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-secondary me-2">Q{{ $index + 1 }}</span>
                                            <span class="fw-bold">{{ Str::limit(strip_tags($question->question_text), 150) }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-info">{{ $question->type_name }}</span>
                                            <span class="badge bg-warning">{{ $question->pivot->marks }} marks</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No questions added to this quiz.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <!-- Stats Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-primary">{{ $stats['total_attempts'] ?? 0 }}</h2>
                        <p class="text-muted">Total Attempts</p>
                    </div>

                    <div class="row">
                        <div class="col-6 text-center mb-3">
                            <h5 class="text-success">{{ $stats['passed'] ?? 0 }}</h5>
                            <small class="text-muted">Passed</small>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <h5 class="text-danger">{{ $stats['failed'] ?? 0 }}</h5>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-sm">
                        <tr>
                            <td>Average Score:</td>
                            <td class="fw-bold">{{ round($stats['average_score'] ?? 0, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>Highest Score:</td>
                            <td class="fw-bold text-success">{{ round($stats['highest_score'] ?? 0, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>Lowest Score:</td>
                            <td class="fw-bold text-danger">{{ round($stats['lowest_score'] ?? 0, 1) }}%</td>
                        </tr>
                        <tr>
                            <td>Avg Time Taken:</td>
                            <td class="fw-bold">{{ round(($stats['avg_time'] ?? 0) / 60, 1) }} min</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($quiz->status == 'draft')
                            <button type="button" class="btn btn-success" onclick="publishQuiz()">
                                <i class="bi bi-check-circle"></i> Publish Quiz
                            </button>
                        @endif
                        <a href="{{ route('instructor.quizzes.results', $quiz) }}" class="btn btn-info">
                            <i class="bi bi-bar-chart"></i> View Detailed Results
                        </a>
                        <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Questions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function publishQuiz() {
        if (confirm('Are you sure you want to publish this quiz? Students will be able to take it.')) {
            $.ajax({
                url: '{{ route("instructor.quizzes.publish", $quiz) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Quiz published successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function() {
                    toastr.error('Error publishing quiz');
                }
            });
        }
    }
</script>
@endpush
