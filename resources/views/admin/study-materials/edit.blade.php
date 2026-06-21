@extends('layouts.admin')

@section('title', 'Edit Study Material')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit: {{ $studyMaterial->title }}</h1>
        <div>
            <a href="{{ route('admin.study-materials.show', $studyMaterial) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Material
            </a>
            <a href="{{ route('admin.study-materials.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Material Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.study-materials.update', $studyMaterial) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $studyMaterial->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $studyMaterial->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="8">{{ old('content', $studyMaterial->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload New File (Leave empty to keep current)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".pdf,.doc,.docx,.mp4,.avi,.mkv">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($studyMaterial->file_path)
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Current file: {{ $studyMaterial->file_name }}
                                    </span>
                                    <br>
                                    <small class="text-muted">Size: {{ $studyMaterial->formatted_file_size }}</small>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="external_url" class="form-label">External URL</label>
                            <input type="url" class="form-control @error('external_url') is-invalid @enderror" 
                                   id="external_url" name="external_url" value="{{ old('external_url', $studyMaterial->external_url) }}">
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
                                    <option value="{{ $type }}" {{ old('material_type', $studyMaterial->material_type) == $type ? 'selected' : '' }}>
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
                                    <option value="{{ $course->id }}" {{ old('course_id', $studyMaterial->course_id) == $course->id ? 'selected' : '' }}>
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
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id', $studyMaterial->module_id) == $module->id ? 'selected' : '' }}>
                                        {{ $module->title }}
                                    </option>
                                @endforeach
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
                                    <option value="{{ $category->id }}" {{ old('category_id', $studyMaterial->category_id) == $category->id ? 'selected' : '' }}>
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
                                <option value="draft" {{ old('status', $studyMaterial->status) == 'draft' ? 'selected' : '' }}>Draft</option>
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
                        <i class="bi bi-save"></i> Update Material
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

        toggleFields();
        
        // Load modules based on current course
        if ($('#course_id').val()) {
            loadModules();
        }
    });

    function toggleFields() {
        const type = $('#material_type').val();
        
        $('#file').closest('.mb-3').hide();
        $('#external_url').closest('.mb-3').hide();
        $('#content').closest('.mb-3').hide();
        
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
        const currentModule = '{{ $studyMaterial->module_id }}';
        
        moduleSelect.empty().append('<option value="">Select Module</option>');
        
        if (courseId) {
            $.ajax({
                url: `/admin/study-materials/modules/${courseId}`,
                method: 'GET',
                success: function(data) {
                    data.forEach(function(module) {
                        const selected = module.id == currentModule ? 'selected' : '';
                        moduleSelect.append(
                            `<option value="${module.id}" ${selected}>${module.title}</option>`
                        );
                    });
                }
            });
        }
    }
</script>
@endpush