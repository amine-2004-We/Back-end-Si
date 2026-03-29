<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusPartner extends Model
{
    protected $table = 'status_partners';

    protected $fillable = ['name'];

    /**
     * Get the partners for this status.
     *
     * @return HasMany
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'status_id');
    }
}