<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller for handling notification-related requests.
 * Supports infinite scroll pagination and role-based message formatting.
 */
class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the notifications page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $category = $request->get('category');
        
        // Initial load of notifications
        $result = NotificationService::getNotifications(
            $user,
            null,
            20,
            $category === 'all' ? null : $category,
            false
        );

        $notifications = collect($result['notifications'])->map(function ($notification) use ($user) {
            return NotificationService::formatForResponse($notification, $user);
        });

        $unreadCount = NotificationService::getUnreadCount($user);
        
        // Get notification categories for filter
        $categories = [
            'all' => 'All Notifications',
            'academic' => 'Academic',
            'security' => 'Security',
            'announcement' => 'Announcements',
            'system' => 'System',
        ];

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'hasMore' => $result['has_more'],
            'nextCursor' => $result['next_cursor'],
            'currentCategory' => $category ?? 'all',
            'categories' => $categories,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    /**
     * Get paginated notifications for infinite scroll (AJAX).
     */
    public function paginate(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cursor = $request->get('cursor');
        $category = $request->get('category');
        $limit = min($request->get('limit', 20), 50); // Max 50 per request

        $result = NotificationService::getNotifications(
            $user,
            $cursor,
            $limit,
            $category === 'all' ? null : $category,
            false
        );

        $notifications = collect($result['notifications'])->map(function ($notification) use ($user) {
            return NotificationService::formatForResponse($notification, $user);
        });

        return response()->json([
            'notifications' => $notifications,
            'has_more' => $result['has_more'],
            'next_cursor' => $result['next_cursor'],
        ]);
    }

    /**
     * Get unviewed notifications count (for badge display).
     * Uses viewed_at to track if user has seen the notification in dropdown.
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = NotificationService::getUnviewedCount(Auth::user());
        return response()->json(['count' => $count]);
    }

    /**
     * Get unread notifications for the dropdown.
     */
    public function getUnread(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = min($request->get('limit', 10), 20);

        $result = NotificationService::getNotifications(
            $user,
            null,
            $limit,
            null,
            true // Unread only
        );

        $notifications = collect($result['notifications'])->map(function ($notification) use ($user) {
            return NotificationService::formatForResponse($notification, $user);
        });

        return response()->json([
            'notifications' => $notifications,
            'count' => NotificationService::getUnviewedCount($user),
            'has_more' => $result['has_more'],
        ]);
    }

    /**
     * Poll for new notifications since a given timestamp.
     * Used for live updates without WebSockets.
     */
    public function poll(Request $request): JsonResponse
    {
        $user = Auth::user();
        $since = $request->get('since');
        
        $query = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10);
        
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        
        $notifications = $query->get();
        
        $formatted = $notifications->map(function ($notification) use ($user) {
            return NotificationService::formatForResponse($notification, $user);
        });
        
        return response()->json([
            'notifications' => $formatted,
            'count' => NotificationService::getUnviewedCount($user),
            'latest_timestamp' => $notifications->first()?->created_at?->toIso8601String(),
        ]);
    }

    /**
     * Mark all notifications as viewed (when user opens dropdown).
     * This clears the badge but doesn't mark as "read".
     */
    public function markAsViewed(): JsonResponse
    {
        $count = NotificationService::markAllAsViewed(Auth::user());

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unviewed_count' => 0,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $success = NotificationService::markAsRead(Auth::user(), $id);

        return response()->json([
            'success' => $success,
            'unread_count' => NotificationService::getUnreadCount(Auth::user()),
            'unviewed_count' => NotificationService::getUnviewedCount(Auth::user()),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = NotificationService::markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }

    /**
     * Get notification preferences for the current user.
     */
    public function getPreferences(): JsonResponse
    {
        $user = Auth::user();
        $prefs = $user->notificationPreferences;

        if (!$prefs) {
            $prefs = \App\Models\NotificationPreference::getDefaults();
        }

        return response()->json(['preferences' => $prefs]);
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'in_app_enabled' => 'sometimes|boolean',
            'email_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
            'enabled_types' => 'sometimes|array',
            'quiet_start' => 'sometimes|nullable|date_format:H:i',
            'quiet_end' => 'sometimes|nullable|date_format:H:i',
        ]);

        $user = Auth::user();
        $prefs = $user->notificationPreferences;

        if (!$prefs) {
            $prefs = new \App\Models\NotificationPreference();
            $prefs->user_id = $user->id;
        }

        $prefs->fill($request->only([
            'in_app_enabled',
            'email_enabled',
            'push_enabled',
            'enabled_types',
            'quiet_start',
            'quiet_end',
        ]));
        
        $prefs->save();

        return response()->json([
            'success' => true,
            'preferences' => $prefs,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::getUnreadCount($user),
        ]);
    }
}
