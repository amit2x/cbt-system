@extends('layouts.admin')

@section('title', 'Question Bank')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Bank</h1>
        <div>
            <a href="{{ route('admin.question-categories.index') }}" class="btn btn-secondary">
                <i class="bi bi-plus-circle"></i> Manage Category
            </a>
            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Question
            </a>
            <a href="{{ route('admin.questions.import') }}" class="btn btn-success">
                <i class="bi bi-upload"></i> Import
            </a>
            <a href="{{ route('admin.questions.export') }}" class="btn btn-info">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.questions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search questions..." value="{{ request('search') }}">
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
                    <select name="difficulty" class="form-control">
                        <option value="">All Difficulties</option>
                        @foreach($difficulties as $value => $label)
                            <option value="{{ $value }}" {{ request('difficulty') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="subject_id" class="form-control select2">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-control select2">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Questions List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Difficulty</th>
                            <th>Marks</th>
                            <th>Subject</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                        <tr>
                            <td>
                                <input type="checkbox" class="question-checkbox" value="{{ $question->id }}">
                            </td>
                            <td>{{ $question->id }}</td>
                            <td>
                                <a href="{{ route('admin.questions.show', $question) }}" class="text-decoration-none">
                                    {{ Str::limit(strip_tags($question->question_text), 50) }}
                                </a>
                                @if($question->tags)
                                    <br>
                                    @foreach($question->tags as $tag)
                                        <span class="badge bg-secondary">{{ $tag }}</span>
                                    @endforeach
                                @endif
                            </td>
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
                            <td>{{ $question->marks }}</td>
                            <td>{{ $question->subject->title ?? 'N/A' }}</td>
                            <td>
                                @foreach($question->categories as $category)
                                    <span class="badge bg-info">{{ $category->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $question->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($question->status) }}
                                </span>
                            </td>
                            <td>{{ $question->creator->name ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.questions.show', $question) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.questions.edit', $question) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteQuestion({{ $question->id }})"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No questions found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

           <!-- Bulk Actions Row -->
<div class="row mt-3">
    <div class="col-12">
        <div class="btn-group">
            <button type="button" class="btn btn-success" onclick="bulkStatus('active')">
                <i class="bi bi-check-circle"></i> Activate Selected
            </button>
            <button type="button" class="btn btn-secondary" onclick="bulkStatus('inactive')">
                <i class="bi bi-x-circle"></i> Deactivate Selected
            </button>
            <button type="button" class="btn btn-danger" onclick="bulkDelete()">
                <i class="bi bi-trash"></i> Delete Selected
            </button>
        </div>
    </div>
</div>

<!-- Pagination Row -->
<div class="row mt-3">
    <div class="col-12">
        <div class="">
            {{ $questions->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
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
document.addEventListener('DOMContentLoaded', function() {

        // Select all checkbox
        $('#select-all').on('change', function() {
            $('.question-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Update select all when individual checkboxes change
        $('.question-checkbox').on('change', function() {
            if ($('.question-checkbox:checked').length === $('.question-checkbox').length) {
                $('#select-all').prop('checked', true);
            } else {
                $('#select-all').prop('checked', false);
            }
        });
    });

    function deleteQuestion(id) {
        if (confirm('Are you sure you want to delete this question?')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/questions/${id}`;
            form.submit();
        }
    }

    function bulkStatus(status) {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one question');
            return;
        }

        if (confirm(`Are you sure you want to ${status} selected questions?`)) {
            $.ajax({
                url: '{{ route("admin.questions.bulk-status") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_ids: selectedIds,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error updating status');
                }
            });
        }
    }

    function bulkDelete() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one question');
            return;
        }

        if (confirm('Are you sure you want to delete selected questions?')) {
            $.ajax({
                url: '{{ route("admin.questions.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    question_ids: selectedIds
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error deleting questions');
                }
            });
        }
    }

    function getSelectedIds() {
        const ids = [];
        $('.question-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
</script>
@endpush
