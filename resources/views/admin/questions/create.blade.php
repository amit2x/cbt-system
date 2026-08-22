{{-- resources/views/admin/questions/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Create Question')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Question</h1>
        <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Question Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <!-- Question Text -->
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror"
                                      id="question_text" name="question_text" rows="4" required>{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question Image -->
                        <div class="mb-3">
                            <label for="question_image" class="form-label">Question Image</label>
                            <input type="file" class="form-control @error('question_image') is-invalid @enderror"
                                   id="question_image" name="question_image" accept="image/*"
                                   onchange="previewQuestionImage(this)">
                            @error('question_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, SVG (Max: 2MB)</small>
                            <div id="questionImagePreview" class="mt-2" style="display: none;">
                                <img id="questionImagePreviewImg" src="#" alt="Question Image Preview" class="img-fluid" style="max-height: 200px;">
                                <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeQuestionImage()">
                                    <i class="bi bi-x"></i> Remove Image
                                </button>
                            </div>
                        </div>

                        <!-- Question Image Alt Text -->
                        <div class="mb-3">
                            <label for="question_image_alt" class="form-label">Image Alt Text</label>
                            <input type="text" class="form-control @error('question_image_alt') is-invalid @enderror"
                                   id="question_image_alt" name="question_image_alt" 
                                   value="{{ old('question_image_alt') }}"
                                   placeholder="Descriptive text for accessibility">
                            @error('question_image_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Explanation -->
                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation</label>
                            <textarea class="form-control @error('explanation') is-invalid @enderror"
                                      id="explanation" name="explanation" rows="3">{{ old('explanation') }}</textarea>
                            <small class="text-muted">Explanation will be shown after answering</small>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Options Container (for choice questions) -->
                        <div id="options-container" style="display: none;">
                            <hr>
                            <h5>Answer Options</h5>
                            <div id="options-list"></div>
                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="addOption()">
                                <i class="bi bi-plus-circle"></i> Add Option
                            </button>
                        </div>

                        <!-- Numerical Answer Container -->
                        <div id="numerical-container" style="display: none;">
                            <hr>
                            <h5>Correct Answer</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="correct_numerical" class="form-label">Numerical Answer <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="correct_numerical" name="correct_numerical" step="any" value="{{ old('correct_numerical') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tolerance" class="form-label">Tolerance (±)</label>
                                        <input type="number" class="form-control" id="tolerance" name="tolerance" step="0.001" value="{{ old('tolerance', 0.001) }}">
                                        <small class="text-muted">Allowed deviation for correct answer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Question Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror"
                                    id="type" name="type" required onchange="handleTypeChange()">
                                <option value="">Select Type</option>
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

                        <!-- Difficulty -->
                        <div class="mb-3">
                            <label for="difficulty" class="form-label">Difficulty <span class="text-danger">*</span></label>
                            <select class="form-control @error('difficulty') is-invalid @enderror"
                                    id="difficulty" name="difficulty" required>
                                @foreach($difficulties as $value => $label)
                                    <option value="{{ $value }}" {{ old('difficulty') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('difficulty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Marks -->
                        <div class="mb-3">
                            <label for="marks" class="form-label">Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('marks') is-invalid @enderror"
                                   id="marks" name="marks" value="{{ old('marks', 1) }}" min="1" required>
                            @error('marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Negative Marks -->
                        <div class="mb-3">
                            <label for="negative_marks" class="form-label">Negative Marks</label>
                            <input type="number" class="form-control @error('negative_marks') is-invalid @enderror"
                                   id="negative_marks" name="negative_marks" value="{{ old('negative_marks', 0) }}"
                                   min="0" step="0.25">
                            @error('negative_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject/Module</label>
                            <select class="form-control select2 @error('subject_id') is-invalid @enderror"
                                    id="subject_id" name="subject_id">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Categories -->
                        <div class="mb-3">
                            <label for="category_ids" class="form-label">Categories</label>
                            <select class="form-control select2 @error('category_ids') is-invalid @enderror"
                                    id="category_ids" name="category_ids[]" multiple>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ in_array($category->id, old('category_ids', [])) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror"
                                   id="tags" name="tags" value="{{ old('tags') }}"
                                   placeholder="Comma separated tags">
                            <small class="text-muted">e.g., algebra, geometry, calculus</small>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="previewQuestion()">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create Question
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .option-row {
        background: #f8f9fa;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        border: 1px solid #e9ecef;
    }
    .correct-badge {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    .option-image-preview {
        max-height: 80px;
        margin-top: 5px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let optionCount = 0;
    let tempOptionImages = {};

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select options',
            allowClear: true
        });

        handleTypeChange();
    });

    function handleTypeChange() {
        const type = $('#type').val();

        $('#options-container').hide();
        $('#numerical-container').hide();

        if (type === 'single_choice' || type === 'multiple_choice') {
            $('#options-list').empty();
            optionCount = 0;
            $('#options-container').show();
            addOption();
            addOption();
        } else if (type === 'numerical') {
            $('#numerical-container').show();
        }
    }

    function addOption() {
        const type = $('#type').val();
        const isMultiple = type === 'multiple_choice';
        const letter = String.fromCharCode(65 + optionCount);
        
        let correctInput = '';
        if (isMultiple) {
            correctInput = `
                <input class="form-check-input" type="checkbox" 
                       name="correct_options[]" 
                       value="${optionCount}"
                       id="correct_option_${optionCount}"
                       onchange="updateCorrectStatus(${optionCount}, this.checked)">
                <label class="form-check-label" for="correct_option_${optionCount}">
                    Correct Answer
                </label>
            `;
        } else {
            correctInput = `
                <input class="form-check-input" type="radio" 
                       name="correct_option" 
                       value="${optionCount}"
                       id="correct_option_${optionCount}"
                       onchange="updateCorrectStatus(${optionCount}, true)">
                <label class="form-check-label" for="correct_option_${optionCount}">
                    Correct Answer
                </label>
            `;
        }
        
        const optionHtml = `
            <div class="option-row" id="option-${optionCount}">
                <div class="row">
                    <div class="col-md-7">
                        <input type="text" class="form-control"
                               name="options[${optionCount}][option_text]"
                               placeholder="Option ${letter} text" 
                               id="option_text_${optionCount}">
                    </div>
                    <div class="col-md-3">
                        <input type="file" class="form-control form-control-sm"
                               name="options[${optionCount}][option_image]"
                               accept="image/*"
                               onchange="previewOptionImage(${optionCount}, this)"
                               id="option_image_${optionCount}">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            ${correctInput}
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-7">
                        <input type="text" class="form-control form-control-sm"
                               name="options[${optionCount}][option_image_alt]"
                               placeholder="Image alt text"
                               id="option_image_alt_${optionCount}">
                    </div>
                    <div class="col-md-3">
                        <div id="option_image_preview_${optionCount}" style="display: none;">
                            <img src="#" alt="Option Image" class="option-image-preview">
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="removeOptionImage(${optionCount})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(${optionCount})">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="options[${optionCount}][order]" value="${optionCount}">
                <input type="hidden" name="options[${optionCount}][is_correct]" value="0" class="is-correct-hidden">
            </div>
        `;

        $('#options-list').append(optionHtml);
        optionCount++;
    }

    function updateCorrectStatus(optionId, isChecked) {
        const type = $('#type').val();
        
        if (type === 'single_choice') {
            // Single choice - only one can be correct
            $('input[name="correct_option"]').not(`[value="${optionId}"]`).prop('checked', false);
            $('.is-correct-hidden').val(0);
            $(`#option-${optionId} .is-correct-hidden`).val(isChecked ? 1 : 0);
        } else {
            // Multiple choice - can have multiple correct
            $(`#option-${optionId} .is-correct-hidden`).val(isChecked ? 1 : 0);
        }
    }

    function removeOption(id) {
        if ($('.option-row').length > 2) {
            $(`#option-${id}`).remove();
        } else {
            toastr.warning('Minimum 2 options required');
        }
    }

    function previewOptionImage(optionId, input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = $(`#option_image_preview_${optionId}`);
                previewDiv.find('img').attr('src', e.target.result);
                previewDiv.show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeOptionImage(optionId) {
        $(`#option_image_${optionId}`).val('');
        $(`#option_image_preview_${optionId}`).hide();
        $(`#option_image_preview_${optionId} img`).attr('src', '#');
    }

    function previewQuestionImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#questionImagePreviewImg').attr('src', e.target.result);
                $('#questionImagePreview').show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeQuestionImage() {
        $('#question_image').val('');
        $('#questionImagePreview').hide();
        $('#questionImagePreviewImg').attr('src', '#');
    }

    function previewQuestion() {
        const questionText = $('#question_text').val();
        const type = $('#type option:selected').text();
        const difficulty = $('#difficulty option:selected').text();
        const marks = $('#marks').val();
        const questionImage = $('#questionImagePreviewImg').attr('src');

        let preview = `
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Question Preview</h6>
                    <div class="mb-3">
                        <span class="badge bg-info me-2">${type}</span>
                        <span class="badge bg-warning me-2">${difficulty}</span>
                        <span class="badge bg-primary">${marks} Mark(s)</span>
                    </div>
                    <div class="question-text mb-4">
                        ${questionText}
                        ${questionImage && questionImage !== '#' ? `<br><img src="${questionImage}" alt="Question image" class="img-fluid mt-2" style="max-height: 200px;">` : ''}
                    </div>
        `;

        // Add options preview
        if ($('#type').val() === 'single_choice' || $('#type').val() === 'multiple_choice') {
            preview += '<h6>Options:</h6><div class="options-preview">';
            $('.option-row').each(function() {
                const optionText = $(this).find('input[name*="[option_text]"]').val();
                const optionImage = $(this).find('img').attr('src');
                const isChecked = $(this).find('input[type="checkbox"]:checked, input[type="radio"]:checked').length > 0;
                preview += `
                    <div class="option-item mb-2 p-2 ${isChecked ? 'bg-success text-white' : 'bg-light'}">
                        ${optionText || ''}
                        ${optionImage && optionImage !== '#' ? `<br><img src="${optionImage}" alt="Option image" class="img-fluid mt-1" style="max-height: 80px;">` : ''}
                        ${isChecked ? '<span class="correct-badge ms-2">✓ Correct</span>' : ''}
                    </div>
                `;
            });
            preview += '</div>';
        }

        // Add numerical preview
        if ($('#type').val() === 'numerical') {
            const correct = $('#correct_numerical').val();
            const tolerance = $('#tolerance').val();
            preview += `
                <div class="alert alert-info">
                    <strong>Correct Answer:</strong> ${correct} ± ${tolerance}
                </div>
            `;
        }

        // Add explanation preview
        const explanation = $('#explanation').val();
        if (explanation) {
            preview += `
                <div class="mt-3">
                    <h6>Explanation:</h6>
                    <div class="text-muted">${explanation}</div>
                </div>
            `;
        }

        preview += '</div></div>';

        $('#previewContent').html(preview);
        $('#previewModal').modal('show');
    }

    // Form validation before submit
    $('#questionForm').on('submit', function(e) {
        const type = $('#type').val();
        
        if (type === 'single_choice' || type === 'multiple_choice') {
            // Check if at least one option has text or image
            let hasOption = false;
            $('.option-row').each(function() {
                const text = $(this).find('input[name*="[option_text]"]').val();
                const image = $(this).find('input[name*="[option_image]"]').val();
                if (text || image) {
                    hasOption = true;
                }
            });
            
            if (!hasOption) {
                toastr.error('Please add at least one option with text or image');
                e.preventDefault();
                return false;
            }
            
            // Check correct answer selection
            if (type === 'single_choice') {
                if ($('input[name="correct_option"]:checked').length === 0) {
                    toastr.error('Please select the correct answer');
                    e.preventDefault();
                    return false;
                }
            } else {
                if ($('input[name="correct_options[]"]:checked').length === 0) {
                    toastr.error('Please select at least one correct answer');
                    e.preventDefault();
                    return false;
                }
            }
        }
        
        if (type === 'numerical') {
            const numericalAnswer = $('#correct_numerical').val();
            if (!numericalAnswer && numericalAnswer !== '0') {
                toastr.error('Please enter the correct numerical answer');
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
</script>
@endpush