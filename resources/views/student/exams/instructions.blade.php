@extends('layouts.student')

@section('title', 'Exam Instructions')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ $exam->title }} - Instructions</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5 class="alert-heading">⚠️ Important Instructions</h5>
                        <p>Please read the following instructions carefully before starting the exam.</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">📋 Exam Details</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Type: </td>
                                            <td><strong>{{ $exam->type_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Duration: </td>
                                            <td><strong>{{ $exam->formatted_duration }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Questions: </td>
                                            <td><strong>{{ $exam->question_count }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Marks: </td>
                                            <td><strong>{{ $exam->total_marks }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Passing Percentage: </td>
                                            <td><strong>{{ $exam->passing_percentage }}%</strong></td>
                                        </tr>
                                        @if($exam->negative_marking > 0)
                                        <tr>
                                            <td>Negative Marking: </td>
                                            <td><strong class="text-danger">{{ $exam->negative_marking }}</strong></td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">⏰ Important Dates</h6>
                                    @if($exam->is_scheduled)
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Start Time: </td>
                                            <td><strong>{{ $exam->start_time->format('d M Y, h:i A') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>End Time: </td>
                                            <td><strong>{{ $exam->end_time->format('d M Y, h:i A') }}</strong></td>
                                        </tr>
                                    </table>
                                    @else
                                    <p class="mb-0">This exam is always available. You can start anytime.</p>
                                    @endif
                                    <hr>
                                    <p class="mb-0">
                                        <strong>Attempts Allowed:</strong>
                                        <span class="badge bg-info">{{ $exam->attempts_allowed }}</span>
                                        @if($attempts > 0)
                                        <br><small class="text-warning">You have used {{ $attempts }} attempt(s)</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($exam->instructions)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">📝 Specific Instructions</h6>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($exam->instructions)) !!}
                        </div>
                    </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">📌 General Guidelines</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">✓ The timer will start as soon as you click "Start Exam"
                                </li>
                                <li class="list-group-item">✓ You can navigate between questions using the question
                                    palette</li>
                                <li class="list-group-item">✓ Answers are auto-saved periodically</li>
                                <li class="list-group-item">✓ You can mark questions for review</li>
                                <li class="list-group-item">✓ Do not refresh the page during the exam</li>
                                <li class="list-group-item">✓ The exam will auto-submit when time expires</li>
                                <li class="list-group-item">✓ Do not switch tabs/windows during the exam</li>
                                <li class="list-group-item">✓ Ensure stable internet connection</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="agreeCheck">
                        <label class="form-check-label" for="agreeCheck">
                            I have read and understood all the instructions. I agree to follow the exam rules.
                        </label>
                    </div>

                    <div class="text-center">
                        <form action="{{ route('student.exams.start', $exam) }}" method="POST" id="startExamForm">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg" id="startBtn" disabled>
                                <i class="bi bi-play-circle"></i> Start Exam
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('agreeCheck').addEventListener('change', function() {
        document.getElementById('startBtn').disabled = !this.checked;
    });

    document.getElementById('startExamForm').addEventListener('submit', function(e) {
        const startBtn = document.getElementById('startBtn');
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Starting Exam...';
    });
</script>
@endsection