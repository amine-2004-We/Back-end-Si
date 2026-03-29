<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EtbacFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'etbac_code',
        'issue_date',
        'bank_account_id',
        'beneficiary_id', 
        'amount',
        'status',
    ];

    const STATUSES = [
        'prepared' => 'Préparé',
        'sent' => 'Envoyé', 
        'rejected' => 'Rejeté',
        'executed' => 'Exécuté',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relation avec le compte bancaire émetteur
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(ProjectBankAccount::class, 'bank_account_id');
    }

    /**
     * Relation avec la facture bénéficiaire
     */
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'beneficiary_id');
    }

    /**
     * Relation avec les ordres de virement 
     */
    public function transferOrders(): HasMany
    {
        return $this->hasMany(TransferOrder::class, 'etbac_file_id');
    }

    /**
     * Récupérer tous les ordres de virement éligibles 
     */
    public function getEligibleTransferOrders()
    {
        return TransferOrder::eligibleForEtbac()->get();
    }

    /**
     * Associer des ordres de virement à ce fichier
     */
    public function attachTransferOrders(array $transferOrderIds): void
    {
        TransferOrder::whereIn('id', $transferOrderIds)
            ->eligibleForEtbac() 
            ->update(['etbac_file_id' => $this->id]);
            
        $this->update(['amount' => $this->calculateTotalAmount()]);
    }

    /**
     * Retirer des ordres de virement du fichier
     */
    public function detachTransferOrders(array $transferOrderIds): void
    {
        TransferOrder::whereIn('id', $transferOrderIds)
            ->where('etbac_file_id', $this->id)
            ->update(['etbac_file_id' => null]);
            
        $this->update(['amount' => $this->calculateTotalAmount()]);
    }

    /**
     * Vérifier si un ordre de virement peut être ajouté
     */
    public function canAttachTransferOrder(TransferOrder $transferOrder): bool
    {
        return $transferOrder->isEligibleForEtbac();
    }

    /**
     * Calcul du montant total basé sur les ordres de virement associés
     */
    public function calculateTotalAmount(): float
    {
        return $this->transferOrders()->sum('amount');
    }

    /**
     * Génération automatique du code ETBAC
     */
    public static function generateEtbacCode(): string
    {
        $year = now()->year;
        $count = self::withTrashed()->where('etbac_code', 'LIKE', "ETB-{$year}-%")->count() + 1;
        
        $code = "ETB-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
        
        while (self::where('etbac_code', $code)->exists()) {
            $count++;
            $code = "ETB-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
        }
        
        return $code;
    }

    /**
     * Boot method pour la génération automatique
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->etbac_code)) {
                $model->etbac_code = self::generateEtbacCode();
            }
            if (empty($model->issue_date)) {
                $model->issue_date = now();
            }
        });

        static::saved(function ($model) {
            $newAmount = $model->calculateTotalAmount();
            if ($model->amount != $newAmount) {
                $model->updateQuietly(['amount' => $newAmount]);
            }
        });
    }
}