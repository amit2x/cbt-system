@extends('layouts.admin')

@section('title', 'Question Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Details</h1>
        <div>
            <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Question
            </a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Question Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Question Information</h6>
                    <span class="badge bg-{{ $question->status == 'active' ? 'success' : 'secondary' }} fs-6">
                        {{ ucfirst($question->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Question Meta -->
                    <div class="mb-4">
                        <span class="badge bg-{{ $question->type_badge }} me-2 fs-6">
                            {{ $question->type_name }}
                        </span>
                        <span class="badge bg-{{ $question->difficulty_badge }} me-2 fs-6">
                            {{ $question->difficulty_name }}
                        </span>
                        <span class="badge bg-primary me-2 fs-6">
                            <i class="bi bi-award"></i> {{ $question->marks }} Mark(s)
                        </span>
                        @if($question->negative_marks > 0)
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-dash-circle"></i> -{{ $question->negative_marks }} for wrong
                            </span>
                        @endif
                    </div>

                    <!-- Question Text & Image -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Question:</h6>
                        <div class="p-3 bg-light rounded">
                            @if($question->question_text)
                                {!! nl2br(e($question->question_text)) !!}
                            @endif
                            
                            @if($question->question_text_hi)
                            </br>
                                {!! nl2br(e($question->question_text_hi)) !!}
                            @endif
                            @if($question->question_image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $question->question_image) }}" 
                                         alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                                         class="img-fluid" 
                                         style="max-height: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                    @if($question->question_image_alt)
                                        <br><small class="text-muted">Alt: {{ $question->question_image_alt }}</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Options (for choice questions) -->
                    @if(in_array($question->type, ['single_choice', 'multiple_choice']))
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Options:</h6>
                            <div class="row">
                                @foreach($question->options as $index => $option)
                                    <div class="col-md-6 mb-2">
                                        <div class="card {{ $option->is_correct ? 'border-success bg-success-light' : '' }}">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-start">
                                                    <span class="badge bg-secondary me-2 mt-1">{{ chr(65 + $index) }}</span>
                                                    <div class="flex-grow-1">
                                                        @if($option->option_text)
                                                            <div class="option-text">{{ $option->option_text }}</div>
                                                        @endif
                                                         @if($option->option_text_hi)
                                                        </br>
                                                            {{ $option->option_text_hi }}
                                                        @endif
                                                        @if($option->option_image)
                                                            <div class="mt-1">
                                                                <img src="{{ asset('storage/' . $option->option_image) }}" 
                                                                     alt="{{ $option->option_image_alt ?? 'Option ' . chr(65 + $index) . ' image' }}" 
                                                                     class="img-fluid option-image" 
                                                                     style="max-height: 100px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                                                @if($option->option_image_alt)
                                                                    <br><small class="text-muted">Alt: {{ $option->option_image_alt }}</small>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if($option->is_correct)
                                                        <i class="bi bi-check-circle-fill text-success fs-5 ms-2"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Numerical Answer -->
                    @if($question->type === 'numerical')
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Correct Answer:</h6>
                            <div class="alert alert-info">
                                @php
                                    $correctOption = $question->options->first();
                                @endphp
                                <strong>{{ $correctOption->option_text ?? 'Not set' }}</strong>
                                @if($correctOption && $correctOption->is_correct)
                                    <span class="badge bg-success ms-2">Correct</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Explanation with Image Support -->
                    @if($question->explanation)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Explanation:</h6>
                            <div class="p-3 bg-info-light rounded explanation-content">
                                {!! $question->explanation !!}
                                
                                @if($question->explanation_hi)
                                   <br> {!! $question->explanation_hi !!}                                    
                                @endif
                                  @if($question->explanation_image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $question->explanation_image) }}" 
                                         alt="{{ $question->explanation_image_alt ?? 'Explanation image' }}" 
                                         class="img-fluid" 
                                         style="max-height: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                    @if($question->explanation_image_alt)
                                        <br><small class="text-muted">Alt: {{ $question->explanation_image_alt }}</small>
                                    @endif
                                </div>
                            @endif
                            </div>
                        </div>
                    @endif

                    <!-- Tags -->
                    @if($question->tags && count($question->tags) > 0)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Tags:</h6>
                            @foreach($question->tags as $tag)
                                <span class="badge bg-secondary me-1">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Usage Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h3 class="text-primary">{{ $question->exams->count() }}</h3>
                            <p class="text-muted">Used in Exams</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3 class="text-success">0</h3>
                            <p class="text-muted">Times Answered</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3 class="text-info">0%</h3>
                            <p class="text-muted">Success Rate</p>
                        </div>
                    </div>

                    @if($question->exams->count() > 0)
                        <hr>
                        <h6 class="mb-3">Used in these exams:</h6>
                        <div class="list-group">
                            @foreach($question->exams as $exam)
                                <a href="{{ route('admin.exams.show', $exam) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $exam->title }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $exam->course->title ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ $exam->pivot->marks }} marks</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Categories -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
                </div>
                <div class="card-body">
                    @if($question->categories->count() > 0)
                        @foreach($question->categories as $category)
                            <span class="badge bg-info d-inline-block mb-1 p-2">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No categories assigned</p>
                    @endif
                </div>
            </div>

            <!-- Subject -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subject/Module</h6>
                </div>
                <div class="card-body">
                    @if($question->subject)
                        <h5>{{ $question->subject->title }}</h5>
                        <p class="text-muted mb-0">{{ $question->subject->course->title ?? 'N/A' }}</p>
                    @else
                        <p class="text-muted mb-0">No subject assigned</p>
                    @endif
                </div>
            </div>

            <!-- Question Image Preview -->
            @if($question->question_image)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Question Image</h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('storage/' . $question->question_image) }}" 
                             alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                             class="img-fluid rounded" 
                             style="max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                        @if($question->question_image_alt)
                            <p class="text-muted mt-2"><small>Alt: {{ $question->question_image_alt }}</small></p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Option Images Preview -->
            @if($question->options->whereNotNull('option_image')->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Option Images</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($question->options->whereNotNull('option_image') as $option)
                                <div class="col-6 mb-2">
                                    <div class="text-center">
                                        <img src="{{ asset('storage/' . $option->option_image) }}" 
                                             alt="{{ $option->option_image_alt ?? 'Option image' }}" 
                                             class="img-fluid rounded" 
                                             style="max-height: 80px; border: 1px solid #ddd; padding: 3px; border-radius: 5px;">
                                        <br>
                                        <small class="text-muted">Option {{ chr(65 + $option->order) }}</small>
                                        @if($option->is_correct)
                                            <span class="badge bg-success">✓</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Metadata</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>ID:</th>
                            <td>{{ $question->id }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>
                                {{ $question->creator->name ?? 'N/A' }}
                                <br>
                                <small class="text-muted">{{ $question->created_at->format('d M Y h:i A') }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>
                                {{ $question->updated_at->diffForHumans() }}
                                <br>
                                <small class="text-muted">{{ $question->updated_at->format('d M Y h:i A') }}</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="previewQuestion()">
                            <i class="bi bi-eye"></i> Preview Question
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="duplicateQuestion()">
                            <i class="bi bi-files"></i> Duplicate
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteQuestion()">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
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

<!-- Delete Form -->
<form id="delete-form" method="POST" action="{{ route('admin.questions.destroy', $question) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .bg-success-light {
        background-color: #d4edda !important;
    }
    .bg-info-light {
        background-color: #e1f5fe !important;
    }
    .border-success {
        border-color: #28a745 !important;
    }
    .option-item {
        border-radius: 5px;
    }
    .option-text {
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .explanation-content img {
        max-height: 200px;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 5px;
        margin: 5px 0;
        display: block;
        max-width: 100%;
    }
    .explanation-content {
        /*white-space: pre-wrap;*/
        word-wrap: break-word;
    }
    .explanation-content p {
        margin-bottom: 10px;
    }
    .option-image {
        max-width: 100%;
        height: auto;
    }
    .card-body .d-flex.align-items-start {
        gap: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewQuestion() {
        const questionText = `{!! addslashes($question->question_text) !!}`;
        const questionImage = `{{ $question->question_image ? asset('storage/' . $question->question_image) : '' }}`;
        const type = '{{ $question->type_name }}';
        const difficulty = '{{ $question->difficulty_name }}';
        const marks = '{{ $question->marks }}';

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
                        ${questionImage ? `<br><img src="${questionImage}" alt="Question image" class="img-fluid mt-2" style="max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">` : ''}
                    </div>
        `;

        @if(in_array($question->type, ['single_choice', 'multiple_choice']))
            preview += '<h6>Options:</h6><div class="options-preview">';
            @foreach($question->options as $option)
                const optionText = `{!! addslashes($option->option_text) !!}`;
                const optionImage = `{{ $option->option_image ? asset('storage/' . $option->option_image) : '' }}`;
                preview += `
                    <div class="option-item mb-2 p-2 {{ $option->is_correct ? 'bg-success text-white' : 'bg-light' }}" style="border-radius: 5px;">
                        ${optionText || ''}
                        ${optionImage ? `<br><img src="${optionImage}" alt="Option image" class="img-fluid mt-1" style="max-height: 80px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">` : ''}
                        @if($option->is_correct)
                            <span class="badge bg-light text-success ms-2">✓ Correct</span>
                        @endif
                    </div>
                `;
            @endforeach
            preview += '</div>';
        @endif

        @if($question->type === 'numerical' && $question->options->first())
            preview += `
                <div class="alert alert-info mt-3">
                    <strong>Correct Answer:</strong> {{ $question->options->first()->option_text }}
                </div>
            `;
        @endif

        @if($question->explanation)
            preview += `
                <div class="mt-3">
                    <h6>Explanation:</h6>
                    <div class="text-muted p-2 bg-light rounded" style="white-space: pre-wrap;">{{ $question->explanation }}</div>
                </div>
            `;
        @endif

        preview += '</div></div>';

        $('#previewContent').html(preview);
        $('#previewModal').modal('show');
    }

    function duplicateQuestion() {
        if (confirm('Create a duplicate of this question?')) {
            $.ajax({
                url: '{{ route("admin.questions.duplicate", $question) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Question duplicated successfully');
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                },
                error: function() {
                    toastr.error('Error duplicating question');
                }
            });
        }
    }

    function deleteQuestion() {
        if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }

    // Handle image loading errors
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img').forEach(function(img) {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const parent = this.parentElement;
                const errorMsg = document.createElement('span');
                errorMsg.className = 'text-muted';
                errorMsg.textContent = '⚠️ Image could not be loaded';
                parent.appendChild(errorMsg);
            });
        });
    });
</script>
@endpush