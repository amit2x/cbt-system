@extends('layouts.admin')

@section('title', 'Exam Results - ' . $exam->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $exam->title }} - Results</h1>
        <div>
            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View Exam
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Exams
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>
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

    <!-- Score Distribution Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Score Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="scoreDistribution" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Student Results</h6>
            <a href="{{ route('admin.exams.export-results', $exam) }}" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export Results
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Batch</th>
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
                        @forelse($results as $result)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $result->student->avatar_url }}" class="rounded-circle me-2" width="30" height="30">
                                    <div>
                                        <strong>{{ $result->student->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $result->student->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($result->student->batches as $batch)
                                    @if($batch->id == $exam->batch_id)
                                        <span class="badge bg-info">{{ $batch->name }}</span>
                                    @endif
                                @endforeach
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
                            <td>{{ gmdate('H:i:s', $result->time_taken_seconds) }}</td>
                            <td>
                                <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                    {{ ucfirst($result->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.analytics.student-detail', $result->student_id) }}?exam_id={{ $exam->id }}"
                                   class="btn btn-sm btn-info" title="View Student">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No results found for this exam.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $results->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        const ctx = document.getElementById('scoreDistribution').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['90-100%', '75-89%', '60-74%', '40-59%', 'Below 40%'],
                datasets: [{
                    label: 'Number of Students',
                    data: [
                        {{ $distribution['90-100'] }},
                        {{ $distribution['75-89'] }},
                        {{ $distribution['60-74'] }},
                        {{ $distribution['40-59'] }},
                        {{ $distribution['below-40'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#fd7e14',
                        '#dc3545'
                    ]
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
</script>
@endpush
