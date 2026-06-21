@extends('layouts.admin')

@section('title', 'Batch Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Batch Analytics</h1>
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
                                Total Batches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBatches }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-grid-3x3-gap-fill fs-2 text-gray-300"></i>
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
                                Ongoing Batches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ongoingBatches }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-play-circle fs-2 text-gray-300"></i>
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
                                Avg Performance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $averageScore }}%</div>
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
                                Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStudents }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Performance Chart -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Performance Comparison</h6>
                </div>
                <div class="card-body">
                    <canvas id="batchPerformanceChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="batchStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Batches Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Batches</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Course</th>
                            <th>Students</th>
                            <th>Exams</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <strong>{{ $batch->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $batch->start_date->format('d M Y') }} - {{ $batch->end_date->format('d M Y') }}</small>
                            </td>
                            <td>{{ $batch->course->title ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $batch->students_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $batch->exams_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $batch->average_score >= 70 ? 'success' : ($batch->average_score >= 50 ? 'warning' : 'danger') }}">
                                    {{ $batch->average_score }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $batch->pass_rate >= 70 ? 'success' : ($batch->pass_rate >= 50 ? 'warning' : 'danger') }}">
                                    {{ $batch->pass_rate }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.analytics.batch-detail', $batch->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-grid-3x3-gap-fill fs-1 text-muted"></i>
                                <p class="mt-2">No batches found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $batches->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {

    // Batch Performance Chart
    const batchCtx = document.getElementById('batchPerformanceChart').getContext('2d');
    new Chart(batchCtx, {
        type: 'bar',
        data: {
            labels: @json($batchLabels),
            datasets: [
                {
                    label: 'Average Score',
                    data: @json($batchScores),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: '#4e73df',
                    borderWidth: 1
                },
                {
                    label: 'Pass Rate',
                    data: @json($batchPassRates),
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: '#1cc88a',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Batch Status Chart
    const statusCtx = document.getElementById('batchStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Ongoing', 'Upcoming', 'Completed'],
            datasets: [{
                data: [
                    {{ $statusCounts['ongoing'] ?? 0 }},
                    {{ $statusCounts['upcoming'] ?? 0 }},
                    {{ $statusCounts['completed'] ?? 0 }}
                ],
                backgroundColor: ['#1cc88a', '#f6c23e', '#858796']
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

});
</script>
@endpush