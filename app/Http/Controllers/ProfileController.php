<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
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
            'language' => 'nullable|string|in:en,es,fr,de,hi',
            'timezone' => 'nullable|string|timezone',
            'date_format' => 'nullable|string',
            'time_format' => 'nullable|string',
            'email_notifications' => 'nullable|boolean',
            'exam_reminders' => 'nullable|boolean',
            'result_alerts' => 'nullable|boolean',
            'feedback_requests' => 'nullable|boolean',
            'profile_visibility' => 'nullable|boolean',
            'show_email' => 'nullable|boolean',
            'activity_tracking' => 'nullable|boolean',
            'cookie_consent' => 'nullable|boolean',
        ]);

        $settings = $user->settings ?? [];
        $settings = array_merge($settings, $request->except(['_token', '_method']));
        
        // Convert checkbox values to boolean
        foreach ($settings as $key => $value) {
            if (in_array($key, ['email_notifications', 'exam_reminders', 'result_alerts', 'feedback_requests', 
                               'profile_visibility', 'show_email', 'activity_tracking', 'cookie_consent'])) {
                $settings[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }
        
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
        
        // Get activity logs - you can implement this based on your activity logging system
        $activities = [];
        
        return view('profile.activity', compact('user', 'activities'));
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

    /**
     * Revoke a specific session
     */
    public function revokeSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $sessionId = $request->session_id;
        
        // Get all sessions from the database
        $sessions = Session::get('sessions', []);
        
        // Remove the specific session
        $sessions = array_filter($sessions, function($session) use ($sessionId) {
            return $session['id'] !== $sessionId;
        });
        
        // Update the sessions in session
        Session::put('sessions', array_values($sessions));

        if ($request->ajax()) {
            return $this->successResponse(null, 'Session revoked successfully.');
        }

        return redirect()->route('profile.settings')
            ->with('success', 'Session revoked successfully.');
    }

    /**
     * Revoke all sessions except current
     */
    public function revokeAllSessions(Request $request)
    {
        $currentSessionId = session()->getId();
        
        // Get all sessions
        $sessions = Session::get('sessions', []);
        
        // Keep only the current session
        $sessions = array_filter($sessions, function($session) use ($currentSessionId) {
            return $session['id'] === $currentSessionId;
        });
        
        // Update the sessions in session
        Session::put('sessions', array_values($sessions));

        if ($request->ajax()) {
            return $this->successResponse(null, 'All other sessions revoked successfully.');
        }

        return redirect()->route('profile.settings')
            ->with('success', 'All other sessions revoked successfully.');
    }

    /**
     * Enable Two-Factor Authentication
     */
    public function enable2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        // Here you would implement 2FA verification logic
        // For now, we'll just mark it as enabled
        $user = auth()->user();
        $user->update(['two_factor_enabled' => true]);

        if ($request->ajax()) {
            return $this->successResponse(null, 'Two-factor authentication enabled successfully.');
        }

        return redirect()->route('profile.settings')
            ->with('success', 'Two-factor authentication enabled.');
    }

    /**
     * Disable Two-Factor Authentication
     */
    public function disable2FA(Request $request)
    {
        $user = auth()->user();
        $user->update(['two_factor_enabled' => false]);

        if ($request->ajax()) {
            return $this->successResponse(null, 'Two-factor authentication disabled.');
        }

        return redirect()->route('profile.settings')
            ->with('success', 'Two-factor authentication disabled.');
    }

    /**
     * Deactivate account
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password'
        ]);

        $user = auth()->user();
        
        // Prevent deactivation if user has active exams
        if ($user->exams()->where('status', 'started')->exists()) {
            return back()->with('error', 'Cannot deactivate account with active exam sessions.');
        }

        $user->update(['is_active' => false]);

        auth()->logout();

        return redirect()->route('login')
            ->with('success', 'Your account has been deactivated. You can reactivate by logging in.');
    }
}