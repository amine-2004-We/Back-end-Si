<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *class Commune
 */
class Commune extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = ['cercle_id', 'name', 'code'];

    /**
     * Get the cercle that owns the commune.
     */
    public function cercle(): BelongsTo
    {
        return $this->belongsTo(Cercle::class);
    }

    /**
     * Get the douars for the commune.
     */
    public function douars(): HasMany
    {
        return $this->hasMany(Douar::class);
    }

    /**
     * Get the sites for the commune.
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
