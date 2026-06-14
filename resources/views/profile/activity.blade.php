@extends('layouts.' . (auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isInstructor() ? 'instructor' : 'student')))

@section('title', 'Activity Log')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Activity Log</h1>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
        </div>
        <div class="card-body">
            <!-- Timeline -->
            <div class="timeline">
                @forelse($activities ?? [] as $activity)
                    <div class="timeline-item">
                        <div class="timeline-badge bg-{{ $activity['color'] ?? 'info' }}">
                            <i class="bi bi-{{ $activity['icon'] ?? 'circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>{{ $activity['title'] }}</h6>
                            <p>{{ $activity['description'] }}</p>
                            <small class="text-muted">{{ $activity['time'] }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history fs-1 text-muted"></i>
                        <p class="mt-2">No activity recorded yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Login History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Login History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Browser</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loginHistory ?? [] as $login)
                            <tr>
                                <td>{{ $login['created_at'] }}</td>
                                <td>{{ $login['ip_address'] }}</td>
                                <td>{{ $login['device'] }}</td>
                                <td>{{ $login['browser'] }}</td>
                                <td>{{ $login['location'] ?? 'Unknown' }}</td>
                                <td>
                                    <span class="badge bg-{{ $login['status'] == 'success' ? 'success' : 'danger' }}">
                                        {{ ucfirst($login['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No login history available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 20px;
    height: 100%;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-badge {
    position: absolute;
    left: 11px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #4e73df;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-content p {
    margin-bottom: 5px;
    color: #5a5c69;
}
</style>
@endsection
