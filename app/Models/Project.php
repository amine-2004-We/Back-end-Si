<?php

namespace App\Models;

use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * class Project
 */
class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Constantes pour les Natures de Projet
    const NATURE_PUBLIC = 'Public';
    const NATURE_PRIVATE = 'Privée';

    // Constantes pour les Phases
    const PHASE_PRE_PROJECT = 'Pre-projet';
    const PHASE_PROJECT = 'Projet';

    /**
     * @var string
     */
    protected $table = 'projects';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'project_code',
        'project_name',
        'project_abbreviation',
        'partner_id',
        'project_nature_id',
        'intervention_axis_id',
        'region_id',
        'province_id',
        'total_budget',
        'start_date',
        'end_date',
        'current_phase',
        'project_status_id',
        'zakoura_contribution',
        'project_bank_account_id',
        'zakoura_amount',
        'exercice_comptable',
        'analytic_code',
        'actual_start_date',
        'responsible_id',
        'created_by_id',
        'program_id',
        'program_type_id',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'total_budget' => 'decimal:2',
        'zakoura_contribution' => 'decimal:2',
        'partner_amount' => 'decimal:2',
        'exercice_comptable' => 'array',
    ];

     /**
     * Le partenaire associé principal (Source ou principal).
     * @return BelongsTo
     */
    public function associatedPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
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
    public function responsible(): BelongsTo
    {
         return $this->belongsTo(User::class, 'responsible_id');
    }


    /**
     * @return BelongsTo
     */
    public function projectBankAccount(): BelongsTo
    {
        return $this->belongsTo(ProjectBankAccount::class);
    }

    /**
     * @return BelongsTo
     */
    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class,'project_nature_id');
    }

    /**
     * @return BelongsTo
     */
    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class);
    }

     /**
     * Axe d'intervention (Nomenclature)
     * @return BelongsTo
     */
    public function interventionAxis(): BelongsTo
    {
        return $this->belongsTo(InterventionAxis::class, 'intervention_axis_id');
    }

    /**
     * @return BelongsToMany
     */
    public function  partners() :  BelongsToMany
    {
        return $this->belongsToMany(Partner::class,'project_partner')
            ->using(ProjectPartner::class)
            ->withPivot(['partner_role','partner_contribution','partner_amount']);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'project_collaborator');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Conventions liées (Cadre ou Opérationnelle)
     * @return HasMany
     */
    public function conventions(): HasMany
    {
        return $this->hasMany(Convention::class);
    }


    /**
     * @return HasManyThrough<BudgetCategory, BudgetLine, Project>
     */
    public function budgetCategories(): HasManyThrough
    {
        return $this->hasManyThrough(
            BudgetCategory::class,
            BudgetLine::class,
            'project_id',
            'id',
            'id',
            'budget_category_id'
        )->distinct();
    }

    public function budgetLines(): BelongsToMany
    {
        return $this->belongsToMany(BudgetLine::class, 'budget_line_project')
            ->using(\App\Models\BudgetLineProject::class)
            ->withPivot([
                'id',
                'consumed_amount',
                'quantity',
                'reliquate_amount',
                'total_amount',
                'remaining_amount',
                'unit_amount',
                'engaged_amount'
            ])
            ->withTimestamps();
    }

    /**
     * Get all financial installments for the project's conventions.
     *
     * This uses a hasManyThrough relation: Project -> Convention -> FinancialInstallment
     *
     * @return HasManyThrough
     */
    public function financialInstallments(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\FinancialInstallment::class,
            \App\Models\Convention::class,
            'project_id',
            'convention_id',
            'id',
            'id'
        );
    }
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Historique des notes du projet
     * @return HasMany
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class)->orderByDesc('created_at');
    }
    public function class(): BelongsToMany
    {
        return $this->belongsToMany(ProjectClass::class, 'class_projects', 'project_id', 'class_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
    public function programType(): BelongsTo
    {
        return $this->belongsTo(ProgramType::class);
    }

    /**
     * @return HasMany
     */
    public function missionOrders(): HasMany
    {
        return $this->hasMany(MissionOrder::class);
    }

}
