@extends('layouts.student')

@section('title', 'My Results')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Results</h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Exams</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_exams'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-journal-bookmark-fill fa-2x text-gray-300"></i>
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
                            <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
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
                            <i class="bi bi-graph-up-arrow fa-2x text-gray-300"></i>
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
                                Best Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['best_score'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trophy-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trend Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Performance Trend</h6>
        </div>
        <div class="card-body">
            <canvas id="trendChart" height="100"></canvas>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('student.results.index') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="exam_type" class="form-control">
                        <option value="">All Exam Types</option>
                        @foreach($examTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('exam_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Passed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('student.results.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Exam Results</h6>
        </div>
        <div class="card-body">
            @if($results->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Exam</th>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Correct</th>
                                <th>Incorrect</th>
                                <th>Unanswered</th>
                                <th>Accuracy</th>
                                <th>Rank</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                <tr>
                                    <td>
                                        <strong>{{ $result->exam->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $result->exam->type)) }}</small>
                                    </td>
                                    <td>{{ $result->created_at->format('d M Y') }}</td>
                                    <td>
                                        <strong class="text-primary">{{ $result->percentage }}%</strong>
                                        <br>
                                        <small>{{ $result->obtained_marks }}/{{ $result->total_marks }}</small>
                                    </td>
                                    <td class="text-success">{{ $result->correct_answers }}</td>
                                    <td class="text-danger">{{ $result->incorrect_answers }}</td>
                                    <td class="text-secondary">{{ $result->unanswered }}</td>
                                    <td>{{ $result->accuracy_percentage }}%</td>
                                    <td>
                                        @if($result->rank)
                                            #{{ $result->rank }} / {{ $result->total_participants }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                            {{ ucfirst($result->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('student.results.show', $result) }}"
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="showAnalytics({{ $result->id }})" title="Analytics">
                                                <i class="bi bi-graph-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="shareResult({{ $result->id }})" title="Share">
                                                <i class="bi bi-share"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $results->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                    <p class="mt-2">No results found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Performance Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="analyticsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Performance Trend Chart
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($trends['labels']),
            datasets: [{
                label: 'Score %',
                data: @json($trends['scores']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Accuracy %',
                data: @json($trends['accuracy']),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    function showAnalytics(resultId) {
        $('#analyticsModal').modal('show');

        $.ajax({
            url: `/student/results/${resultId}/analytics`,
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    displayAnalytics(response.data);
                }
            },
            error: function() {
                $('#analyticsContent').html('<p class="text-danger text-center">Error loading analytics</p>');
            }
        });
    }

    function displayAnalytics(data) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Overall Performance</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td>Percentage:</td>
                                    <td><strong>${data.overall.percentage}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Rank:</td>
                                    <td><strong>#${data.overall.rank}</strong></td>
                                </tr>
                                <tr>
                                    <td>Percentile:</td>
                                    <td><strong>${data.overall.percentile}</strong></td>
                                </tr>
                                <tr>
                                    <td>Accuracy:</td>
                                    <td><strong>${data.overall.accuracy}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Speed:</td>
                                    <td><strong>${data.overall.speed} Q/min</strong></td>
                                </tr>
                                <tr>
                                    <td>Time Taken:</td>
                                    <td><strong>${data.overall.time_taken}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Comparison</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td>Your Score:</td>
                                    <td><strong>${data.overall.percentage}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Average Score:</td>
                                    <td><strong>${Math.round(data.comparison.average_score)}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Highest Score:</td>
                                    <td><strong>${Math.round(data.comparison.highest_score)}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Participants:</td>
                                    <td><strong>${data.comparison.total_participants}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add sectional analysis if available
        if (data.sectional && data.sectional.length > 0) {
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Section-wise Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Section</th>
                                        <th>Correct</th>
                                        <th>Incorrect</th>
                                        <th>Unanswered</th>
                                        <th>Accuracy</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            data.sectional.forEach(section => {
                html += `
                    <tr>
                        <td>${section.section_name}</td>
                        <td class="text-success">${section.correct}</td>
                        <td class="text-danger">${section.incorrect}</td>
                        <td class="text-secondary">${section.unanswered}</td>
                        <td>${section.accuracy}%</td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        // Add difficulty analysis
        if (data.difficulty) {
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">Difficulty-wise Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Difficulty</th>
                                        <th>Total</th>
                                        <th>Correct</th>
                                        <th>Incorrect</th>
                                        <th>Accuracy</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            for (const [level, stats] of Object.entries(data.difficulty)) {
                html += `
                    <tr>
                        <td>${level.charAt(0).toUpperCase() + level.slice(1)}</td>
                        <td>${stats.total}</td>
                        <td class="text-success">${stats.correct}</td>
                        <td class="text-danger">${stats.incorrect}</td>
                        <td>${stats.accuracy}%</td>
                    </tr>
                `;
            }

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#analyticsContent').html(html);
    }

    function shareResult(resultId) {
        $.ajax({
            url: `/student/results/${resultId}/share`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    navigator.clipboard.writeText(response.data.share_url);
                    toastr.success('Share link copied to clipboard!');
                }
            },
            error: function() {
                toastr.error('Error sharing result');
            }
        });
    }
</script>
@endpush
