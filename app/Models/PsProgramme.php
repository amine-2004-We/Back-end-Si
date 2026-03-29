<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsProgramme extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'activities',
        'activities_ar',
        'level',
        'subcomponent',
    ];

    /***
     * @return BelongsTo
     */
    public function phase():BelongsTo
    {
        return $this->belongsTo(Phase::class, 'subcomponent');
    }
}
