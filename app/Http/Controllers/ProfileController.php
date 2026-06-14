<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponse;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user's profile
     */
    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only(['name', 'email', 'phone']));

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        $user = auth()->user();
        return view('profile.settings', compact('user'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'notifications' => 'boolean',
            'language' => 'string|in:en,es,fr,de',
            'timezone' => 'string|timezone',
        ]);

        $settings = $user->settings ?? [];
        $settings = array_merge($settings, $request->only(['notifications', 'language', 'timezone']));

        $user->update(['settings' => $settings]);

        return redirect()->route('profile.settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048'
        ]);

        $user = auth()->user();

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        if ($request->ajax()) {
            return $this->successResponse([
                'avatar_url' => asset('storage/' . $path)
            ], 'Avatar uploaded successfully');
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Avatar updated successfully.');
    }

    /**
     * Show activity log
     */
    public function activity()
    {
        $user = auth()->user();

        // You can implement activity logging here
        // For now, return a simple view
        return view('profile.activity', compact('user'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('profile.settings')
            ->with('success', 'Password updated successfully.');
    }
}
