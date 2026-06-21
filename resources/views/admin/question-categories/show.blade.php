@extends('layouts.admin')

@section('title', 'Category Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $category->name }}</h1>
        <div>
            <a href="{{ route('admin.question-categories.edit', $category) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Category
            </a>
            <a href="{{ route('admin.question-categories.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Name:</th>
                            <td><strong>{{ $category->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $category->description ?? 'No description' }}</td>
                        </tr>
                        <tr>
                            <th>Parent Category:</th>
                            <td>
                                @if($category->parent)
                                    <a href="{{ route('admin.question-categories.show', $category->parent) }}">
                                        {{ $category->parent->name }}
                                    </a>
                                @else
                                    None
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $category->created_at->format('d M Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $category->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Subcategories -->
            @if($category->children->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Subcategories</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($category->children as $child)
                                <a href="{{ route('admin.question-categories.show', $child) }}" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    {{ $child->name }}
                                    <span class="badge bg-primary">{{ $child->questions_count }} questions</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-primary">{{ $category->questions_count }}</h2>
                        <p class="text-muted">Total Questions</p>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subcategories:</span>
                        <span class="fw-bold">{{ $category->children->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Full Path:</span>
                        <span class="fw-bold">{{ $category->full_path }}</span>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.questions.create') }}?category_id={{ $category->id }}" class="btn btn-success">
                            <i class="bi bi-question-circle"></i> Add Question to this Category
                        </a>
                        <a href="{{ route('admin.question-categories.edit', $category) }}" class="btn btn-info">
                            <i class="bi bi-pencil"></i> Edit Category
                        </a>
                        <form action="{{ route('admin.question-categories.toggle-status', $category) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-{{ $category->is_active ? 'warning' : 'success' }} w-100">
                                <i class="bi bi-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection