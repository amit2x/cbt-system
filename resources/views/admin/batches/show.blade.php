@extends('layouts.admin')

@section('title', $batch->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $batch->name }}</h1>
        <div>
            <a href="{{ route('admin.batches.edit', $batch) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Batch
            </a>
            <a href="{{ route('admin.batches.add-students', $batch) }}" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Add Students
            </a>
            <a href="{{ route('admin.batches.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Batch Overview -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Course:</th>
                                    <td>{{ $batch->course->title }}</td>
                                </tr>
                                <tr>
                                    <th>Instructor:</th>
                                    <td>{{ $batch->instructor->name }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td>{{ $batch->start_date->format('d M Y') }} - {{ $batch->end_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Capacity:</th>
                                    <td>{{ $batch->students_count }}/{{ $batch->capacity }} students</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Status:</th>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'upcoming' => 'secondary',
                                                'ongoing' => 'success',
                                                'completed' => 'primary'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$batch->status] }} fs-6">
                                            {{ ucfirst($batch->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Available Seats:</th>
                                    <td>
                                        <span class="text-{{ $batch->available_seats > 0 ? 'success' : 'danger' }}">
                                            {{ $batch->available_seats }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Progress:</th>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                 style="width: {{ $batch->progress }}%">
                                                {{ round($batch->progress) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $batch->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($batch->description)
                        <div class="mt-3">
                            <h6>Description:</h6>
                            <p class="text-muted">{{ $batch->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Students List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Enrolled Students</h6>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-upload"></i> Import
                        </button>
                        <a href="{{ route('admin.batches.export-students', $batch) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-download"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($batch->students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Enrolled Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->students as $student)
                                    <tr>
                                        <td>{{ $student->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $student->avatar_url }}"
                                                     alt="{{ $student->name }}"
                                                     class="rounded-circle me-2" width="30" height="30">
                                                {{ $student->name }}
                                            </div>
                                        </td>
                                        <td>{{ $student->email }}</td>
                                        <td>{{ $student->phone ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d M Y') }}</td>
                                        <td>
                                            <select class="form-select form-select-sm status-select"
                                                    data-student-id="{{ $student->id }}"
                                                    style="width: auto;">
                                                <option value="active" {{ $student->pivot->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="completed" {{ $student->pivot->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="dropped" {{ $student->pivot->status == 'dropped' ? 'selected' : '' }}>Dropped</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-student"
                                                    data-student-id="{{ $student->id }}">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted my-4">
                            No students enrolled yet.
                            <a href="{{ route('admin.batches.add-students', $batch) }}">Add students</a> or
                            <a href="#" data-bs-toggle="modal" data-bs-target="#importModal">import from Excel</a>.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Active Students:</span>
                            <span class="badge bg-success">{{ $batch->students->where('pivot.status', 'active')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Completed:</span>
                            <span class="badge bg-primary">{{ $batch->students->where('pivot.status', 'completed')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Dropped:</span>
                            <span class="badge bg-danger">{{ $batch->students->where('pivot.status', 'dropped')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Exams Scheduled:</span>
                            <span class="badge bg-info">{{ $batch->exams->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Exams</h6>
                </div>
                <div class="card-body">
                    @php
                        $upcomingExams = $batch->exams->where('start_time', '>', now())->take(3);
                    @endphp

                    @if($upcomingExams->count() > 0)
                        @foreach($upcomingExams as $exam)
                            <div class="mb-3 pb-2 border-bottom">
                                <h6>{{ $exam->title }}</h6>
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar"></i> {{ $exam->start_time->format('d M Y h:i A') }}
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Duration: {{ $exam->duration_minutes }} mins
                                </small>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No upcoming exams</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center">Activity log coming soon...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Students Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.batches.import-students', $batch) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Students</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Excel File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">
                            Download template <a href="#">here</a>. Supported formats: .xlsx, .xls, .csv
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

    document.addEventListener("DOMContentLoaded", function () {

    if (window.$) {



    $(document).ready(function() {
        // Update student status
        $('.status-select').on('change', function() {
            const studentId = $(this).data('student-id');
            const status = $(this).val();

            $.ajax({
                url: `/admin/batches/{{ $batch->id }}/students/${studentId}/status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error updating status');
                }
            });
        });

        // Remove student from batch
        $('.remove-student').on('click', function() {
            if (!confirm('Are you sure you want to remove this student from the batch?')) return;

            const studentId = $(this).data('student-id');

            $.ajax({
                url: `/admin/batches/{{ $batch->id }}/students/${studentId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Student removed successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    toastr.error('Error removing student');
                }
            });
        });
    });

    }

});
</script>
@endpush
