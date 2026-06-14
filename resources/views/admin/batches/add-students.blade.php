@extends('layouts.admin')

@section('title', 'Add Students to Batch')


@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Students to: {{ $batch->name }}</h1>
        <a href="{{ route('admin.batches.show', $batch) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Batch
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Select Students</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.batches.store-students', $batch) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="student_ids" class="form-label">Students</label>
                            <select class="form-control select2 @error('student_ids') is-invalid @enderror"
                                    id="student_ids" name="student_ids[]" multiple required>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select one or more students to add to this batch</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Add Selected Students
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Batch Name:</th>
                            <td>{{ $batch->name }}</td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td>{{ $batch->course->title }}</td>
                        </tr>
                        <tr>
                            <th>Capacity:</th>
                            <td>{{ $batch->students_count }}/{{ $batch->capacity }}</td>
                        </tr>
                        <tr>
                            <th>Available Seats:</th>
                            <td class="text-success">{{ $batch->available_seats }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <!-- Change this from <a> to a button that opens modal -->
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload"></i> Import via Excel
                    </button>
                    <a href="{{ route('admin.batches.export-students', $batch) }}" class="btn btn-info w-100">
                        <i class="bi bi-download"></i> Export Current List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.batches.import-students', $batch) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Students</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Excel File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">
                            Download template <a href="#" id="downloadTemplate">here</a>. Supported formats: .xlsx, .xls, .csv
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <h6>File Format:</h6>
                        <p class="mb-0">The file should have columns: name, email, phone (optional)</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
        // Optional: Handle template download
    document.getElementById('downloadTemplate')?.addEventListener('click', function(e) {
        e.preventDefault();
        // You can implement template download logic here
        window.location.href = '{{ route("admin.students.import-template") }}';
    });
</script>
@endpush
