<?php

namespace App\Models;

use App\Observers\AssuranceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *class Assurance
 */
#[ObservedBy([AssuranceObserver::class])]
class Assurance extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'insurances_training';

    /**
     * @var string[]
     */
    protected $fillable = [
        'insurance_id',
        'personne_assuree_type',
        'personne_assuree_id',
        'insurance_type',
        'insurance_organization',
        'affiliation_date',
        'termination_date',
        'comments',
        'status'
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'affiliation_date' => 'date',
            'termination_date' => 'date',
        ];
    }


    /**
     * @return MorphTo
     */
    public function personneAssuree(): MorphTo
    {
        return $this->morphTo();
    }
}
