{{-- resources/views/admin/users/reset-password.blade.php --}}

@extends('layouts.admin')

@section('title', 'Reset Password - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reset Password: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to User
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Reset Password for {{ $user->email }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reset-password.post', $user) }}" method="POST">
                @csrf

                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Warning:</strong> This action will immediately change the user's password. 
                    The user will need to use the new password to log in.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 8 characters</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                       id="password_confirmation" name="password_confirmation" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="show_password" onclick="toggleAllPasswords()">
                    <label class="form-check-label" for="show_password">Show passwords</label>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-secondary me-2" onclick="generateRandomPassword()">
                        <i class="bi bi-shuffle"></i> Generate Random Password
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key"></i> Reset Password
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.type = field.type === 'password' ? 'text' : 'password';
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

    function generateRandomPassword() {
        const length = 12;
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
        let password = '';
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset.charAt(randomIndex);
        }
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        
        // Copy to clipboard
        navigator.clipboard.writeText(password).then(function() {
            toastr.success('Random password generated and copied to clipboard!');
        }).catch(function() {
            toastr.success('Random password generated! Please copy it manually.');
        });
    }
</script>
@endsection