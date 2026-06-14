@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">User Details: {{ $user->name }}</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         class="rounded-circle mb-3" width="120" height="120">
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    <p class="text-muted">{{ $user->phone ?? 'No phone' }}</p>

                    <div class="mb-3">
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </div>

                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>

                    <hr>

                    <div class="text-start">
                        <p><strong>Member Since:</strong><br>{{ $user->created_at->format('d M Y') }}</p>
                        <p><strong>Last Updated:</strong><br>{{ $user->updated_at->format('d M Y') }}</p>
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
                            <h5 class="text-warning">{{ $stats['batches_count'] }}</h5>
                            <small class="text-muted">Batches</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Batches Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Enrolled Batches</h6>
                </div>
                <div class="card-body">
                    @if($user->batches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Batch Name</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Enrolled Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->batches as $batch)
                                        <tr>
                                            <td>{{ $batch->name }}</td>
                                            <td>{{ $batch->course->title }}</td>
                                            <td>
                                                <span class="badge bg-{{ $batch->pivot->status == 'active' ? 'success' : 'secondary' }}">
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

            <!-- Recent Activity Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Exam Activity</h6>
                </div>
                <div class="card-body">
                    @if($recentExams->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Exam</th>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentExams as $studentExam)
                                        <tr>
                                            <td>{{ $studentExam->exam->title }}</td>
                                            <td>{{ $studentExam->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                @if($studentExam->result)
                                                    {{ $studentExam->result->percentage }}%
                                                @else
                                                    In Progress
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $studentExam->status == 'completed' ? 'success' :
                                                    ($studentExam->status == 'started' ? 'warning' : 'secondary')
                                                }}">
                                                    {{ ucfirst($studentExam->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No exam activity yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
