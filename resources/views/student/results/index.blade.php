@extends('layouts.student')

@section('title', 'My Results')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Results</h1>
        </div>

        <!-- Statistics Cards -->
        <!--<div class="row mb-4">-->
        <!--    <div class="col-xl-3 col-md-6 mb-4">-->
        <!--        <div class="card border-left-primary shadow h-100 py-2">-->
        <!--            <div class="card-body">-->
        <!--                <div class="row no-gutters align-items-center">-->
        <!--                    <div class="col mr-2">-->
        <!--                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">-->
        <!--                            Total Exams</div>-->
        <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_exams'] }}</div>-->
        <!--                    </div>-->
        <!--                    <div class="col-auto">-->
        <!--                        <i class="bi bi-journal-bookmark-fill fa-2x text-primary"></i>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->

        <!--    <div class="col-xl-3 col-md-6 mb-4">-->
        <!--        <div class="card border-left-success shadow h-100 py-2">-->
        <!--            <div class="card-body">-->
        <!--                <div class="row no-gutters align-items-center">-->
        <!--                    <div class="col mr-2">-->
        <!--                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">-->
        <!--                            Passed</div>-->
        <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['passed'] }}</div>-->
        <!--                    </div>-->
        <!--                    <div class="col-auto">-->
        <!--                        <i class="bi bi-check-circle-fill fa-2x text-success"></i>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->

        <!--    <div class="col-xl-3 col-md-6 mb-4">-->
        <!--        <div class="card border-left-info shadow h-100 py-2">-->
        <!--            <div class="card-body">-->
        <!--                <div class="row no-gutters align-items-center">-->
        <!--                    <div class="col mr-2">-->
        <!--                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">-->
        <!--                            Average Score</div>-->
        <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>-->
        <!--                    </div>-->
        <!--                    <div class="col-auto">-->
        <!--                        <i class="bi bi-graph-up-arrow fa-2x text-info"></i>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->

        <!--    <div class="col-xl-3 col-md-6 mb-4">-->
        <!--        <div class="card border-left-warning shadow h-100 py-2">-->
        <!--            <div class="card-body">-->
        <!--                <div class="row no-gutters align-items-center">-->
        <!--                    <div class="col mr-2">-->
        <!--                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">-->
        <!--                            Best Score</div>-->
        <!--                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['best_score'] }}%</div>-->
        <!--                    </div>-->
        <!--                    <div class="col-auto">-->
        <!--                        <i class="bi bi-trophy-fill fa-2x text-warning"></i>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="row mb-4">
            <!-- Total Exams -->
            <div class="col-xl-3 col-md-6 col-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Exams</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_exams'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-journal-bookmark-fill fs-2 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passed -->
            <div class="col-xl-3 col-md-6 col-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Passed</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['passed'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Score -->
            <div class="col-xl-3 col-md-6 col-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Average Score</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-graph-up-arrow fs-2 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Best Score -->
            <div class="col-xl-3 col-md-6 col-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Best Score</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['best_score'] }}%</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-trophy-fill fs-2 text-warning"></i>
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
                            @foreach ($examTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('exam_type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_from" class="form-control" placeholder="From Date"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date"
                            value="{{ request('date_to') }}">
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
                @if ($results->count() > 0)
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
                                @foreach ($results as $result)
                                    <tr>
                                        <td>
                                            <strong>{{ $result->exam->title }}</strong>
                                            <br>
                                            <small
                                                class="text-muted">{{ ucfirst(str_replace('_', ' ', $result->exam->type)) }}</small>
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
                                            @if ($result->rank)
                                                #{{ $result->rank }} / {{ $result->total_participants }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
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
                        {{ $results->withQueryString()->links('pagination::bootstrap-5') }}
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

        // function showAnalytics(resultId) {
        //     $('#analyticsModal').modal('show');

        //     $.ajax({
        //         url: `/student/results/${resultId}/analytics`,
        //         method: 'GET',
        //         success: function(response) {
        //             if (response.status === 'success') {
        //                 displayAnalytics(response.data);
        //             }
        //         },
        //         error: function() {
        //             $('#analyticsContent').html('<p class="text-danger text-center">Error loading analytics</p>');
        //         }
        //     });
        // }

        function showAnalytics(resultId) {
            // 1. Target elements using native DOM and jQuery
            const modalElement = document.getElementById('analyticsModal');
            const $content = $('#analyticsContent');

            // 2. Insert a clean Bootstrap loading spinner
            $content.html(`
        <div class="d-flex flex-column align-items-center justify-content-center py-5">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted small mb-0">Fetching your performance metrics...</p>
        </div>
    `);

            // 3. Open the modal using Vite-compatible API syntax
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalElement);
            bsModal.show();

            // 4. Fire the jQuery AJAX request
            $.ajax({
                url: `/student/results/${resultId}/analytics`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.status === 'success') {
                        displayAnalytics(response.data);
                    } else {
                        $content.html(`
                    <div class="alert alert-warning m-3 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>Failed to parse analytics data payload.</div>
                    </div>
                `);
                    }
                },
                error: function() {
                    $content.html(`
                <div class="alert alert-danger m-3 d-flex flex-column align-items-center text-center p-4 border-0" role="alert">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-2 mb-2"></i>
                    <h6 class="alert-heading fw-bold mb-1">Connection Error</h6>
                    <p class="small mb-0 text-secondary">Could not fetch analytics. Please try again later.</p>
                </div>
            `);
                }
            });
        }


        function displayAnalytics(data) {
            let html = '';

            // Helper function to calculate accuracy
            function calculateAccuracy(correct, incorrect) {
                const total = (correct || 0) + (incorrect || 0);
                if (total === 0) return 0;
                return (correct || 0) / total * 100;
            }
            //show the exam title

            html += `
        <div class"text-center text-primary mb-1 fw-bold">${data.title}</div>
    `;

            // 1. Overall Stats Card with colorful borders
            if (data.overall) {
                const overall = data.overall;
                const accuracy = overall.accuracy || 0;
                const percentile = overall.percentile || 0;

                // Determine color based on performance
                const scoreColor = accuracy >= 80 ? 'success' : accuracy >= 60 ? 'primary' : accuracy >= 40 ? 'warning' :
                    'danger';
                const percentileColor = percentile >= 80 ? 'success' : percentile >= 60 ? 'primary' : percentile >= 40 ?
                    'warning' : 'danger';

                html += `
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-${scoreColor} border-2 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Score</h6>
                            <h3 class="fw-bold text-${scoreColor}">${overall.percentage || 0}%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-success border-2 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Rank</h6>
                            <h3 class="fw-bold text-success">#${overall.rank || 1}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-${percentileColor} border-2 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Percentile</h6>
                            <h3 class="fw-bold text-${percentileColor}">${percentile}%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-info border-2 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Accuracy</h6>
                            <h3 class="fw-bold text-info">${accuracy}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        `;

                // Additional overall stats
                html += `
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-secondary border-2 shadow-sm">
                        <div class="card-body text-center">
                            <small class="text-muted d-block">Speed</small>
                            <span class="fw-bold">${overall.speed || 0}/min</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-secondary border-2 shadow-sm">
                        <div class="card-body text-center">
                            <small class="text-muted d-block">Time Taken</small>
                            <span class="fw-bold">${formatTime(overall.time_taken || 0)}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-secondary border-2 shadow-sm">
                        <div class="card-body text-center">
                            <small class="text-muted d-block">Total Participants</small>
                            <span class="fw-bold">${data.comparison?.total_participants || 0}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
            }

            // 2. Comparison Stats
            if (data.comparison) {
                const avgScore = data.comparison.average_score || 0;
                const yourScore = data.overall?.percentage || 0;
                const diff = yourScore - avgScore;
                const diffColor = diff > 0 ? 'success' : diff < 0 ? 'danger' : 'secondary';
                const diffText = diff > 0 ? `+${diff.toFixed(1)}% above average` : diff < 0 ?
                    `${diff.toFixed(1)}% below average` : 'Same as average';

                html += `
            <div class="card border-${diffColor} border-2 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                        Comparison with Peers
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Average Score</small>
                                <h5 class="fw-bold mb-0">${avgScore}%</h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Highest Score</small>
                                <h5 class="fw-bold mb-0 text-success">${data.comparison.highest_score || 0}%</h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 bg-${diffColor}-subtle">
                                <small class="text-muted d-block">Your Position</small>
                                <h5 class="fw-bold mb-0 text-${diffColor}">${diffText}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            }

            // 3. Sectional Analysis - FIXED ACCURACY CALCULATION
            if (data.sectional && Object.keys(data.sectional).length > 0) {
                html += `
            <div class="card border-warning border-2 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>
                        Sectional Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
        `;

                Object.entries(data.sectional).forEach(([section, details]) => {
                    // FIX: Calculate accuracy from correct/incorrect
                    const correct = parseInt(details.correct) || 0;
                    const incorrect = parseInt(details.incorrect) || 0;
                    const attempted = correct + incorrect;
                    const accuracy = attempted > 0 ? (correct / attempted) * 100 : 0;
                    const totalQuestions = details.total_questions || details.total || attempted;

                    const accuracyColor = accuracy >= 80 ? 'success' : accuracy >= 60 ? 'primary' : accuracy >= 40 ?
                        'warning' : 'danger';

                    html += `
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold mb-2">${section}</h6>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>Attempted: ${attempted}/${totalQuestions}</span>
                                <span class="text-success">✓ ${correct}</span>
                                <span class="text-danger">✗ ${incorrect}</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: ${totalQuestions > 0 ? (correct/totalQuestions)*100 : 0}%"></div>
                            <div class="progress-bar bg-danger" style="width: ${totalQuestions > 0 ? (incorrect/totalQuestions)*100 : 0}%"></div>
                        </div>
                        <div class="mt-2 text-center">
                            <span class="badge bg-${accuracyColor}">Accuracy: ${accuracy.toFixed(1)}%</span>
                            <span class="badge bg-secondary ms-1">${attempted}/${totalQuestions} attempted</span>
                        </div>
                    </div>
                </div>
            `;
                });

                html += `
                    </div>
                </div>
            </div>
        `;
            }

            // 4. Topic Analysis - FIXED ACCURACY CALCULATION
            if (data.topic && Object.keys(data.topic).length > 0) {
                html += `
            <div class="card border-success border-2 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i>
                        Topic Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
        `;

                Object.entries(data.topic).forEach(([topic, details]) => {
                    // FIX: Calculate accuracy from correct/incorrect
                    const correct = parseInt(details.correct) || 0;
                    const incorrect = parseInt(details.incorrect) || 0;
                    const total = correct + incorrect;
                    const accuracy = total > 0 ? (correct / total) * 100 : 0;

                    const accuracyColor = accuracy >= 80 ? 'success' : accuracy >= 60 ? 'primary' : accuracy >= 40 ?
                        'warning' : 'danger';

                    html += `
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold mb-2">${topic}</h6>
                        <div class="progress mb-2" style="height: 10px; background-color: #e9ecef;">
                            <div class="progress-bar bg-${accuracyColor}" style="width: ${accuracy}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Accuracy: <strong>${accuracy.toFixed(1)}%</strong></span>
                            <span class="text-success">✓ ${correct}</span>
                            <span class="text-danger">✗ ${incorrect}</span>
                        </div>
                        ${total > 0 ? `<small class="text-muted d-block mt-1">Total: ${total} questions</small>` : ''}
                    </div>
                </div>
            `;
                });

                html += `
                    </div>
                </div>
            </div>
        `;
            }

            // 5. Difficulty Analysis - FIXED ACCURACY CALCULATION AND BAR DISPLAY
            if (data.difficulty && Object.keys(data.difficulty).length > 0) {
                html += `
            <div class="card border-danger border-2 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-bar-chart-steps text-primary me-2"></i>
                        Difficulty Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
        `;

                Object.entries(data.difficulty).forEach(([level, details]) => {
                    // FIX: Calculate accuracy from correct/incorrect
                    const correct = parseInt(details.correct) || 0;
                    const incorrect = parseInt(details.incorrect) || 0;
                    const total = correct + incorrect;
                    const accuracy = total > 0 ? (correct / total) * 100 : 0;

                    const bgColor = level.toLowerCase() === 'easy' ? 'success' :
                        level.toLowerCase() === 'medium' ? 'warning' : 'danger';

                    html += `
                <div class="col-md-4 mb-3">
                    <div class="border rounded p-3 h-100 text-center">
                        <h6 class="fw-bold text-capitalize">${level}</h6>
                        <div class="progress mb-2" style="height: 20px; background-color: #e9ecef;">
                            <div class="progress-bar bg-${bgColor}"
                                 style="width: ${accuracy}%; transition: width 0.5s;">
                                ${accuracy > 0 ? accuracy.toFixed(1) + '%' : '0%'}
                            </div>
                        </div>
                        <div class="d-flex justify-content-around mt-2 small">
                            <span class="text-success"><i class="bi bi-check-circle"></i> ${correct}</span>
                            <span class="text-danger"><i class="bi bi-x-circle"></i> ${incorrect}</span>
                            <span class="text-secondary">${total} total</span>
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-${bgColor}">${accuracy.toFixed(1)}%</span>
                        </div>
                    </div>
                </div>
            `;
                });

                html += `
                    </div>
                </div>
            </div>
        `;
            }

            // 6. Question Type Analysis - FIXED ACCURACY CALCULATION
            if (data.by_type && Object.keys(data.by_type).length > 0) {
                html += `
            <div class="card border-info border-2 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-list-ul text-primary me-2"></i>
                        Performance by Question Type
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Accuracy</th>
                                    <th>Correct</th>
                                    <th>Incorrect</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

                Object.entries(data.by_type).forEach(([type, details]) => {
                    // FIX: Calculate accuracy from correct/incorrect
                    const correct = parseInt(details.correct) || 0;
                    const incorrect = parseInt(details.incorrect) || 0;
                    const total = correct + incorrect;
                    const accuracy = total > 0 ? (correct / total) * 100 : 0;

                    const accuracyColor = accuracy >= 80 ? 'success' : accuracy >= 60 ? 'primary' : accuracy >= 40 ?
                        'warning' : 'danger';
                    const displayType = type.replace(/_/g, ' ').toUpperCase();

                    // Only show rows where total > 0
                    if (total === 0) {
                        return;
                    }

                    html += `
                <tr>
                    <td><strong>${displayType}</strong></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px; background-color: #e9ecef; min-width: 80px;">
                                <div class="progress-bar bg-${accuracyColor}"
                                     style="width: ${accuracy}%; transition: width 0.5s;">
                                </div>
                            </div>
                            <span class="fw-bold text-${accuracyColor}">${accuracy.toFixed(1)}%</span>
                        </div>
                    </td>
                    <td class="text-success"><i class="bi bi-check-circle"></i> ${correct}</td>
                    <td class="text-danger"><i class="bi bi-x-circle"></i> ${incorrect}</td>
                    <td>${total}</td>
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

            // 7. No analytics available
            if (!data.sectional || Object.keys(data.sectional).length === 0 &&
                !data.difficulty || Object.keys(data.difficulty).length === 0 &&
                !data.topic || Object.keys(data.topic).length === 0) {
                html += `
            <div class="alert alert-info text-center border-2 border-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                No detailed analytics available for this exam.
            </div>
        `;
            }

            // Render everything
            $('#analyticsContent').html(html);
        }

        // Helper function to format time
        function formatTime(seconds) {
            if (!seconds || seconds === 0) return '0s';

            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;

            if (mins === 0) return `${secs}s`;
            if (secs === 0) return `${mins}m`;
            return `${mins}m ${secs}s`;
        }





        // function displayAnalytics(data) {
        //     let html = `
    //         <div class="row">
    //             <div class="col-md-6">
    //                 <div class="card mb-3">
    //                     <div class="card-header bg-primary text-white">
    //                         <h6 class="mb-0">Overall Performance</h6>
    //                     </div>
    //                     <div class="card-body">
    //                         <table class="table table-sm">
    //                             <tr>
    //                                 <td>Percentage:</td>
    //                                 <td><strong>${data.overall.percentage}%</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Rank:</td>
    //                                 <td><strong>#${data.overall.rank}</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Percentile:</td>
    //                                 <td><strong>${data.overall.percentile}</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Accuracy:</td>
    //                                 <td><strong>${data.overall.accuracy}%</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Speed:</td>
    //                                 <td><strong>${data.overall.speed} Q/min</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Time Taken:</td>
    //                                 <td><strong>${data.overall.time_taken}</strong></td>
    //                             </tr>
    //                         </table>
    //                     </div>
    //                 </div>
    //             </div>
    //             <div class="col-md-6">
    //                 <div class="card mb-3">
    //                     <div class="card-header bg-info text-white">
    //                         <h6 class="mb-0">Comparison</h6>
    //                     </div>
    //                     <div class="card-body">
    //                         <table class="table table-sm">
    //                             <tr>
    //                                 <td>Your Score:</td>
    //                                 <td><strong>${data.overall.percentage}%</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Average Score:</td>
    //                                 <td><strong>${Math.round(data.comparison.average_score)}%</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Highest Score:</td>
    //                                 <td><strong>${Math.round(data.comparison.highest_score)}%</strong></td>
    //                             </tr>
    //                             <tr>
    //                                 <td>Total Participants:</td>
    //                                 <td><strong>${data.comparison.total_participants}</strong></td>
    //                             </tr>
    //                         </table>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>
    //     `;

        //     // Add sectional analysis if available
        //     if (data.sectional && data.sectional.length > 0) {
        //         html += `
    //             <div class="card mb-3">
    //                 <div class="card-header bg-success text-white">
    //                     <h6 class="mb-0">Section-wise Performance</h6>
    //                 </div>
    //                 <div class="card-body">
    //                     <div class="table-responsive">
    //                         <table class="table table-bordered">
    //                             <thead>
    //                                 <tr>
    //                                     <th>Section</th>
    //                                     <th>Correct</th>
    //                                     <th>Incorrect</th>
    //                                     <th>Unanswered</th>
    //                                     <th>Accuracy</th>
    //                                 </tr>
    //                             </thead>
    //                             <tbody>
    //         `;

        //         data.sectional.forEach(section => {
        //             html += `
    //                 <tr>
    //                     <td>${section.section_name}</td>
    //                     <td class="text-success">${section.correct}</td>
    //                     <td class="text-danger">${section.incorrect}</td>
    //                     <td class="text-secondary">${section.unanswered}</td>
    //                     <td>${section.accuracy}%</td>
    //                 </tr>
    //             `;
        //         });

        //         html += `
    //                             </tbody>
    //                         </table>
    //                     </div>
    //                 </div>
    //             </div>
    //         `;
        //     }

        //     // Add difficulty analysis
        //     if (data.difficulty) {
        //         html += `
    //             <div class="card mb-3">
    //                 <div class="card-header bg-warning">
    //                     <h6 class="mb-0">Difficulty-wise Performance</h6>
    //                 </div>
    //                 <div class="card-body">
    //                     <div class="table-responsive">
    //                         <table class="table table-bordered">
    //                             <thead>
    //                                 <tr>
    //                                     <th>Difficulty</th>
    //                                     <th>Total</th>
    //                                     <th>Correct</th>
    //                                     <th>Incorrect</th>
    //                                     <th>Accuracy</th>
    //                                 </tr>
    //                             </thead>
    //                             <tbody>
    //         `;

        //         for (const [level, stats] of Object.entries(data.difficulty)) {
        //             html += `
    //                 <tr>
    //                     <td>${level.charAt(0).toUpperCase() + level.slice(1)}</td>
    //                     <td>${stats.total}</td>
    //                     <td class="text-success">${stats.correct}</td>
    //                     <td class="text-danger">${stats.incorrect}</td>
    //                     <td>${stats.accuracy}%</td>
    //                 </tr>
    //             `;
        //         }

        //         html += `
    //                             </tbody>
    //                         </table>
    //                     </div>
    //                 </div>
    //             </div>
    //         `;
        //     }

        //     $('#analyticsContent').html(html);
        // }

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
