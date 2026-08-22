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
            <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm">
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
                        <input type="hidden" id="correct_option_value" name="correct_option_value" value="">

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
                                        <input type="number" class="form-control" id="correct_numerical" name="correct_numerical" step="any">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tolerance" class="form-label">Tolerance (±)</label>
                                        <input type="number" class="form-control" id="tolerance" name="tolerance" step="0.001" value="0.001">
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
    }
    .correct-badge {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    
    document.getElementById('question_text').addEventListener('keydown', function(e) {
      if (e.key === 'Tab') {
        e.preventDefault(); // Stop focus from leaving the textarea
        
        // Get cursor position
        const start = this.selectionStart;
        const end = this.selectionEnd;
    
        // Insert 4 spaces at cursor position
        this.value = this.value.substring(0, start) + "    " + this.value.substring(end);
    
        // Put cursor back in the correct place
        this.selectionStart = this.selectionEnd = start + 4;
      }
    });
    

    let optionCount = 0;

    // $(document).ready(function() {
    //     $('.select2').select2({
    //         placeholder: 'Select options',
    //         allowClear: true
    //     });

    //     // Initialize type change handler
    //     handleTypeChange();

        // Fix: Ensure correct_option is handled properly on form submit
        // $('#questionForm').on('submit', function(e) {
        //     const type = $('#type').val();
            
        //     if (type === 'single_choice') {
        //         // Find which radio is selected
        //         const selectedRadio = $('input[name="correct_option"]:checked');
        //         if (selectedRadio.length === 0) {
        //             toastr.error('Please select the correct answer');
        //             e.preventDefault();
        //             return false;
        //         }
                
        //         const selectedIndex = parseInt(selectedRadio.val());
                
        //         // Set ALL options to is_correct=0 first
        //         $('.option-row').each(function(index) {
        //             $(`input[name="options[${index}][is_correct]"]`).val(0);
        //         });
                
        //         // Set the selected option to is_correct=1
        //         $(`input[name="options[${selectedIndex}][is_correct]"]`).val(1);
                
        //         // Debug: Log the values
        //         console.log('Selected index:', selectedIndex);
        //         $('.option-row').each(function(index) {
        //             console.log(`Option ${index} is_correct:`, $(`input[name="options[${index}][is_correct]"]`).val());
        //         });
                
        //         // Remove the correct_option field from submission to avoid confusion
        //         // We already have the is_correct values set
        //     }
            
        //     return true; // Allow form submission
        // });
        
        
      
    // });
    
    $(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Select options',
        allowClear: true
    });

    // Initialize type change handler
    handleTypeChange();

    // Handle checkbox changes for multiple choice
    $(document).on('change', 'input[name="correct_options[]"]', function() {
        const isChecked = $(this).prop('checked');
        const optionIndex = $(this).val();
        // Update the corresponding hidden field
        const hiddenField = $(`input[name="options[${optionIndex}][is_correct]"]`);
        if (hiddenField.length) {
            hiddenField.val(isChecked ? '1' : '0');
        }
    });

    // Handle form submission
    $('#questionForm').on('submit', function(e) {
        const type = $('#type').val();
        
        if (type === 'multiple_choice') {
            // First, sync all checkbox states to hidden fields
            $('input[name="correct_options[]"]').each(function() {
                const isChecked = $(this).prop('checked');
                const optionIndex = $(this).val();
                $(`input[name="options[${optionIndex}][is_correct]"]`).val(isChecked ? '1' : '0');
            });
            
            // Check if at least one checkbox is checked
            const checkedCount = $('input[name="correct_options[]"]:checked').length;
            if (checkedCount === 0) {
                toastr.error('Please select at least one correct answer');
                e.preventDefault();
                return false;
            }
        } else if (type === 'single_choice') {
            // Check if a radio button is selected
            if ($('input[name="correct_option"]:checked').length === 0) {
                toastr.error('Please select the correct answer');
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
});

function addOption() {
    const type = $('#type').val();
    const isMultiple = type === 'multiple_choice';
    const letter = String.fromCharCode(65 + optionCount);
    
    let correctInput = '';
    if (isMultiple) {
        // Multiple choice - use checkbox with a unique name pattern
        correctInput = `
            <input class="form-check-input" type="checkbox" 
                   name="correct_options[]" 
                   value="${optionCount}"
                   id="correct_option_${optionCount}">
            <label class="form-check-label" for="correct_option_${optionCount}">
                Correct Answer
            </label>
        `;
    } else {
        // Single choice - use radio button
        correctInput = `
            <input class="form-check-input" type="radio" 
                   name="correct_option" 
                   value="${optionCount}"
                   id="correct_option_${optionCount}">
            <label class="form-check-label" for="correct_option_${optionCount}">
                Correct Answer
            </label>
        `;
    }
    
    const optionHtml = `
        <div class="option-row" id="option-${optionCount}">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control"
                           name="options[${optionCount}][option_text]"
                           placeholder="Option ${letter}" required>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-2">
                        ${correctInput}
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(${optionCount})">
                        <i class="bi bi-x"></i>
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

    function handleTypeChange() {
        const type = $('#type').val();

        // Hide all containers
        $('#options-container').hide();
        $('#numerical-container').hide();

        // Reset option count and clear list when type changes
        if (type === 'single_choice' || type === 'multiple_choice') {
            $('#options-list').empty();
            optionCount = 0;
            $('#options-container').show();
            addOption(); // Add first option
            addOption(); // Add second option
        } else if (type === 'numerical') {
            $('#numerical-container').show();
        }
    }

    // function addOption() {
    //     const type = $('#type').val();
    //     const isMultiple = type === 'multiple_choice';
    //     const letter = String.fromCharCode(65 + optionCount);
        
    //     let correctInput = '';
    //     if (isMultiple) {
    //         // Multiple choice - use checkbox with hidden field
    //         correctInput = `
    //             <input class="form-check-input" type="checkbox" 
    //                   name="options[${optionCount}][is_correct]" 
    //                   value="1">
    //             <input type="hidden" name="options[${optionCount}][is_correct]" value="0">
    //         `;
    //     } else {
    //         // Single choice - use radio button with hidden field
    //         correctInput = `
    //             <input class="form-check-input" type="radio" 
    //                   name="correct_option" 
    //                   value="${optionCount}"
    //                   id="correct_option_${optionCount}">
    //             <label class="form-check-label" for="correct_option_${optionCount}">
    //                 Correct Answer
    //             </label>
    //             <input type="hidden" name="options[${optionCount}][is_correct]" value="0">
    //         `;
    //     }
        
    //     const optionHtml = `
    //         <div class="option-row" id="option-${optionCount}">
    //             <div class="row">
    //                 <div class="col-md-8">
    //                     <input type="text" class="form-control"
    //                           name="options[${optionCount}][option_text]"
    //                           placeholder="Option ${letter}" required>
    //                 </div>
    //                 <div class="col-md-3">
    //                     <div class="form-check mt-2">
    //                         ${correctInput}
    //                     </div>
    //                 </div>
    //                 <div class="col-md-1">
    //                     <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(${optionCount})">
    //                         <i class="bi bi-x"></i>
    //                     </button>
    //                 </div>
    //             </div>
    //             <input type="hidden" name="options[${optionCount}][order]" value="${optionCount}">
    //         </div>
    //     `;

    //     $('#options-list').append(optionHtml);
    //     optionCount++;
    // }


    function removeOption(id) {
        if ($('.option-row').length > 2) {
            // If the removed option was selected, clear the radio selection
            $(`input[name="correct_option"][value="${id}"]`).prop('checked', false);
            $(`#option-${id}`).remove();
        } else {
            toastr.warning('Minimum 2 options required');
        }
    }

    function previewQuestion() {
        // Collect form data
        const questionText = $('#question_text').val();
        const type = $('#type option:selected').text();
        const difficulty = $('#difficulty option:selected').text();
        const marks = $('#marks').val();

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
                    </div>
        `;

        // Add options preview
        if ($('#type').val() === 'single_choice' || $('#type').val() === 'multiple_choice') {
            preview += '<h6>Options:</h6><div class="options-preview">';
            $('.option-row').each(function() {
                const optionText = $(this).find('input[name*="[option_text]"]').val();
                const isChecked = $(this).find('input[type="checkbox"]:checked, input[type="radio"]:checked').length > 0;
                preview += `
                    <div class="option-item mb-2 p-2 ${isChecked ? 'bg-success text-white' : 'bg-light'}">
                        ${optionText}
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

    // Debug: Log form data before submit
    // $(document).on('submit', '#questionForm', function(e) {
    //     console.log('Form submitting...');
    //     const formData = new FormData(this);
    //     for (let pair of formData.entries()) {
    //         console.log(pair[0] + ': ' + pair[1]);
    //     }
    // });
    
   

// Also handle form submission to ensure all options have is_correct set
$(document).on('submit', '#questionForm', function(e) {
    const type = $('#type').val();
    
    if (type === 'multiple_choice') {
        // Check if at least one checkbox is checked
        const checkedCount = $('input[name="correct_options[]"]:checked').length;
        if (checkedCount === 0) {
            toastr.error('Please select at least one correct answer');
            e.preventDefault();
            return false;
        }
    } else if (type === 'single_choice') {
        // Check if a radio button is selected
        if ($('input[name="correct_option"]:checked').length === 0) {
            toastr.error('Please select the correct answer');
            e.preventDefault();
            return false;
        }
    }
    
    return true;
});
</script>
@endpush