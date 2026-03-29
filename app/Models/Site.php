<?php

namespace App\Models;

use App\Observers\SiteObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([SiteObserver::class])]

class Site extends Model
{
    use HasFactory, SoftDeletes;

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'internal_code',
        'type',
        'commune_id',
        'douar_id',
        'country_id',
        'start_date',
        'status',
        'latitude',
        'longitude',
        'observations',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'deleted_at' => 'datetime',
    ];

    /**
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            if (empty($site->site_id) && !empty($site->country_id)) {
                $site->loadMissing('country');
                $countryCode = $site->country->code ?? 'UNK';
                $site->site_id = (new \App\Repositories\SiteRepository())->generateUniqueSiteId($countryCode);
            }
            if (empty($site->created_by)) {
                $site->created_by = auth()->id();
            }
        });
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
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * @return BelongsTo
     */
    public function localOperationalManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'local_operational_manager_id');
    }

    /**
     * @return BelongsTo
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * @return BelongsTo
     */
    public function douar(): BelongsTo
    {
        return $this->belongsTo(Douar::class);
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
