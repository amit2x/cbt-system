<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\ApiResponse;

class SettingsController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('order')->get()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array'
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        // Clear cache
        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048'
        ]);

        $path = $request->file('logo')->store('settings', 'public');

        Setting::set('app_logo', $path, 'image', 'general');

        Cache::forget('settings');

        return $this->successResponse([
            'url' => asset('storage/' . $path)
        ], 'Logo uploaded successfully');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|max:1024'
        ]);

        $path = $request->file('favicon')->store('settings', 'public');

        Setting::set('app_favicon', $path, 'image', 'general');

        Cache::forget('settings');

        return $this->successResponse([
            'url' => asset('storage/' . $path)
        ], 'Favicon uploaded successfully');
    }

    /**
     * Reset to default settings
     */
    public function reset()
    {
        // Run settings seeder
        \Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);

        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings reset to default values');
    }

    /**
     * Get setting value via AJAX
     */
    public function get($key)
    {
        $value = Setting::get($key);

        return $this->successResponse([$key => $value]);
    }
}
