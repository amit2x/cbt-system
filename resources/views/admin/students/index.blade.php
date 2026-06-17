@extends('layouts.admin')

@section('title', 'Student Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Student Management</h1>
        <div>
            <a href="{{ route('admin.students.import') }}" class="btn btn-success me-2">
                <i class="bi bi-upload"></i> Import
            </a>
            <a href="{{ route('admin.students.export') }}" class="btn btn-info me-2">
                <i class="bi bi-download"></i> Export
            </a>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Student
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.students.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by name, email, roll no..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="batch_id" class="form-control select2">
                        <option value="">All Batches</option>
                        @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ request('batch_id')==$batch->id ? 'selected' : '' }}>
                            {{ $batch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Roll No</th>
                            <th>Batches</th>
                            <th>Exams</th>
                            <th>Avg Score</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>
                                <input type="checkbox" class="student-checkbox" value="{{ $student->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}"
                                        class="rounded-circle me-2" width="35" height="35">
                                    <div>
                                        <strong>{{ $student->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->roll_no ?? 'N/A' }}</td>
                            <td>
                                @foreach($student->batches as $batch)
                                <span class="badge bg-info">{{ $batch->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $student->exams_taken }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $student->average_score >= 70 ? 'success' : ($student->average_score >= 50 ? 'warning' : 'danger') }}">
                                    {{ $student->average_score }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">
                                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student) }}"
                                        class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-{{ $student->is_active ? 'warning' : 'success' }}"
                                        onclick="toggleStatus({{ $student->id }})"
                                        title="{{ $student->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $student->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="deleteStudent({{ $student->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                No students found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="row mt-3">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        {{ $students->withQueryString()->links() }}
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

        // Select all checkbox
        $('#select-all').on('change', function() {
            $('.student-checkbox').prop('checked', $(this).prop('checked'));
        });
    });

    function deleteStudent(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/students/${id}`;
            form.submit();
        }
    }

    function toggleStatus(id) {
        if (confirm('Are you sure you want to change this student\'s status?')) {
            $.ajax({
                url: `/admin/students/${id}/toggle-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error updating status');
                }
            });
        }
    }

    function bulkStatus(status) {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }

        if (confirm(`Are you sure you want to ${status} selected students?`)) {
            $.ajax({
                url: '{{ route("admin.students.bulk-status") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    student_ids: selectedIds,
                    status: status
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error updating status');
                }
            });
        }
    }

    function bulkDelete() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }

        if (confirm('Are you sure you want to delete selected students?')) {
            $.ajax({
                url: '{{ route("admin.students.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    student_ids: selectedIds
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error deleting students');
                }
            });
        }
    }

    function getSelectedIds() {
        const ids = [];
        $('.student-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
</script>
@endpush