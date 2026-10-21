{{-- resources/views/admin/roles/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Role: {{ $role->name }}</h1>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $role->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <hr>



                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold mb-0">Assign Permissions</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllGlobal">
                                <label class="form-check-label fw-bold" for="selectAllGlobal">Select All Globally</label>
                            </div>
                        </div>
                        <div class="row mt-3">
                            @foreach ($permissions->groupBy(function ($perm) {
            return explode('.', $perm->name)[0];
        }) as $group => $perms)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-secondary">
                                        <div class="card-header bg-light py-2">
                                            <div class="form-check">
                                                <input class="form-check-input group-checkbox" type="checkbox"
                                                    id="group_{{ $group }}" data-group="{{ $group }}">
                                                <label class="form-check-label fw-bold text-capitalize"
                                                    for="group_{{ $group }}">
                                                    {{ $group }} Module
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            @foreach ($perms as $permission)
                                                <div class="form-check">
                                                    <input class="form-check-input perm-checkbox group-{{ $group }}"
                                                        type="checkbox" name="permissions[]"
                                                        value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                        {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ ucfirst(explode('.', $permission->name)[1] ?? $permission->name) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Group Select Logic
            document.querySelectorAll('.group-checkbox').forEach(groupBox => {
                groupBox.addEventListener('change', function() {
                    const group = this.dataset.group;
                    const isChecked = this.checked;
                    document.querySelectorAll(`.group-${group}`).forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });
            });

            // 2. Global Select All Logic
            document.getElementById('selectAllGlobal').addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.perm-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                document.querySelectorAll('.group-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        });
    </script>
@endpush
@push('scripts')
    <script>
        // document.querySelectorAll('.group-checkbox').forEach(groupBox => {
        //     groupBox.addEventListener('change', function() {
        //         const group = this.dataset.group;
        //         const isChecked = this.checked;
        //         document.querySelectorAll(`.group-${group}`).forEach(checkbox => {
        //             checkbox.checked = isChecked;
        //         });
        //     });
        // });
    </script>
@endpush
