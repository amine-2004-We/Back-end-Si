<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cercle extends Model
{
    use HasFactory;

    protected $fillable = ['province_id', 'name', 'code'];

    /**
     * Get the province that owns the cercle.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the communes for the cercle.
     */
    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }
}
