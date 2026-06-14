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
                    <div class="mb-3">
                        <label class="fw-bold">Question:</label>
                        <p class="border p-3 bg-light rounded">{!! $question->question_text !!}</p>
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

                    @if($question->explanation)
                        <div class="mt-3">
                            <label class="fw-bold">Explanation:</label>
                            <p class="text-muted">{{ $question->explanation }}</p>
                        </div>
                    @endif

                    @if($question->options->count() > 0)
                        <div class="mt-3">
                            <label class="fw-bold">Options:</label>
                            <div class="row">
                                @foreach($question->options as $option)
                                    <div class="col-md-6 mb-2">
                                        <div class="p-2 rounded {{ $option->is_correct ? 'bg-success text-white' : 'bg-light' }}">
                                            {{ $option->option_text }}
                                            @if($option->is_correct)
                                                <i class="bi bi-check-circle-fill float-end"></i>
                                            @endif
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
                        <h2 class="display-4 text-primary">{{ $analysis['metrics']['accuracy_rate'] }}%</h2>
                        <p class="text-muted">Accuracy Rate</p>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td>Total Attempts:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['total_attempts'] }}</td>
                        </tr>
                        <tr>
                            <td>Correct:</td>
                            <td class="fw-bold text-success">{{ $analysis['metrics']['correct'] }}</td>
                        </tr>
                        <tr>
                            <td>Incorrect:</td>
                            <td class="fw-bold text-danger">{{ $analysis['metrics']['incorrect'] }}</td>
                        </tr>
                        <tr>
                            <td>Unanswered:</td>
                            <td class="fw-bold text-secondary">{{ $analysis['metrics']['unanswered'] }}</td>
                        </tr>
                        <tr>
                            <td>Difficulty Index:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['difficulty_index'] }}%</td>
                        </tr>
                        <tr>
                            <td>Discrimination Index:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['discrimination_index'] }}</td>
                        </tr>
                        <tr>
                            <td>Average Time:</td>
                            <td class="fw-bold">{{ $analysis['metrics']['avg_time'] }}</td>
                        </tr>
                        <tr>
                            <td>Effectiveness:</td>
                            <td>
                                <span class="badge bg-{{
                                    $analysis['metrics']['effectiveness_level'] == 'good' ? 'success' :
                                    ($analysis['metrics']['effectiveness_level'] == 'average' ? 'warning' : 'danger')
                                }}">
                                    {{ ucfirst($analysis['metrics']['effectiveness_level']) }}
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
                                    <th>Selected Count</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis['option_analysis'] as $option)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $option['option_text'] }}</td>
                                        <td>{{ $option['count'] }}</td>
                                        <td>
                                            @if($analysis['metrics']['total_attempts'] > 0)
                                                {{ round(($option['count'] / $analysis['metrics']['total_attempts']) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </td>
                                        <td>
                                            @if($option['is_correct'])
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
                        @foreach($exams as $exam)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.analytics.exam-detail', $exam) }}">
                                        {{ $exam->title }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</span>
                                </td>
                                <td>{{ $exam->created_at->format('d M Y') }}</td>
                                <td>{{ $exam->student_exams_count }}</td>
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
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.analytics.question-detail', $question->id) }}?exam_id={{ $exam->id }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

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
</script>
@endpush
