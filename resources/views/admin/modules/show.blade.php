@extends('layouts.admin')

@section('title', 'Module Details - ' . $module->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $module->title }}</h1>
        <div>
            <a href="{{ route('admin.modules.edit', [$course, $module]) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Module
            </a>
            <a href="{{ route('admin.modules.index', $course) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Modules
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Module Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Title:</th>
                            <td><strong>{{ $module->title }}</strong></td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td>{{ $module->course->title }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $module->description ?? 'No description' }}</td>
                        </tr>
                        <tr>
                            <th>Order:</th>
                            <td>{{ $module->order + 1 }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $module->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($module->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $module->created_at->format('d M Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $module->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Associated Exams -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Associated Exams</h6>
                </div>
                <div class="card-body">
                    @php
                        // Get exams that use questions from this module via subject_id
                        $examQuestions = DB::table('exam_questions')
                            ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
                            ->where('questions.subject_id', $module->id)
                            ->distinct()
                            ->pluck('exam_questions.exam_id');
                        
                        $exams = \App\Models\Exam::whereIn('id', $examQuestions)->get();
                    @endphp
                    
                    @if($exams->count() > 0)
                        <div class="list-group">
                            @foreach($exams as $exam)
                                <a href="{{ route('admin.exams.show', $exam) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $exam->title }}</strong>
                                            <br>
                                            <small class="text-muted">Type: {{ $exam->type_name }} | Duration: {{ $exam->duration_minutes }} min</small>
                                        </div>
                                        <span class="badge bg-primary">{{ $exam->questions_count ?? 0 }} questions</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No exams associated with this module.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-primary">{{ $exams->count() ?? 0 }}</h2>
                        <p class="text-muted">Total Exams</p>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Questions:</span>
                        <span class="fw-bold">
                            @php
                                $totalQuestions = \App\Models\Question::where('subject_id', $module->id)->count();
                            @endphp
                            {{ $totalQuestions }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Students:</span>
                        <span class="fw-bold">
                            @php
                                $totalStudents = 0;
                                foreach($exams ?? [] as $exam) {
                                    $totalStudents += $exam->studentExams()->count();
                                }
                            @endphp
                            {{ $totalStudents }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.exams.create') }}?module_id={{ $module->id }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Create Exam for this Module
                        </a>
                        <a href="{{ route('admin.questions.create') }}?module_id={{ $module->id }}" class="btn btn-info">
                            <i class="bi bi-question-circle"></i> Add Questions
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteModule({{ $course->id }}, {{ $module->id }})">
                            <i class="bi bi-trash"></i> Delete Module
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function deleteModule(courseId, moduleId) {
        if (confirm('Are you sure you want to delete this module?')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/courses/${courseId}/modules/${moduleId}`;
            form.submit();
        }
    }
</script>
@endpush