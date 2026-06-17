@extends('layouts.admin')

@section('title', 'Student Details - ' . $student->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $student->name }} - Student Details</h1>
        <div>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Student
            </a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" class="rounded-circle mb-3"
                        width="120" height="120">
                    <h4>{{ $student->name }}</h4>
                    <p class="text-muted">{{ $student->email }}</p>
                    <p class="text-muted">Roll No: {{ $student->roll_no ?? 'N/A' }}</p>
                    <p class="text-muted">Phone: {{ $student->phone ?? 'N/A' }}</p>

                    <div class="mb-3">
                        <span class="badge bg-success">Student</span>
                        <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">
                            {{ $student->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <hr>

                    <div class="text-start">
                        <p><strong>Member Since:</strong><br>{{ $student->created_at->format('d M Y h:i A') }}</p>
                        <p><strong>Last Updated:</strong><br>{{ $student->updated_at->format('d M Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h5 class="text-primary">{{ $stats['total_exams'] }}</h5>
                            <small class="text-muted">Total Exams</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h5 class="text-success">{{ $stats['completed_exams'] }}</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-info">{{ $stats['average_score'] }}%</h5>
                            <small class="text-muted">Avg Score</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning">{{ $stats['highest_score'] }}%</h5>
                            <small class="text-muted">Highest Score</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Correct:</span>
                        <span class="fw-bold text-success">{{ $stats['total_correct'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Incorrect:</span>
                        <span class="fw-bold text-danger">{{ $stats['total_incorrect'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Time Spent:</span>
                        <span class="fw-bold">{{ $stats['total_time_spent'] }} hours</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Performance Trend Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="250"></canvas>
                </div>
            </div>

            <!-- Subject-wise Performance -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subject-wise Performance</h6>
                </div>
                <div class="card-body">
                    @if(count($subjectPerformance) > 0)
                    @foreach($subjectPerformance as $subject)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $subject['name'] }}</span>
                            <span class="fw-bold">{{ $subject['score'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $subject['score'] >= 80 ? 'success' : ($subject['score'] >= 60 ? 'info' : ($subject['score'] >= 40 ? 'warning' : 'danger')) }}"
                                style="width: {{ $subject['score'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted text-center py-3">No subject data available</p>
                    @endif
                </div>
            </div>

            <!-- Batches Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Enrolled Batches</h6>
                </div>
                <div class="card-body">
                    @if($student->batches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Batch Name</th>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Status</th>
                                    <th>Enrolled Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->batches as $batch)
                                <tr>
                                    <td>{{ $batch->name }}</td>
                                    <td>{{ $batch->course->title }}</td>
                                    <td>{{ $batch->instructor->name }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $batch->pivot->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($batch->pivot->status) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($batch->pivot->enrolled_at)->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center">No batches enrolled</p>
                    @endif
                </div>
            </div>

            <!-- Recent Exams -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Exams</h6>
                </div>
                <div class="card-body">
                    @if($student->results->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->results->take(10) as $result)
                                <tr>
                                    <td>{{ $result->exam->title }}</td>
                                    <td>{{ $result->created_at->format('d M Y') }}</td>
                                    <td>{{ $result->obtained_marks }}/{{ $result->total_marks }}</td>
                                    <td>
                                        <span class="badge bg-{{ $result->percentage >= 70 ? 'success' : 'warning' }}">
                                            {{ round($result->percentage, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                            {{ ucfirst($result->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center">No exams taken yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Performance Trend Chart
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [{
                label: 'Score %',
                data: @json($trendScores),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                fill: true,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush