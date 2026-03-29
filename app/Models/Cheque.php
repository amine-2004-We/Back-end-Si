<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ChequeStatusEnum;
use App\Observers\ChequeObserver;

/**
 *class Cheque
 */
#[ObservedBy([ChequeObserver::class])]

class Cheque extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'cheques';

    /**
     * @var string[]
     */
    protected $fillable = [
        'cheque_id',
        'number',
        'emission_date',
        'amount',
        'beneficiary_id',
        'project_bank_account_id',
        'status',
        'created_by',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'emission_date' => 'date',
        'amount' => 'decimal:2',
        'status' => ChequeStatusEnum::class,
    ];

    /**
     * @return BelongsTo
     */
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }


    /**
     * @return BelongsTo
     */
    public function projectBankAccount(): BelongsTo
    {
        return $this->belongsTo(ProjectBankAccount::class, 'project_bank_account_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
