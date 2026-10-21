{{-- resources/views/admin/permissions/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Permissions Management')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Permissions</h1>
            <div>
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Permission
                </a>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-success">
                    <i class="bi bi-person"></i> Back to Roles
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            @foreach ($groupedPermissions as $group => $permissions)
                <div class="col-md-4 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 text-capitalize">{{ $group }} Module</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach ($permissions as $permission)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $permission->name }}</strong>
                                            <br>
                                            <small class="text-muted">Used in {{ $permission->roles_count }} roles</small>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.permissions.edit', $permission) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.permissions.destroy', $permission) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete permission?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
