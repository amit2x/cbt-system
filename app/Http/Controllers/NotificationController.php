<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications with unread count
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get unread count
        $unreadCount = $user->unreadNotificationCount();
        
        // Get latest 15 notifications
        $notifications = $user->notifications()
            ->limit(15)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'icon' => $notification->data['icon'] ?? 'bi-bell',
                    'type' => $notification->data['type'] ?? 'info',
                    'time' => $notification->created_at->diffForHumans(),
                    'is_read' => !is_null($notification->read_at)
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    /**
     * Get just the unread count (for polling)
     */
    public function unreadCount()
    {
        return response()->json([
            'unread_count' => Auth::user()->unreadNotificationCount()
        ]);
    }
}