<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{use SoftDeletes;

    protected $fillable = ['name', 'type', 'status', 'capacity', 'latitude', 'longitude', 'address', 'province_id', 'created_by'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
