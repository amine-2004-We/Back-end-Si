<?php

namespace App\Models;

use App\Enums\ConventionStatus;
use App\Enums\ConventionType;
use App\Enums\CurrencyEnum;
use App\Enums\ReportingPeriodicity;  
use App\Observers\ConventionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ConventionObserver::class])]

class Convention extends Model
{
    use SoftDeletes;

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agreement_code',
        'type',
        'partner_id',
        'project_id', 
        'signed_at',
        'estimated_end_date',
        'amount',
        'amount_in_mad',
        'reporting_periodicity',
        'status',
        'signed_document',
        'title',
        'created_by',
        'devise',
        'tracked_individual_id',
        'observations',
        'duration_months', 
        'responsible_id', 
        'reporting_next_date'
    ];

    /**
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => ConventionType::class,
        'signed_at' => 'date', 
        'estimated_end_date' => 'date', 
        'status' => ConventionStatus::class,
        'devise' => CurrencyEnum::class,
        'reporting_periodicity' => ReportingPeriodicity::class,  
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * ✅ UPDATED: A Convention now belongs to a single Project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function installments(): HasMany
    {
        return $this->hasMany(FinancialInstallment::class);
    }


    public function delete()
    {
        $this->installments()->each(function ($installment) {
            $installment->delete();
        });

        return parent::delete();
    }

    public function trackedIndividual()
    {
        return $this->belongsTo(Collaborator::class, 'tracked_individual_id');
    }

    /**
     * ✅ ADDED: Get the responsible collaborator from migration.
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'responsible_id');
    }


    public function computeReportingNextDate(): ?string
{
    if (!$this->signed_at || !$this->reporting_periodicity) {
        return null;
    }

    $date = $this->signed_at->copy();

    return match ($this->reporting_periodicity->value) {
        'trimestriel' => $date->addMonths(3)->subDays(3)->toDateString(),
        'semestriel'  => $date->addMonths(6)->subDays(3)->toDateString(),
        'annuel'      => $date->addYear()->subDays(3)->toDateString(),
        default       => null,
    };
}


}