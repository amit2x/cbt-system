@extends('layouts.admin')

@section('title', 'Edit Course')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Course: {{ $course->title }}</h1>
        <div>
            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Course
            </a>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Course Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $course->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="8">{{ old('description', $course->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">Current Thumbnail</label>
                            @if($course->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                         alt="{{ $course->title }}"
                                         class="img-fluid rounded" style="max-height: 150px;">
                                </div>
                            @endif
                            <label for="thumbnail" class="form-label">Update Thumbnail</label>
                            <input type="file" class="form-control @error('thumbnail') is-invalid @enderror"
                                   id="thumbnail" name="thumbnail" accept="image/*">
                            <small class="text-muted">Max size: 2MB. Leave empty to keep current</small>
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="instructors" class="form-label">Assign Instructors</label>
                            <select class="form-control select2 @error('instructors') is-invalid @enderror"
                                    id="instructors" name="instructors[]" multiple>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}"
                                        {{ in_array($instructor->id, old('instructors', $course->instructors->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructors')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $course->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $course->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modules Management Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Course Modules</h6>
            <div>
                <a href="{{ route('admin.modules.create', $course) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle"></i> Add Module
                </a>
                <a href="{{ route('admin.modules.index', $course) }}" class="btn btn-sm btn-info">
                    <i class="bi bi-list"></i> Manage All
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($course->modules->count() > 0)
                <div class="modules-list" id="modules-list">
                    @foreach($course->modules as $module)
                        <div class="card mb-2 module-item" data-id="{{ $module->id }}">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $module->title }}</h6>
                                    <small class="text-muted">{{ Str::limit($module->description, 100) }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $module->status == 'active' ? 'success' : 'secondary' }} me-2">
                                        {{ ucfirst($module->status) }}
                                    </span>
                                    <a href="{{ route('admin.modules.show', [$course, $module]) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.modules.edit', [$course, $module]) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-module" 
                                            data-id="{{ $module->id }}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted my-3">No modules added yet.</p>
            @endif
        </div>
    </div>
</div>

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addModuleForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="module_title" class="form-label">Module Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="module_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="module_description" class="form-label">Description</label>
                        <textarea class="form-control" id="module_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editModuleForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_module_id" name="module_id">
                    <div class="mb-3">
                        <label for="edit_module_title" class="form-label">Module Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_module_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_module_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_module_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .module-item {
        cursor: move;
    }
    .module-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        // Initialize Select2
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2').select2({
                placeholder: 'Select instructors',
                allowClear: true
            });
        }

        // Initialize Sortable for modules
        const modulesList = document.getElementById('modules-list');
        if (modulesList && typeof Sortable !== 'undefined') {
            new Sortable(modulesList, {
                animation: 150,
                onEnd: function() {
                    updateModuleOrder();
                }
            });
        }

        function showToast(type, message) {
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(message);
            }
        }

        function closeModal(modalId) {
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        // Add Module Form Submit - Using new module route
        const addModuleForm = document.getElementById('addModuleForm');
        if (addModuleForm) {
            addModuleForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                // Changed to use the new module route
                const url = '{{ route("admin.modules.store", $course) }}';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.success) {
                        closeModal('addModuleModal');
                        addModuleForm.reset();
                        showToast('success', data.message || 'Module added successfully');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Error adding module');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Error adding module');
                });
            });
        }

        // Edit Module Button Click
        document.querySelectorAll('.edit-module').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const title = this.dataset.title;
                const description = this.dataset.description;

                document.getElementById('edit_module_id').value = id;
                document.getElementById('edit_module_title').value = title;
                document.getElementById('edit_module_description').value = description;

                const modal = new bootstrap.Modal(document.getElementById('editModuleModal'));
                modal.show();
            });
        });

        // Update Module Form Submit - Using new module route
        const editModuleForm = document.getElementById('editModuleForm');
        if (editModuleForm) {
            editModuleForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const moduleId = document.getElementById('edit_module_id').value;
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                // Changed to use the new module route
                const url = '{{ route("admin.modules.update", ["course" => $course, "module" => "__MODULE_ID__"]) }}'.replace('__MODULE_ID__', moduleId);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.success) {
                        closeModal('editModuleModal');
                        showToast('success', data.message || 'Module updated successfully');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Error updating module');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Error updating module');
                });
            });
        }

        // Delete Module Button Click
        document.querySelectorAll('.delete-module').forEach(button => {
            button.addEventListener('click', function() {
                if (!confirm('Are you sure you want to delete this module?')) return;

                const moduleId = this.dataset.id;
                const url = '{{ route("admin.modules.destroy", ["course" => $course, "module" => "__MODULE_ID__"]) }}'.replace('__MODULE_ID__', moduleId);

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.success) {
                        showToast('success', data.message || 'Module deleted successfully');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Error deleting module');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Error deleting module');
                });
            });
        });

        // Update module order function - Using new module route
        function updateModuleOrder() {
            const modules = [];
            document.querySelectorAll('.module-item').forEach((item, index) => {
                modules.push({
                    id: item.dataset.id,
                    order: index
                });
            });

            // Changed to use the new module route
            const url = '{{ route("admin.modules.order", $course) }}';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ modules: modules })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.success) {
                    showToast('success', 'Module order updated');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });

})();
</script>
@endpush
