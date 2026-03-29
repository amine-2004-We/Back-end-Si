<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 *
 */
class NotificationController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        // Log::info('auth user',[$user]);
        if (!$user->collaborator) {
            return response()->json([
                'message' => 'user is not a collaborator',
                'notifications' => [],
                'total_notifications' => 0
            ]);
        }

        $collaboratorId = $user->collaborator->id;
        // Log::info('collaborator',$user->collaborator->toArray());
        $query = NotificationRecipient::with([
            'detail',
            'detail.sender.user'
        ])->where('receiver_id', $collaboratorId);

        if ($request->has('is_seen')) {
            if ($request->query('is_seen') === 'false') {
                $query->where('is_seen', false);
            }
        }

        $totalNotifications = $query->count();

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 4);

        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        $formattedNotifications = $notifications->getCollection()->map(function ($recipient) {
            if (!$recipient->detail) {
                return null;
            }
            $sender = $recipient->detail->sender;
            $senderName = $sender ? ($sender->first_name . ' ' . $sender->last_name) : 'System';

            return [
                'id' => $recipient->id,
                'title' => $recipient->detail->title,
                'text' => $recipient->detail->text,
                'action' => json_decode($recipient->detail->target_url_data, true),
                'sender' => [
                    'id' => $sender ? $sender->id : null,
                    'name' => $senderName,
                ],
                'is_seen' => (bool) $recipient->is_seen,
                'created_at' => $recipient->created_at->toISOString(),
                'formatted_date' => $recipient->created_at->locale('fr_FR')->diffForHumans(),
            ];
        })->filter()->values();

        return response()->json([
            'notifications' => $formattedNotifications,
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $notifications->lastPage(),
            ],
            'total_notifications' => $totalNotifications,
        ]);
    }

    /**
     * @param Request $request
     * @param NotificationRecipient $notificationRecipient
     * @return JsonResponse
     */
    public function markAsRead(Request $request, NotificationRecipient $notificationRecipient): JsonResponse
    {
        $user = $request->user();
        if (!$user->collaborator || $notificationRecipient->receiver_id !== $user->collaborator->id) {
            return response()->json([
                'message' => 'Unauthorized to modify this notification'
            ], 403);
        }

        $notificationRecipient->is_seen = true;
        $notificationRecipient->save();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => [
                'id' => $notificationRecipient->id,
                'is_seen' => $notificationRecipient->is_seen
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->collaborator) {
                return response()->json([
                    'message' => 'User is not a collaborator'
                ], 403);
            }

            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer',
            ]);

            $updatedCount = NotificationRecipient::whereIn('id', $validated['ids'])
                ->where('receiver_id', $user->collaborator->id)
                ->update(['is_seen' => true]);

            return response()->json([
                'message' => 'Notifications marked as read',
                'updated_count' => $updatedCount
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to mark multiple notifications as read', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'ids' => $request->input('ids')
            ]);

            return response()->json([
                'message' => 'Failed to mark notifications as read'
            ], 500);
        }
    }
}
