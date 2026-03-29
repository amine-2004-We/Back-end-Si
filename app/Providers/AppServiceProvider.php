<?php

namespace App\Providers;

use App\Http\Resources\FinancialResourceResources;
use App\Models\Advance;
use App\Models\Article;
use App\Models\Bank;
use App\Models\BudgetLine;
use App\Models\Calltender;
use App\Models\Avenant;
use App\Models\Checkout;
use App\Models\Collaborator;
use App\Models\ContactPerson;
use App\Models\DeliveryRequest;
use App\Models\Evaluation;
use App\Models\EvaluationCriteriaModel;
use App\Models\EvaluationCriteriaOperationModal;
use App\Models\EvaluationGridModel;
use App\Models\EvaluationGridOperation;
use App\Models\EvaluationOperation;
use App\Models\FinancialResource;
use App\Models\MedicalRecord;
use App\Models\Pack;
use App\Models\PartialReceipt;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Position;
use App\Models\ProjectClass;
use App\Models\Task;
use App\Observers\AdvanceObserver;
use App\Observers\BankObserver;
use App\Observers\CheckoutObserver;
use App\Observers\ClassObserver;
use App\Observers\CollaboratorObserver;
use App\Observers\ContactPersonObserver;
use App\Observers\DeliveryRequestObserver;
use App\Observers\EvaluationCriteriaObserver;
use App\Observers\EvaluationCriteriaOperationsObserver;
use App\Observers\EvaluationGridObserver;
use App\Observers\EvaluationGridOperationObserver;
use App\Observers\EvaluationObserver;
use App\Observers\EvaluationOperationObserver;
use App\Observers\FinancialResourceObserver;
use App\Observers\MedicalRecordObserver;
use App\Observers\PackObserver;
use App\Models\Project;
use App\Models\Level;
use App\Observers\ArticleObserver;
use App\Observers\BudgetLigneObserver;
use App\Observers\PartialReceiptObserver;
use App\Observers\PaymentObserver;
use App\Observers\PayrollObserver;
use App\Observers\PositionObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Observers\TrainingGroupObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Observers\CategoryObserver;
use App\Models\Product;
use App\Models\Site;
use App\Observers\LevelObserver;
use App\Observers\ProductObserver;
use App\Observers\SiteObserver;
use App\Observers\GroupObserver;
use App\Observers\CompetencyGridObserver;
use App\Models\Group;
use App\Models\CompetencyGrid;
use App\Models\ParentModel;
use App\Observers\ParentObserver;
use App\Observers\CalltenderObserver;
use App\Observers\AvenantObserver;
use App\Observers\MissionOrderObserver;
use App\Models\MissionOrder;
use App\Models\Cabinet;
use App\Models\ExpenseNote;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quote;
use App\Observers\PurchaseRequestObserver;
use App\Models\Supplier;
use App\Observers\PurchaseOrderObserver;
use App\Observers\QuoteObserver;
use App\Observers\SupplierObserver;
use App\Models\PurchaseList;
use App\Models\Training;
use App\Observers\CabinetObserver;
use App\Observers\PurchaseListObserver;
use App\Observers\TrainingObserver;
use App\Models\Report;
use App\Observers\ReportObserver;
use App\Models\RecruitmentRequest;
use App\Observers\RecruitmentRequestObserver;
use App\Models\Phase;
use App\Models\Insurance;
use App\Models\Trainer;
use App\Observers\PhaseObserver;
use App\Observers\InsuranceObserver;
use App\Models\External;
use App\Observers\ExternalObserver;
use App\Observers\TrainerObserver;
use App\Models\ExternalTrainer;
use App\Observers\ExternalTrainerObserver;
use App\Observers\CompetencyCriterionObserver;
use App\Models\CompetencyCriterion;
use App\Models\Module;
use App\Observers\ModuleObserver;
use App\Models\ModuleEvaluation;
use App\Observers\ModuleEvaluationObserver;
use App\Models\RouteModel;
use App\Observers\RouteObserver;
use App\Models\Assurance;
use App\Models\Candidate;
use App\Observers\AssuranceObserver;
use App\Models\TraineeCollaborator; // ← added
use App\Models\User;
use App\Observers\ExpenseNoteObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Cheque;
use App\Observers\ChequeObserver;

