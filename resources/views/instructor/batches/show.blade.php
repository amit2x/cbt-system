@extends('layouts.instructor')

@section('title', $batch->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $batch->name }}</h1>
        <div>
            <a href="{{ route('instructor.batches.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Batches
            </a>
        </div>
    </div>

    <!-- Batch Info Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] }}</div>
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
                                Active Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_students'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fs-2 text-gray-300"></i>
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
                                Total Exams</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_exams'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil-square fs-2 text-gray-300"></i>
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
                                Avg Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_score'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Course Info -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Batch Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Course:</th>
                            <td>{{ $batch->course->title }}</td>
                        </tr>
                        <tr>
                            <th>Instructor:</th>
                            <td>{{ $batch->instructor->name }}</td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td>{{ $batch->start_date->format('d M Y') }} - {{ $batch->end_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span
                                    class="badge bg-{{ $batch->status == 'ongoing' ? 'success' : ($batch->status == 'upcoming' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Progress:</th>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $batch->progress }}%">
                                        {{ round($batch->progress) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="col-md-8 mb-4">
            <div class="card shadow">
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
                                    <th>Exams Taken</th>
                                    <th>Average Score</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($studentPerformance, 0, 10) as $index => $performance)
                                <tr>
                                    <td>#{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $performance['student']->avatar_url }}"
                                                class="rounded-circle me-2" width="30" height="30">
                                            <div>
                                                <strong>{{ $performance['student']->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $performance['student']->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $performance['exams_taken'] }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $performance['average_score'] >= 80 ? 'success' : ($performance['average_score'] >= 60 ? 'info' : 'warning') }}">
                                            {{ $performance['average_score'] }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $performance['student']->pivot->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($performance['student']->pivot->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.analytics.student-detail', $performance['student']->id) }}?batch_id={{ $batch->id }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Exams in this Batch</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Type</th>
                            <th>Questions</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examStats as $stat)
                        <tr>
                            <td>
                                <a href="{{ route('instructor.exams.show', $stat['exam']) }}">
                                    {{ $stat['exam']->title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $stat['exam']->type))
                                    }}</span>
                            </td>
                            <td>{{ $stat['exam']->questions_count }}</td>
                            <td>{{ $stat['attempts'] }}Needs
                            <td>
                                <span
                                    class="badge bg-{{ $stat['average_score'] >= 70 ? 'success' : ($stat['average_score'] >= 50 ? 'warning' : 'danger') }}">
                                    {{ $stat['average_score'] }}%
                                </span>
                            </td>
                            <td>{{ $stat['average_score'] > 0 ? round(($stat['passed'] / $stat['attempts']) * 100, 1) :
                                0 }}%
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $stat['exam']->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($stat['exam']->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('instructor.exams.results', $stat['exam']) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="bi bi-bar-chart"></i> View Results
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection