<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NaturePartner extends Model
{
    protected $table = 'nature_partners';

    protected $fillable = ['name'];

    /**
     * Get the partners for this nature.
     *
     * @return HasMany
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'nature_partner_id');
    }
}