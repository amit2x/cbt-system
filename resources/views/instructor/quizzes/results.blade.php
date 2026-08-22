@extends('layouts.instructor')

@section('title', 'Quiz Results')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quiz Results: {{ $quiz->title }}</h1>
        <div>
            <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Quiz
            </a>
            <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
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
                                Total Attempts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_attempts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
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
                                Passed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['passed'] }}</div>
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
                                Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ round($stats['average_score'], 1) }}%</div>
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
                                Pass Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_attempts'] > 0 ? round(($stats['passed'] / $stats['total_attempts']) * 100, 1) : 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy fs-2 text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Score Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="scoreDistribution" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Question-wise Analysis</h6>
                </div>
                <div class="card-body">
                    <canvas id="questionAnalysis" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Student Results</h6>
            <button class="btn btn-sm btn-success" onclick="exportResults()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Correct</th>
                            <th>Incorrect</th>
                            <th>Unanswered</th>
                            <th>Time Taken</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $result->student->avatar_url }}" class="rounded-circle me-2" width="30" height="30">
                                    <div>
                                        {{ $result->student->name }}
                                        <br>
                                        <small class="text-muted">{{ $result->student->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $result->obtained_marks }}/{{ $result->total_marks }}</td>
                            <td>
                                <span class="badge bg-{{ $result->percentage >= 60 ? 'success' : 'warning' }}">
                                    {{ round($result->percentage, 1) }}%
                                </span>
                            </td>
                            <td class="text-success">{{ $result->correct_answers }}</td>
                            <td class="text-danger">{{ $result->incorrect_answers }}</td>
                            <td class="text-secondary">{{ $result->unanswered }}</td>
                            <td>{{ gmdate('i:s', $result->time_taken_seconds) }}</td>
                            <td>
                                <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                    {{ ucfirst($result->status) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" onclick="viewStudentResult({{ $result->student_id }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $results->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', (event) => {
        // Score Distribution Chart
        const scoreCtx = document.getElementById('scoreDistribution').getContext('2d');
        new Chart(scoreCtx, {
            type: 'bar',
            data: {
                labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
                datasets: [{
                    label: 'Number of Students',
                    data: @json($distribution),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
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

        // Question Analysis Chart
        const questionCtx = document.getElementById('questionAnalysis').getContext('2d');
        new Chart(questionCtx, {
            type: 'bar',
            data: {
                labels: @json($questionLabels),
                datasets: [
                    {
                        label: 'Correct',
                        data: @json($correctData),
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Incorrect',
                        data: @json($incorrectData),
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
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

    function exportResults() {
        window.location.href = '{{ route("instructor.quizzes.export-results", $quiz) }}';
    }

    function viewStudentResult(studentId) {
        // Implement viewing individual student result
    }
</script>
@endpush
