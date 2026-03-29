<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRecipient extends Model
{
    protected $table = 'notification_recipients';
    protected $fillable = [
        'notification_id',
        'receiver_id',
    ];
    

    /**
     * Get the notification detail associated with this recipient record.
     */
    public function detail(): BelongsTo
    {
         return $this->belongsTo(NotificationDetail::class, 'notification_id');
    }

    /**
     * Get the collaborator (receiver) associated with this recipient record.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }
}
    
