<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Enums\ServiceProvisionTypeEnum;
use App\Observers\ServiceProvisionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ServiceProvisionObserver::class])]
class ServiceProvision extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_provisions';

    protected $fillable = [
        'reference',
        'budget_line_id',
        'provision_date',
        'amount',
        'supplier',
        'description',
        'justification_path',
        'type',
        'created_by',
    ];

    protected $casts = [
        'provision_date' => 'date',
        'amount' => 'decimal:2',
        'type' => ServiceProvisionTypeEnum::class,
    ];

    /**
     * Get the URL for the justification file.
     */
    protected function justificationUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->justification_path ? Storage::url($this->justification_path) : null,
        );
    }

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class, 'budget_line_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}