@extends('layouts.instructor')

@section('title', 'My Quizzes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Quizzes</h1>
        <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Quiz
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('instructor.quizzes.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search quizzes..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="batch_id" class="form-control select2">
                        <option value="">All Batches</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quizzes List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Quiz Title</th>
                            <th>Batch</th>
                            <th>Questions</th>
                            <th>Duration</th>
                            <th>Total Marks</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quizzes as $quiz)
                        <tr>
                            <td>{{ $quiz->id }}</td>
                            <td>
                                <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="text-decoration-none fw-bold">
                                    {{ $quiz->title }}
                                </a>
                            </td>
                            <td>{{ $quiz->batch->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $quiz->questions_count }}</span>
                            </td>
                            <td>{{ $quiz->duration_minutes }} min</td>
                            <td>{{ $quiz->total_marks }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $quiz->student_exams_count }}</span>
                                @if($quiz->attempts_allowed > 1)
                                    <br><small>max {{ $quiz->attempts_allowed }}</small>
                                @endif
                            </td>
                            <td>
                                @if($quiz->average_score > 0)
                                    <span class="text-{{ $quiz->average_score >= 60 ? 'success' : 'warning' }}">
                                        {{ round($quiz->average_score, 1) }}%
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($quiz->status) }}
                                </span>
                            </td>
                            <td>{{ $quiz->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('instructor.quizzes.show', $quiz) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('instructor.quizzes.edit', $quiz) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success"
                                            onclick="viewResults({{ $quiz->id }})" title="Results">
                                        <i class="bi bi-bar-chart"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteQuiz({{ $quiz->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="bi bi-lightning fs-3 d-block mb-2"></i>
                                No quizzes found.
                                <a href="{{ route('instructor.quizzes.create') }}" class="d-block mt-2">
                                    Create your first quiz
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $quizzes->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function viewResults(quizId) {
        window.location.href = `/instructor/quizzes/${quizId}/results`;
    }

    function deleteQuiz(id) {
        if (confirm('Are you sure you want to delete this quiz?')) {
            const form = document.getElementById('delete-form');
            form.action = `/instructor/quizzes/${id}`;
            form.submit();
        }
    }
</script>
@endpush
