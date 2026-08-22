@extends('layouts.admin')

@section('title', 'Edit Question')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Question</h1>
        <div>
            <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Question
            </a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Question Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.questions.update', $question) }}" method="POST" id="questionForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <!-- Question Text -->
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text</label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror"
                                      id="question_text" name="question_text" rows="4">{{ old('question_text', $question->question_text) }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Either text or image is required</small>
                        </div>

                        <!-- Question Image -->
                        <div class="mb-3">
                            <label for="question_image" class="form-label">Question Image</label>
                            @if($question->question_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $question->question_image) }}" 
                                         alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                                         class="img-fluid" 
                                         style="max-height: 150px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                    <div class="mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="remove_question_image" id="remove_question_image" value="1">
                                            <label class="form-check-label text-danger" for="remove_question_image">
                                                Remove current image
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('question_image') is-invalid @enderror"
                                   id="question_image" name="question_image" accept="image/*"
                                   onchange="previewQuestionImage(this)">
                            @error('question_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, SVG (Max: 2MB)</small>
                            <div id="questionImagePreview" class="mt-2" style="display: none;">
                                <img id="questionImagePreviewImg" src="#" alt="Question Image Preview" 
                                     class="img-fluid" style="max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeQuestionImagePreview()">
                                    <i class="bi bi-x"></i> Remove Preview
                                </button>
                            </div>
                        </div>

                        <!-- Question Image Alt Text -->
                        <div class="mb-3">
                            <label for="question_image_alt" class="form-label">Image Alt Text</label>
                            <input type="text" class="form-control @error('question_image_alt') is-invalid @enderror"
                                   id="question_image_alt" name="question_image_alt" 
                                   value="{{ old('question_image_alt', $question->question_image_alt) }}"
                                   placeholder="Descriptive text for accessibility">
                            @error('question_image_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Explanation -->
                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation</label>
                            <textarea class="form-control @error('explanation') is-invalid @enderror"
                                      id="explanation" name="explanation" rows="3">{{ old('explanation', $question->explanation) }}</textarea>
                            <small class="text-muted">Explanation will be shown after answering</small>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Options Container (for choice questions) -->
                        <div id="options-container" style="{{ in_array($question->type, ['single_choice', 'multiple_choice']) ? '' : 'display: none;' }}">
                            <hr>
                            <h5>Answer Options</h5>
                            <div id="options-list">
                                @if($question->options->count() > 0)
                                    @foreach($question->options as $index => $option)
                                        <div class="option-row" id="option-{{ $index }}">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control"
                                                           name="options[{{ $index }}][option_text]"
                                                           placeholder="Option {{ chr(65 + $index) }} text"
                                                           value="{{ $option->option_text }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <input type="file" class="form-control form-control-sm"
                                                               name="options[{{ $index }}][option_image]"
                                                               accept="image/*"
                                                               onchange="previewOptionImage({{ $index }}, this)"
                                                               id="option_image_{{ $index }}">
                                                    </div>
                                                    @if($option->option_image)
                                                        <div id="option_image_preview_{{ $index }}" class="mt-1">
                                                            <img src="{{ asset('storage/' . $option->option_image) }}" 
                                                                 alt="{{ $option->option_image_alt ?? 'Option image' }}" 
                                                                 class="option-image-preview">
                                                            <div class="form-check mt-1">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="options[{{ $index }}][remove_image]" 
                                                                       id="remove_option_image_{{ $index }}" value="1">
                                                                <label class="form-check-label text-danger" for="remove_option_image_{{ $index }}">
                                                                    Remove image
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div id="option_image_preview_{{ $index }}" style="display: none;">
                                                            <img src="#" alt="Option Image" class="option-image-preview">
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="removeOptionImagePreview({{ $index }})">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    <input type="text" class="form-control form-control-sm mt-1"
                                                           name="options[{{ $index }}][option_image_alt]"
                                                           placeholder="Image alt text"
                                                           value="{{ $option->option_image_alt }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}"
                                                               name="{{ $question->type === 'multiple_choice' ? 'options[' . $index . '][is_correct]' : 'correct_option' }}"
                                                               value="{{ $index }}"
                                                               {{ $option->is_correct ? 'checked' : '' }}
                                                               id="correct_option_{{ $index }}">
                                                        <label class="form-check-label" for="correct_option_{{ $index }}">
                                                            Correct
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeOption({{ $index }})">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="options[{{ $index }}][order]" value="{{ $index }}">
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="addOption()">
                                <i class="bi bi-plus-circle"></i> Add Option
                            </button>
                        </div>

                        <!-- Numerical Answer Container -->
                        <div id="numerical-container" style="{{ $question->type === 'numerical' ? '' : 'display: none;' }}">
                            <hr>
                            <h5>Correct Answer</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="correct_numerical" class="form-label">Numerical Answer <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="correct_numerical" name="correct_numerical"
                                               step="any" value="{{ $question->options->first()->option_text ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tolerance" class="form-label">Tolerance (±)</label>
                                        <input type="number" class="form-control" id="tolerance" name="tolerance"
                                               step="0.001" value="0.001">
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
                                    <option value="{{ $value }}" {{ old('type', $question->type) == $value ? 'selected' : '' }}>
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
                                    <option value="{{ $value }}" {{ old('difficulty', $question->difficulty) == $value ? 'selected' : '' }}>
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
                                   id="marks" name="marks" value="{{ old('marks', $question->marks) }}" min="1" required>
                            @error('marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Negative Marks -->
                        <div class="mb-3">
                            <label for="negative_marks" class="form-label">Negative Marks</label>
                            <input type="number" class="form-control @error('negative_marks') is-invalid @enderror"
                                   id="negative_marks" name="negative_marks" value="{{ old('negative_marks', $question->negative_marks) }}"
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
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $question->subject_id) == $subject->id ? 'selected' : '' }}>
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
                                        {{ in_array($category->id, old('category_ids', $question->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
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
                                   id="tags" name="tags" value="{{ old('tags', is_array($question->tags) ? implode(',', $question->tags) : $question->tags) }}"
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
                                <option value="active" {{ old('status', $question->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $question->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                        <i class="bi bi-save"></i> Update Question
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
    .option-image-preview {
        max-height: 80px;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 5px;
        margin-top: 5px;
    }
    .option-row .form-check {
        padding-top: 8px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let optionCount = {{ $question->options->count() }};

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select options',
            allowClear: true
        });

        // Initialize option type based on current type
        handleTypeChange();
    });

    function handleTypeChange() {
        const type = $('#type').val();

        // Hide all containers
        $('#options-container').hide();
        $('#numerical-container').hide();

        // Show relevant container
        if (type === 'single_choice' || type === 'multiple_choice') {
            $('#options-container').show();
            // Update radio/checkbox types
            updateOptionTypes(type);
        } else if (type === 'numerical') {
            $('#numerical-container').show();
        }
    }

    function updateOptionTypes(type) {
        const isMultiple = type === 'multiple_choice';
        $('.option-row').each(function(index) {
            const input = $(this).find('input[type="checkbox"], input[type="radio"]');
            input.attr('type', isMultiple ? 'checkbox' : 'radio');
            if (!isMultiple) {
                input.attr('name', 'correct_option');
                input.attr('value', index);
            } else {
                input.attr('name', 'options[' + index + '][is_correct]');
                input.attr('value', '1');
            }
        });
    }

    function addOption() {
        const type = $('#type').val();
        const isMultiple = type === 'multiple_choice';
        const letter = String.fromCharCode(65 + optionCount);
        
        const optionHtml = `
            <div class="option-row" id="option-${optionCount}">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control"
                               name="options[${optionCount}][option_text]"
                               placeholder="Option ${letter} text">
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1">
                            <input type="file" class="form-control form-control-sm"
                                   name="options[${optionCount}][option_image]"
                                   accept="image/*"
                                   onchange="previewOptionImage(${optionCount}, this)"
                                   id="option_image_${optionCount}">
                        </div>
                        <div id="option_image_preview_${optionCount}" style="display: none;">
                            <img src="#" alt="Option Image" class="option-image-preview">
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="removeOptionImagePreview(${optionCount})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <input type="text" class="form-control form-control-sm mt-1"
                               name="options[${optionCount}][option_image_alt]"
                               placeholder="Image alt text">
                    </div>
                    <div class="col-md-1">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="${isMultiple ? 'checkbox' : 'radio'}"
                                   name="${isMultiple ? 'options[' + optionCount + '][is_correct]' : 'correct_option'}"
                                   value="${isMultiple ? '1' : optionCount}"
                                   id="correct_option_${optionCount}">
                            <label class="form-check-label" for="correct_option_${optionCount}">
                                Correct
                            </label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(${optionCount})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="options[${optionCount}][order]" value="${optionCount}">
            </div>
        `;

        $('#options-list').append(optionHtml);
        optionCount++;
    }

    function removeOption(id) {
        if ($('.option-row').length > 2) {
            $(`#option-${id}`).remove();
        } else {
            toastr.warning('Minimum 2 options required');
        }
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

    function removeQuestionImagePreview() {
        $('#question_image').val('');
        $('#questionImagePreview').hide();
        $('#questionImagePreviewImg').attr('src', '#');
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

    function removeOptionImagePreview(optionId) {
        $(`#option_image_${optionId}`).val('');
        $(`#option_image_preview_${optionId}`).hide();
        $(`#option_image_preview_${optionId} img`).attr('src', '#');
    }

    function previewQuestion() {
        const questionText = $('#question_text').val();
        const type = $('#type option:selected').text();
        const difficulty = $('#difficulty option:selected').text();
        const marks = $('#marks').val();
        const questionImagePreview = $('#questionImagePreviewImg').attr('src');

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
                        ${questionText || ''}
                        ${questionImagePreview && questionImagePreview !== '#' ? `<br><img src="${questionImagePreview}" alt="Question image" class="img-fluid mt-2" style="max-height: 200px;">` : ''}
                    </div>
        `;

        if ($('#type').val() === 'single_choice' || $('#type').val() === 'multiple_choice') {
            preview += '<h6>Options:</h6><div class="options-preview">';
            $('.option-row').each(function() {
                const optionText = $(this).find('input[name*="[option_text]"]').val();
                const optionImage = $(this).find('img').attr('src');
                const isCorrect = $(this).find('input[type="checkbox"]:checked, input[type="radio"]:checked').length > 0;
                preview += `
                    <div class="option-item mb-2 p-2 ${isCorrect ? 'bg-success text-white' : 'bg-light'}" style="border-radius: 5px;">
                        ${optionText || ''}
                        ${optionImage && optionImage !== '#' ? `<br><img src="${optionImage}" alt="Option image" class="img-fluid mt-1" style="max-height: 80px;">` : ''}
                        ${isCorrect ? '<span class="badge bg-light text-success ms-2">✓ Correct</span>' : ''}
                    </div>
                `;
            });
            preview += '</div>';
        }

        if ($('#type').val() === 'numerical') {
            const correct = $('#correct_numerical').val();
            const tolerance = $('#tolerance').val();
            preview += `
                <div class="alert alert-info">
                    <strong>Correct Answer:</strong> ${correct} ± ${tolerance}
                </div>
            `;
        }

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
        const questionText = $('#question_text').val();
        const questionImage = $('#question_image').val();
        const type = $('#type').val();
        
        // Check if question has text or image
        if (!questionText && !questionImage && !$('#questionImagePreview').is(':visible') && !$('#remove_question_image').is(':checked')) {
            toastr.error('Please provide either question text or an image');
            e.preventDefault();
            return false;
        }
        
        if (type === 'single_choice' || type === 'multiple_choice') {
            // Check if at least one option has text or image
            let hasOption = false;
            $('.option-row').each(function() {
                const text = $(this).find('input[name*="[option_text]"]').val();
                const image = $(this).find('input[name*="[option_image]"]').val();
                const existingImage = $(this).find('img').attr('src') && $(this).find('img').attr('src') !== '#';
                if (text || image || existingImage) {
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
                if ($('input[name*="[is_correct]"]:checked').length === 0) {
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