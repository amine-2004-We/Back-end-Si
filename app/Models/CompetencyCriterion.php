<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *class CompetencyCriterion
 */
class CompetencyCriterion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'identifier',
        'title',
        'description',
        'type',
        'scoring_scale',
        'display_order',
        'weight',
        'notes',
        'is_active',
        'created_by_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'decimal:2',
    ];


    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }


    /**
     * @return belongsToMany
     */
    public function competencyGrids(): BelongsToMany
    {
        return $this->belongsToMany(
            CompetencyGrid::class,
            'competency_grid_criteria',
            'criterion_id',
            'competency_grid_id'
        );
    }
}
