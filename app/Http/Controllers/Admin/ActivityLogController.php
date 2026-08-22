<?php
// app/Http/Controllers/Admin/ActivityLogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display activity logs.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        if ($request->filled('search')) {
            $query->where('description', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.activity-logs.index', compact('logs'));
    }

    /**
     * Clear all activity logs.
     */
    public function clear(Request $request)
    {
        try {
            Activity::truncate();

            return response()->json([
                'status' => 'success',
                'message' => 'Activity logs cleared successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error clearing logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export activity logs.
     */
    public function export(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'activity-logs-' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, ['ID', 'Log Name', 'Event', 'Description', 'Causer', 'Subject', 'Properties', 'Date']);

        // Add data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->log_name ?? 'default',
                $log->event ?? 'unknown',
                $log->description ?? 'N/A',
                $log->causer?->name ?? 'System',
                $log->subject ? class_basename($log->subject_type) . ' #' . $log->subject_id : 'N/A',
                json_encode($log->properties->toArray()),
                $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A'
            ]);
        }

        fclose($handle);
        exit;
    }
}