<?php

namespace App\Providers;

use App\Models\Collaborator;
use App\Models\Departement;
use App\Models\Evaluation;
use App\Models\EvaluationCriteriaModel;
use App\Models\EvaluationCriteriaOperationModal;
use App\Models\EvaluationGridModel;
use App\Models\EvaluationGridOperation;
use App\Models\EvaluationOperation;
use App\Models\Leave;
use App\Models\Pack;
use App\Models\Position;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProjectClass;
use App\Models\Report;
use App\Models\RequestModel;
use App\Models\TrainingGroup;
use App\Policies\CollaboratorPolicy;
use App\Policies\DepartementPolicy;
use App\Policies\EvaluationCriteriaModelPolicy;
use App\Policies\EvaluationCriteriaOperationModalPolicy;
use App\Policies\EvaluationGridModelPolicy;
use App\Policies\EvaluationGridOperationPolicy;
use App\Policies\EvaluationOperationPolicy;
use App\Policies\EvaluationPolicy;
use App\Policies\LeavePolicy;
use App\Policies\PackPolicy;
use App\Policies\PositionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductTypePolicy;
use App\Policies\ProjectClassPolicy;
use App\Policies\RequestModelPolicy;
use App\Policies\TrainingGroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Category;
use App\Policies\CategoryPolicy;
use App\Policies\ReportPolicy;
use App\Models\PresenceSheet;
use App\Policies\PresenceSheetPolicy;
use App\Models\Beneficiary;
use App\Policies\BeneficiaryPolicy;
use App\Models\Phase;
use App\Policies\PhasePolicy;
use App\Policies\ExternalPolicy;
use App\Models\External;
use App\Models\JobPosting;
use App\Policies\JobPostingPolicy;
use App\Policies\ExternalTrainerPolicy;
use App\Models\ExternalTrainer;
use App\Policies\CompetencyCriterionPolicy;
use App\Models\CompetencyCriterion;
use App\Models\CompetencyGrid;
use App\Policies\CompetencyGridPolicy;
use App\Models\Cycle;
use App\Policies\CyclePolicy;
use App\Models\Level;
use App\Policies\LevelPolicy;
use App\Models\Site;
use App\Policies\SitePolicy;
use App\Models\Program;
use App\Policies\ProgramPolicy;
use App\Models\Task;
use App\Policies\TaskPolicy;
use App\Models\Unit;
use App\Policies\UnitPolicy;
use App\Models\ProgrammePedagogique;
use App\Policies\ProgrammePedagogiquePolicy;
use App\Models\Assurance;
use App\Policies\AssurancePolicy;





class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Product::class => ProductPolicy::class,
        Leave::class => LeavePolicy::class,
        ProjectClass::class=> ProjectClassPolicy::class,
        Report::class => ReportPolicy::class,
        PresenceSheet::class => PresenceSheetPolicy::class,
        Beneficiary::class => BeneficiaryPolicy::class,
        Phase::class =>PhasePolicy::class,
        Pack::class => PackPolicy::class,
        ProductType::class => ProductTypePolicy::class,
        TrainingGroup::class => TrainingGroupPolicy::class,
        EvaluationOperation::class => EvaluationOperationPolicy::class,
        EvaluationGridOperation::class => EvaluationGridOperationPolicy::class,
        EvaluationCriteriaOperationModal::class => EvaluationCriteriaOperationModalPolicy::class,
        Collaborator::class => CollaboratorPolicy::class,
        Departement::class => DepartementPolicy::class,
        Position::class => PositionPolicy::class,
        RequestModel::class  => RequestModelPolicy::class,
        EvaluationCriteriaModel::class  => EvaluationCriteriaModelPolicy::class,
        EvaluationGridModel::class  => EvaluationGridModelPolicy::class,
        Evaluation::class => EvaluationPolicy::class,
        External::class => ExternalPolicy::class, 
        JobPosting::class => JobPostingPolicy::class,
        ExternalTrainer::class => ExternalTrainerPolicy::class,
        CompetencyCriterion::class => CompetencyCriterionPolicy::class,
        CompetencyGrid::class => CompetencyGridPolicy::class,
        Cycle::class => CyclePolicy::class,
        Level::class => LevelPolicy::class,
        Site::class => SitePolicy::class,
        Program::class => ProgramPolicy::class,
        Task::class => TaskPolicy::class,
        Unit::class => UnitPolicy::class,
        ProgrammePedagogique::class => ProgrammePedagogiquePolicy::class,
        Assurance::class => AssurancePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
