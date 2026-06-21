@extends('layouts.student')

@section('title', 'Study Materials')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Study Materials</h1>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('student.study-materials') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search materials..." value="{{ request('search') }}">
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
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                        <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Document</option>
                        <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="link" {{ request('type') == 'link' ? 'selected' : '' }}>Link</option>
                        <option value="content" {{ request('type') == 'content' ? 'selected' : '' }}>Content</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <a href="{{ route('student.study-materials') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Materials Grid -->
    <div class="row">
        @forelse($materials as $material)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-{{ $material->material_type_badge }}">
                                {{ ucfirst($material->material_type) }}
                            </span>
                            @if($material->category)
                                <span class="badge bg-secondary">{{ $material->category->name }}</span>
                            @endif
                        </div>
                        
                        <h5 class="card-title">
                            <a href="{{ route('student.study-materials.show', $material) }}" class="text-decoration-none">
                                {{ $material->title }}
                            </a>
                        </h5>
                        
                        <p class="card-text text-muted small">
                            {{ Str::limit($material->description, 100) }}
                        </p>
                        
                        <div class="mb-2">
                            @if($material->course)
                                <small class="text-muted">
                                    <i class="bi bi-book"></i> {{ $material->course->title }}
                                </small>
                                @if($material->module)
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-grid"></i> Module: {{ $material->module->title }}
                                    </small>
                                @endif
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-eye"></i> {{ $material->views }} views
                                @if($material->file_path)
                                    <i class="bi bi-download ms-2"></i> {{ $material->downloads }}
                                @endif
                            </small>
                            <span class="badge bg-light text-muted">{{ $material->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        @if($material->material_type == 'link' && $material->external_url)
                            <a href="{{ $material->external_url }}" target="_blank" class="btn btn-sm btn-info w-100">
                                <i class="bi bi-box-arrow-up-right"></i> Open Link
                            </a>
                        @elseif($material->file_path)
                            <a href="{{ route('student.study-materials.download', $material) }}" class="btn btn-sm btn-success w-100">
                                <i class="bi bi-download"></i> Download
                            </a>
                        @else
                            <a href="{{ route('student.study-materials.show', $material) }}" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-eye"></i> View Content
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-journal fs-1 text-muted"></i>
                    <h4 class="mt-3">No Study Materials Available</h4>
                    <p class="text-muted">Check back later for new materials.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $materials->withQueryString()->links() }}
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