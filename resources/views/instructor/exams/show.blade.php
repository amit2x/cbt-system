@extends('layouts.instructor')

@section('title', $exam->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $exam->title }}</h1>
        <div>
            <a href="{{ route('instructor.exams.results', $exam) }}" class="btn btn-success">
                <i class="bi bi-bar-chart"></i> View Results
            </a>
            <a href="{{ route('instructor.exams.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Exams
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Exam Details -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Exam Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Exam Type:</th>
                                    <td>{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</td>
                                </tr>
                                <tr>
                                    <th>Batch:</th>
                                    <td>{{ $exam->batch->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Course:</th>
                                    <td>{{ $exam->course->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $exam->duration_minutes }} minutes</td>
                                </tr>
                                <tr>
                                    <th>Total Marks:</th>
                                    <td>{{ $exam->total_marks }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
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
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span
                                            class="badge bg-{{ $exam->status == 'published' ? 'success' : ($exam->status == 'draft' ? 'secondary' : 'info') }}">
                                            {{ ucfirst($exam->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $exam->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($exam->instructions)
                    <hr>
                    <h6>Instructions:</h6>
                    <p class="text-muted">{{ $exam->instructions }}</p>
                    @endif
                </div>
            </div>

            <!-- Questions List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Questions ({{ $exam->questions_count }})</h6>
                </div>
                <div class="card-body">
                    @if($exam->questions->count() > 0)
                    <div class="list-group">
                        @foreach($exam->questions as $index => $question)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-secondary me-2">Q{{ $index + 1 }}</span>
                                    <strong>{{ Str::limit(strip_tags($question->question_text), 150) }}</strong>
                                    <br>
                                    <small class="text-muted">Type: {{ $question->type_name }} | Difficulty: {{
                                        $question->difficulty_name }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-primary">{{ $question->pivot->marks ?? $question->marks }}
                                        marks</span>
                                    @if($question->pivot->negative_marks > 0)
                                    <span class="badge bg-danger">-{{ $question->pivot->negative_marks ??
                                        $question->negative_marks }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-3">No questions added to this exam.</p>
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

            <!-- Recent Results -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Results</h6>
                </div>
                <div class="card-body">
                    @if(isset($recentResults) && $recentResults->count() > 0)
                    @foreach($recentResults as $result)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <strong>{{ $result->student->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $result->created_at->format('d M Y') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                {{ round($result->percentage, 1) }}%
                            </span>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted text-center py-3">No results yet</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($exam->status == 'draft')
                        <button type="button" class="btn btn-success" onclick="publishExam()">
                            <i class="bi bi-check-circle"></i> Publish Exam
                        </button>
                        @endif
                        <a href="{{ route('instructor.exams.results', $exam) }}" class="btn btn-info">
                            <i class="bi bi-bar-chart"></i> Detailed Results
                        </a>
                        <button type="button" class="btn btn-primary" onclick="shareExam()">
                            <i class="bi bi-share"></i> Share Exam Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function publishExam() {
        if (confirm('Are you sure you want to publish this exam?')) {
            $.ajax({
                url: '{{ route("instructor.exams.publish", $exam) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success('Exam published successfully');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function() {
                    toastr.error('Error publishing exam');
                }
            });
        }
    }

    function shareExam() {
        const url = '{{ route("student.exams.show", $exam) }}';
        navigator.clipboard.writeText(url);
        toastr.success('Exam link copied to clipboard!');
    }
</script>
@endpush
@endsection