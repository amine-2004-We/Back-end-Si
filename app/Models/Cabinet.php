<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Cabinet
 */
class Cabinet extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'cabinets';

    /**
     * @var string[]
     */
    protected $fillable = [
        'cabinet_id',
        'name',
        'responsible_name',
        'contact_phone',
        'contact_email',
        'address',
        'place_id',
        'contracts',
        'average_rating',
        'notes',
        'created_by_id',
    ];

    protected $casts = [
        'contracts' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'responsible_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasMany
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * @return HasMany
     */
    public function externalTrainers(): HasMany
    {
        return $this->hasMany(ExternalTrainer::class);
    }

    /**
     * @return BelongsTo
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

}
