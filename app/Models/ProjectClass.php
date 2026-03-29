<?php

namespace App\Models;

use App\Enums\ClassStateEnum;
use App\Enums\ClassStatusValueEnum;
use App\Observers\ClassStatusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ClassStatusObserver::class])]
class ProjectClass extends Model
{
    use SoftDeletes;

    protected $table = 'class';

    protected $fillable = [
        'class_name',
        'class_code',
        'internal_class_code',
        'region_id',
        'external_reference_code',
        'unit_id',
        'cycle_id',
        'local_pedagogical_coordinator',
        'note',
        'user_id',
        'class_status_id',
        'class_state',
        'transfer_to_project_id',
        'relocate_to_class_id',
        'relocation_date',
        'class_status_value',
        'status_change_date',
        'status_change_reason',
        'perpetuation_project_id',
        'douar_id'
    ];

    protected $casts = [
        'class_state' => ClassStateEnum::class,
        'class_status_value' => ClassStatusValueEnum::class,
        'relocation_date' => 'date',
        'status_change_date' => 'date',

    ];
    public function classResources(): HasMany
    {
        return $this->hasMany(ClassResource::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

   

    public function cycles(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function classStatus(): BelongsTo
    {
        return $this->belongsTo(ClassStatus::class, 'class_status_id');
    }

    /**
     * Get the project this class is transferred to (if applicable).
     */
    public function transferToProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'transfer_to_project_id');
    }

    /**
     * Get the class this class is relocated to (if applicable).
     */
    public function relocateToClass(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'relocate_to_class_id');
    }

    /**
     * Get the project this class is perpetuated to (if applicable).
     */
    public function perpetuationProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'perpetuation_project_id');
    }

    /**
     * Get the region for filtering purposes.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function favoriteClassResource()
{
    \Log::info('Accessing favoriteClassResource for ProjectClass ID: ' . $this->id);
    return $this->hasOne(ClassResource::class, 'class_id')
                ->where('isFavorite', true);
}

    /**
     * Get the history records for all resources in this class.
     *
     * @return HasMany
     */
    public function resourceHistory(): HasMany
    {
        return $this->hasMany(ClassResourceHistory::class, 'class_id')->orderByDesc('created_at');
    }

    /**
     * Get the status history for this class.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(ClassStatusHistory::class, 'class_id')->orderByDesc('change_date');
    }

    public function douar(){
        return $this->belongsTo(Douar::class,'douar_id');
    }
}
