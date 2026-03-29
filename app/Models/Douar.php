<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Douar extends Model
{
    use HasFactory;

    protected $fillable = ['commune_id', 'name', 'code'];

    /**
     * Get the commune that owns the douar.
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Get the sites for the douar.
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
