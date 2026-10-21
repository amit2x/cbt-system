{{-- resources/views/admin/roles/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Roles Management</h1>
            <div>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Role
                </a>

                <a href="{{ route('admin.permissions.index') }}" class="btn btn-warning">
                    <i class="bi bi-solid-shield"></i> Manage Permission
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Role Name</th>
                                <th>Users Count</th>
                                <th>Permissions Assigned</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $role->name }}</span>
                                    </td>
                                    <td>{{ $role->users_count }}</td>
                                    <td>
                                        @if ($role->permissions->count() > 0)
                                            @foreach ($role->permissions->take(5) as $perm)
                                                <span class="badge bg-secondary">{{ $perm->name }}</span>
                                            @endforeach
                                            @if ($role->permissions->count() > 5)
                                                <span class="badge bg-info">+{{ $role->permissions->count() - 5 }}
                                                    more</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No permissions assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        @if (!in_array($role->name, ['superadmin']))
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this role?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No roles found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
