<?php

namespace App\Services;

use App\Models\NotificationDetail;
use App\Models\NotificationRecipient;

class NotificationService
{
    /**
     * Save a new notification with recipients
     */
    public static function save(
        string $title,
        string $text,
        int $senderCollaboratorId,   
        array $targetUrlData,
        array $recipients
    ): NotificationDetail {
        $notification = new NotificationDetail();
        $notification->title = $title;
        $notification->text = $text;
        $notification->sender_id = $senderCollaboratorId; 
        $notification->target_url_data = json_encode($targetUrlData);
        $notification->save();
        foreach ($recipients as $receiverId) {
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'receiver_id' => $receiverId,
            ]);
        }
    
        return $notification;
    }

    /**
     * updating notification
     */
    public static function update(int $id, array $data): ?NotificationDetail
    {
        $notification = NotificationDetail::find($id);

        if ($notification) {
            $notification->update($data);
        }
        return $notification;
    }

    /**
     * Get notifications (optionally filter by is_seen)
     */
    public static function getAll(int $receiverId, ?bool $isSeen = null)
    {
        $query = NotificationRecipient::where('receiver_id', $receiverId)
            ->with('notificationDetail');

        if (!is_null($isSeen)) {
            $query->where('is_seen', $isSeen);
        }

        return $query->get();
    }
}
