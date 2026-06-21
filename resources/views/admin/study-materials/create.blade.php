@extends('layouts.admin')

@section('title', 'Add Study Material')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Study Material</h1>
        <a href="{{ route('admin.study-materials.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Material Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.study-materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content (for text-based materials)</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="8">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".pdf,.doc,.docx,.mp4,.avi,.mkv,.xlsx,.xls,.ppt,.pptx">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Max size: 20MB. Supported: PDF, DOC, DOCX, MP4, AVI, MKV</small>
                        </div>

                        <div class="mb-3">
                            <label for="external_url" class="form-label">External URL (for link type)</label>
                            <input type="url" class="form-control @error('external_url') is-invalid @enderror" 
                                   id="external_url" name="external_url" value="{{ old('external_url') }}" placeholder="https://example.com">
                            @error('external_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="material_type" class="form-label">Material Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('material_type') is-invalid @enderror" 
                                    id="material_type" name="material_type" required onchange="toggleFields()">
                                @foreach($materialTypes as $type)
                                    <option value="{{ $type }}" {{ old('material_type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('material_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('course_id') is-invalid @enderror" 
                                    id="course_id" name="course_id" required onchange="loadModules()">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="module_id" class="form-label">Module (Optional)</label>
                            <select class="form-control select2 @error('module_id') is-invalid @enderror" 
                                    id="module_id" name="module_id">
                                <option value="">Select Module</option>
                            </select>
                            @error('module_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-control select2 @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create Material
                    </button>
                </div>
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
            placeholder: 'Select an option',
            allowClear: true
        });

        // Load modules when course is selected
        if ($('#course_id').val()) {
            loadModules();
        }
    });

    function toggleFields() {
        const type = $('#material_type').val();
        
        // Hide all optional fields first
        $('#file').closest('.mb-3').hide();
        $('#external_url').closest('.mb-3').hide();
        $('#content').closest('.mb-3').hide();
        
        // Show relevant fields based on type
        if (type === 'pdf' || type === 'document' || type === 'video') {
            $('#file').closest('.mb-3').show();
        } else if (type === 'link') {
            $('#external_url').closest('.mb-3').show();
        } else if (type === 'content') {
            $('#content').closest('.mb-3').show();
        }
    }

    function loadModules() {
        const courseId = $('#course_id').val();
        const moduleSelect = $('#module_id');
        
        moduleSelect.empty().append('<option value="">Select Module</option>');
        
        if (courseId) {
            $.ajax({
                url: `/admin/study-materials/modules/${courseId}`,
                method: 'GET',
                success: function(data) {
                    if (data.length > 0) {
                        data.forEach(function(module) {
                            moduleSelect.append(
                                `<option value="${module.id}">${module.title}</option>`
                            );
                        });
                    } else {
                        moduleSelect.append('<option value="">No modules available</option>');
                    }
                },
                error: function() {
                    moduleSelect.append('<option value="">Error loading modules</option>');
                }
            });
        }
    }

    // Trigger toggle on page load
    $(document).ready(function() {
        toggleFields();
    });
</script>
@endpush