{{-- resources/views/admin/reports/exams.blade.php --}}

@extends('layouts.admin')

@section('title', 'Exam-wise Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Exam-wise Reports</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Exams</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.exams') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by title..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($examTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Exams List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Exams</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Questions</th>
                            <th>Participants</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            @php
                                $stats = $exam->getStatistics();
                            @endphp
                            <tr>
                                <td>{{ $exam->id }}</td>
                                <td>
                                    <a href="{{ route('admin.reports.exam-detail', $exam) }}">
                                        <strong>{{ $exam->title }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($exam->type) }}</span>
                                </td>
                                <td>{{ $exam->questions_count ?? 0 }}</td>
                                <td>{{ $stats['participants'] ?? 0 }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $stats['average_score'] ?? 0 }}%</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ ($stats['pass_rate'] ?? 0) >= 60 ? 'success' : 'warning' }}">
                                        {{ $stats['pass_rate'] ?? 0 }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $exam->status == 'published' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reports.exam-detail', $exam) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.reports.export-exam', $exam) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No exams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $exams->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection