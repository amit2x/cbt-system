@extends('layouts.admin')

@section('title', 'Courses Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Courses</h1>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Course
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Modules</th>
                            <th>Batches</th>
                            <th>Instructors</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>
                                @if($course->thumbnail)
                                    <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                         alt="{{ $course->title }}"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->modules_count }}</td>
                            <td>{{ $course->batches_count }}</td>
                            <td>
                                @foreach($course->instructors as $instructor)
                                    <span class="badge bg-info">{{ $instructor->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $course->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.courses.show', $course) }}"
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.courses.edit', $course) }}"
                                   class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.courses.destroy', $course) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No courses found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
document.addEventListener("DOMContentLoaded", function () {

    if (window.toastr) {
        toastr.success("Toastr is working!");
        console.log("Toastr test fired");
    } else {
        console.log("Toastr not loaded");
    }

});
</script>
@endpush
