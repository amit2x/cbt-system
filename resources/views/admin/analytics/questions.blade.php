@extends('layouts.admin')

@section('title', 'Question Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Analytics</h1>
        <div>
            <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Questions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalQuestions }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-question-circle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Questions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeQuestions }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg Difficulty</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $avgDifficulty = ($questions->avg('difficulty_score') ?? 0) * 100;
                                @endphp
                                {{ round($avgDifficulty) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCategories }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tags fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Question Type Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Difficulty Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="difficultyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Question List</h6>
            <div>
                <button type="button" class="btn btn-sm btn-success" onclick="exportQuestions()">
                    <i class="bi bi-download"></i> Export
                </button>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Manage Questions
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Difficulty</th>
                            <th>Subject</th>
                            <th>Categories</th>
                            <th>Used In</th>
                            <th>Success Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                        <tr>
                            <td>
                                <a href="{{ route('admin.analytics.question-detail', $question->id) }}" class="text-decoration-none">
                                    {{ Str::limit(strip_tags($question->question_text), 60) }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-{{ $question->type_badge }}">
                                    {{ $question->type_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $question->difficulty_badge }}">
                                    {{ $question->difficulty_name }}
                                </span>
                            </td>
                            <td>{{ $question->subject->title ?? 'N/A' }}</td>
                            <td>
                                @foreach($question->categories as $category)
                                    <span class="badge bg-secondary">{{ $category->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $question->exams_count }}</span>
                            </td>
                            <td>
                                @php
                                    $successRate = $question->success_rate ?? 0;
                                @endphp
                                <span class="badge bg-{{ $successRate >= 70 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger') }}">
                                    {{ $successRate }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $question->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($question->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.analytics.question-detail', $question->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-question-circle fs-1 text-muted"></i>
                                <p class="mt-2">No questions found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $questions->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {

    // Question Type Distribution Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Single Choice', 'Multiple Choice', 'Numerical'],
            datasets: [{
                data: @json(array_values($typeDistribution)),
                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Difficulty Distribution Chart
    const diffCtx = document.getElementById('difficultyChart').getContext('2d');
    new Chart(diffCtx, {
        type: 'bar',
        data: {
            labels: ['Easy', 'Medium', 'Hard'],
            datasets: [{
                label: 'Number of Questions',
                data: @json(array_values($difficultyDistribution)),
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b']
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }
        }
    });
    
    });

    function exportQuestions() {
        window.location.href = '{{ route("admin.questions.export") }}';
    }
</script>
@endpush