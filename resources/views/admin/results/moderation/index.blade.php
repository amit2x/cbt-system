{{-- resources/views/admin/results/moderation/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Result Moderation - ' . $exam->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Result Moderation</h1>
            <p class="text-muted small mb-0">Exam: <strong>{{ $exam->title }}</strong></p>
        </div>
        <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Exam
        </a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['total'] }}</h3>
                    <p class="mb-0">Total Submissions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['draft'] }}</h3>
                    <p class="mb-0">Pending (Draft)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $stats['published'] }}</h3>
                    <p class="mb-0">Published</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status')=='published' ? 'selected' : '' }}>Published
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-success" onclick="bulkPublish()">
                        <i class="bi bi-check-circle"></i> Publish Selected
                    </button>
                    <button type="button" class="btn btn-warning" onclick="bulkUnpublish()">
                        <i class="bi bi-x-circle"></i> Unpublish Selected
                    </button>
                    <button type="button" class="btn btn-info" onclick="recalcRanks()">
                        <i class="bi bi-arrow-clockwise"></i> Recalculate Ranks
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAll"></th>
                            <th>Student</th>
                            <th>Submitted</th>
                            <th>Score</th>
                            <th>Rank</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        <tr>
                            <td><input type="checkbox" class="result-checkbox" value="{{ $result->id }}"></td>
                            <td>
                                <strong>{{ $result->student->name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $result->student->email ?? '' }}</small>
                            </td>
                            <td>{{ $result->studentExam->submitted_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                            <td>{{ $result->obtained_marks }}/{{ $result->total_marks }}</td>
                            <td>{{ $result->rank ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $result->percentage >= 60 ? 'success' : 'danger' }}">
                                    {{ round($result->percentage, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $result->status == 'passed' ? 'success' : 'danger' }}">
                                    {{ ucfirst($result->status) }}
                                </span>
                            </td>
                            <td>
                                @if($result->is_published)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Published
                                </span>
                                <br><small class="text-muted">{{ $result->published_at?->format('d M Y, h:i A')
                                    }}</small>
                                @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-hourglass-split"></i> Draft
                                </span>
                                @endif
                            </td>
                            <td>
                                @if(!$result->is_published)
                                <a href="{{ route('admin.results.show', $result) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Review
                                </a>
                                @else
                                <a href="{{ route('admin.results.show', $result) }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan=" 8" class="text-center py-4">No results found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $results->withQueryString()->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.result-checkbox').forEach(cb => cb.checked = this.checked);
    });

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.result-checkbox:checked')).map(cb => cb.value);
    }

    async function bulkPublish() {
        const ids = getSelectedIds();
        if (ids.length === 0) return toastr.warning('Select at least one result.');

        const notes = prompt('Optional notes (visible to admin only):') || '';
        try {
            const res = await fetch('{{ route("admin.results.moderation.bulk-publish", $exam) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ result_ids: ids, notes })
            });
            const data = await res.json();
            if (data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message);
            }
        } catch (e) {
            toastr.error('Error publishing results');
        }
    }

    async function bulkUnpublish() {
        const ids = getSelectedIds();
        if (ids.length === 0) return toastr.warning('Select at least one result.');

        if (!confirm('Unpublish selected results? Students will no longer see them.')) return;

        try {
            const res = await fetch('{{ route("admin.results.moderation.bulk-unpublish", $exam) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ result_ids: ids })
            });
            const data = await res.json();
            if (data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message);
            }
        } catch (e) {
            toastr.error('Error unpublishing results');
        }
    }

    async function recalcRanks() {
        const res = await fetch('{{ route("admin.results.moderation.recalculate-ranks", $exam) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        });
        const data = await res.json();
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(data.message);
        }
    }
</script>
@endpush
@endsection