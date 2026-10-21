{{-- resources/views/admin/permissions/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Create Permission')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create New Permission</h1>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.permissions.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Module Group <span class="text-danger">*</span></label>
                            <input type="text" name="group" class="form-control @error('group') is-invalid @enderror"
                                value="{{ old('group') }}" placeholder="e.g. exam, user, result" required>
                            @error('group')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Lowercase, no spaces (e.g., "exam")</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Action Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="e.g. create, edit, delete" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Lowercase, no spaces (e.g., "create")</small>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> The permission will be saved as:
                        <strong>{{ old('group', 'module') }}.{{ old('name', 'action') }}</strong>
                    </div>

                    <div class="mt-3 border-top pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
