<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NotificationDetail extends Model
{
    //
    protected $table = 'notification_details';
     public function sender(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'sender_id');
}
}