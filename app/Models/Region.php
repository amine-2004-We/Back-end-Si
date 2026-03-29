<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code','country_id'];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    public function country(): BelongsTo 
    {
        return $this->belongsTo(Country::class);
    }
}
