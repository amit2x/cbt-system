@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit User: {{ $user->name }}</h1>
        <div>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control @error('role') is-invalid @enderror"
                                    id="role" name="role" required onchange="handleRoleChange()">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Batches for students (hidden by default) -->
                <div id="batch-section" style="{{ $user->hasRole('student') ? '' : 'display: none;' }}">
                    <div class="mb-3">
                        <label for="batch_ids" class="form-label">Assign to Batches</label>
                        <select class="form-control select2" id="batch_ids" name="batch_ids[]" multiple>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}"
                                    {{ in_array($batch->id, old('batch_ids', $userBatches)) ? 'selected' : '' }}>
                                    {{ $batch->name }} ({{ $batch->course->title }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select batches for student enrollment</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="avatar" class="form-label">Profile Picture</label>
                    <div class="d-flex align-items-center mb-2">
                        @if($user->avatar)
                            <img src="{{ $user->avatar_url }}" alt="Current avatar" class="rounded-circle me-3" width="60" height="60">
                        @endif
                        <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                               id="avatar" name="avatar" accept="image/*">
                    </div>
                    @error('avatar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           {{ $user->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active Account</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update User
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function handleRoleChange() {
        const role = document.getElementById('role').value;
        const batchSection = document.getElementById('batch-section');

        if (role === 'student') {
            batchSection.style.display = 'block';
        } else {
            batchSection.style.display = 'none';
        }
    }
</script>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select batches',
            allowClear: true
        });
    });
</script>
@endpush
