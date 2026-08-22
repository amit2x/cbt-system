@extends('layouts.admin')

@section('title', 'Batches Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Batches</h1>
        <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Batch
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.batches.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="course_id" class="form-control select2">
                        <option value="">All Courses</option>
                        @foreach(\App\Models\Course::all() as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('admin.batches.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Batches List -->
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
                            <th>Batch Name</th>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Duration</th>
                            <th>Students</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <input type="checkbox" class="batch-checkbox" value="{{ $batch->id }}">
                            </td>
                            <td>{{ $batch->id }}</td>
                            <td>
                                <a href="{{ route('admin.batches.show', $batch) }}" class="text-decoration-none">
                                    {{ $batch->name }}
                                </a>
                            </td>
                            <td>
                                {{ $batch->course->title ?? 'N/A' }}
                                @if(!$batch->course)
                                    <span class="badge bg-warning text-dark ms-1" title="No course assigned">⚠️</span>
                                @endif
                            </td>
                            <td>
                                {{ $batch->instructor->name ?? 'N/A' }}
                                @if(!$batch->instructor)
                                    <span class="badge bg-warning text-dark ms-1" title="No instructor assigned">⚠️</span>
                                @endif
                            </td>
                            <td>
                                @if($batch->start_date && $batch->end_date)
                                    {{ $batch->start_date->format('d M Y') }} <br>
                                    <small class="text-muted">to {{ $batch->end_date->format('d M Y') }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $batch->students_count ?? 0 }}/{{ $batch->capacity ?? 0 }}</span>
                                @if(($batch->available_seats ?? 0) > 0)
                                    <br><small class="text-success">{{ $batch->available_seats }} seats left</small>
                                @else
                                    <br><small class="text-danger">Full</small>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $batch->progress ?? 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($batch->progress ?? 0) }}%</small>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'upcoming' => 'secondary',
                                        'ongoing' => 'success',
                                        'completed' => 'primary'
                                    ];
                                    $status = $batch->status ?? 'upcoming';
                                @endphp
                                <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.batches.show', $batch) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.batches.edit', $batch) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success"
                                            onclick="exportBatch({{ $batch->id }})" title="Export Students">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <form action="{{ route('admin.batches.destroy', $batch) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure? This will delete the batch and all associated data.')"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No batches found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" onclick="bulkDelete()">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $batches->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('.select2').select2({ 
            placeholder: 'Select Course', 
            allowClear: true 
        });
    
        // Select all checkbox
        $('#select-all').on('change', function() {
            $('.batch-checkbox').prop('checked', $(this).prop('checked'));
        });
    });

    function exportBatch(batchId) {
        window.location.href = `/admin/batches/${batchId}/export-students`;
    }
    
    function getSelectedIds() {
        const ids = [];
        $('.batch-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }

    function bulkStatus(status) {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one batch');
            return;
        }

        // Convert status for batches (active/inactive vs ongoing/completed)
        let batchStatus = status;
        if (status === 'active') {
            batchStatus = 'ongoing';
        } else if (status === 'inactive') {
            batchStatus = 'completed';
        }

        if (confirm(`Are you sure you want to mark selected batches as ${batchStatus}?`)) {
            $.ajax({
                url: '{{ route("admin.batches.bulk-status") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    batch_ids: selectedIds,
                    status: batchStatus
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message || 'Error updating status');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Error updating status';
                    toastr.error(errorMsg);
                }
            });
        }
    }

    function bulkDelete() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one batch');
            return;
        }

        if (confirm('Are you sure you want to delete selected batches? This action cannot be undone.')) {
            $.ajax({
                url: '{{ route("admin.batches.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    batch_ids: selectedIds
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message || 'Error deleting batches');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Error deleting batches';
                    toastr.error(errorMsg);
                }
            });
        }
    }
</script>
@endpush