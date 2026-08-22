@extends('layouts.admin')

@section('title', 'Modules - ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $course->title }} - Modules</h1>
        <div>
            <a href="{{ route('admin.modules.create', $course) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Module
            </a>
            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Course
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Module List</h6>
        </div>
        <div class="card-body">
            @if($modules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>#</th>
                                <th>Title</th>
                                <th>Subject_id</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-modules">
                            @foreach($modules as $index => $module)
                            <tr data-id="{{ $module->id }}" data-order="{{ $module->order }}">
                                <td>
                                    <input type="checkbox" class="module-checkbox" value="{{ $module->id }}">
                                </td>
                                <td>
                                    <i class="bi bi-grip-vertical me-2 drag-handle" style="cursor: move;"></i>
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.modules.show', [$course, $module]) }}" class="fw-bold">
                                        {{ $module->title }}
                                    </a>
                                </td>
                                <td class="text-center text-bold"> {{ $module->id }}  </td>
                                <td>{{ Str::limit($module->description, 50) }}</td>
                                <td>
                                    <span class="badge bg-{{ $module->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($module->status) }}
                                    </span>
                                </td>
                                <td>{{ $module->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.modules.show', [$course, $module]) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.modules.edit', [$course, $module]) }}" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-{{ $module->status == 'active' ? 'warning' : 'success' }}" 
                                                onclick="toggleStatus({{ $course->id }}, {{ $module->id }})" 
                                                title="{{ $module->status == 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $module->status == 'active' ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteModule({{ $course->id }}, {{ $module->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-grid-3x3-gap-fill fs-1 text-muted"></i>
                    <p class="mt-2">No modules found for this course.</p>
                    <a href="{{ route('admin.modules.create', $course) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add First Module
                    </a>
                </div>
            @endif
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
<style>
    .drag-handle {
        cursor: move;
        color: #bdc3c7;
    }
    .drag-handle:hover {
        color: #2c3e50;
    }
    .sortable-ghost {
        opacity: 0.5;
        background: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Sortable
        const modulesList = document.getElementById('sortable-modules');
        if (modulesList) {
            new Sortable(modulesList, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    updateModuleOrder({{ $course->id }});
                }
            });
        }

        // Select all checkbox
        $('#select-all').on('change', function() {
            $('.module-checkbox').prop('checked', $(this).prop('checked'));
        });
    });

    function updateModuleOrder(courseId) {
        const modules = [];
        $('#sortable-modules tr').each(function(index) {
            modules.push({
                id: $(this).data('id'),
                order: index
            });
        });

        $.ajax({
            url: `/admin/courses/${courseId}/modules/order`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                modules: modules
            },
            success: function(response) {
                toastr.success('Module order updated');
            },
            error: function() {
                toastr.error('Error updating module order');
            }
        });
    }

    function deleteModule(courseId, moduleId) {
        if (confirm('Are you sure you want to delete this module?')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/courses/${courseId}/modules/${moduleId}`;
            form.submit();
        }
    }

    function toggleStatus(courseId, moduleId) {
        if (confirm('Are you sure you want to change this module\'s status?')) {
            $.ajax({
                url: `/admin/courses/${courseId}/modules/${moduleId}/toggle-status`,
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
            toastr.warning('Please select at least one module');
            return;
        }

        if (confirm(`Are you sure you want to ${status} selected modules?`)) {
            $.ajax({
                url: '{{ route("admin.modules.bulk-status", $course) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    module_ids: selectedIds,
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

    function bulkDelete(courseId) {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one module');
            return;
        }

        if (confirm('Are you sure you want to delete selected modules?')) {
            $.ajax({
                url: `/admin/courses/${courseId}/modules/bulk-delete`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    module_ids: selectedIds
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    toastr.error('Error deleting modules');
                }
            });
        }
    }

    function getSelectedIds() {
        const ids = [];
        $('.module-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
</script>
@endpush