@extends('layouts.admin')

@section('title', 'Study Material Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $studyMaterial->title }}</h1>
        <div>
            <a href="{{ route('admin.study-materials.edit', $studyMaterial) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @if($studyMaterial->file_path)
                <a href="{{ route('admin.study-materials.download', $studyMaterial) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Download
                </a>
            @endif
            <a href="{{ route('admin.study-materials.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Material Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Material Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Title:</th>
                            <td><strong>{{ $studyMaterial->title }}</strong></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>
                                <span class="badge bg-{{ $studyMaterial->material_type_badge }}">
                                    {{ ucfirst($studyMaterial->material_type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $studyMaterial->description ?? 'No description' }}</td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td>{{ $studyMaterial->course->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Module:</th>
                            <td>{{ $studyMaterial->module->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td>{{ $studyMaterial->category->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $studyMaterial->status_badge }}">
                                    {{ ucfirst($studyMaterial->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $studyMaterial->creator->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $studyMaterial->created_at->format('d M Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $studyMaterial->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Content -->
            @if($studyMaterial->content)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Content</h6>
                    </div>
                    <div class="card-body">
                        {!! nl2br(e($studyMaterial->content)) !!}
                    </div>
                </div>
            @endif

            <!-- External URL -->
            @if($studyMaterial->external_url)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">External Link</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ $studyMaterial->external_url }}" target="_blank" class="btn btn-info">
                            <i class="bi bi-box-arrow-up-right"></i> Open Link
                        </a>
                    </div>
                </div>
            @endif

            <!-- File Preview -->
            @if($studyMaterial->file_path)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">File</h6>
                    </div>
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-pdf display-1 text-danger"></i>
                        <p class="mt-2">
                            <strong>{{ $studyMaterial->file_name }}</strong>
                            <br>
                            <span class="text-muted">{{ $studyMaterial->formatted_file_size }}</span>
                        </p>
                        <a href="{{ route('admin.study-materials.download', $studyMaterial) }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Download File
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-primary">{{ $studyMaterial->views }}</h2>
                        <p class="text-muted">Total Views</p>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Downloads:</span>
                        <span class="fw-bold">{{ $studyMaterial->downloads }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Created:</span>
                        <span class="fw-bold">{{ $studyMaterial->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.study-materials.edit', $studyMaterial) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Material
                        </a>
                        @if($studyMaterial->file_path)
                            <a href="{{ route('admin.study-materials.download', $studyMaterial) }}" class="btn btn-success">
                                <i class="bi bi-download"></i> Download File
                            </a>
                        @endif
                        <form action="{{ route('admin.study-materials.toggle-status', $studyMaterial) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-{{ $studyMaterial->status == 'published' ? 'warning' : 'success' }} w-100">
                                <i class="bi bi-{{ $studyMaterial->status == 'published' ? 'pause' : 'play' }}"></i>
                                {{ $studyMaterial->status == 'published' ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.study-materials.destroy', $studyMaterial) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this material?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Material
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection