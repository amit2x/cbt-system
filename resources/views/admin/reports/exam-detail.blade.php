{{-- resources/views/admin/reports/exam-detail.blade.php --}}

@extends('layouts.admin')

@section('title', 'Exam Detail - ' . $exam->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Exam Analysis: {{ $exam->title }}</h1>
        <div>
            <a href="{{ route('admin.reports.export-exam', $exam) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Report
            </a>
            <a href="{{ route('admin.reports.exams') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Exams
            </a>
        </div>
    </div>

    @if($analytics)
        <!-- Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $analytics['overview']['total_participants'] ?? 0 }}</h3>
                        <p class="mb-0">Participants</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $analytics['overview']['average_score'] ?? 0 }}%</h3>
                        <p class="mb-0">Average Score</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>{{ $analytics['overview']['pass_rate'] ?? 0 }}%</h3>
                        <p class="mb-0">Pass Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>{{ $analytics['overview']['highest_score'] ?? 0 }}%</h3>
                        <p class="mb-0">Highest Score</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribution -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Score Distribution</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($analytics['distribution'] ?? [] as $range => $count)
                        <div class="col-md-2 text-center mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4>{{ $count }}</h4>
                                    <small>{{ str_replace('_', '-', $range) }}%</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        @if(!empty($analytics['top_performers']))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performers</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['top_performers'] as $index => $performer)
                                    <tr>
                                        <td>#{{ $index + 1 }}</td>
                                        <td>{{ $performer['student_name'] ?? 'Unknown' }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ $performer['percentage'] }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted"></i>
                <p class="text-muted mt-3">No analytics data available for this exam.</p>
                <p class="text-muted">No results have been recorded yet.</p>
            </div>
        </div>
    @endif
</div>
@endsection