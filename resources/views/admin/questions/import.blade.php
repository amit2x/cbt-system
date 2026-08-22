{{-- resources/views/admin/questions/import.blade.php --}}

@extends('layouts.admin')

@section('title', 'Import Questions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Import Questions</h1>
        <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Import Completed with Warnings</strong>
            <p>{{ session('success') }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="collapse" data-bs-target="#importErrors">
                    <i class="bi bi-chevron-down"></i> View Error Details
                </button>
                <div class="collapse mt-2" id="importErrors">
                    <div class="card card-body bg-light">
                        <ul class="mb-0">
                            @foreach(session('import_errors') as $error)
                                <li class="text-danger">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Import Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload File</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">Excel File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Supported formats: .xlsx, .xls, .csv (Max: 10MB)
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Question Type (Optional)</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type">
                                <option value="">Auto-detect from file</option>
                                <option value="single_choice">Single Choice</option>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="numerical">Numerical</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select if all questions in file are of same type</small>
                        </div>

                        <!-- ✅ NEW: Image Upload Section -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-images me-2"></i>Upload Images (Optional)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Upload images that are referenced in the Excel file. 
                                    The system will automatically match images by filename.
                                    Supported formats: JPG, PNG, GIF, WebP, SVG
                                </div>

                                <div class="mb-3">
                                    <label for="images" class="form-label">Select Images</label>
                                    <input type="file" class="form-control @error('images') is-invalid @enderror"
                                           id="images" name="images[]" accept="image/*" multiple>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        You can select multiple images at once. Max size per image: 5MB
                                    </small>
                                </div>

                                <!-- Image Preview -->
                                <div id="imagePreviewContainer" class="row g-2 mt-2" style="display: none;">
                                    <div class="col-12">
                                        <h6 class="text-muted">Selected Images:</h6>
                                    </div>
                                    <div id="imagePreviewList" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import Questions
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Instructions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Instructions</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>File Format Requirements:</h6>
                        <ul class="mb-0">
                            <li>First row must contain column headers</li>
                            <li>Required columns: question_text, type, difficulty, marks</li>
                            <li>For MCQ: option_1_text, option_1_correct, option_2_text, option_2_correct, etc.</li>
                            <li>For numerical: correct_numerical, tolerance (optional)</li>
                            <li>Max 10 options per question</li>
                        </ul>
                    </div>

                    <hr>

                    <h6>Image Support:</h6>
                    <ul class="mb-2">
                        <li><strong>URL Images:</strong> https://example.com/image.png</li>
                        <li><strong>Google Drive:</strong> https://drive.google.com/file/d/ID/view</li>
                        <li><strong>Uploaded Images:</strong> Just put filename (e.g., image.png)</li>
                    </ul>

                    <hr>

                    <h6>Sample Data:</h6>
                    <pre class="bg-light p-2 rounded" style="font-size: 11px; overflow-x: auto;">
question_text,type,difficulty,marks,negative_marks,option_1_text,option_1_correct,option_2_text,option_2_correct,option_1_image
"What is 2+2?",single_choice,easy,1,0,"3",0,"4",1,"option_b.png"
                    </pre>

                    <a href="{{ route('admin.questions.template') }}" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
            </div>

            <!-- Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Use 1 for correct, 0 for incorrect</li>
                        <li>Difficulty: easy, medium, hard</li>
                        <li>Type: single_choice, multiple_choice, numerical</li>
                        <li>Max file size: 10MB</li>
                        <li>All questions will be set to active status</li>
                        <li>Uploaded images are stored permanently</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('images');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewList = document.getElementById('imagePreviewList');

        imageInput.addEventListener('change', function() {
            previewList.innerHTML = '';
            
            if (this.files.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }

            previewContainer.style.display = 'block';

            Array.from(this.files).forEach(function(file) {
                const reader = new FileReader();
                const previewItem = document.createElement('div');
                previewItem.className = 'position-relative';
                previewItem.style.width = '80px';
                previewItem.style.height = '80px';
                
                reader.onload = function(e) {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" 
                             alt="${file.name}" 
                             class="img-fluid rounded border" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white text-center small" 
                             style="font-size: 10px; padding: 2px;">
                            ${file.name.length > 10 ? file.name.substring(0, 8) + '...' : file.name}
                        </div>
                    `;
                    previewList.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endpush