@extends('layouts.instructor')

@section('title', 'My Courses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Courses</h1>
    </div>

    <div class="row">
        @forelse($courses as $course)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">{{ $course->title }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ Str::limit($course->description, 100) }}</p>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-grid"></i> Modules:</span>
                                <span class="badge bg-info">{{ $course->modules_count }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-people"></i> Batches:</span>
                                <span class="badge bg-success">{{ $course->batches_count }}</span>
                            </div>
                        </div>

                        @if($course->batches->count() > 0)
                            <h6 class="mb-2">Your Batches:</h6>
                            <div class="list-group mb-3">
                                @foreach($course->batches as $batch)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $batch->name }}
                                        <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : 'secondary' }}">
                                            {{ $batch->students_count }} students
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('instructor.courses.show', $course) }}"
                           class="btn btn-primary w-100">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-book fs-1 text-muted"></i>
                    <p class="mt-3">No courses assigned yet.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $courses->links() }}
    </div>
</div>
@endsection
