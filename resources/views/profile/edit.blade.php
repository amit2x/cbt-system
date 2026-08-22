@extends('layouts.' . (auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isInstructor() ? 'instructor' : 'student')))

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Profile Picture Card -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="rounded-circle mb-3"
                         style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #e9ecef;">

                    <!-- Avatar Upload Form - Direct Submission -->
                    <form action="{{ route('profile.upload-avatar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                   id="avatar" name="avatar" accept="image/*" required>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Max size: 4MB (JPG, PNG, GIF)</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload"></i> Upload Picture
                        </button>
                    </form>

                    <!-- Remove Avatar Button -->
                    @if(auth()->user()->avatar)
                        <form action="{{ route('profile.remove-avatar') }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                                <i class="bi bi-trash"></i> Remove Picture
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Profile Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ ucfirst(auth()->user()->roles->first()->name ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Member Since:</strong></td>
                            <td>{{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ auth()->user()->updated_at ? auth()->user()->updated_at->diffForHumans() : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Profile Information</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email - Read Only -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                   readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Email address cannot be changed. Contact administrator for assistance.
                            </small>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" 
                                   placeholder="Enter your phone number">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Organization Name - New Field -->
                        <div class="mb-3">
                            <label for="organization_name" class="form-label">Organization / Company Name</label>
                            <input type="text" class="form-control @error('organization_name') is-invalid @enderror"
                                   id="organization_name" name="organization_name" 
                                   value="{{ old('organization_name', auth()->user()->organization_name) }}" 
                                   placeholder="Enter your organization name">
                            @error('organization_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address - New Field -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3" 
                                      placeholder="Enter your full address">{{ old('address', auth()->user()->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Note about Password -->
                        <div class="alert alert-info mb-3" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Password Management:</strong> 
                            Password changes are managed by the administrator. 
                            Please contact admin if you need to reset your password.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Profile
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-control[readonly] {
        background-color: #e9ecef !important;
        opacity: 0.8;
        cursor: not-allowed;
    }
    .form-control[readonly]:focus {
        border-color: #ced4da;
        box-shadow: none;
    }
</style>
@endpush