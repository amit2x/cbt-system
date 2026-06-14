@extends('layouts.instructor')

@section('title', $course->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $course->title }}</h1>
        <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Course Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Information</h6>
                </div>
                <div class="card-body">
                    <p>{{ $course->description ?? 'No description available' }}</p>
                </div>
            </div>

            <!-- Modules -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Course Modules</h6>
                </div>
                <div class="card-body">
                    @if($course->modules->count() > 0)
                    <div class="accordion" id="modulesAccordion">
                        @foreach($course->modules as $index => $module)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#module{{ $module->id }}">
                                    {{ $module->title }}
                                </button>
                            </h2>
                            <div id="module{{ $module->id }}"
                                class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}">
                                <div class="accordion-body">
                                    {{ $module->description ?? 'No description' }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No modules available</p>
                    @endif
                </div>
            </div>

            <!-- Recent Exams -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Exams</h6>
                </div>
                <div class="card-body">
                    @if($recentExams->count() > 0)
                    <div class="list-group">
                        @foreach($recentExams as $exam)
                        <a href="{{ route('instructor.exams.show', $exam) }}"
                            class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $exam->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $exam->questions_count }} questions</small>
                                </div>
                                <span class="badge bg-success">{{ $exam->duration_minutes }} min</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No exams yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Your Batches -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Batches</h6>
                </div>
                <div class="card-body">
                    @if($course->batches->count() > 0)
                    @foreach($course->batches as $batch)
                    <div class="mb-3 pb-2 border-bottom">
                        <h6>{{ $batch->name }}</h6>
                        <div class="small">
                            <div>Status:
                                <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </div>
                            <div>Students: {{ $batch->students_count }}</div>
                            <div>Duration: {{ $batch->start_date->format('d M') }} - {{ $batch->end_date->format('d M')
                                }}</div>
                        </div>
                        <a href="{{ route('instructor.batches.show', $batch) }}"
                            class="btn btn-sm btn-outline-primary mt-2">
                            Manage Batch
                        </a>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted mb-0">No batches assigned</p>
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
                        <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-success">
                            <i class="bi bi-lightning"></i> Launch Quick Quiz
                        </a>
                        <button type="button" class="btn btn-info" onclick="launchFeedback()">
                            <i class="bi bi-chat"></i> Get Class Feedback
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function launchFeedback() {
    // Launch feedback modal
    $('#feedbackModal').modal('show');
}
</script>
@endsection