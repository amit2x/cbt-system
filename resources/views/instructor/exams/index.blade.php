@extends('layouts.instructor')

@section('title', 'My Exams')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Exams</h1>
        <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Quiz
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('instructor.exams.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search exams..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="mock_test" {{ request('type')=='mock_test' ? 'selected' : '' }}>Mock Test
                        </option>
                        <option value="full_exam" {{ request('type')=='full_exam' ? 'selected' : '' }}>Full Exam
                        </option>
                        <option value="quiz" {{ request('type')=='quiz' ? 'selected' : '' }}>Quiz</option>
                        <option value="practice" {{ request('type')=='practice' ? 'selected' : '' }}>Practice</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status')=='published' ? 'selected' : '' }}>Published
                        </option>
                        <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="batch_id" class="form-control select2">
                        <option value="">All Batches</option>
                        @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ request('batch_id')==$batch->id ? 'selected' : '' }}>
                            {{ $batch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('instructor.exams.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Exams List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Batch</th>
                            <th>Type</th>
                            <th>Questions</th>
                            <th>Duration</th>
                            <th>Total Marks</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                        <tr>
                            <td>{{ $exam->id }}</td>
                            <td>
                                <a href="{{ route('instructor.exams.show', $exam) }}"
                                    class="text-decoration-none fw-bold">
                                    {{ $exam->title }}
                                </a>
                            </td>
                            <td>{{ $exam->batch->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $exam->questions_count }}</span>
                            </td>
                            <td>{{ $exam->duration_minutes }} min</td>
                            <td>{{ $exam->total_marks }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $exam->student_exams_count }}</span>
                            </td>
                            <td>
                                @php
                                $avgScore = $exam->average_score ?? 0;
                                @endphp
                                <span
                                    class="badge bg-{{ $avgScore >= 70 ? 'success' : ($avgScore >= 50 ? 'warning' : 'danger') }}">
                                    {{ $avgScore }}%
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $exam->status == 'published' ? 'success' : ($exam->status == 'draft' ? 'secondary' : 'info') }}">
                                    {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('instructor.exams.show', $exam) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('instructor.exams.results', $exam) }}"
                                        class="btn btn-sm btn-success" title="Results">
                                        <i class="bi bi-bar-chart"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No exams found.
                                <a href="{{ route('instructor.quizzes.create') }}" class="d-block mt-2">Create your
                                    first quiz</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $exams->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select Batch',
            allowClear: true
        });
    });
</script>
@endpush