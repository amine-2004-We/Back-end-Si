<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterventionAxis extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'intervention_axes';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Le nom de l'axe
        'code', // Un code éventuel pour la nomenclature
        'description'
    ];

    /**
     * Relation vers les projets liés à cet axe.
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'intervention_axis_id');
    }
}
