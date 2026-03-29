<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class ProjectBankAccount
 */
class ProjectBankAccount extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'project_bank_accounts';

    /**
     * @var string[]
     */
    protected $fillable = [
        'rib_iban',
        'bank_id',
        'agency',
        'account_title',
        'bic_swift',
        'opening_date',
        'opening_country',
        'status',
        'created_by_id',
        'supporting_document',
        'comments',
        'created_by_id',
        'account_holder_name'
    ];


    /**
     * @var string[]
     */
    protected $casts = [
        'opening_date' => 'date:Y-m-d',
    ];

    /**
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return BelongsTo
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
