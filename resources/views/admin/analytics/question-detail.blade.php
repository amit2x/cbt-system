@extends('layouts.admin')

@section('title', 'Question Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Analytics</h1>
        <div>
            <select class="form-select d-inline-block w-auto me-2" id="examFilter" onchange="filterByExam()">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                        {{ $exam->title }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('admin.analytics.questions') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Question Info -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Question Details</h6>
                </div>
                <div class="card-body">
                    <!-- Question Text & Image -->
                    <div class="mb-3">
                        <label class="fw-bold">Question:</label>
                        <div class="border p-3 bg-light rounded">
                            @if($question->question_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $question->question_image) }}" 
                                         alt="{{ $question->question_image_alt ?? 'Question image' }}" 
                                         class="img-fluid" 
                                         style="max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                    @if($question->question_image_alt)
                                        <br><small class="text-muted">Alt: {{ $question->question_image_alt }}</small>
                                    @endif
                                </div>
                            @endif
                            <p class="mb-0">{!! $question->question_text !!}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Type:</strong> <span class="badge bg-info">{{ $question->type_name }}</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Difficulty:</strong>
                                <span class="badge bg-{{ $question->difficulty_badge }}">
                                    {{ $question->difficulty_name }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Marks:</strong> {{ $question->marks }}</p>
                        </div>
                    </div>

                    <!-- Explanation with Image Support -->
                    @if($question->explanation)
                        <div class="mt-3">
                            <label class="fw-bold">Explanation:</label>
                            <div class="p-3 bg-info-light rounded explanation-content">
                                {!! $question->explanation !!}
                            </div>
                        </div>
                    @endif

                    <!-- Options with Image Support -->
                    @if($question->options->count() > 0)
                        <div class="mt-3">
                            <label class="fw-bold">Options:</label>
                            <div class="row">
                                @foreach($question->options as $option)
                                    <div class="col-md-12 mb-2">
                                        <div class="p-2 rounded {{ $option->is_correct ? 'bg-success text-white' : 'bg-light' }}">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-secondary me-2">{{ chr(65 + $loop->index) }}</span>
                                                <div class="flex-grow-1">
                                                    @if($option->option_image)
                                                        <div class="mb-1">
                                                            <img src="{{ asset('storage/' . $option->option_image) }}" 
                                                                 alt="{{ $option->option_image_alt ?? 'Option ' . chr(65 + $loop->index) . ' image' }}" 
                                                                 class="img-fluid option_image" >
                                                        </div>
                                                    @endif
                                                    <span>{{ $option->option_text }}</span>
                                                </div>
                                                @if($option->is_correct)
                                                    <i class="bi bi-check-circle-fill text-white ms-2"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($analysis)
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="display-4 text-primary">{{ $analysis['metrics']['accuracy_rate'] ?? 0 }}%</h2>
                        <p class="text-muted">Accuracy Rate</p>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td>Total Attempts:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['total_attempts'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Correct:</td>
                            <td class="fw-bold text-success">{{ $analysis['metrics']['correct'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Incorrect:</td>
                            <td class="fw-bold text-danger">{{ $analysis['metrics']['incorrect'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Unanswered:</td>
                            <td class="fw-bold text-secondary">{{ $analysis['metrics']['unanswered'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Difficulty Index:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['difficulty_index'] ?? 0 }}%</td>
                        </tr>
                        <tr>
                            <td>Discrimination Index:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['discrimination_index'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Average Time:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['avg_time'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Effectiveness:</td>
                            <td>
                                @php
                                    $level = $analysis['metrics']['effectiveness_level'] ?? 'N/A';
                                    $color = $level == 'good' ? 'success' : ($level == 'average' ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ ucfirst($level) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($analysis && $analysis['option_analysis'])
    <!-- Option Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Option Selection Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Option</th>
                                    <th>Text</th>
                                    <th>Image</th>
                                    <th>Selected Count</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis['option_analysis'] as $key => $option)
                                    @php
                                        // Try to find the actual option to get image
                                        $actualOption = $question->options->where('id', str_replace('option_', '', $key))->first();
                                        $totalAttempts = $analysis['metrics']['total_attempts'] ?? 0;
                                        $percentage = $totalAttempts > 0 ? round(($option['count'] / $totalAttempts) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $option['option_text'] ?? 'N/A' }}</td>
                                        <td>
                                            @if($actualOption && $actualOption->option_image)
                                                <img src="{{ asset('storage/' . $actualOption->option_image) }}" 
                                                     alt="{{ $actualOption->option_image_alt ?? 'Option image' }}" 
                                                     class="img-fluid option_image" >
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td>
                                        <td>{{ $option['count'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $option['is_correct'] ? 'success' : 'secondary' }}" 
                                                         style="width: {{ $percentage }}%">
                                                    </div>
                                                </div>
                                                <span>{{ $percentage }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($option['is_correct'] ?? false)
                                                <span class="badge bg-success">Correct Answer</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Exam Usage -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Used in Exams</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Attempts</th>
                            <th>Performance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.analytics.exam-detail', $exam) }}">
                                        {{ $exam->title }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</span>
                                </td>
                                <td>{{ $exam->created_at ? $exam->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $exam->student_exams_count ?? 0 }}</td>
                                <td>
                                    @php
                                        $questionPerf = \App\Models\QuestionPerformance::where('question_id', $question->id)
                                            ->where('exam_id', $exam->id)
                                            ->first();
                                    @endphp
                                    @if($questionPerf)
                                        <span class="badge bg-{{ $questionPerf->accuracy_rate >= 70 ? 'success' : ($questionPerf->accuracy_rate >= 50 ? 'warning' : 'danger') }}">
                                            {{ $questionPerf->accuracy_rate }}%
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.question-detail', $question->id) }}?exam_id={{ $exam->id }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No exams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .explanation-content p {
        margin-bottom: 10px;
    }
    .border-success {
        border-color: #28a745 !important;
    }
    .option-image-thumb {
        max-height: 40px;
        border: 1px solid #ddd;
        padding: 3px;
        border-radius: 4px;
        margin-right: 5px;
    }
    .table td {
        vertical-align: middle;
    }
    
    .question_image{
        min-height: 400px; width:auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;
    }
    
    .option_image{
        min-height: 200px; width:auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;
    }
</style>
@endpush

@push('scripts')
<script>
    function filterByExam() {
        const examId = $('#examFilter').val();
        const url = new URL(window.location.href);
        if (examId) {
            url.searchParams.set('exam_id', examId);
        } else {
            url.searchParams.delete('exam_id');
        }
        window.location.href = url.toString();
    }

    // Handle image loading errors
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img').forEach(function(img) {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const parent = this.parentElement;
                const errorMsg = document.createElement('span');
                errorMsg.className = 'text-muted small';
                errorMsg.textContent = '⚠️ Image not available';
                parent.appendChild(errorMsg);
            });
        });
    });
</script>
@endpush