<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Batch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->withCount('batches');

        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15);

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show form for creating new user
     */
    public function create()
    {
        $roles = Role::all();
        $batches = Batch::where('status', 'ongoing')->get();

        return view('admin.users.create', compact('roles', 'batches'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
            'avatar' => 'nullable|image|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user = User::create($data);

        // Assign role
        $user->assignRole($request->role);

        // Assign batches for students
        if ($request->role === 'student' && $request->has('batch_ids')) {
            $user->batches()->syncWithPivotValues(
                $request->batch_ids,
                ['enrolled_at' => now(), 'status' => 'active']
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'batches.course']);

        // Get user statistics
        $stats = [
            'total_exams' => $user->exams()->count(),
            'completed_exams' => $user->exams()->where('status', 'completed')->count(),
            'average_score' => round($user->results()->avg('percentage') ?? 0, 2),
            'batches_count' => $user->batches()->count()
        ];

        // Get recent activity
        $recentExams = $user->exams()
            ->with('exam')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'stats', 'recentExams'));
    }

    /**
     * Show form for editing user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $batches = Batch::where('status', 'ongoing')->get();
        $userBatches = $user->batches()->pluck('batches.id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'batches', 'userBatches'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
            'avatar' => 'nullable|image|max:2048'
        ]);
    
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];
    
        // Handle password update if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
    
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }
    
        $user->update($data);
    
        // Sync role
        $user->syncRoles([$request->role]);
    
        // Sync batches for students
        if ($request->role === 'student') {
            $batchData = [];
            if ($request->has('batch_ids')) {
                foreach ($request->batch_ids as $batchId) {
                    $batchData[$batchId] = ['enrolled_at' => now(), 'status' => 'active'];
                }
            }
            $user->batches()->sync($batchData);
        } else {
            $user->batches()->detach();
        }
    
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has exam attempts
        if ($user->exams()->count() > 0) {
            return back()->with('error', 'Cannot delete user with exam attempts.');
        }

        // Delete avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->syncRoles([$request->role]);

        if ($request->ajax()) {
            return $this->successResponse(null, 'Role assigned successfully');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Role assigned successfully.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return $this->errorResponse('You cannot deactivate your own account.', 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        if ($request->ajax()) {
            return $this->successResponse([
                'is_active' => $user->is_active
            ], "User {$status} successfully");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        // Remove current user from array
        $userIds = array_filter($request->user_ids, function($id) {
            return $id != auth()->id();
        });

        // Check if users have exam attempts
        $usersWithExams = User::whereIn('id', $userIds)
            ->whereHas('exams')
            ->count();

        if ($usersWithExams > 0) {
            return $this->errorResponse('Some users have exam attempts and cannot be deleted.', 422);
        }

        // Delete avatars
        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        User::whereIn('id', $userIds)->delete();

        return $this->successResponse(null, 'Selected users deleted successfully.');
    }

    /**
     * Export users list
     */
    public function export(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->get();

        // Generate CSV
        $filename = 'users-' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Created At']);

        // Add data
        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                $user->roles->pluck('name')->implode(', '),
                $user->is_active ? 'Active' : 'Inactive',
                $user->created_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Get user activity log
     */
    public function activity(User $user)
    {
        // You can implement activity logging here
        // For now, return a simple view
        return view('admin.users.activity', compact('user'));
    }

    /**
     * Impersonate user
     */
    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot impersonate yourself.');
        }

        session()->put('impersonate', $user->id);
        session()->put('impersonate_by', auth()->id());

        return redirect()->route('dashboard');
    }

    /**
     * Leave impersonation
     */
    public function leaveImpersonate()
    {
        if (!session()->has('impersonate')) {
            return redirect()->route('admin.users.index');
        }

        session()->forget('impersonate');
        session()->forget('impersonate_by');

        return redirect()->route('admin.users.index')
            ->with('success', 'Returned to your account.');
    }
    
    
    
    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        $user->update([
            'password' => Hash::make($request->password)
        ]);
    
        if ($request->ajax()) {
            return $this->successResponse(null, 'Password reset successfully.');
        }
    
        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Password reset successfully.');
    }
    
    /**
     * Show password reset form
     */
    public function showResetPasswordForm(User $user)
    {
        return view('admin.users.reset-password', compact('user'));
    }
}
