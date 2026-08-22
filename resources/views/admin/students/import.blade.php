@extends('layouts.admin')

@section('title', 'Import Students')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Import Students</h1>
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload File</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.import.post') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file"
                                name="file" accept=".xlsx,.xls,.csv" required>
                            @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supported formats: .xlsx, .xls, .csv (Max: 10MB)</small>
                        </div>

                        <div class="mb-3">
                            <label for="batch_id" class="form-label">Assign to Batch <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2 @error('batch_id') is-invalid @enderror" id="batch_id"
                                name="batch_id" required>
                                <option value="">Select Batch</option>
                                @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ old('batch_id')==$batch->id ? 'selected' : '' }}>
                                    {{ $batch->name }} ({{ $batch->course->title }})
                                </option>
                                @endforeach
                            </select>
                            @error('batch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import Students
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Instructions</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>File Format Requirements:</h6>
                        <ul class="mb-0">
                            <li>First row must contain column headers</li>
                            <li>Required columns: name, email</li>
                            <li>Optional columns: phone, roll_no</li>
                        </ul>
                    </div>

                    <hr>

                    <h6>Sample Data:</h6>
                    <pre class="bg-light p-2 rounded" style="font-size: 12px;">
name, email, phone, organization_name, address
Amit Kumar, amitk2@aai.aero, 7633947548, AAI, NSCBIA Kolkata
                    </pre>

                    <a href="{{ route('admin.students.download-template') }}" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Students will be created with default password: <strong>password123</strong></li>
                        <li>Existing students will be skipped</li>
                        <li>All students will be assigned the 'student' role</li>
                        <li>Students will be enrolled in the selected batch</li>
                    </ul>
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
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select Batch',
            allowClear: true
        });
    });
</script>
@endpush