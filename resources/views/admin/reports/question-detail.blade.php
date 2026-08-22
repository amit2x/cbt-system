{{-- resources/views/admin/reports/question-detail.blade.php --}}

@extends('layouts.admin')

@section('title', 'Question Detail - ' . Str::limit(strip_tags($question->question_text), 50))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Analysis Detail</h1>
        <div>
            <a href="{{ route('admin.reports.question-analysis') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Question Analysis
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Question Info -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Question Information</h6>
                    <div>
                        <span class="badge bg-info me-2">{{ $question->type_name ?? 'N/A' }}</span>
                        <span class="badge bg-{{ $question->difficulty == 'easy' ? 'success' : ($question->difficulty == 'medium' ? 'warning' : 'danger') }}">
                            {{ ucfirst($question->difficulty ?? 'N/A') }}
                        </span>
                        <span class="badge bg-{{ $question->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($question->status ?? 'N/A') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Question Text -->
                    <div class="mb-4">
                        <h6 class="text-muted">Question:</h6>
                        <div class="p-3 bg-light rounded">
                            @if($question->question_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $question->question_image) }}" 
                                         alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                                         class="img-fluid" 
                                         style="max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                </div>
                            @endif
                            <p>{!! nl2br(e($question->question_text)) !!}</p>
                        </div>
                    </div>

                    <!-- Explanation -->
                    @if($question->explanation)
                        <div class="mb-4">
                            <h6 class="text-muted">Explanation:</h6>
                            <div class="p-3 bg-info-light rounded">
                                {!! nl2br(e($question->explanation)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Options -->
                    @if(in_array($question->type, ['single_choice', 'multiple_choice']))
                        <div class="mb-4">
                            <h6 class="text-muted">Options:</h6>
                            <div class="row">
                                @foreach($question->options as $index => $option)
                                    <div class="col-md-6 mb-2">
                                        <div class="card {{ $option->is_correct ? 'border-success bg-success-light' : '' }}">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-secondary me-2">{{ chr(65 + $index) }}</span>
                                                    <span class="flex-grow-1">
                                                        @if($option->option_image)
                                                            <img src="{{ asset('storage/' . $option->option_image) }}" 
                                                                 alt="{{ $option->option_image_alt ?? 'Option image' }}" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 50px; border: 1px solid #ddd; padding: 3px; border-radius: 4px;">
                                                            <br>
                                                        @endif
                                                        {{ $option->option_text }}
                                                    </span>
                                                    @if($option->is_correct)
                                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Question ID:</strong></td>
                                    <td>{{ $question->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Marks:</strong></td>
                                    <td>{{ $question->marks }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Negative Marks:</strong></td>
                                    <td>{{ $question->negative_marks ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Subject:</strong></td>
                                    <td>{{ $question->subject->title ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $question->created_at ? $question->created_at->format('d M Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>{{ $question->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tags:</strong></td>
                                    <td>
                                        @if($question->tags)
                                            @foreach($question->tags as $tag)
                                                <span class="badge bg-secondary">{{ $tag }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    @if($analysis)
                        @php
                            $metrics = $analysis['metrics'] ?? [];
                            $totalAttempts = $metrics['total_attempts'] ?? 0;
                            $correct = $metrics['correct'] ?? 0;
                            $incorrect = $metrics['incorrect'] ?? 0;
                            $unanswered = $metrics['unanswered'] ?? 0;
                            $accuracyRate = $metrics['accuracy_rate'] ?? 0;
                            $difficultyIndex = $metrics['difficulty_index'] ?? 0;
                            $discriminationIndex = $metrics['discrimination_index'] ?? 0;
                            $effectivenessLevel = $metrics['effectiveness_level'] ?? 'N/A';
                            
                            // Determine colors
                            $accuracyColor = $accuracyRate >= 60 ? 'success' : 'warning';
                            $difficultyColor = $difficultyIndex >= 60 ? 'danger' : 'warning';
                            $discriminationColor = $discriminationIndex >= 0.3 ? 'success' : 'warning';
                            $effectivenessColor = $effectivenessLevel == 'good' ? 'success' : ($effectivenessLevel == 'average' ? 'warning' : 'danger');
                        @endphp
                        
                        <div class="text-center mb-4">
                            <h2 class="display-4 text-primary">{{ $accuracyRate }}%</h2>
                            <p class="text-muted">Accuracy Rate</p>
                        </div>

                        <div class="row">
                            <div class="col-4 text-center">
                                <h5 class="text-success">{{ $correct }}</h5>
                                <small class="text-muted">Correct</small>
                            </div>
                            <div class="col-4 text-center">
                                <h5 class="text-danger">{{ $incorrect }}</h5>
                                <small class="text-muted">Incorrect</small>
                            </div>
                            <div class="col-4 text-center">
                                <h5 class="text-secondary">{{ $unanswered }}</h5>
                                <small class="text-muted">Unanswered</small>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Total Attempts</small>
                                <strong>{{ $totalAttempts }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Difficulty Index</small>
                                <span class="badge bg-{{ $difficultyColor }}">{{ $difficultyIndex }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $difficultyColor }}" 
                                     style="width: {{ $difficultyIndex }}%"></div>
                            </div>
                            <small class="text-muted">Higher = More Difficult</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Discrimination Index</small>
                                <span class="badge bg-{{ $discriminationColor }}">{{ number_format($discriminationIndex, 2) }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $discriminationColor }}" 
                                     style="width: {{ min($discriminationIndex * 100, 100) }}%"></div>
                            </div>
                            <small class="text-muted">Higher = Better Discrimination</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Effectiveness</small>
                                <span class="badge bg-{{ $effectivenessColor }}">{{ ucfirst($effectivenessLevel) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Avg Time</small>
                                <strong>{{ $metrics['avg_time'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-bar-chart fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No performance data available for this question.</p>
                            <small class="text-muted">This question hasn't been attempted yet.</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Option Analysis -->
            @if($analysis && isset($analysis['option_analysis']))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Option Analysis</h6>
                    </div>
                    <div class="card-body">
                        @foreach($analysis['option_analysis'] as $key => $optionData)
                            @php
                                $optionText = $optionData['option_text'] ?? 'N/A';
                                $count = $optionData['count'] ?? 0;
                                $isCorrect = $optionData['is_correct'] ?? false;
                                $percentage = $totalAttempts > 0 ? round(($count / $totalAttempts) * 100, 2) : 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        {{ $optionText }}
                                        @if($isCorrect)
                                            <span class="badge bg-success">✓ Correct</span>
                                        @endif
                                    </span>
                                    <span>{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $isCorrect ? 'success' : 'secondary' }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Question
                        </a>
                        <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-info">
                            <i class="bi bi-eye"></i> View Full Question
                        </a>
                        @if($question->exams->count() > 0)
                            <a href="{{ route('admin.exams.show', $question->exams->first()) }}" class="btn btn-primary">
                                <i class="bi bi-journal"></i> View in Exam
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
    .badge {
        font-size: 0.85rem;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
    }
</style>
@endpush