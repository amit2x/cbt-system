@extends('layouts.admin')

@section('title', 'Edit Module - ' . $module->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Module: {{ $module->title }}</h1>
        <div>
            <a href="{{ route('admin.modules.show', [$course, $module]) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Module
            </a>
            <a href="{{ route('admin.modules.index', $course) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Modules
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Module Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.modules.update', [$course, $module]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Module Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title', $module->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="5">{{ old('description', $module->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control @error('status') is-invalid @enderror" 
                            id="status" name="status">
                        <option value="active" {{ old('status', $module->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Module
                </button>
            </form>
        </div>
    </div>
</div>
@endsection