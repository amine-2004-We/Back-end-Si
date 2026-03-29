<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'issue_date',
        'debited_bank_account_id',
        'beneficiary_name',
        'beneficiary_bank_account_id',
        'motif_type',
        'motif_id',
        'amount',
        'status',
        'etbac_file_id',
    ];

    const STATUSES = [
        'en_attente',
        'envoye',
        'valide',
        'rejete',
    ];

    public function debitedBankAccount(): BelongsTo
    {
        return $this->belongsTo(ProjectBankAccount::class, 'debited_bank_account_id');
    }

    public function beneficiaryBankAccount(): BelongsTo
    {
        return $this->belongsTo(ProjectBankAccount::class, 'beneficiary_bank_account_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'motif_id');
    }

    public function expenseReport(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class, 'motif_id');
    }

    /**
     * Relation avec le fichier ETBAC
     */
    public function etbacFile(): BelongsTo
    {
        return $this->belongsTo(EtbacFile::class, 'etbac_file_id');
    }

    public function getMotifAttribute()
    {
        if ($this->motif_type === 'facture') {
            return $this->invoice;
        } elseif ($this->motif_type === 'depense') {
            return $this->expenseReport;
        }
        return null;
    }

    public function scopeWithMotif($query)
    {
        return $query->with(['invoice', 'expenseReport']);
    }

    /**
     * Scope pour les virements sans fichier ETBAC
     */
    public function scopeWithoutEtbacFile($query)
    {
        return $query->whereNull('etbac_file_id');
    }

    /**
     * Scope pour les virements éligibles 
     */
    public function scopeEligibleForEtbac($query)
    {
        return $query->where('status', 'valide')
                    ->whereNull('etbac_file_id');
    }

    /**
     * Vérifier si le virement est éligible pour ETBAC
     */
    public function isEligibleForEtbac(): bool
    {
        return $this->status === 'valide' && empty($this->etbac_file_id);
    }

    /**
     * Récupérer le code ETBAC via la relation
     */
    public function getEtbacCodeAttribute(): ?string
    {
        return $this->etbacFile?->etbac_code;
    }
}