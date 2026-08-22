<?php
// app/Http/Controllers/Admin/AirportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AirportController extends Controller
{
    /**
     * Display a listing of airports.
     */
    public function index(Request $request)
    {
        $query = Airport::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $airports = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Airport::count(),
            'active' => Airport::where('status', 'active')->count(),
            'inactive' => Airport::where('status', 'inactive')->count(),
            'suspended' => Airport::where('status', 'suspended')->count(),
            'total_students' => User::whereHas('roles', function($q) {
                $q->where('name', 'student');
            })->count(),
            'total_instructors' => User::whereHas('roles', function($q) {
                $q->where('name', 'instructor');
            })->count(),
        ];

        return view('admin.airports.index', compact('airports', 'stats'));
    }

    /**
     * Show the form for creating a new airport.
     */
    public function create()
    {
        $timezones = $this->getTimezones();
        $countries = $this->getCountries();
        $subscriptionPlans = ['basic', 'standard', 'premium', 'enterprise'];
        
        return view('admin.airports.create', compact('timezones', 'countries', 'subscriptionPlans'));
    }

    /**
     * Store a newly created airport in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:airports,code',
            'icao_code' => 'nullable|string|max:4',
            'slug' => 'required|string|max:255|unique:airports,slug',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive,suspended,maintenance',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'subscription_expires_at' => 'nullable|date|after:now',
            'max_students' => 'required|integer|min:0',
            'max_instructors' => 'required|integer|min:0',
            'max_exams' => 'required|integer|min:0',
            'storage_limit_gb' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $airport = Airport::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'icao_code' => strtoupper($request->icao_code),
                'slug' => Str::slug($request->slug),
                'domain' => $request->domain,
                'city' => $request->city,
                'country' => $request->country,
                'timezone' => $request->timezone,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'status' => $request->status,
                'subscription_plan' => $request->subscription_plan,
                'subscription_expires_at' => $request->subscription_expires_at,
                'max_students' => $request->max_students,
                'max_instructors' => $request->max_instructors,
                'max_exams' => $request->max_exams,
                'storage_limit_gb' => $request->storage_limit_gb,
                'settings' => $request->settings ?? [],
                'features' => $request->features ?? [],
                'created_by' => auth()->id()
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $this->uploadLogo($airport, $request->file('logo'));
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                $this->uploadFavicon($airport, $request->file('favicon'));
            }

            // Clear cache
            Cache::forget('active_airports');
            Cache::forget('default_airport_id');

            return redirect()->route('admin.airports.index')
                ->with('success', 'Airport created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create airport: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified airport.
     */
    public function show(Airport $airport)
    {
        // Get statistics for this airport
        $stats = [
            'total_users' => $airport->users()->count(),
            'students' => $airport->users()->whereHas('roles', function($q) {
                $q->where('name', 'student');
            })->count(),
            'instructors' => $airport->users()->whereHas('roles', function($q) {
                $q->where('name', 'instructor');
            })->count(),
            'courses' => $airport->courses()->count(),
            'batches' => $airport->batches()->count(),
            'exams' => $airport->exams()->count(),
            'questions' => $airport->questions()->count(),
            'modules' => $airport->modules()->count(),
            'active_exams' => $airport->exams()->where('status', 'published')->count(),
            'completed_exams' => $airport->exams()->where('status', 'completed')->count(),
            'usage_percentage' => $airport->getUsagePercentage(),
        ];

        // Recent activity
        $recentUsers = $airport->users()->latest()->limit(5)->get();
        $recentExams = $airport->exams()->latest()->limit(5)->get();

        return view('admin.airports.show', compact('airport', 'stats', 'recentUsers', 'recentExams'));
    }

    /**
     * Show the form for editing the specified airport.
     */
    public function edit(Airport $airport)
    {
        $timezones = $this->getTimezones();
        $countries = $this->getCountries();
        $subscriptionPlans = ['basic', 'standard', 'premium', 'enterprise'];
        $statuses = ['active', 'inactive', 'suspended', 'maintenance'];

        return view('admin.airports.edit', compact('airport', 'timezones', 'countries', 'subscriptionPlans', 'statuses'));
    }

    /**
     * Update the specified airport in storage.
     */
    public function update(Request $request, Airport $airport)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:airports,code,' . $airport->id,
            'icao_code' => 'nullable|string|max:4',
            'slug' => 'required|string|max:255|unique:airports,slug,' . $airport->id,
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive,suspended,maintenance',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'subscription_expires_at' => 'nullable|date',
            'max_students' => 'required|integer|min:0',
            'max_instructors' => 'required|integer|min:0',
            'max_exams' => 'required|integer|min:0',
            'storage_limit_gb' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $airport->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'icao_code' => strtoupper($request->icao_code),
                'slug' => Str::slug($request->slug),
                'domain' => $request->domain,
                'city' => $request->city,
                'country' => $request->country,
                'timezone' => $request->timezone,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'status' => $request->status,
                'subscription_plan' => $request->subscription_plan,
                'subscription_expires_at' => $request->subscription_expires_at,
                'max_students' => $request->max_students,
                'max_instructors' => $request->max_instructors,
                'max_exams' => $request->max_exams,
                'storage_limit_gb' => $request->storage_limit_gb,
                'settings' => $request->settings ?? [],
                'features' => $request->features ?? [],
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $this->deleteLogo($airport);
                $this->uploadLogo($airport, $request->file('logo'));
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                $this->deleteFavicon($airport);
                $this->uploadFavicon($airport, $request->file('favicon'));
            }

            // Clear cache
            Cache::forget('active_airports');
            Cache::forget('airport_' . $airport->id);

            return redirect()->route('admin.airports.index')
                ->with('success', 'Airport updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update airport: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified airport from storage.
     */
    public function destroy(Airport $airport)
    {
        try {
            // Check if airport has users
            if ($airport->users()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete airport with associated users. Please reassign or delete users first.');
            }

            // Check if airport has courses
            if ($airport->courses()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete airport with associated courses. Please reassign or delete courses first.');
            }

            // Delete logo and favicon
            $this->deleteLogo($airport);
            $this->deleteFavicon($airport);

            // Soft delete the airport
            $airport->delete();

            // Clear cache
            Cache::forget('active_airports');
            Cache::forget('default_airport_id');
            Cache::forget('airport_' . $airport->id);

            return redirect()->route('admin.airports.index')
                ->with('success', 'Airport deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete airport: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted airport.
     */
    public function restore($id)
    {
        try {
            $airport = Airport::withTrashed()->findOrFail($id);
            $airport->restore();

            Cache::forget('active_airports');
            Cache::forget('airport_' . $airport->id);

            return redirect()->route('admin.airports.index')
                ->with('success', 'Airport restored successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to restore airport: ' . $e->getMessage());
        }
    }

    /**
     * Toggle airport status.
     */
    public function toggleStatus(Airport $airport)
    {
        try {
            $newStatus = $airport->status === 'active' ? 'inactive' : 'active';
            $airport->update(['status' => $newStatus]);

            Cache::forget('active_airports');
            Cache::forget('airport_' . $airport->id);

            return redirect()->back()
                ->with('success', 'Airport status updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update airport status: ' . $e->getMessage());
        }
    }

    /**
     * Upload airport logo.
     */
    protected function uploadLogo(Airport $airport, $file)
    {
        $path = $file->store('airports/' . $airport->id . '/logo', 'public');
        $airport->update(['logo' => $path]);
    }

    /**
     * Delete airport logo.
     */
    protected function deleteLogo(Airport $airport)
    {
        if ($airport->logo && Storage::disk('public')->exists($airport->logo)) {
            Storage::disk('public')->delete($airport->logo);
            // Delete directory if empty
            $directory = dirname($airport->logo);
            if (Storage::disk('public')->exists($directory)) {
                $files = Storage::disk('public')->files($directory);
                if (empty($files)) {
                    Storage::disk('public')->deleteDirectory($directory);
                }
            }
        }
    }

    /**
     * Upload airport favicon.
     */
    protected function uploadFavicon(Airport $airport, $file)
    {
        $path = $file->store('airports/' . $airport->id . '/favicon', 'public');
        $airport->update(['favicon' => $path]);
    }

    /**
     * Delete airport favicon.
     */
    protected function deleteFavicon(Airport $airport)
    {
        if ($airport->favicon && Storage::disk('public')->exists($airport->favicon)) {
            Storage::disk('public')->delete($airport->favicon);
        }
    }

    /**
     * Get list of timezones.
     */
    protected function getTimezones()
    {
        return [
            'UTC' => 'UTC',
            'Asia/Kolkata' => 'Asia/Kolkata (UTC+5:30)',
            'Asia/Dubai' => 'Asia/Dubai (UTC+4:00)',
            'Asia/Singapore' => 'Asia/Singapore (UTC+8:00)',
            'Asia/Tokyo' => 'Asia/Tokyo (UTC+9:00)',
            'America/New_York' => 'America/New_York (UTC-5:00)',
            'America/Los_Angeles' => 'America/Los_Angeles (UTC-8:00)',
            'Europe/London' => 'Europe/London (UTC+0:00)',
            'Europe/Paris' => 'Europe/Paris (UTC+1:00)',
            'Australia/Sydney' => 'Australia/Sydney (UTC+10:00)',
        ];
    }

    /**
     * Get list of countries.
     */
    protected function getCountries()
    {
        return [
            'India' => 'India',
            'United States' => 'United States',
            'United Kingdom' => 'United Kingdom',
            'Australia' => 'Australia',
            'Canada' => 'Canada',
            'Germany' => 'Germany',
            'France' => 'France',
            'Japan' => 'Japan',
            'Singapore' => 'Singapore',
            'UAE' => 'UAE',
        ];
    }
}