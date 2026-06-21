@extends('layouts.admin')

@section('title', 'Exam Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Exams</h1>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Exam
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.exams.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search exams..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="course_id" class="form-control">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
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
                            <th>Type</th>
                            <th>Course/Batch</th>
                            <th>Duration</th>
                            <th>Questions</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                        <tr>
                            <td>{{ $exam->id }}</td>
                            <td>
                                <a href="{{ route('admin.exams.show', $exam) }}" class="text-decoration-none fw-bold">
                                    {{ $exam->title }}
                                </a>
                                <br>
                                <small class="text-muted">Created: {{ $exam->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $exam->type_badge }}">
                                    {{ $exam->type_name }}
                                </span>
                            </td>
                            <td>
                                @if($exam->course)
                                    <strong>Course:</strong> {{ $exam->course->title }}<br>
                                @endif
                                @if($exam->batch)
                                    <strong>Batch:</strong> {{ $exam->batch->name }}
                                @endif
                                @if($exam->batches->count() > 0)
                                    <strong>Batches:</strong> {{ $exam->batches->count() }}
                                @endif
                            </td>
                            <td>{{ $exam->formatted_duration }}</td>
                            <td>
                                <span class="badge bg-info">{{ $exam->questions_count }}</span>
                                <br>
                                <small class="text-muted">{{ $exam->total_marks }} marks</small>
                            </td>
                            <td>
                                @if($exam->is_scheduled)
                                    <small>
                                        <i class="bi bi-calendar"></i> {{ $exam->start_time->format('d M Y') }}<br>
                                        <i class="bi bi-clock"></i> {{ $exam->start_time->format('h:i A') }}
                                    </small>
                                @else
                                    <span class="badge bg-secondary">Not scheduled</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $exam->status_badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                </span>
                                @if($exam->is_scheduled && $exam->is_available)
                                    <br><small class="text-success">Available now</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $exam->student_exams_count }}</span>/
                                <span class="badge bg-secondary">{{ $exam->total_students }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.exams.show', $exam) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.edit', $exam) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($exam->status == 'draft')
                                        <button type="button" class="btn btn-sm btn-success"
                                                onclick="publishExam({{ $exam->id }})" title="Publish">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteExam({{ $exam->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No exams found.
                                <a href="{{ route('admin.exams.create') }}" class="d-block mt-2">Create your first exam</a>
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

<!-- Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
    

    function publishExam(id) {
        if (confirm('Are you sure you want to publish this exam?')) {
            $.ajax({
                url: `/admin/exams/${id}/publish`,
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

    function deleteExam(id) {
        if (confirm('Are you sure you want to delete this exam?')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/exams/${id}`;
            form.submit();
        }
    }
</script>
@endpush
