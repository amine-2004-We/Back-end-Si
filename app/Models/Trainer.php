<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Trainer
 */
class Trainer extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'trainers';

    /**
     * @var array
     */
    protected $fillable = [
        'is_available',
        'created_by_id',
        'interventions_evaluation',
        'remarks',
        'type',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'interventions_evaluation' => 'double',
    ];

    /**
     * @return BelongsTo<User, Trainer>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasOne<InternalTrainer, Trainer>
     */
    public function internalTrainer(): HasOne
    {
        return $this->hasOne(InternalTrainer::class, 'trainer_id', 'id');
    }

    /**
     * @return HasOne<ExternalTrainer, Trainer>
     */
    public function externalTrainer(): HasOne
    {
        return $this->hasOne(ExternalTrainer::class);
    }

    /**
     * @return bool
     */
    public function getIsInternalAttribute(): bool
    {
        return $this->type === 'internal';
    }

    /**
     * @return bool
     */
    public function getIsExternalAttribute(): bool
    {
        return $this->type === 'external';
    }

    /**
     * @return ?string
     */
    public function getDisplayNameAttribute(): ?string
    {
        if ($this->is_internal && $this->relationLoaded('internalTrainer')) {
            $collab = $this->internalTrainer?->collaborator;
            if ($collab) {
                $name = trim(($collab->first_name ?? '') . ' ' . ($collab->last_name ?? ''));
                return $name !== '' ? $name : ($collab->collaborator_code ?? null);
            }
        }

        if ($this->is_external && $this->relationLoaded('externalTrainer')) {
            $ext = $this->externalTrainer;
            if ($ext) {
                // Prefer person name if present, fallback to full_name (string)
                $full = trim(($ext->full_name ?? ''));
                return $full !== '' ? $full : null;
            }
        }

        return null;
    }


    /**
     * @param mixed $query
     */
    public function scopeOnlyInternal($query)
    {
        return $query->where('type', 'internal');
    }

    /**
     * @param mixed $query
     */
    public function scopeOnlyExternal($query)
    {
        return $query->where('type', 'external');
    }

    /**
     * @return HasMany<Module, Trainer>
     */
    public function modules():HasMany
    {
        return $this->hasMany(Module::class);
    }
}
