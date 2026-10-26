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

                        <!--<div class="mb-3">-->
                        <!--    <label for="instructions" class="form-label">Instructions for Students</label>-->
                        <!--    <textarea class="form-control @error('instructions') is-invalid @enderror"-->
                        <!--              id="instructions" name="instructions" rows="3">{{ old('instructions') }}</textarea>-->
                        <!--    @error('instructions')-->
                        <!--        <div class="invalid-feedback">{{ $message }}</div>-->
                        <!--    @enderror-->
                        <!--</div>-->
                        <div class="mb-3">
    <label for="instructions" class="form-label fw-semibold">Instructions for Students</label>
    <textarea class="form-control @error('instructions') is-invalid @enderror"
              id="instructions" 
              name="instructions" 
              rows="8">{{ old('instructions', "1. Ensure you have a stable internet connection before starting the examination.\n2. Do not refresh the browser page, navigate backward, or close the exam tab once the assessment begins.\n3. All questions are mandatory unless specified otherwise. Keep track of the countdown timer visible on your screen.\n4. Copying, pasting, printing, or attempting to inspect elements/shortcuts is strictly monitored and blocked.\n5. Switching tabs or minimizing the browser window during the test will flag your account for suspicious activity.\n6. Review your marked answers before clicking the final 'Submit Examination' button. Once submitted, answers cannot be edited.") }}</textarea>
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

                        <!-- Course and Module -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('course_id') is-invalid @enderror"
                                            id="course_id" name="course_id" required>
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
                                    <label for="module_id" class="form-label">Module <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('module_id') is-invalid @enderror"
                                            id="module_id" name="module_id" required>
                                        <option value="">Select Module</option>
                                    </select>
                                    @error('module_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Batch Selection -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Batch Assignment</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="batch_id" class="form-label">Primary Batch <span class="text-danger">*</span></label>
                                            <select class="form-control select2 @error('batch_id') is-invalid @enderror"
                                                    id="batch_id" name="batch_id" required>
                                                <option value="">Select Primary Batch</option>
                                                <!-- Options will be populated dynamically -->
                                            </select>
                                            @error('batch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Main batch for this exam</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="batch_ids" class="form-label">Additional Batches</label>
                                            <select class="form-control select2 @error('batch_ids') is-invalid @enderror"
                                                    id="batch_ids" name="batch_ids[]" multiple>
                                                <!-- Options will be populated dynamically -->
                                            </select>
                                            @error('batch_ids')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Select additional batches for this exam</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                   id="passing_percentage" name="passing_percentage" value="{{ old('passing_percentage', 80) }}"
                                   min="0" max="100" step="0.1" required>
                            @error('passing_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="negative_marking" class="form-label">Negative Marking</label>
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
                        <!--<div class="card mb-3">-->
                        <!--    <div class="card-header">-->
                        <!--        <h6 class="mb-0">Exam Settings</h6>-->
                        <!--    </div>-->
                        <!--    <div class="card-body">-->
                        <!--        <div class="form-check mb-2">-->
                        <!--            <input class="form-check-input" type="checkbox"-->
                        <!--                   id="randomize_questions" name="randomize_questions" value="1"-->
                        <!--                   {{ old('randomize_questions') ? 'checked' : '' }}>-->
                        <!--            <label class="form-check-label" for="randomize_questions">-->
                        <!--                Randomize Questions-->
                        <!--            </label>-->
                        <!--        </div>-->

                        <!--        <div class="form-check mb-2">-->
                        <!--            <input class="form-check-input" type="checkbox"-->
                        <!--                   id="randomize_options" name="randomize_options" value="1"-->
                        <!--                   {{ old('randomize_options') ? 'checked' : '' }}>-->
                        <!--            <label class="form-check-label" for="randomize_options">-->
                        <!--                Randomize Options-->
                        <!--            </label>-->
                        <!--        </div>-->

                        <!--        <div class="form-check mb-2">-->
                        <!--            <input class="form-check-input" type="checkbox"-->
                        <!--                   id="show_result_immediately" name="show_result_immediately" value="1"-->
                        <!--                   {{ old('show_result_immediately', true) ? 'checked' : '' }}>-->
                        <!--            <label class="form-check-label" for="show_result_immediately">-->
                        <!--                Show Result Immediately-->
                        <!--            </label>-->
                        <!--        </div>-->

                        <!--        <div class="form-check mb-2">-->
                        <!--            <input class="form-check-input" type="checkbox"-->
                        <!--                   id="allow_review" name="allow_review" value="1"-->
                        <!--                   {{ old('allow_review', true) ? 'checked' : '' }}>-->
                        <!--            <label class="form-check-label" for="allow_review">-->
                        <!--                Allow Review of Answers-->
                        <!--            </label>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!-- Settings -->
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Exam Settings</h6>
    </div>
    <div class="card-body">
        
        {{-- ✅ FIX: Hidden input ensures 0 is sent if unchecked --}}
        <input type="hidden" name="randomize_questions" value="0">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="randomize_questions" name="randomize_questions" value="1"
                   {{ old('randomize_questions', $exam->randomize_questions ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="randomize_questions">
                Randomize Questions
            </label>
        </div>

        {{-- ✅ FIX: Hidden input ensures 0 is sent if unchecked --}}
        <input type="hidden" name="randomize_options" value="0">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="randomize_options" name="randomize_options" value="1"
                   {{ old('randomize_options', $exam->randomize_options ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="randomize_options">
                Randomize Options
            </label>
        </div>

        {{-- ✅ FIX: Hidden input ensures 0 is sent if unchecked --}}
        <input type="hidden" name="show_result_immediately" value="0">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="show_result_immediately" name="show_result_immediately" value="1"
                   {{ old('show_result_immediately', $exam->show_result_immediately ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="show_result_immediately">
                Show Result Immediately
            </label>
        </div>

        {{-- ✅ FIX: Hidden input ensures 0 is sent if unchecked --}}
        <input type="hidden" name="allow_review" value="0">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="allow_review" name="allow_review" value="1"
                   {{ old('allow_review', $exam->allow_review ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="allow_review">
                Allow Review of Answers
            </label>
        </div>
        
        {{-- ✅ FIX: Hidden input ensures 0 is sent if unchecked --}}
        <input type="hidden" name="is_scheduled" value="0">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox"
                   id="is_scheduled" name="is_scheduled" value="1"
                   {{ old('is_scheduled', $exam->is_scheduled ?? false) ? 'checked' : '' }}
                   onchange="toggleSchedule()">
            <label class="form-check-label" for="is_scheduled">
                Schedule this exam
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
<style>
    .select2-container {
        width: 100% !important;
    }
    .loading-modules {
        position: relative;
    }
    .loading-modules::after {
        content: 'Loading...';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #999;
    }
    .loading-batches {
        position: relative;
    }
    .loading-batches::after {
        content: 'Loading batches...';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #999;
    }
    .has-error .select2-selection {
        border-color: #dc3545 !important;
    }
    .has-error .select2-selection__placeholder {
        color: #dc3545 !important;
    }
</style>
@endpush

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {

        $(document).ready(function() {
            console.log('Exam form initialized');
            

            const $courseSelect = $('#course_id');
            const $moduleSelect = $('#module_id');
            const $batchSelect = $('#batch_id');
            const $additionalBatches = $('#batch_ids');

            // ========== LOAD MODULES ==========
            function loadModules(courseId) {
                $moduleSelect.empty().append('<option value="">Select Module</option>');
                $moduleSelect.prop('disabled', true);
                $moduleSelect.closest('.mb-3').addClass('loading-modules');

                if (!courseId) {
                    $moduleSelect.prop('disabled', false);
                    $moduleSelect.closest('.mb-3').removeClass('loading-modules');
                    $moduleSelect.trigger('change');
                    return;
                }

                $.ajax({
                    url: `/admin/courses/${courseId}/modules/api`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            $.each(response.data, function(index, module) {
                                $moduleSelect.append(
                                    `<option value="${module.id}">${module.title}</option>`
                                );
                            });
                        } else {
                            toastr.error("No Modules Available for the selected course!");
                            $moduleSelect.append('<option value="">No modules available</option>');
                        }
                    },
                    error: function(xhr) {
                        toastr.error("Error occured whiled loading related modules!");
                        console.error('Error loading modules:', xhr);
                        $moduleSelect.append('<option value="">Error loading modules</option>');
                    },
                    complete: function() {
                        $moduleSelect.prop('disabled', false);
                        $moduleSelect.closest('.mb-3').removeClass('loading-modules');
                        $moduleSelect.trigger('change');
                    }
                });
            }

            // ========== LOAD BATCHES ==========
            function loadBatches(courseId) {
                // Clear both batch selects
                $batchSelect.empty().append('<option value="">Select Primary Batch</option>');
                $additionalBatches.empty();

                // Disable selects while loading
                $batchSelect.prop('disabled', true);
                $additionalBatches.prop('disabled', true);
                $batchSelect.closest('.mb-3').addClass('loading-batches');

                if (!courseId) {
                    $batchSelect.prop('disabled', false);
                    $additionalBatches.prop('disabled', false);
                    $batchSelect.closest('.mb-3').removeClass('loading-batches');
                    $batchSelect.trigger('change');
                    $additionalBatches.trigger('change');
                    return;
                }

                $.ajax({
                    url: `/admin/courses/${courseId}/batches/api`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            $.each(response.data, function(index, batch) {
                                // Add to primary batch select
                                $batchSelect.append(
                                    `<option value="${batch.id}">${batch.name} (${batch.status})</option>`
                                );
                                // Add to additional batches select
                                $additionalBatches.append(
                                    `<option value="${batch.id}">${batch.name} (${batch.status})</option>`
                                );
                            });
                        } else {
                            toastr.error("No Batch Available!");
                            $batchSelect.append('<option value="">No batches available, please add batch first</option>');
                            $additionalBatches.append('<option value="">No batches available</option>');
                        }
                    },
                    error: function(xhr) {
                        toastr.error("Error occured while loading batches!");
                        console.error('Error loading batches:', xhr);
                        $batchSelect.append('<option value="">Error loading batches</option>');
                        $additionalBatches.append('<option value="">Error loading batches</option>');
                    },
                    complete: function() {
                        $batchSelect.prop('disabled', false);
                        $additionalBatches.prop('disabled', false);
                        $batchSelect.closest('.mb-3').removeClass('loading-batches');
                        $batchSelect.trigger('change');
                        $additionalBatches.trigger('change');
                    }
                });
            }

            // ========== COURSE CHANGE HANDLER ==========
            $courseSelect.on('change', function() {
                const courseId = $(this).val();
                console.log('Course changed to:', courseId);
                
                loadModules(courseId);
                loadBatches(courseId);
            });

            // ========== VALIDATE MODULE ON SUBMIT ==========
            $('#examForm').on('submit', function(e) {
                const moduleId = $moduleSelect.val();
                if (!moduleId) {
                    e.preventDefault();
                    const errorMsg = 'Please select a module for this exam';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                    $moduleSelect.closest('.mb-3').addClass('has-error');
                    setTimeout(function() {
                        $moduleSelect.closest('.mb-3').removeClass('has-error');
                    }, 3000);
                    if ($moduleSelect.hasClass('select2-hidden-accessible')) {
                        $moduleSelect.select2('open');
                    } else {
                        $moduleSelect.focus();
                    }
                    return false;
                }
                return true;
            });

            // ========== REMOVE ERROR STATE ON CHANGE ==========
            $moduleSelect.on('change', function() {
                if ($(this).val()) {
                    $(this).closest('.mb-3').removeClass('has-error');
                }
            });

            // ========== TRIGGER ON PAGE LOAD ==========
            if ($courseSelect.val()) {
                console.log('Pre-selected course found, triggering change');
                $courseSelect.trigger('change');
            }

            // ========== LOGGING ==========
            console.log('Select2 initialized:', $('.select2').length);
            console.log('Course select value:', $courseSelect.val());

            // Override ajax for debugging
            const originalAjax = $.ajax;
            $.ajax = function(settings) {
                console.log('AJAX request:', settings.url);
                return originalAjax.call(this, settings);
            };

            window.onerror = function(message, source, lineno, colno, error) {
                console.error('Global error:', message, source, lineno, colno, error);
                return false;
            };
        });
    });
        

    function toggleSchedule() {
        const isChecked = document.getElementById('is_scheduled').checked;
        const scheduleFields = document.getElementById('schedule-fields');
        const startTime = document.getElementById('start_time');
        const endTime = document.getElementById('end_time');
        
        scheduleFields.style.display = isChecked ? 'block' : 'none';
        
        if (isChecked) {
            startTime.setAttribute('required', 'required');
            endTime.setAttribute('required', 'required');
        } else {
            startTime.removeAttribute('required');
            endTime.removeAttribute('required');
        }
    }
</script>
@endpush