<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Partner
 */
class Partner extends Model
{
     use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'partners';

    /**
     * @var string[]
     */
    protected $fillable = [
        'partner_name',
        'abbreviation',
        'phone',
        'email',
        'partner_type',
        'nature_partner_id',
        'structure_partner_id',
        'status_id',
        'phase_id',
        'closure_reason',
        'date_debut_partenariat',
        'address',
        'country',
        'partner_logo',
        'created_by_id',
        'is_active'
    ];

    /**
     *  Relation pour les notes multiples
     */
    public function notes(): HasMany
    {
        return $this->hasMany(PartnerNote::class, 'partner_id');
    }

    /**
     * Get the status for this partner.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusPartner::class, 'status_id');
    }

    /**
     * Get the phase for this partner.
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class, 'phase_id');
    }

    /**
     * Get the nature for this partner.
     */
    public function naturePartner(): BelongsTo
    {
        return $this->belongsTo(NaturePartner::class, 'nature_partner_id');
    }

    /**
     * Get the structure for this partner.
     */
    public function structurePartner(): BelongsTo
    {
        return $this->belongsTo(StructurePartner::class, 'structure_partner_id');
    }

    /**
     * Get the contact people for this partner.
     */
    public function contactPeople(): HasMany
    {
        return $this->hasMany(ContactPerson::class, 'partner_id');
    }

    /**
     * @return BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_partner')
            ->using(ProjectPartner::class)
            ->withPivot(['partner_role', 'partner_contribution']);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
