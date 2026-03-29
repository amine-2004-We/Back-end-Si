<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammePedagogiqueKader extends Model
{
    use SoftDeletes;

    protected $table = 'programme_pedagogique_kader';

    protected $fillable = [
        'subcomponent',
        'project_pedagogique',
        'project_pedagogique_arabe',
        'sections',
        'sections_arabe',
        'activities',
        'activities_arabe',
        'observation',
    ];

    protected $casts = [
        'sections' => 'array',
        'activities' => 'array',
    ];

    public function phase()
    {
        return $this->belongsTo(Phase::class, 'subcomponent');
    }
}
