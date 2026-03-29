<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerNote extends Model
{
    protected $fillable = ['partner_id', 'note'];
    public function partner(): BelongsTo { return $this->belongsTo(Partner::class); }
}
