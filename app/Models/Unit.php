<?php

namespace App\Models;

use App\Observers\UnitObserver;
use App\UnitStatusEnum;
use App\UnitTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a Unit in the system, such as a preschool, school, or community center.
 */
#[ObservedBy([UnitObserver::class])]
class Unit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'name',
        'internal_code',
        'partner_code',
        'site_id',
        'type',
        'number_of_classes',
        'status',
        'educator_id',
        'observations',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'number_of_classes' => 'integer',
        'type' => UnitTypeEnum::class,
        'status' => UnitStatusEnum::class,
    ];


    /**
     * @return BelongsTo
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }


    /**
     * @return BelongsTo
     */
    public function educator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_id');
    }


    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * @return HasMany
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ProjectClass::class);
    }

}
