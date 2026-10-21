<?php

// app/Http/Controllers/Admin/PermissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = Permission::withCount('roles')->get();

        // Group by prefix for UI
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0] ?? 'general';
        });

        return view('admin.permissions.index', compact('groupedPermissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'group' => 'required|string|alpha_dash',
            'name' => 'required|string|alpha_dash',
        ]);

        // Format: group.name (e.g., exam.create)
        $fullName = strtolower(trim($request->group)).'.'.strtolower(trim($request->name));

        // Check if exists
        if (Permission::where('name', $fullName)->exists()) {
            return back()->withErrors(['name' => 'This permission already exists.'])->withInput();
        }

        Permission::create(['name' => $fullName, 'guard_name' => 'web']);

        // Clear cache so it shows up immediately
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|alpha_dash|unique:permissions,name,'.$permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
