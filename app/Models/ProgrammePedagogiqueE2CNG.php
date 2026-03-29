<?php

namespace App\Models;

use App\Enums\AtelierE2CNGEnum;
use App\Enums\MatiereE2CNGEnum;
use App\Enums\MetierE2CNGEnum;
use App\Enums\ProgrammeTypeEnum;
use App\Enums\ProfessionalOptionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammePedagogiqueE2CNG extends Model
{
    use SoftDeletes;

    protected $table = 'programme_pedagogique_e2cng';

    protected $fillable = [
        'groupe_id',
        'project_id',
        'class_id',
        'type', 
        'professional_option', 
        'project_pedagogique',
        'project_pedagogique_arabe',
        'metier',
        'metier_arabe',
        'ateliers',
        'ateliers_arabe',
        'observation',
        'date_prevu',
        'date_realisation',
        'date_prevue', 
        'real_start_date', 
        'real_end_date', 
    ];

    protected $casts = [
        'type' => ProgrammeTypeEnum::class,
        'professional_option' => ProfessionalOptionEnum::class,        
        'project_pedagogique' => MatiereE2CNGEnum::class,
        'ateliers' => AtelierE2CNGEnum::class,        
        'ateliers_arabe' => 'array',
        'date_prevu' => 'date',
        'date_realisation' => 'date',
        'date_prevue' => 'date',
        'real_start_date' => 'date',
        'real_end_date' => 'date',
    ];

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'groupe_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}