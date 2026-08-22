{{-- resources/views/admin/reports/question-analysis.blade.php --}}

@extends('layouts.admin')

@section('title', 'Question Analysis')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Analysis</h1>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Questions</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.question-analysis') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="exam_id" class="form-control">
                        <option value="">All Exams</option>
                        @foreach($exams ?? [] as $exam)
                            <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="single_choice" {{ request('type') == 'single_choice' ? 'selected' : '' }}>Single Choice</option>
                        <option value="multiple_choice" {{ request('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="numerical" {{ request('type') == 'numerical' ? 'selected' : '' }}>Numerical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="difficulty" class="form-control">
                        <option value="">All Difficulties</option>
                        <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="effectiveness" class="form-control">
                        <option value="">All Effectiveness</option>
                        <option value="good" {{ request('effectiveness') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="average" {{ request('effectiveness') == 'average' ? 'selected' : '' }}>Average</option>
                        <option value="poor" {{ request('effectiveness') == 'poor' ? 'selected' : '' }}>Poor</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Questions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_questions'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-question-circle-fill fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Avg Accuracy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['avg_accuracy'] ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bullseye fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avg Difficulty</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['avg_difficulty'] ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up-arrow fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Good Questions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['good_questions'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-star-fill fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Question Analysis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Difficulty</th>
                            <th>Attempts</th>
                            <th>Accuracy</th>
                            <th>Difficulty Index</th>
                            <th>Discrimination</th>
                            <th>Effectiveness</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                            @php
                                $performance = $question->performance ?? null;
                                
                                // Safely get performance values with defaults
                                $totalAttempts = $performance->total_attempts ?? 0;
                                $accuracyRate = $performance->accuracy_rate ?? 0;
                                $difficultyIndex = $performance->difficulty_index ?? 0;
                                $discriminationIndex = $performance->discrimination_index ?? 0;
                                $effectivenessLevel = $performance->effectiveness_level ?? null;
                                
                                // Determine colors
                                $accuracyColor = $accuracyRate >= 60 ? 'success' : 'warning';
                                $difficultyColor = $difficultyIndex >= 60 ? 'danger' : 'warning';
                                $discriminationColor = $discriminationIndex >= 0.3 ? 'success' : 'warning';
                                
                                // Determine effectiveness color and label
                                if ($effectivenessLevel == 'good') {
                                    $effectivenessColor = 'success';
                                    $effectivenessLabel = 'Good';
                                } elseif ($effectivenessLevel == 'average') {
                                    $effectivenessColor = 'warning';
                                    $effectivenessLabel = 'Average';
                                } elseif ($effectivenessLevel == 'poor') {
                                    $effectivenessColor = 'danger';
                                    $effectivenessLabel = 'Poor';
                                } else {
                                    $effectivenessColor = 'secondary';
                                    $effectivenessLabel = 'N/A';
                                }
                            @endphp
                            <tr>
                                <td>{{ $question->id }}</td>
                                <td>
                                    <a href="{{ route('admin.reports.question-detail', $question) }}">
                                        <strong>{{ Str::limit(strip_tags($question->question_text ?? ''), 50) }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $question->type_name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @php
                                        $diffColor = $question->difficulty == 'easy' ? 'success' : ($question->difficulty == 'medium' ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $diffColor }}">
                                        {{ ucfirst($question->difficulty ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>{{ $totalAttempts }}</td>
                                <td>
                                    <span class="badge bg-{{ $accuracyColor }}">
                                        {{ $accuracyRate }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $difficultyColor }}">
                                        {{ $difficultyIndex }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $discriminationColor }}">
                                        {{ number_format($discriminationIndex, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $effectivenessColor }}">
                                        {{ $effectivenessLabel }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reports.question-detail', $question) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No questions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $questions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection