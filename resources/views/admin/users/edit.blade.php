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

                <!-- Password Reset Section -->
                <div class="card bg-light mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-key me-2"></i>Password Reset
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password" placeholder="Leave blank to keep current password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Leave blank to keep current password. Minimum 8 characters.</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                               id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="show_password" onclick="toggleAllPasswords()">
                            <label class="form-check-label" for="show_password">Show passwords</label>
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

                <!-- Password Reset Warning -->
                <div class="alert alert-warning mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Note:</strong> If you change the password, the user will need to use the new password to log in.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
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

    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            if (field.type === 'password') {
                field.type = 'text';
            } else {
                field.type = 'password';
            }
        }
    }

    function toggleAllPasswords() {
        const show = document.getElementById('show_password').checked;
        const passwordFields = ['password', 'password_confirmation'];
        passwordFields.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.type = show ? 'text' : 'password';
            }
        });
    }

    // Auto-generate random password (optional)
    function generateRandomPassword() {
        const length = 12;
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
        let password = '';
        for (let i = 0; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Show a toast notification
        toastr.success('Random password generated!');
    }
</script>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .input-group .btn-outline-secondary {
        border-left: none;
    }
    .input-group .form-control:focus + .btn-outline-secondary {
        border-color: #86b7fe;
    }
</style>
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