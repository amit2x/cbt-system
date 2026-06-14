@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New User
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Batches</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" class="user-checkbox" value="{{ $user->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}"
                                         alt="{{ $user->name }}"
                                         class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $user->batches_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button type="button" class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}"
                                                onclick="toggleStatus({{ $user->id }})"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="deleteUser({{ $user->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                No users found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" onclick="bulkDelete()">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportUsers()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Status Toggle Form -->
<form id="status-form" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
    });

    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user?')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/users/${id}`;
            form.submit();
        }
    }

    function toggleStatus(id) {
        if (confirm('Are you sure you want to change this user\'s status?')) {
            $.ajax({
                url: `/admin/users/${id}/toggle-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error updating status');
                }
            });
        }
    }

    function bulkDelete() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one user');
            return;
        }

        if (confirm('Are you sure you want to delete selected users?')) {
            $.ajax({
                url: '{{ route("admin.users.bulk-delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_ids: selectedIds
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error deleting users');
                }
            });
        }
    }

    function exportUsers() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '{{ route("admin.users.export") }}?' + params.toString();
    }

    function getSelectedIds() {
        const ids = [];
        $('.user-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }
</script>
@endpush
