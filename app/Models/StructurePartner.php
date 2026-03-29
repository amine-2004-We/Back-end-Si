<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StructurePartner extends Model
{
    protected $table = 'structure_partners';

    protected $fillable = ['name'];

    /**
     * Get the partners for this structure.
     *
     * @return HasMany
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'structure_partner_id');
    }
}