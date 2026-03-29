<?php

namespace App\Models;

use App\Observers\ClassResourceObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([ClassResourceObserver::class])]
class ClassResource extends Model
{
    protected $table = 'class_resources';

    protected $fillable = [
        'class_id',
        'project_id',
        'collaborator_id',
        'start_date',
        'end_date',
        'isStill',
        'isFavorite'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'isStill' => 'boolean',
    ];

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function projectClass(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'class_id');
    }

    /**
     * Get the history records for this resource.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(ClassResourceHistory::class, 'class_resource_id');
    }
}
