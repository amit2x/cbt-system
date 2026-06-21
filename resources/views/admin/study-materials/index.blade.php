@extends('layouts.admin')

@section('title', 'Study Materials')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Study Materials</h1>
        <div>
            <a href="{{ route('admin.study-materials.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Material
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.study-materials.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="course_id" class="form-control select2">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="material_type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($materialTypes as $type)
                            <option value="{{ $type }}" {{ request('material_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.study-materials.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Materials List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Course/Module</th>
                            <th>Category</th>
                            <th>File</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $material)
                        <tr>
                            <td>
                                <a href="{{ route('admin.study-materials.show', $material) }}" class="fw-bold">
                                    {{ $material->title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-{{ $material->material_type_badge }}">
                                    {{ ucfirst($material->material_type) }}
                                </span>
                            </td>
                            <td>
                                @if($material->course)
                                    <strong>{{ $material->course->title }}</strong>
                                    @if($material->module)
                                        <br>
                                        <small class="text-muted">Module: {{ $material->module->title }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($material->category)
                                    {{ $material->category->name }}
                                @endif
                            </td>
                            <td>
                                @if($material->file_path)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Uploaded
                                    </span>
                                    <br>
                                    <small>{{ $material->formatted_file_size }}</small>
                                @elseif($material->external_url)
                                    <span class="badge bg-info">
                                        <i class="bi bi-link"></i> Link
                                    </span>
                                @else
                                    <span class="badge bg-secondary">No file</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $material->status_badge }}">
                                    {{ ucfirst($material->status) }}
                                </span>
                            </td>
                            <td>{{ $material->views }}</td>
                            <td>{{ $material->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.study-materials.show', $material) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.study-materials.edit', $material) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($material->file_path)
                                        <a href="{{ route('admin.study-materials.download', $material) }}" 
                                           class="btn btn-sm btn-success" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.study-materials.toggle-status', $material) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $material->status == 'published' ? 'warning' : 'success' }}" 
                                                title="{{ $material->status == 'published' ? 'Unpublish' : 'Publish' }}">
                                            <i class="bi bi-{{ $material->status == 'published' ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.study-materials.destroy', $material) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this material?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-journal fs-1 text-muted"></i>
                                <p class="mt-2">No study materials found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $materials->withQueryString()->links() }}
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
            placeholder: 'Select Course',
            allowClear: true
        });
    });
</script>
@endpush