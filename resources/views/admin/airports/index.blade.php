{{-- resources/views/admin/airports/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Airport Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Airport Management</h1>
        <div>
            <a href="{{ route('admin.airports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Airport
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Airports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plane fa-2x text-gray-300"></i>
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
                                Active Airports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Total Instructors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_instructors'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.airports.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name, code, city..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.airports.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-undo"></i> Reset
                    </a>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('admin.airports.create') }}" class="btn btn-success w-100">
                        <i class="bi bi-plus"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Airports Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Airports List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Created</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($airports as $airport)
                        <tr>
                            <td>
                                <input type="checkbox" class="airport-checkbox" value="{{ $airport->id }}">
                            </td>
                            <td>{{ $airport->id }}</td>
                            <td>
                                <a href="{{ route('admin.airports.show', $airport) }}">
                                    <strong>{{ $airport->name }}</strong>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $airport->code }}</span>
                                @if($airport->icao_code)
                                <small class="text-muted">({{ $airport->icao_code }})</small>
                                @endif
                            </td>
                            <td>{{ $airport->city ?? '-' }}</td>
                            <td>{{ $airport->country ?? '-' }}</td>
                            <td>{!! $airport->status_badge !!}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $airport->users()->whereHas('roles', function($q) { 
                                        $q->where('name', 'student'); 
                                    })->count() }}
                                </span>
                            </td>
                            <td>{{ $airport->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.airports.show', $airport) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.airports.edit', $airport) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-{{ $airport->status === 'active' ? 'secondary' : 'success' }}" onclick="toggleStatus({{ $airport->id }})" title="{{ $airport->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $airport->status === 'active' ? 'pause-fill' : 'play-fill' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteAirport({{ $airport->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No airports found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $airports->firstItem() ?? 0 }} to {{ $airports->lastItem() ?? 0 }} 
                    of {{ $airports->total() }} entries
                </div>
                <div>
                    {{ $airports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(id) {
    if (confirm('Are you sure you want to toggle the status of this airport?')) {
        // Create form data
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'POST');
        
        // Use fetch API with proper URL construction
        var url = '{{ route("admin.airports.toggle-status", ":id") }}'.replace(':id', id);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to toggle status');
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error.message);
        });
    }
}

function deleteAirport(id) {
    if (confirm('Are you sure you want to delete this airport? This action cannot be undone.')) {
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');
        
        var url = '{{ route("admin.airports.destroy", ":id") }}'.replace(':id', id);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete airport');
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error.message);
        });
    }
}

// Select all checkboxes
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.airport-checkbox').forEach(function(checkbox) {
        checkbox.checked = this.checked;
    }, this);
});
</script>
@endpush
@endsection