@extends('layouts.instructor')

@section('title', $studyMaterial->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $studyMaterial->title }}</h4>
                    <div>
                        @if($studyMaterial->material_type == 'link' && $studyMaterial->external_url)
                            <a href="{{ $studyMaterial->external_url }}" target="_blank" class="btn btn-light btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i> Open Link
                            </a>
                        @endif
                        @if($studyMaterial->file_path)
                            <a href="{{ route('instructor.study-materials.download', $studyMaterial) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-download"></i> Download
                            </a>
                        @endif
                        <a href="{{ route('instructor.study-materials.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Material Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Type:</th>
                                    <td>
                                        <span class="badge bg-{{ $studyMaterial->material_type_badge }}">
                                            {{ ucfirst($studyMaterial->material_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Course:</th>
                                    <td>{{ $studyMaterial->course->title ?? 'N/A' }}</td>
                                </tr>
                                @if($studyMaterial->module)
                                <tr>
                                    <th>Module:</th>
                                    <td>{{ $studyMaterial->module->title }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $studyMaterial->category->name ?? 'Uncategorized' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Views:</th>
                                    <td>{{ $studyMaterial->views }}</td>
                                </tr>
                                @if($studyMaterial->file_path)
                                <tr>
                                    <th>Downloads:</th>
                                    <td>{{ $studyMaterial->downloads }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Published:</th>
                                    <td>{{ $studyMaterial->created_at->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $studyMaterial->updated_at->diffForHumans() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($studyMaterial->description)
                        <div class="mb-4">
                            <h5>Description</h5>
                            <p class="text-muted">{{ $studyMaterial->description }}</p>
                        </div>
                    @endif

                    <!-- Content -->
                    @if($studyMaterial->content)
                        <div class="mb-4">
                            <h5>Content</h5>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($studyMaterial->content)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- External URL -->
                    @if($studyMaterial->material_type == 'link' && $studyMaterial->external_url)
                        <div class="mb-4 text-center">
                            <a href="{{ $studyMaterial->external_url }}" target="_blank" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-up-right"></i> Visit Resource
                            </a>
                        </div>
                    @endif

                    <!-- File Preview -->
                    @if($studyMaterial->file_path)
                        <div class="mb-4">
                            <h5>File Information</h5>
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf fs-1 me-3 text-danger"></i>
                                    <div>
                                        <strong>{{ $studyMaterial->file_name }}</strong>
                                        <br>
                                        <span class="text-muted">{{ $studyMaterial->formatted_file_size }}</span>
                                    </div>
                                    <div class="ms-auto">
                                        <a href="{{ route('instructor.study-materials.download', $studyMaterial) }}" class="btn btn-success">
                                            <i class="bi bi-download"></i> Download File
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PDF Preview -->
                        @if($studyMaterial->file_type == 'application/pdf')
                            <div class="mb-4">
                                <h5>Preview</h5>
                                <div class="border rounded p-2">
                                    <iframe src="{{ route('instructor.study-materials.view', $studyMaterial) }}" 
                                            width="100%" height="600" style="border: none;">
                                    </iframe>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-eye"></i> {{ $studyMaterial->views }} views
                            @if($studyMaterial->file_path)
                                <i class="bi bi-download ms-2"></i> {{ $studyMaterial->downloads }} downloads
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection