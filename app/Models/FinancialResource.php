<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\FinancialResourcesTypeEnum;
use App\Enums\FinancialTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class FinancialResource extends Model
{
    use softDeletes;
    protected $table = 'financial_resources';
    protected $fillable = [
        'financial_resources_code',
        'financial_resources_type',
        'partner_id',
        'project_id',
        'slice',
        'amount_received',
        'slice_date',
        'currency',
        'financial_type',
        'signature_date',
        'project_start_date',
        'project_end_date',
        'financial_status',
        'attachment',
        'comments',
        'created_by',
    ];

    protected $casts = [
        'financial_resources_type' =>FinancialResourcesTypeEnum::class,
        'currency' => CurrencyEnum::class,
        'financial_type' => FinancialTypeEnum::class,
        'slice_date' => 'date',
    ];
    public final function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public final function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
