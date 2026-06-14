@extends('layouts.admin')

@section('title', 'Batches Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Batches</h1>
        <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Batch
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.batches.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="course_id" class="form-control select2">
                        <option value="">All Courses</option>
                        @foreach(\App\Models\Course::all() as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('admin.batches.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Batches List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Batch Name</th>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Duration</th>
                            <th>Students</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>{{ $batch->id }}</td>
                            <td>
                                <a href="{{ route('admin.batches.show', $batch) }}" class="text-decoration-none">
                                    {{ $batch->name }}
                                </a>
                            </td>
                            <td>{{ $batch->course->title }}</td>
                            <td>{{ $batch->instructor->name }}</td>
                            <td>
                                {{ $batch->start_date->format('d M Y') }} <br>
                                <small class="text-muted">to {{ $batch->end_date->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $batch->students_count }}/{{ $batch->capacity }}</span>
                                @if($batch->available_seats > 0)
                                    <br><small class="text-success">{{ $batch->available_seats }} seats left</small>
                                @else
                                    <br><small class="text-danger">Full</small>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $batch->progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($batch->progress) }}%</small>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'upcoming' => 'secondary',
                                        'ongoing' => 'success',
                                        'completed' => 'primary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$batch->status] }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.batches.show', $batch) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.batches.edit', $batch) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success"
                                            onclick="exportBatch({{ $batch->id }})" title="Export Students">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <form action="{{ route('admin.batches.destroy', $batch) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure? This will delete the batch and all associated data.')"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No batches found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $batches->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

    function exportBatch(batchId) {
        window.location.href = `/admin/batches/${batchId}/export-students`;
    }
</script>
@endpush