use App\Models\Convention;
use App\Models\Grant;
use App\Models\Payroll;
use App\Models\Place;
use App\Models\TrainingGroup;
use App\Observers\CandidateObserver;
use App\Observers\ConventionObserver;
use App\Observers\GrantObserver;
use App\Observers\PlaceObserver;
use App\Observers\DeliveryOrderObserver;
use App\Models\DeliveryOrder;
use App\Models\Prospection;
use App\Observers\ProspectionObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Project::observe(ProjectObserver::class);
        Category::observe(CategoryObserver::class);
        Convention::observe(ConventionObserver::class);
        Product::observe(ProductObserver::class);
        Article::observe(ArticleObserver::class);
        Pack::observe(PackObserver::class);
        Site::observe(SiteObserver::class);
        Article::observe(ArticleObserver::class);
        BudgetLine::observe(BudgetLigneObserver::class);
        Task::observe(TaskObserver::class);
        Bank::observe(BankObserver::class);
        ProjectClass::observe(ClassObserver::class);
        Level::observe(LevelObserver::class);
        Group::observe(GroupObserver::class);
        ParentModel::observe(ParentObserver::class);
        Calltender::observe(CalltenderObserver::class);
        Avenant::observe(AvenantObserver::class);
        MissionOrder::observe(MissionOrderObserver::class);
        Position::observe(PositionObserver::class);
        PurchaseRequest::observe(PurchaseRequestObserver::class);
        EvaluationCriteriaModel::observe(EvaluationCriteriaObserver::class);
        EvaluationGridModel::observe(EvaluationGridObserver::class);
        Supplier::observe(SupplierObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        Quote::observe(QuoteObserver::class);
        Collaborator::observe(CollaboratorObserver::class);
        Evaluation::observe(EvaluationObserver::class);
        PurchaseList::observe(PurchaseListObserver::class);
        Cabinet::observe(CabinetObserver::class);
        Training::observe(TrainingObserver::class);
        EvaluationGridOperation::observe(EvaluationGridOperationObserver::class);
        Report::observe(ReportObserver::class);
        RecruitmentRequest::observe(RecruitmentRequestObserver::class);
        Phase::observe(PhaseObserver::class);
        EvaluationCriteriaOperationModal::observe(EvaluationCriteriaOperationsObserver::class);
        EvaluationOperation::observe(EvaluationOperationObserver::class);
        Insurance::observe(InsuranceObserver::class);
        External::observe(ExternalObserver::class);
        Trainer::observe(TrainerObserver::class);
        MedicalRecord::observe(MedicalRecordObserver::class);
        ExternalTrainer::observe(ExternalTrainerObserver::class);
        ExpenseNote::observe(ExpenseNoteObserver::class);
        CompetencyCriterion::observe(CompetencyCriterionObserver::class);
        CompetencyGrid::observe(CompetencyGridObserver::class);
        Module::observe(ModuleObserver::class);
        ModuleEvaluation::observe(ModuleEvaluationObserver::class);
        Assurance::observe(AssuranceObserver::class);
        RouteModel::observe(RouteObserver::class);
        ContactPerson::observe(ContactPersonObserver::class);
        Payment::observe(PaymentObserver::class);
        FinancialResource::observe(FinancialResourceObserver::class);
        Cheque::observe(ChequeObserver::class);
        Candidate::observe(CandidateObserver::class);
        Checkout::observe(CheckoutObserver::class);
        Advance::observe(AdvanceObserver::class);
        Grant::observe(GrantObserver::class);
        Payroll::observe(PayrollObserver::class);
        Place::observe(PlaceObserver::class);
        TrainingGroup::observe(TrainingGroupObserver::class);
        DeliveryOrder::observe(DeliveryOrderObserver::class);
        DeliveryRequest::observe(DeliveryRequestObserver::class);
        PartialReceipt::observe(PartialReceiptObserver::class);
        Prospection::observe(ProspectionObserver::class);
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

    }
}
