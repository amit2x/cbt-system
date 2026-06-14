@extends('layouts.admin')

@section('title', 'Exam Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Exam Analytics</h1>
        <button type="button" class="btn btn-primary" onclick="generateReport()">
            <i class="bi bi-file-earmark-plus"></i> Generate Report
        </button>
    </div>

    <!-- Exams List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Exams</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Exam</th>
                            <th>Type</th>
                            <th>Questions</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Last Attempt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                            <tr>
                                <td>
                                    <strong>{{ $exam->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $exam->course->title ?? 'No Course' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</span>
                                </td>
                                <td>{{ $exam->questions_count }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $exam->results_count }}</span>
                                    @if($exam->total_students > 0)
                                        <br>
                                        <small>{{ round(($exam->results_count / $exam->total_students) * 100, 1) }}% participation</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $avgScore = round($exam->results()->avg('percentage') ?? 0, 1);
                                    @endphp
                                    <span class="text-{{ $avgScore >= 60 ? 'success' : ($avgScore >= 40 ? 'warning' : 'danger') }}">
                                        {{ $avgScore }}%
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $passed = $exam->results()->where('status', 'passed')->count();
                                        $passRate = $exam->results_count > 0 ? round(($passed / $exam->results_count) * 100, 1) : 0;
                                    @endphp
                                    {{ $passRate }}%
                                </td>
                                <td>
                                    @php
                                        $lastAttempt = $exam->results()->latest()->first();
                                    @endphp
                                    @if($lastAttempt)
                                        {{ $lastAttempt->created_at->format('d M Y') }}
                                    @else
                                        Never
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.analytics.exam-detail', $exam) }}"
                                           class="btn btn-sm btn-info" title="View Analytics">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                onclick="viewExamDetails({{ $exam->id }})" title="Quick View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="examModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exam Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="examModalBody">
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

@push('scripts')
<script>
    function viewExamDetails(examId) {
        $('#examModal').modal('show');

        $.ajax({
            url: '{{ route("admin.analytics.exams") }}',
            method: 'GET',
            data: { exam_id: examId },
            success: function(response) {
                if (response.status === 'success') {
                    displayExamAnalytics(response.data);
                }
            },
            error: function() {
                $('#examModalBody').html('<p class="text-danger text-center">Error loading analytics</p>');
            }
        });
    }

    function displayExamAnalytics(data) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Overview</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td>Total Participants:</td>
                                    <td><strong>${data.overview.total_participants}</strong></td>
                                </tr>
                                <tr>
                                    <td>Participation Rate:</td>
                                    <td><strong>${data.overview.participation_rate}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Average Score:</td>
                                    <td><strong>${data.overview.average_score}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Highest Score:</td>
                                    <td><strong class="text-success">${data.overview.highest_score}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Lowest Score:</td>
                                    <td><strong class="text-danger">${data.overview.lowest_score}%</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Performance Metrics</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td>Passed:</td>
                                    <td><strong class="text-success">${data.performance_metrics.passed}</strong></td>
                                </tr>
                                <tr>
                                    <td>Failed:</td>
                                    <td><strong class="text-danger">${data.performance_metrics.failed}</strong></td>
                                </tr>
                                <tr>
                                    <td>Pass Rate:</td>
                                    <td><strong>${data.performance_metrics.pass_rate}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Average Accuracy:</td>
                                    <td><strong>${data.performance_metrics.average_accuracy}%</strong></td>
                                </tr>
                                <tr>
                                    <td>Average Speed:</td>
                                    <td><strong>${data.performance_metrics.average_speed} Q/min</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Score Distribution</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <h5 class="text-primary">90-100%</h5>
                                        <p class="mb-0">${data.distribution['90_100']} students</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <h5 class="text-success">75-89%</h5>
                                        <p class="mb-0">${data.distribution['75_89']} students</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <h5 class="text-info">60-74%</h5>
                                        <p class="mb-0">${data.distribution['60_74']} students</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <h5 class="text-warning">40-59%</h5>
                                        <p class="mb-0">${data.distribution['40_59']} students</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <h5 class="text-danger">Below 40%</h5>
                                        <p class="mb-0">${data.distribution['below_40']} students</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#examModalBody').html(html);
    }

    function generateReport() {
        // Redirect to report generation with exam filter
        window.location.href = '{{ route("admin.analytics.reports") }}?report_type=exam';
    }
</script>
@endpush
