@extends('layouts.admin')

@section('title', 'Create Exam')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Exam</h1>
        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Exam Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <!-- Basic Information -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Exam Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions for Students</label>
                            <textarea class="form-control @error('instructions') is-invalid @enderror"
                                      id="instructions" name="instructions" rows="3">{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type and Category -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror"
                                            id="type" name="type" required>
                                        @foreach($types as $value => $label)
                                            <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category') is-invalid @enderror"
                                            id="category" name="category" required>
                                        @foreach($categories as $value => $label)
                                            <option value="{{ $value }}" {{ old('category') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Course and Batch -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course</label>
                                    <select class="form-control select2 @error('course_id') is-invalid @enderror"
                                            id="course_id" name="course_id">
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
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_id" class="form-label">Batch</label>
                                    <select class="form-control select2 @error('batch_id') is-invalid @enderror"
                                            id="batch_id" name="batch_id">
                                        <option value="">Select Batch</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->name }} ({{ $batch->course->title }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('batch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Multiple Batches -->
                        <div class="mb-3">
                            <label for="batch_ids" class="form-label">Additional Batches (Optional)</label>
                            <select class="form-control select2" id="batch_ids" name="batch_ids[]" multiple>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}"
                                        {{ in_array($batch->id, old('batch_ids', [])) ? 'selected' : '' }}>
                                        {{ $batch->name }} ({{ $batch->course->title }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select multiple batches if exam is for multiple batches</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Duration and Marks -->
                        <div class="mb-3">
                            <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror"
                                   id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 60) }}"
                                   min="1" max="1440" required>
                            @error('duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="passing_percentage" class="form-label">Passing Percentage <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('passing_percentage') is-invalid @enderror"
                                   id="passing_percentage" name="passing_percentage" value="{{ old('passing_percentage', 40) }}"
                                   min="0" max="100" step="0.1" required>
                            @error('passing_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="negative_marking" class="form-label">Negative Marking (per wrong answer)</label>
                            <input type="number" class="form-control @error('negative_marking') is-invalid @enderror"
                                   id="negative_marking" name="negative_marking" value="{{ old('negative_marking', 0) }}"
                                   min="0" step="0.25">
                            @error('negative_marking')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attempts_allowed" class="form-label">Attempts Allowed <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('attempts_allowed') is-invalid @enderror"
                                   id="attempts_allowed" name="attempts_allowed" value="{{ old('attempts_allowed', 1) }}"
                                   min="1" max="10" required>
                            @error('attempts_allowed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Settings -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Exam Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           id="randomize_questions" name="randomize_questions" value="1"
                                           {{ old('randomize_questions') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_questions">
                                        Randomize Questions
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           id="randomize_options" name="randomize_options" value="1"
                                           {{ old('randomize_options') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_options">
                                        Randomize Options
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           id="show_result_immediately" name="show_result_immediately" value="1"
                                           {{ old('show_result_immediately', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_result_immediately">
                                        Show Result Immediately
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           id="allow_review" name="allow_review" value="1"
                                           {{ old('allow_review', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_review">
                                        Allow Review of Answers
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduling -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Schedule Exam</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox"
                                           id="is_scheduled" name="is_scheduled" value="1"
                                           {{ old('is_scheduled') ? 'checked' : '' }}
                                           onchange="toggleSchedule()">
                                    <label class="form-check-label" for="is_scheduled">
                                        Schedule this exam
                                    </label>
                                </div>

                                <div id="schedule-fields" style="{{ old('is_scheduled') ? '' : 'display: none;' }}">
                                    <div class="mb-3">
                                        <label for="start_time" class="form-label">Start Time</label>
                                        <input type="datetime-local" class="form-control"
                                               id="start_time" name="start_time"
                                               value="{{ old('start_time') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="end_time" class="form-label">End Time</label>
                                        <input type="datetime-local" class="form-control"
                                               id="end_time" name="end_time"
                                               value="{{ old('end_time') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Initial Status</label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            <small class="text-muted">Draft: Only you can see. Published: Students can take it.</small>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create Exam
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

        // Course change handler - filter batches
        $('#course_id').on('change', function() {
            const courseId = $(this).val();
            if (courseId) {
                // Filter batches by course
                $('#batch_id option').each(function() {
                    const batch = $(this);
                    if (batch.val() && !batch.text().includes($('#course_id option:selected').text())) {
                        batch.hide();
                    } else {
                        batch.show();
                    }
                });
            } else {
                $('#batch_id option').show();
            }
            $('#batch_id').val(null).trigger('change');
        });
    });

    function toggleSchedule() {
        const isChecked = $('#is_scheduled').is(':checked');
        $('#schedule-fields').toggle(isChecked);

        if (isChecked) {
            $('#start_time').attr('required', true);
            $('#end_time').attr('required', true);
        } else {
            $('#start_time').removeAttr('required');
            $('#end_time').removeAttr('required');
        }
    }
</script>
@endpush
