@extends('layouts.admin')

@section('title', 'Add Student')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Student</h1>
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Student Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ old('phone') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="roll_no" class="form-label">Roll Number</label>
                            <input type="text" class="form-control @error('roll_no') is-invalid @enderror" id="roll_no"
                                name="roll_no" value="{{ old('roll_no') }}">
                            @error('roll_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="batch_ids" class="form-label">Assign to Batches</label>
                    <select class="form-control select2 @error('batch_ids') is-invalid @enderror" id="batch_ids"
                        name="batch_ids[]" multiple>
                        @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ in_array($batch->id, old('batch_ids', [])) ? 'selected' : ''
                            }}>
                            {{ $batch->name }} ({{ $batch->course->title }})
                        </option>
                        @endforeach
                    </select>
                    @error('batch_ids')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select one or more batches for this student</small>
                </div>

                <div class="mb-3">
                    <label for="avatar" class="form-label">Profile Picture</label>
                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar"
                        name="avatar" accept="image/*">
                    @error('avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Max size: 2MB. Supported: JPG, PNG, GIF</small>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active Account</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Create Student
                </button>
            </form>
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
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select batches',
            allowClear: true
        });
    });
</script>
@endpush