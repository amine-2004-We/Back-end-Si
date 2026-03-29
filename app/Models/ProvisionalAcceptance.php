<?php

namespace App\Models;

use App\Enums\ProvisionalAcceptanceStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvisionalAcceptance extends Model
{
    use SoftDeletes;

    protected $table = 'provisional_acceptances';

    protected $fillable = [
        'identifier',
        'delivery_receipt_id',
        'provisional_acceptance_date',
        'calltender_id',
        'reserves',
        'corrective_actions',
        'status',
    ];

    protected $casts = [
        'provisional_acceptance_date' => 'date',
        'status' => ProvisionalAcceptanceStatusEnum::class,
    ];

    /**
     * Les PV Définitifs associés (relation many-to-many).
     */
    public function finalAcceptances(): BelongsToMany
    {
        return $this->belongsToMany(
            FinalAcceptance::class,
            'final_acceptance_provisional_acceptance',
            'provisional_acceptance_id',
            'final_acceptance_id'
        );
    }

    /**
     * Le Bon de Réception (BR) associé.
     */
    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class, 'delivery_receipt_id');
    }

    /**
     * La liste des articles réceptionnés pour ce PV.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProvisionalAcceptanceItem::class, 'provisional_acceptance_id');
    }

     /**
     * Le comité de réception (Liste d'utilisateurs).
     */
    public function committeeMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'provisional_acceptance_user', 'provisional_acceptance_id', 'user_id');
    }

    /**
     * Le Marché/Contrat associé.
     */
    public function calltender(): BelongsTo
    {
        return $this->belongsTo(Calltender::class, 'calltender_id');

    }
    /**
     * Les reçus partiels associés (plusieurs, via table pivot).
     */
    public function partialReceipts(): BelongsToMany
    {
        return $this->belongsToMany(PartialReceipt::class, 'provisional_acceptance_partial_receipt', 'provisional_acceptance_id', 'partial_receipt_id');
    }

}
