@extends('layouts.admin')

@section('title', 'Edit Batch')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Batch: {{ $batch->name }}</h1>
        <div>
            <a href="{{ route('admin.batches.show', $batch) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Batch
            </a>
            <a href="{{ route('admin.batches.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Batch Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.batches.update', $batch) }}" method="POST" id="batchForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Batch Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $batch->name) }}" 
                                   maxlength="255" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('course_id') is-invalid @enderror"
                                    id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id', $batch->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="instructor_id" class="form-label">Instructor <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('instructor_id') is-invalid @enderror"
                                    id="instructor_id" name="instructor_id" required>
                                <option value="">Select Instructor</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}"
                                        {{ old('instructor_id', $batch->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="upcoming" {{ old('status', $batch->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="ongoing" {{ old('status', $batch->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ old('status', $batch->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted status-hint"></small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date"
                                   value="{{ old('start_date', $batch->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date"
                                   value="{{ old('end_date', $batch->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted end-date-hint"></small>
                        </div>

                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                   id="capacity" name="capacity" 
                                   value="{{ old('capacity', $batch->capacity) }}"
                                   min="1" max="1000" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Current enrolled: {{ $batch->students_count ?? 0 }} students</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4" 
                                      maxlength="1000">{{ old('description', $batch->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted"><span id="charCount">0</span>/1000 characters</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.batches.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-save"></i> Update Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Batch Statistics</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">{{ $batch->students->count() ?? 0 }}</h3>
                        <p class="text-muted">Total Students</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">{{ $batch->available_seats ?? 0 }}</h3>
                        <p class="text-muted">Available Seats</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">{{ round($batch->progress ?? 0) }}%</h3>
                        <p class="text-muted">Progress</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">{{ $batch->exams->count() ?? 0 }}</h3>
                        <p class="text-muted">Exams Conducted</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Character counter for description
        const description = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (description && charCount) {
            charCount.textContent = description.value.length;
            description.addEventListener('input', function() {
                charCount.textContent = this.value.length;
                if (this.value.length > 1000) {
                    charCount.classList.add('text-danger');
                } else {
                    charCount.classList.remove('text-danger');
                }
            });
        }

        // Date validation and status hints
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const status = document.getElementById('status');
        const statusHint = document.querySelector('.status-hint');
        const endDateHint = document.querySelector('.end-date-hint');
        
        function validateDates() {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Clear previous states
            endDate.classList.remove('is-invalid');
            endDateHint.textContent = '';
            status.classList.remove('is-invalid');
            statusHint.textContent = '';
            
            // Validate end date is after start date
            if (startDate.value && endDate.value) {
                if (end <= start) {
                    endDate.classList.add('is-invalid');
                    endDateHint.textContent = 'End date must be after start date';
                    endDateHint.classList.add('text-danger');
                    return false;
                } else {
                    // Calculate duration
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    endDateHint.textContent = `Duration: ${diffDays} days`;
                    endDateHint.classList.remove('text-danger');
                    endDateHint.classList.add('text-muted');
                }
                
                // Show status suggestions based on dates
                if (today < start) {
                    statusHint.textContent = 'This batch should be "Upcoming"';
                } else if (today >= start && today <= end) {
                    statusHint.textContent = 'This batch should be "Ongoing"';
                } else if (today > end) {
                    statusHint.textContent = 'This batch should be "Completed"';
                }
                statusHint.classList.add('text-info');
            }
            
            return true;
        }
        
        // Validate capacity against enrolled students
        function validateCapacity() {
            const capacity = document.getElementById('capacity');
            const enrolledStudents = {{ $batch->students_count ?? 0 }};
            
            if (capacity && enrolledStudents > 0) {
                if (parseInt(capacity.value) < enrolledStudents) {
                    capacity.classList.add('is-invalid');
                    if (!capacity.nextElementSibling || !capacity.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.classList.add('invalid-feedback');
                        feedback.textContent = `Capacity cannot be less than enrolled students (${enrolledStudents})`;
                        capacity.parentNode.appendChild(feedback);
                    }
                    return false;
                } else {
                    capacity.classList.remove('is-invalid');
                    const existingFeedback = capacity.parentNode.querySelector('.invalid-feedback');
                    if (existingFeedback) {
                        existingFeedback.remove();
                    }
                }
            }
            return true;
        }
        
        // Event listeners
        if (startDate && endDate) {
            startDate.addEventListener('change', validateDates);
            endDate.addEventListener('change', validateDates);
        }
        
        if (document.getElementById('capacity')) {
            document.getElementById('capacity').addEventListener('change', validateCapacity);
        }
        
        // Form submission validation
        document.getElementById('batchForm').addEventListener('submit', function(e) {
            const isDatesValid = validateDates();
            const isCapacityValid = validateCapacity();
            
            if (!isDatesValid || !isCapacityValid) {
                e.preventDefault();
                toastr.error('Please fix the validation errors before submitting.');
            }
        });
        
        // Run validation on page load
        validateDates();
        validateCapacity();
    });
</script>
@endpush