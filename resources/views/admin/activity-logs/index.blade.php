{{-- resources/views/admin/activity-logs/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Activity Logs</h1>
        <div>
            <button type="button" class="btn btn-danger" onclick="clearLogs()">
                <i class="bi bi-trash"></i> Clear Logs
            </button>
            <a href="{{ route('admin.activity-logs.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search description..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="log_name" class="form-control">
                        <option value="">All Logs</option>
                        <option value="default" {{ request('log_name') == 'default' ? 'selected' : '' }}>Default</option>
                        <option value="auth" {{ request('log_name') == 'auth' ? 'selected' : '' }}>Auth</option>
                        <option value="exam" {{ request('log_name') == 'exam' ? 'selected' : '' }}>Exam</option>
                        <option value="security" {{ request('log_name') == 'security' ? 'selected' : '' }}>Security</option>
                        <option value="request" {{ request('log_name') == 'request' ? 'selected' : '' }}>Request</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="event" class="form-control">
                        <option value="">All Events</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>Restored</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Log</th>
                            <th>Event</th>
                            <th>Description</th>
                            <th>Subject</th>
                            <th>Causer</th>
                            <th>Properties</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                @php
                                    $logColors = [
                                        'auth' => 'info',
                                        'security' => 'danger',
                                        'exam' => 'warning',
                                        'request' => 'primary',
                                        'default' => 'secondary'
                                    ];
                                    $logName = $log->log_name ?? 'default';
                                    $logColor = $logColors[$logName] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $logColor }}">
                                    {{ ucfirst($logName) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $eventColors = [
                                        'created' => 'success',
                                        'updated' => 'primary',
                                        'deleted' => 'danger',
                                        'restored' => 'info'
                                    ];
                                    $eventName = $log->event ?? 'unknown';
                                    $eventColor = $eventColors[$eventName] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $eventColor }}">
                                    {{ ucfirst($eventName) }}
                                </span>
                            </td>
                            <td>{{ $log->description ?? 'N/A' }}</td>
                            <td>
                                @if($log->subject)
                                    <span class="badge bg-secondary">
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    </span>
                                    @if(isset($log->subject->name))
                                        <br><small>{{ $log->subject->name }}</small>
                                    @elseif(isset($log->subject->title))
                                        <br><small>{{ $log->subject->title }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($log->causer)
                                    {{ $log->causer->name ?? 'Unknown' }}
                                    <br>
                                    <small class="text-muted">ID: {{ $log->causer_id }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $properties = $log->properties ? $log->properties->toArray() : [];
                                    unset($properties['ip'], $properties['user_agent'], $properties['url'], $properties['method']);
                                @endphp
                                @if(!empty($properties))
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="showProperties({{ json_encode($properties) }})">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>
                                @if($log->created_at)
                                    <small>{{ $log->created_at->format('d M Y H:i:s') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No activity logs found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class=" mt-4">
                {{ $logs->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Properties Modal -->
<div class="modal fade" id="propertiesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Properties</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="propertiesContent" style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showProperties(properties) {
        document.getElementById('propertiesContent').textContent = JSON.stringify(properties, null, 2);
        new bootstrap.Modal(document.getElementById('propertiesModal')).show();
    }

    function clearLogs() {
        if (confirm('Are you sure you want to clear all activity logs? This action cannot be undone.')) {
            $.ajax({
                url: '{{ route("admin.activity-logs.clear") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message || 'Error clearing logs');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Error clearing logs';
                    toastr.error(errorMsg);
                }
            });
        }
    }
</script>
@endpush
@endsection