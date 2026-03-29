<?php

use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\AdvanceController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\BudgetCategoryController;
use App\Http\Controllers\Api\BudgetLineProjectController;
use App\Http\Controllers\Api\CabinetController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\ProgrammePedagogiqueController;
use App\Http\Controllers\Api\ProgrammePedagogiqueE2CNGController;
use App\Http\Controllers\Api\ClassStatusController;
use App\Http\Controllers\Api\ClassTypesController;
use App\Http\Controllers\Api\CollaboratorController;
use App\Http\Controllers\Api\CollaboratorStatusController;
use App\Http\Controllers\Api\ContactPersonController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ContractStatusController;
use App\Http\Controllers\Api\ContractTypesController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeliveryRequestController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DownloadFileController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\EvaluationCriteriaOperationController;
use App\Http\Controllers\Api\EvaluationCriteriaController;
use App\Http\Controllers\Api\EvaluationGridController;
use App\Http\Controllers\Api\EvaluationGridOperationController;
use App\Http\Controllers\Api\EvaluationHrController;
use App\Http\Controllers\Api\EvaluationMetaController;
use App\Http\Controllers\Api\EvaluationOperationController;
use App\Http\Controllers\Api\EvaluationOperationsMetaController;
use App\Http\Controllers\Api\EtebacController;
use App\Http\Controllers\Api\FinancialResourceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\BudgetLigneController;
use App\Http\Controllers\Api\CalltenderController;
use App\Http\Controllers\Api\AvenantController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GeographicController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\PartialReceiptController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductTypeController;
use App\Http\Controllers\Api\ProjectBankAccountController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectStatusController;
use App\Http\Controllers\Api\ProjectTypeController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\RequestTypeController;
use App\Http\Controllers\Api\TraineeCollaboratorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\NaturePartnerController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\StatusPartnerController;
use App\Http\Controllers\Api\StructurePartnerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\CycleController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\CategoryTypeController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GroupTypeController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\BeneficiaryController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\ExpenseNoteController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\InternalTrainerController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\PurchaseListController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TypeDepartmentController;
use App\Http\Controllers\Api\MissionOrderController;
use App\Http\Controllers\Api\PhaseController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\VisionTestController;
use App\Http\Controllers\Api\PediatreTestController;
use App\Http\Controllers\Api\DentaireTestController;
use App\Http\Controllers\Api\OrlTestController;
use App\Http\Controllers\Api\VaccinTestController;
use App\Http\Controllers\Api\ConsultationExportController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\TrainingGroupController;
use App\Http\Controllers\Api\ReportController;
use App\Models\Report;
use App\Models\RouteModel;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\PresenceSheetController;
use App\Http\Controllers\Api\RecruitmentRequestController;
use App\Http\Controllers\Api\ExternalTrainerController;
use App\Http\Controllers\Api\CompetencyCriterionController;
use App\Http\Controllers\Api\CompetencyGridController;
use App\Http\Controllers\Api\TrainingSessionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ConventionController;
use App\Http\Controllers\Api\ModuleEvaluationController;
use App\Http\Controllers\Api\AssuranceController;
use App\Http\Controllers\Api\CallsForProjectsController;
use App\Http\Controllers\Api\CompagnieAssuranceController;
use App\Http\Controllers\Api\GeneralAccountController;
use App\Http\Controllers\Api\ThirdPartyAccountController;
use App\Http\Controllers\Api\ChequeController;
use App\Http\Controllers\Api\DeliveryReceiptController;
use App\Http\Controllers\Api\DeliveryOrderController;
use App\Http\Controllers\Api\ServiceProvisionController;
use App\Http\Controllers\Api\GrantController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\FinalAcceptanceController;
use App\Http\Controllers\ProvisionalAcceptanceController;
use App\Http\Controllers\Api\FinancialInstallmentController;
use App\Http\Controllers\Api\ExpenseReportController;
use App\Http\Controllers\Api\ExpenseLineController;
use App\Http\Controllers\Api\TransferOrderController;
use App\Http\Controllers\Api\EtbacFileController;
use App\Http\Controllers\Api\ProspectionController;
use App\Http\Controllers\ClassResourceHistoryController;
use App\Http\Controllers\Api\KpiChartController;
use App\Http\Controllers\Api\PartnershipDashboardController;
use App\Models\Cabinet;
use App\Http\Controllers\Api\ProgramTypeController;
use App\Http\Controllers\Api\TeacherEvaluationController;
use App\Models\Candidate;
use App\Models\Category;
use App\Models\ExpenseNote;
use App\Models\Grant;
use App\Models\Insurance;
use App\Models\InternalTrainer;
use App\Models\Invoice;
use App\Models\JobPosting;
use App\Models\Product;
use App\Models\RecruitmentRequest;
use App\Models\TraineeCollaborator;
use App\Models\Module;
use App\Models\Place;




Route::get('/purchase-requests/{id}/pdf-preview', [PurchaseRequestController::class, 'pdfPreview']);
Route::get('/purchase-requests/{id}/pdf-download', [PurchaseRequestController::class, 'pdfDownload']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->group(function () {
    Route::prefix('categories')->group(function () {
        Route::bind('category', function ($value) {
            return Category::withTrashed()->findOrFail($value);
        });
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('{category}', [CategoryController::class, 'show']);
        Route::put('{category}', [CategoryController::class, 'update']);
        Route::delete('{category}', [CategoryController::class, 'destroy']);
        Route::post('bulk-delete', [CategoryController::class, 'bulkDestroy']);
        Route::get('products-by-category/{category}', [CategoryController::class, 'getProductsByCategory']);
        Route::post('{category}/restore', [CategoryController::class, 'restore']);
    });

    Route::prefix('type-departements')->group(function () {
        Route::get('/', [TypeDepartmentController::class, 'index']);
        Route::post('/', [TypeDepartmentController::class, 'store']);
        Route::get('{id}', [TypeDepartmentController::class, 'show']);
        Route::put('{id}', [TypeDepartmentController::class, 'update']);
        Route::delete('{id}', [TypeDepartmentController::class, 'destroy']);
        Route::post('bulk-delete', [TypeDepartmentController::class, 'bulkDestroy']);
        Route::post('{id}/restore', [TypeDepartmentController::class, 'restore']);
    });
    Route::prefix('products')->group(function () {
        Route::bind('product', function ($value) {
            return Product::withTrashed()->findOrFail($value);
        });
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('{product}', [ProductController::class, 'show']);
        Route::put('{product}', [ProductController::class, 'update']);
        Route::delete('{product}', [ProductController::class, 'destroy']);
        Route::post('{product}/restore', [ProductController::class, 'restore']);
        Route::get('by-category/{category}', [ProductController::class, 'getByCategory']);
    });
    Route::prefix('product-types')->group(function () {
        Route::get('/', [ProductTypeController::class, 'index']);
        Route::post('/', [ProductTypeController::class, 'store']);
        Route::get('{productType}', [ProductTypeController::class, 'show']);
        Route::put('{productType}', [ProductTypeController::class, 'update']);
        Route::delete('{productType}', [ProductTypeController::class, 'destroy']);
        Route::post('bulk-delete', [ProductTypeController::class, 'bulkDestroy']);
        Route::post('{productType}/restore', [ProductTypeController::class, 'restore']);
    });
    Route::get('projects/options', [ProjectController::class, 'formOptions']);
    Route::apiResource('projects', ProjectController::class);
    Route::put('projects/{id}/restore', [ProjectController::class, 'restore']);
    Route::get('/projects/{id}/collaborators', [ProjectController::class, 'getCollaboratorsByProject']);
    Route::get('/projects/{id}/units', [ProjectController::class, 'getUnitsByProject']);
    Route::post('/projects/{id}/collaborators', [ProjectController::class, 'assignCollaborators']);
    Route::delete('/projects/{project}/collaborators/{collaborators}', [ProjectController::class, 'removeCollaborator']);
    Route::apiResource('project-types', ProjectTypeController::class);
    Route::get('/projects/{projectId}/budget-categories', [ProjectController::class, 'showBudgetCategories']);
    Route::get('/projects/{projectId}/budget-categories/{categoryId}/budget-lines', [ProjectController::class, 'showBudgetLinesByCategory']);
    Route::get('/projects/{projectId}/budget-lines', [ProjectController::class, 'getProjectBudgetLines']);
    Route::get('/projects/{projectId}/budget-categories-with-aggregates', [ProjectController::class, 'getProjectBudgetCategoriesWithAggregates']);
    Route::put('project-types/{id}/restore', [ProjectTypeController::class, 'restore']);
    Route::apiResource('project-statuses', ProjectStatusController::class);
    Route::put('project-statuses/{id}/restore', [ProjectStatusController::class, 'restore']);
    Route::get('project-bank-accounts/options', [ProjectBankAccountController::class, 'options']);
    Route::apiResource('project-bank-accounts', ProjectBankAccountController::class);
    Route::put('project-bank-accounts/{id}/restore', [ProjectBankAccountController::class, 'restore']);
    Route::post('bank-accounts/{id}/supporting-document', [ProjectBankAccountController::class, 'updateSupportingDocument']);
    Route::post('projects/bulk-delete', [ProjectController::class, 'bulkDestroy']);
    Route::get('/projects/{project}/partners', [ProjectController::class, 'getProjectPartners'])
    ->name('projects.partners');

    Route::prefix('/delivery-requests')->group(function () {
        Route::get('/{id}/pdf-revise', [DeliveryRequestController::class, 'pdfRevise']);
        Route::get('/{id}/pdf-download', [DeliveryRequestController::class, 'pdfDownload']);
        Route::get('/meta', [DeliveryRequestController::class, 'enums']);
        Route::post('/bulk-delete', [DeliveryRequestController::class, 'bulkDelete']);
        Route::get('/', [DeliveryRequestController::class, 'index']);
        Route::get('/list', [DeliveryRequestController::class, 'getDeliveryRequests']);
        // Nouvelle route: toutes les demandes de livraison sans pagination

        Route::get('/all-full', [DeliveryRequestController::class, 'allFull']);
        Route::get('/{deliveryRequest}', [DeliveryRequestController::class, 'show']);
        Route::post('/', [DeliveryRequestController::class, 'store']);
        Route::post('/{deliveryRequest}', [DeliveryRequestController::class, 'update']);
        Route::delete('/{deliveryRequest}', [DeliveryRequestController::class, 'destroy']);
        Route::post('/{id}/restore', [DeliveryRequestController::class, 'restore']);
        Route::patch('/{deliveryRequest}/validate', [DeliveryRequestController::class, 'validateDeliveryRequest']);
    });

    Route::apiResource('nature-partners', NaturePartnerController::class);
    Route::apiResource('structure-partners', StructurePartnerController::class);
    Route::apiResource('status-partners', StatusPartnerController::class);
    Route::post('partners/bulk-delete', [PartnerController::class, 'bulkDelete']);
    Route::put('partners/bulk-restore', [PartnerController::class, 'bulkRestore']);
    Route::post('partners/{partner}/validate', [PartnerController::class, 'validatePartner']);
    Route::post('partners/{partner}/activate', [PartnerController::class, 'activatePartner']);
    Route::post('partners/{partner}/close', [PartnerController::class, 'closePartner']);
    Route::post('partners/{partner}/notes', [PartnerController::class, 'addNote']);
    Route::apiResource('partners', PartnerController::class);

    Route::get('users', [SiteController::class, 'getSiteUsers']);
    Route::prefix('geographic')->group(function () {
        Route::get('countries', [GeographicController::class, 'getCountries']);
        Route::get('regions', [GeographicController::class, 'getRegions']);
        Route::get('provinces/{regionId?}', [GeographicController::class, 'getProvinces']);
        Route::get('cercles/{provinceId?}', [GeographicController::class, 'getCercles']);
        Route::get('communes/{cercleId?}', [GeographicController::class, 'getCommunes']);
        Route::get('communes-by-province/{provinceId?}', [GeographicController::class, 'getCommunesByProvince']);
        Route::get('douars/{communeId?}', [GeographicController::class, 'getDouars']);
        Route::get('units-by-douar/{douarId?}', [GeographicController::class, 'getUnitsByDouar']);
    });
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::post('/', [ArticleController::class, 'store']);
        Route::get('unit-options', [ArticleController::class, 'getUnitOptions']);
        Route::get('{id}', [ArticleController::class, 'show']);
        Route::put('{id}', [ArticleController::class, 'update']);
        Route::patch('{id}/brand', [ArticleController::class, 'updateBrand']);
        Route::delete('{id}', [ArticleController::class, 'destroy']);
        Route::post('bulk-delete', [ArticleController::class, 'bulkDestroy']);
        Route::post('{id}/restore', [ArticleController::class, 'restore']);
    });
    Route::prefix('packs')->group(function () {
        Route::get('/', [PackController::class, 'index']);
        Route::post('/', [PackController::class, 'store']);
        Route::get('/{pack}', [PackController::class, 'show']);
        Route::put('/{pack}', [PackController::class, 'update']);
        Route::delete('/{pack}', [PackController::class, 'destroy']);
        Route::post('bulk-delete', [PackController::class, 'bulkDestroy']);
        Route::post('/{pack}/restore', [PackController::class, 'restore']);
    });
    Route::prefix('budget-categories')->group(function () {
        Route::get('/', [BudgetCategoryController::class, 'index']);
        Route::get('/{id}', [BudgetCategoryController::class, 'show']);
        Route::post('/', [BudgetCategoryController::class, 'store']);
        Route::put('/{id}', [BudgetCategoryController::class, 'update']);
        Route::delete('/{id}', [BudgetCategoryController::class, 'destroy']);
        Route::put('/{id}/restore', [BudgetCategoryController::class, 'restore']);
        Route::post('/bulk-delete', [BudgetCategoryController::class, 'bulkDestroy']);
    });
     Route::get("collaborators/educators", [CollaboratorController::class, 'getEducators']);
    Route::apiResource('collaborators', CollaboratorController::class);
    Route::get('/fetchCollaborators', [CollaboratorController::class, 'fetchCollaborators']);
    Route::prefix('collaborators')->group(function () {
       Route::get('/isCollabSupervisor/{createdBy}', [CollaboratorController::class, 'isCollabSupervisor']);
        Route::put('/{collaborator}', [CollaboratorController::class, 'update']);
        Route::post('/bulk-delete', [CollaboratorController::class, 'bulkDelete']);
        Route::post("/{id}/restore", [CollaboratorController::class, 'restore']);
        Route::get("/{collaborator}/archived", [CollaboratorController::class, 'showDeletedCollaborators']);
        Route::post("/{id}/projects", [CollaboratorController::class, 'assignProjects']);
        Route::get("/{id}/projects", [CollaboratorController::class, 'getProjects']);
        Route::get("/{collaborator}/user", [CollaboratorController::class, 'getCollaboratorByUserID']);
        Route::post('/bulk', [CollaboratorController::class, 'storeBulk']);

    });



    Route::apiResource('collaborator-status', CollaboratorStatusController::class);

    Route::prefix('collaborator-status')->group(function () {
        Route::post('/bulk-delete', [CollaboratorStatusController::class, 'bulkDelete']);
        Route::post("/{id}/restore", [CollaboratorStatusController::class, 'restore']);
    });

    Route::apiResource('contract-status', ContractStatusController::class);
    Route::prefix('contract-status')->group(function () {
        Route::post('/bulk-delete', [ContractStatusController::class, 'bulkDelete']);
        Route::post("/{id}/restore", [ContractStatusController::class, 'restore']);
    });
    Route::apiResource('contract-type', ContractTypesController::class);
    Route::prefix('contract-type')->group(function () {
        Route::post('/bulk-delete', [ContractTypesController::class, 'bulkDelete']);
        Route::post("/{id}/restore", [ContractTypesController::class, 'restore']);
    });
    Route::get('/packs', [PackController::class, 'index']);
    Route::get('/packs/{pack}/products', [PackController::class, 'getProducts']);
    Route::post('/packs', [PackController::class, 'store']);
    Route::get('/packs/{pack}', [PackController::class, 'show']);
    Route::put('/packs/{pack}', [PackController::class, 'update']);
    Route::delete('/packs/{pack}', [PackController::class, 'destroy']);
    Route::prefix('budget-lines')->group(function () {
        Route::get('/', [BudgetLigneController::class, 'index']);
        Route::get('/options', [BudgetLigneController::class, 'options']);
        Route::get('/{id}', [BudgetLigneController::class, 'show']);
        Route::post('/', [BudgetLigneController::class, 'store']);
        Route::put('/{id}', [BudgetLigneController::class, 'update']);
        Route::delete('/{id}', [BudgetLigneController::class, 'destroy']);
        Route::put('/{id}/restore', [BudgetLigneController::class, 'restore']);
        Route::post('/bulk-delete', [BudgetLigneController::class, 'bulkDestroy']);
    });
    Route::post('sites/bulk-delete', [SiteController::class, 'bulkDelete']);
    Route::apiResource('sites', SiteController::class);
    Route::prefix('geographic')->group(function () {
        Route::get('regions', [GeographicController::class, 'getRegions']);
        Route::get('provinces/{regionId?}', [GeographicController::class, 'getProvinces']);
        Route::get('cercles/{provinceId?}', [GeographicController::class, 'getCercles']);
        Route::get('communes/{cercleId?}', [GeographicController::class, 'getCommunes']);
        Route::get('douars/{communeId?}', [GeographicController::class, 'getDouars']);
    });
    Route::post('import/geographic-data', [ImportController::class, 'importGeographicData']);
    Route::prefix('tasks')->group(function () {
        Route::get('/by-pedagogical-project/{projectId}/{collabId}', [TaskController::class, 'getTasksGroupedByPedagogicalProject']);
        Route::get('/SmallSection',[TaskController::class, 'getPaginatedSmallSection']);
        Route::get('/PreSchool',[TaskController::class, 'getPaginatedPreSchool']);
        Route::get('/kader-tasks',[TaskController::class, 'getPaginatedKader']);
        Route::get('/support-plan',[TaskController::class,'getPaginatedSupportPlan']);
        Route::get('/create-options', [TaskController::class, 'create'])->name('tasks.createOptions');
        Route::post('/toggle-activation', [TaskController::class, 'toggleActivation'])->name('tasks.toggleActivation');
        Route::get('/by-phase/{projectId}', [TaskController::class, 'getTasksByPhase'])
            ->name('tasks.byPhase');
        Route::get('/by-collaborator', [TaskController::class, 'getTasksByCollaborator']);
        Route::post('/projects/{id}/regenerate-tasks', [ProjectController::class, 'regenerateTasks']);
    });
    Route::apiResource('tasks', TaskController::class);

    Route::prefix('banks')->group(function () {
        Route::get('/', [BankController::class, 'index']);
        Route::post('/', [BankController::class, 'store']);
        Route::get('{id}', [BankController::class, 'show']);
        Route::put('{id}', [BankController::class, 'update']);
        Route::delete('{id}', [BankController::class, 'destroy']);
        Route::put('/{id}/restore', [BankController::class, 'restore']);
        Route::post('bulk-delete', [BankController::class, 'bulkDelete']);
    });

    Route::prefix('invoices')->group(function () {
        Route::bind('invoice', function ($value) {
            return Invoice::withTrashed()->findOrFail($value);
        });

        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/options', [InvoiceController::class, 'options']);
        Route::get('{invoice}', [InvoiceController::class, 'show']);
        Route::put('{invoice}', [InvoiceController::class, 'update']);
        Route::delete('{invoice}', [InvoiceController::class, 'destroy']);
        Route::post('/bulk-delete', [InvoiceController::class, 'bulkDelete']);
        Route::post('/{invoice}/restore', [InvoiceController::class, 'restore']);
        Route::post('/unreceived', [InvoiceController::class, 'storeUnreceived']);

        // Use this endpoint to process existing unreceived invoices and mark them as "received"
        Route::post('/received', [InvoiceController::class, 'storeReceived']);
    });

    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::get('/{leave}', [LeaveController::class, 'show']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::put('{leave}', [LeaveController::class, 'update']);
        Route::delete('{leave}', [LeaveController::class, 'destroy']);
        Route::post('bulk-delete', [LeaveController::class, 'bulkDelete']);
        Route::post('{leave}/restore', [LeaveController::class, 'restore']);
        Route::post('{leave}/validate', [LeaveController::class, 'validateLeave']);
    });

    Route::apiResource('leave-types', LeaveTypeController::class);
    Route::prefix('leave-types')->group(function () {
        Route::post('/bulk-delete', [LeaveTypeController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [LeaveTypeController::class, 'restore']);
    });
    /*class*/
    //status
    Route::apiResource('class-status', ClassStatusController::class);
    Route::prefix('class-status')->group(function () {
        Route::post('/{id}/restore', [ClassStatusController::class, 'restore']);
        Route::post('/bulk-delete', [ClassStatusController::class, 'bulkDelete']);
    });
    //types
    Route::apiResource('class-types', ClassTypesController::class);
    Route::prefix('class-types')->group(function () {
        Route::post('/{id}/restore', [ClassTypesController::class, 'restore']);
        Route::post('/bulk-delete', [ClassTypesController::class, 'bulkDelete']);
    });
    //class
    Route::prefix('class')->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::get('/{project_class}', [ClassController::class, 'show']);
        Route::post('/', [ClassController::class, 'store']);
        Route::put('{project_class}', [ClassController::class, 'update']);
        Route::delete('{project_class}', [ClassController::class, 'destroy']);
        Route::post('/bulk-delete', [ClassController::class, 'bulkDelete']);
        Route::post('/{project_class}/restore', [ClassController::class, 'restore']);
        Route::get('/{project_class}/beneficiaries/count', [BeneficiaryController::class, 'countByClass']);

        // Operational tracking routes
        Route::get('/operational-tracking/options', [ClassController::class, 'getOperationalOptions']);
        Route::put('/{project_class}/state', [ClassController::class, 'updateState']);
        Route::put('/{project_class}/status', [ClassController::class, 'updateStatus']);
        Route::get('/{project_class}/status-history', [ClassController::class, 'getStatusHistory']);
    });

    Route::get('/classes/{id}/resources', [ClassController::class, 'getClassResources']);
    Route::get('/classes/{id}/resources/archive', [ClassController::class, 'getClassResourcesArchive']);

        // Class Resource History Routes
    Route::prefix('class-history')->group(function () {
        Route::get('/class/{class}', [ClassResourceHistoryController::class, 'getClassHistory']); // Get all history for a class
        Route::get('/class/{classId}/resource/{classResourceId}', [ClassResourceHistoryController::class, 'getResourceHistory']); // Get history for a specific resource
        Route::get('/statistics/class/{class}', [ClassResourceHistoryController::class, 'getStatistics']); // Get statistics
        Route::get('/{history}', [ClassResourceHistoryController::class, 'show']); // Get single history record
    });
    // programme pedagogique CORP
    Route::prefix('programme-pedagogique')->group(function () {
        Route::get('/options', [ProgrammePedagogiqueController::class, 'options']);
        Route::get('/', [ProgrammePedagogiqueController::class, 'index']);
        Route::get('/{programme_pedagogique}', [ProgrammePedagogiqueController::class, 'show']);
        Route::post('/', [ProgrammePedagogiqueController::class, 'store']);
        Route::put('/{programme_pedagogique}', [ProgrammePedagogiqueController::class, 'update']);
        Route::delete('/{programme_pedagogique}', [ProgrammePedagogiqueController::class, 'destroy']);
        Route::post('/bulk-delete', [ProgrammePedagogiqueController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [ProgrammePedagogiqueController::class, 'restore']);
    });
    Route::post('units/bulk-delete', [UnitController::class, 'destroy']);
    Route::post('units/bulk-delete', [UnitController::class, 'toggleActivation']);
    Route::get('units/create', [UnitController::class, 'create']);
    Route::get('units/{id}/edit', [UnitController::class, 'edit']);
    Route::apiResource('units', UnitController::class);
    Route::post('cycles/bulk-delete', [CycleController::class, 'bulkDelete']);
    Route::apiResource('cycles', CycleController::class);
    Route::post('levels/bulk-delete', [LevelController::class, 'bulkToggleStatus']);
    Route::get('level-form-options', [LevelController::class, 'getFormOptions']);
    Route::apiResource('levels', LevelController::class);
    Route::get('units/region/{regionId}', [UnitController::class, 'getFilteredUnits']);

    Route::get('/download/{filename}/{directory}', DownloadFileController::class)
        ->where('directory', '.*');

    Route::prefix('absences')->group(function () {
        Route::get('/', [AbsenceController::class, 'index']);
        Route::post('/', [AbsenceController::class, 'store']);
        Route::get('/options', [AbsenceController::class, 'options']);
        Route::get('{absence}', [AbsenceController::class, 'show']);
        Route::put('{id}', [AbsenceController::class, 'update']);
        Route::delete('{id}', [AbsenceController::class, 'destroy']);
        Route::put('/{id}/restore', [AbsenceController::class, 'restore']);
        Route::post('bulk-delete', [AbsenceController::class, 'bulkDelete']);
    });

    Route::prefix('calltenders')->group(function () {
        Route::get('/download-file/{fileName}', [CalltenderController::class, 'downloadFile']); // Move this to the top
        Route::get('/', [CalltenderController::class, 'index']);
        Route::post('/', [CalltenderController::class, 'store']);
        Route::get('{id}', [CalltenderController::class, 'show'])->where('id', '[0-9]+');
        Route::put('{id}', [CalltenderController::class, 'update']);
        Route::delete('{id}', [CalltenderController::class, 'destroy']);
        Route::put('/{id}/restore', [CalltenderController::class, 'restore']);
        Route::post('bulk-delete', [CalltenderController::class, 'bulkDelete']);
    });

    Route::prefix('avenants')->group(function () {
        Route::get('/download-file/{fileName}', [AvenantController::class, 'downloadFile']);
        Route::get('/', [AvenantController::class, 'index']);
        Route::post('/', [AvenantController::class, 'store']);
        Route::get('{id}', [AvenantController::class, 'show'])->where('id', '[0-9]+');
        Route::put('{id}', [AvenantController::class, 'update']);
        Route::delete('{id}', [AvenantController::class, 'destroy']);
        Route::put('/{id}/restore', [AvenantController::class, 'restore']);
        Route::post('bulk-delete', [AvenantController::class, 'bulkDelete']);
    });

    Route::prefix('positions')->group(function () {
        Route::get('/', [PositionController::class, 'index']);
        Route::post('/', [PositionController::class, 'store']);
        Route::get('{position}', [PositionController::class, 'show']);
        Route::put('{position}', [PositionController::class, 'update']);
        Route::delete('{position}', [PositionController::class, 'destroy']);
        Route::post('/bulk-delete', [PositionController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [PositionController::class, 'restore']);
    });
    Route::prefix('departements')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('{departement}', [DepartmentController::class, 'show']);
        Route::put('{departement}', [DepartmentController::class, 'update']);
        Route::delete('{departement}', [DepartmentController::class, 'destroy']);
        Route::post('/bulk-delete', [DepartmentController::class, 'bulkDestroy']);
        Route::post('/{id}/restore', [DepartmentController::class, 'restore']);
    });

    Route::prefix('category-types')->group(function () {

        Route::get('/', [CategoryTypeController::class, 'index']);
        Route::post('/', [CategoryTypeController::class, 'store']);
        Route::get('{id}', [CategoryTypeController::class, 'show']);
        Route::put('{id}', [CategoryTypeController::class, 'update']);
        Route::delete('{id}', [CategoryTypeController::class, 'destroy']);
        Route::post('bulk-delete', [CategoryTypeController::class, 'bulkDelete']);
        Route::put('{id}/restore', [CategoryTypeController::class, 'restore']);
    });

    Route::prefix('groups')->group(function () {
        Route::get('/options', [GroupController::class, 'options']);
        Route::get('/classes/{classId}/groups', [GroupController::class, 'getGroupsByClass']);
        Route::post('/bulk-delete', [GroupController::class, 'bulkDelete']);
        Route::put('/{id}/restore', [GroupController::class, 'restore']);
        //get levels based on class cycle
        Route::get('/class/{classId}/levels', [GroupController::class, 'getLevelsByClassCycle']);
    });
    Route::apiResource('groups', GroupController::class);

    Route::prefix('group-types')->group(function () {
        Route::get('/', [GroupTypeController::class, 'index']);
        Route::post('/', [GroupTypeController::class, 'store']);
        Route::get('{id}', [GroupTypeController::class, 'show']);
        Route::put('{id}', [GroupTypeController::class, 'update']);
        Route::delete('{id}', [GroupTypeController::class, 'destroy']);
        Route::post('bulk-delete', [GroupTypeController::class, 'bulkDelete']);
        Route::put('{id}/restore', [GroupTypeController::class, 'restore']);
    });

    Route::prefix('parents')->group(function () {
        Route::get('/options', [ParentController::class, 'options']);
        Route::post('/bulk-delete', [ParentController::class, 'bulkDelete']);
        Route::put('/{id}/restore', [ParentController::class, 'restore']);
    });
    Route::apiResource('parents', ParentController::class);

    Route::prefix('mission-orders')->group(function () {
        Route::get('/options', [MissionOrderController::class, 'options']);
        Route::post('/bulk-delete', [MissionOrderController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [MissionOrderController::class, 'restore']);
        Route::post('/{id}/approve', [MissionOrderController::class, 'approve']);
        Route::post('/{id}/refuse', [MissionOrderController::class, 'refuse']);
    });
    Route::apiResource('mission-orders', MissionOrderController::class);

    Route::get('beneficiaries/create', [BeneficiaryController::class, 'create']);
    Route::get('beneficiaries/assurance/export', [BeneficiaryController::class, 'exportAssurance']);
    Route::post('beneficiaries/assurance/import', [BeneficiaryController::class, 'importAssurance']);
    Route::apiResource('beneficiaries', BeneficiaryController::class);
    Route::post('beneficiaries/toggle-activation', [BeneficiaryController::class, 'toggleActivation']);
    Route::get('beneficiaries/{beneficiary}/edit', [BeneficiaryController::class, 'edit']);
    Route::prefix('beneficiaries/{beneficiaryId}')->group(function () {
        Route::get('/vision-test', [VisionTestController::class, 'show']);
        Route::post('/vision-test', [VisionTestController::class, 'upsert']);

        Route::get('/export', [BeneficiaryController::class, 'exportBeneficiaryData']);
        Route::post('/import', [BeneficiaryController::class, 'importBeneficiaryData']);
    });
    Route::patch('/beneficiaries/{beneficiary}/validate', [BeneficiaryController::class, 'validateBeneficiary']);

    Route::prefix('beneficiaries/{beneficiaryId}')->group(function () {
        Route::get('pediatre', [PediatreTestController::class, 'show']);
        Route::post('pediatre', [PediatreTestController::class, 'upsert']);
    });

    Route::prefix('beneficiaries/{beneficiaryId}')->group(function () {
        Route::get('dentaire', [DentaireTestController::class, 'show']);
        Route::post('dentaire', [DentaireTestController::class, 'upsert']);
    });

    Route::prefix('beneficiaries/{beneficiaryId}')->group(function () {
        Route::get('orl-test', [OrlTestController::class, 'show']);
        Route::post('orl-test', [OrlTestController::class, 'upsert']);
    });

    Route::get('/consultations/export', [ConsultationExportController::class, 'export']);

    Route::prefix('beneficiaries/{beneficiaryId}')->group(function () {
        Route::get('vaccin', [VaccinTestController::class, 'show']);
        Route::post('vaccin', [VaccinTestController::class, 'upsert']);
    });

    Route::prefix('programs')->group(function () {
        Route::get('/create', [ProgramController::class, 'create'])->name('programs.create');
        Route::post('/toggle-activation', [ProgramController::class, 'toggleActivation'])->name('programs.toggleActivation');
    });
    Route::get('intervention-axes/{interventionAxis}/programs', [ProgramController::class, 'getProgramsByInterventionAxis'])
        ->whereNumber('interventionAxis');
    Route::get('programs/{program}/program-types', [ProgramController::class, 'getProgramTypes'])
        ->whereNumber('program');
    Route::apiResource('programs', ProgramController::class);

    Route::apiResource('request-type', RequestTypeController::class);
    Route::get('request-types', [RequestTypeController::class, 'getRequestTypes']);
    Route::prefix('request-type')->group(function () {
        Route::post('/bulk-delete', [RequestTypeController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [RequestTypeController::class, 'restore']);
    });
    Route::get('requests', [RequestController::class, 'getRequests']);
    Route::prefix('request')->group(function () {
        Route::get("/", [RequestController::class, "index"]);
        Route::get('/{id}', [RequestController::class, 'show']);
        Route::post("/", [RequestController::class, 'store']);
        Route::put("/{requestModel}", [RequestController::class, 'update']);
        Route::delete("/{requestModel}", [RequestController::class, 'destroy']);
        Route::post('/bulk-delete', [RequestController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [RequestController::class, 'restore']);
    });

    Route::apiResource('purchase-requests', PurchaseRequestController::class);
    Route::prefix('purchase-requests')->group(function () {
        Route::patch('/{id}/update-status', [PurchaseRequestController::class, 'updateStatus']);
        Route::put("/{id}/restore", [PurchaseRequestController::class, 'restore']);
        Route::post('/bulk-delete', [PurchaseRequestController::class, 'bulkDelete']);
        Route::patch('/{purchaseRequest}/validate', [PurchaseRequestController::class, 'validatePurchaseRequest']);

    });
    Route::get('evaluation-grids', [EvaluationGridController::class, 'getEvaluationGrids']);
    Route::prefix('evaluation-grid')->group(function () {
        Route::get('/', [EvaluationGridController::class, 'index']);
        Route::post('/', [EvaluationGridController::class, 'store']);
        Route::get('{evaluationGridModel}', [EvaluationGridController::class, 'show']);
        Route::post('{evaluationGridModel}', [EvaluationGridController::class, 'update']);
        Route::delete('{evaluationGridModel}', [EvaluationGridController::class, 'destroy']);
        Route::post('/bulk-delete', [EvaluationGridController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [EvaluationGridController::class, 'restore']);
    });

    Route::get('evaluation-criterias', [EvaluationCriteriaController::class, 'getEvaluationCriteria']);
    Route::prefix('evaluation-criteria')->group(function () {
        Route::get('/', [EvaluationCriteriaController::class, 'index']);
        Route::post('/', [EvaluationCriteriaController::class, 'store']);
        Route::get('{evaluationCriteriaModel}', [EvaluationCriteriaController::class, 'show']);
        Route::put('{evaluationCriteriaModel}', [EvaluationCriteriaController::class, 'update']);
        Route::delete('{evaluationCriteriaModel}', [EvaluationCriteriaController::class, 'destroy']);
        Route::post('/bulk-delete', [EvaluationCriteriaController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [EvaluationCriteriaController::class, 'restore']);
    });

    Route::prefix('suppliers')->group(
        function () {
            Route::get('/download-document/{fileName}', [SupplierController::class, 'downloadDocument'])
                ->where('fileName', '.*');
            Route::get('/', [SupplierController::class, 'index']);
            Route::post('/', [SupplierController::class, 'store']);
            Route::get('{id}', [SupplierController::class, 'show']);
            Route::put('{id}', [SupplierController::class, 'update']);
            Route::delete('{id}', [SupplierController::class, 'destroy']);
            Route::put('{id}/restore', [SupplierController::class, 'restore']);
            Route::post('bulk-delete', [SupplierController::class, 'bulkDelete']);
        }
    );
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/me/collaborator', [AuthController::class, 'currentCollaborator']);
    Route::post('/me/change-password', [AuthController::class, 'changePassword']);
    Route::prefix('purchase-orders')->group(function () {
        // Récupérer tous les bons de commande liés à une demande d'achat (sans pagination)
 Route::get('/by-purchase-request/{purchaseRequestId}', 
        [PurchaseOrderController::class, 'getByPurchaseRequest']
    )->whereNumber('purchaseRequestId');        
    Route::get('/items/{purchaseOrderId}',[PurchaseOrderController::class,'getPurchaseOrderLines']);
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::post('/{id}/terms-file', [PurchaseOrderController::class, 'updateTermsFile']);
        Route::post('/', [PurchaseOrderController::class, 'store']);
        Route::get('/options', [PurchaseOrderController::class, 'options']);
        Route::get('/{purchase_order}', [PurchaseOrderController::class, 'show'])->whereNumber('purchase_order');
        Route::put('/{id}', [PurchaseOrderController::class, 'update']);
        Route::get('/quote/{quoteId}', [PurchaseOrderController::class, 'quotePurchaseRequests']);
        Route::get('/quote/{quoteId}/purchase-request/{purchaseRequestId}/items', [PurchaseOrderController::class, 'quotePurchaseRequestItems']);
        Route::delete('/purchase-orders/{id}', [PurchaseOrderController::class, 'destroy']);
        Route::get('/{id}/pdf/download', [PurchaseOrderController::class, 'pdfDownload']);
        Route::get('/{id}/pdf/preview',[PurchaseOrderController::class, 'pdfPreview']);



        Route::delete('/{id}', [PurchaseOrderController::class, 'destroy']);
        Route::post('/bulk-delete', [PurchaseOrderController::class, 'bulkDelete']);
        Route::put('/{id}/restore', [PurchaseOrderController::class, 'restore']);
        Route::post('/{id}/validate', [PurchaseOrderController::class, 'validatePurchaseOrder']);
    });

    Route::get('service-orders/sources', [ServiceOrderController::class, 'getSources']);
    Route::apiResource('service-orders', ServiceOrderController::class);
    Route::post('service-orders/{id}/restore', [ServiceOrderController::class, 'restore'])->withTrashed();
    Route::delete('service-orders/{service_order}/document', [ServiceOrderController::class, 'deleteDocument']);
    Route::post('/service-orders/bulk-delete', [ServiceOrderController::class, 'bulkDelete'])
     ->name('service-orders.bulk-delete');
     Route::get('service-orders/{id}/pdfpreview', [ServiceOrderController::class, 'pdfPreview']);
     Route::get('service-orders/{id}/pdfdownload', [ServiceOrderController::class, 'pdfDownload']);


    Route::prefix('quotes')->group(function () {
        Route::post('/{quote}/validate', [QuoteController::class, 'validateQuote']);
        Route::post('/{quote}/reject',   [QuoteController::class, 'rejectQuote']);
        Route::get('/', [QuoteController::class, 'index']);
        Route::get('/options', [QuoteController::class, 'options']);
        Route::post('/', [QuoteController::class, 'store']);
        Route::get('{quote}', [QuoteController::class, 'show']);
        Route::put('{quote}/restore', [QuoteController::class, 'restore']);
        Route::put('{quote}', [QuoteController::class, 'update']);
        Route::delete('{quote}', [QuoteController::class, 'destroy']);
        Route::post('/bulk-delete', [QuoteController::class, 'bulkDelete']);
    });

    Route::prefix('purchase-lists')->group(function () {
        Route::get('/', [PurchaseListController::class, 'index']);
        Route::get('/{id}/pdf-preview', [PurchaseListController::class, 'previewPdf']);
        Route::get('/{id}/pdf-download', [PurchaseListController::class, 'downloadPdf']);
        Route::post('/{purchaseList}/validate', [PurchaseListController::class, 'validatePurchaseList']);
        Route::post('/{purchaseList}/reject',   [PurchaseListController::class, 'rejectPurchaseList']);
         Route::get('/{purchaseList}/export-rfq', [PurchaseListController::class, 'exportRFQ']);
        Route::get('/{id}/lines',[PurchaseListController::class, 'getPurchaseRequestLinesByPurchaseListId']);
        Route::get('/options', [PurchaseListController::class, 'options']);
        Route::post('/', [PurchaseListController::class, 'store']);
        Route::get('{id}', [PurchaseListController::class, 'show']);
        Route::put('{id}', [PurchaseListController::class, 'update']);
        Route::delete('{id}', [PurchaseListController::class, 'destroy']);
        Route::post('/bulk-delete', [PurchaseListController::class, 'bulkDestroy']);
        Route::post('/{id}/restore', [PurchaseListController::class, 'restore']);
         Route::patch('/{purchaseList}/validate', [PurchaseListController::class, 'validatePurchaseRequest']);
    });
    Route::delete('purchase-lists/bulk', [PurchaseListController::class, 'bulkDestroy']);

    Route::prefix('cabinets')->group(function () {
        Route::bind('cabinet', function ($value) {
            return Cabinet::withTrashed()->findOrFail($value);
        });
        Route::get('/', [CabinetController::class, 'index']);
        Route::get('/options', [CabinetController::class, 'options']);
        Route::post('/', [CabinetController::class, 'store']);
        Route::get('{cabinet}', [CabinetController::class, 'show']);
        Route::put('{cabinet}', [CabinetController::class, 'update']);
        Route::delete('{cabinet}', [CabinetController::class, 'destroy']);
        Route::post('/bulk-delete', [CabinetController::class, 'bulkDestroy']);
        Route::post('/{cabinet}/restore', [CabinetController::class, 'restore']);
    });

    Route::get('/evaluations/meta', [EvaluationMetaController::class, 'enums']);
    Route::get('/evaluations-operations/meta', [EvaluationOperationsMetaController::class, 'enums']);
    Route::get('evaluations-operations/meta/criteria', [EvaluationOperationsMetaController::class, 'enumsEvaluationCriteria']);

    Route::get('/evaluations/list', [EvaluationController::class, 'list']);
    Route::prefix('evaluations')->group(function () {
        Route::get('/', [EvaluationController::class, 'index']);
        Route::post('/', [EvaluationController::class, 'store']);
        Route::get('{evaluation}', [EvaluationController::class, 'show']);
        Route::post('{evaluation}', [EvaluationController::class, 'update']);
        Route::delete('{evaluation}', [EvaluationController::class, 'destroy']);
        Route::post('bulk-delete', [EvaluationController::class, 'bulkDestroy']);
        Route::post('{id}/restore', [EvaluationController::class, 'restore']);
        Route::post('{id}/calculate-score', [EvaluationController::class, 'calculateScore']);
        Route::get('{id}/criteria', [EvaluationController::class, 'getEvaluationWithCriteria']);
    });
    Route::prefix('trainings')->group(function () {
        Route::get('/', [TrainingController::class, 'index']);
        Route::get('/options', [TrainingController::class, 'options']);
        Route::post('/', [TrainingController::class, 'store']);
        Route::get('{id}', [TrainingController::class, 'show']);
        Route::put('{id}', [TrainingController::class, 'update']);
        Route::delete('{id}', [TrainingController::class, 'destroy']);
        Route::post('/bulk-delete', [TrainingController::class, 'bulkDestroy']);
        Route::post('/{id}/restore', [TrainingController::class, 'restore']);
    });
    Route::get('evaluation-grid-operations', [EvaluationGridOperationController::class, 'list']);;
    Route::prefix('evaluation-grid-operation')->group(function () {
        Route::get('/task-evaluations', [EvaluationGridOperationController::class, 'getTaskEvaluations']);
        Route::post('/', [EvaluationGridOperationController::class, 'store']);
        Route::get('/', [EvaluationGridOperationController::class, 'index']);
        Route::get('{evaluationGridOperation}', [EvaluationGridOperationController::class, 'show']);
        Route::post('{evaluationGridOperation}', [EvaluationGridOperationController::class, 'update']);
        Route::delete('{evaluationGridOperation}', [EvaluationGridOperationController::class, 'destroy']);
        Route::post('bulk-delete', [EvaluationGridOperationController::class, 'bulkDestroy']);
        Route::post('{id}/restore', [EvaluationGridOperationController::class, 'restore']);
    });

    Route::prefix('training-groups')->group(function () {
        Route::get('/', [TrainingGroupController::class, 'index']);
        Route::get('/options', [TrainingGroupController::class, 'options']);

        // La route spécifique doit être avant la route avec paramètre catch-all
        Route::get('/search-participant', [TrainingGroupController::class, 'searchByCin']);

        Route::post('/', [TrainingGroupController::class, 'store']);
        Route::get('{trainingGroup}', [TrainingGroupController::class, 'show']);
        Route::put('{trainingGroup}', [TrainingGroupController::class, 'update']);
        Route::delete('{trainingGroup}', [TrainingGroupController::class, 'destroy']);
        Route::post('/bulk-delete', [TrainingGroupController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [TrainingGroupController::class, 'restore']);
    });

    Route::prefix('reports')->group(function () {
        Route::bind('report', function ($value) {
            return Report::withTrashed()->findOrFail($value);
        });
        Route::get('/download-file/{fileName}', [ReportController::class, 'downloadFile']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/', [ReportController::class, 'index']);
        Route::get('{report}', [ReportController::class, 'show']);
        Route::put('{report}', [ReportController::class, 'update']);
        Route::delete('{report}', [ReportController::class, 'destroy']);
        Route::post('bulk-delete', [ReportController::class, 'bulkDelete']);
        Route::put('/{report}/restore', [ReportController::class, 'restore']);
    });
    Route::post('/presence-sheets/toggle-activation', [PresenceSheetController::class, 'toggleActivation'])
        ->name('presence-sheets.toggle-activation');
    Route::apiResource('presences', PresenceController::class);
    Route::apiResource('presence-sheets', PresenceSheetController::class);
    Route::get('/tasks/{task}/participants', [PresenceController::class, 'getParticipantsForTask'])
        ->name('tasks.participants');
    Route::post('/presences/bulk', [PresenceController::class, 'storeBulk'])
        ->name('presences.store.bulk');
    Route::get('/presence-form-options', [PresenceController::class, 'getFormOptions'])
        ->name('presences.form-options');
    Route::get('/presence-sheets/find', [PresenceSheetController::class, 'findByTaskAndDate']);
    Route::post('/presence-sheets/{presenceSheet}/validate', [PresenceSheetController::class, 'validateSheet']);

    Route::prefix('recruitment-requests')->group(function () {
        Route::bind('recruitment_request', function ($value) {
            return RecruitmentRequest::withTrashed()->findOrFail($value);
        });
        Route::get('/', [RecruitmentRequestController::class, 'index']);
        Route::post('/', [RecruitmentRequestController::class, 'store']);
        Route::get('{recruitment_request}', [RecruitmentRequestController::class, 'show']);
        Route::put('{recruitment_request}', [RecruitmentRequestController::class, 'update']);
        Route::delete('{recruitment_request}', [RecruitmentRequestController::class, 'destroy']);
        Route::post('bulk-delete', [RecruitmentRequestController::class, 'bulkDestroy']);
        Route::put('{recruitment_request}/restore', [RecruitmentRequestController::class, 'restore']);
        Route::post('{recruitment_request}/validate', [RecruitmentRequestController::class, 'validateRequest']);
        Route::post('{recruitment_request}/reject', [RecruitmentRequestController::class, 'rejectRequest']);
    });
    Route::get('evaluation-criteria-operations', [EvaluationCriteriaOperationController::class, 'getEvaluationCriteria']);
    Route::prefix('evaluation-criteria-operation')->group(function () {
        Route::post('/', [EvaluationCriteriaOperationController::class, 'store']);
        Route::get('/', [EvaluationCriteriaOperationController::class, 'index']);
        Route::get('{evaluation_criteria_operation}', [EvaluationCriteriaOperationController::class, 'show']);
        Route::put('{evaluation_criteria_operation}', [EvaluationCriteriaOperationController::class, 'update']);
        Route::delete('{evaluation_criteria_operation}', [EvaluationCriteriaOperationController::class, 'destroy']);
        Route::post('/bulk-delete', [EvaluationCriteriaOperationController::class, 'bulkDelete']);
        Route::post('/{id}/restore', [EvaluationCriteriaOperationController::class, 'restore']);
    });

    Route::get('/phases/form-options', [PhaseController::class, 'getFormOptions']);
    Route::get('phases/tag', [PhaseController::class, 'phasesBytag']);
    Route::apiResource('phases', PhaseController::class);
    Route::post('phases/toggle-activation', [PhaseController::class, 'toggleActivation']);
    Route::prefix('insurances')->group(function () {
        Route::bind('insurance', function ($value) {
            return Insurance::withTrashed()->findOrFail($value);
        });
        Route::get('insuranceTypes', [InsuranceController::class, 'getInsuranceTypes']);
        Route::get('/', [InsuranceController::class, 'index']);
        Route::post('/', [InsuranceController::class, 'store']);
        Route::get('{insurance}', [InsuranceController::class, 'show']);
        Route::put('{insurance}', [InsuranceController::class, 'update']);
        Route::delete('{insurance}', [InsuranceController::class, 'destroy']);
        Route::post('bulk-delete', [InsuranceController::class, 'bulkDestroy']);
        Route::put('{insurance}/restore', [InsuranceController::class, 'restore']);

    });
    Route::get('getEvaluators',[EvaluationOperationController::class,'getEvaluators']);
    Route::prefix('evaluation-operations')->group(function () {
        Route::get('list', [EvaluationOperationController::class, 'list']);
        Route::get('/', [EvaluationOperationController::class, 'index']);
        Route::get('{evaluationOperation}', [EvaluationOperationController::class, 'show']);
        Route::post('/', [EvaluationOperationController::class, 'store']);
        Route::post('{evaluationOperation}', [EvaluationOperationController::class, 'update']);
        Route::delete('{evaluationOperation}', [EvaluationOperationController::class, 'destroy']);
        Route::post('bulk-delete', [EvaluationOperationController::class, 'bulkDestroy']);
        Route::post('/{id}/restore', [EvaluationOperationController::class, 'restore']);
        Route::post('{id}/calculate-score', [EvaluationOperationController::class, 'calculateScore']);
        Route::get('{id}/criteria', [EvaluationOperationController::class, 'getEvaluationWithCriteria']);
        Route::get('/getEvaluators',[EvaluationOperationController::class,'getEvaluators']);
    });
    Route::get('/meta-evaluation-status', [EvaluationOperationController::class, 'getEvaluationsStatus']);
    Route::get('/sessions', [EvaluationOperationController::class, 'getSessions']);
    Route::prefix('trainee-collaborators')->group(function () {
        Route::bind('traineeCollaborator', function ($value) {
            return TraineeCollaborator::withTrashed()->findOrFail($value);
        });

        Route::get('/', [TraineeCollaboratorController::class, 'index']);
        Route::get('/options', [TraineeCollaboratorController::class, 'options']);
        Route::post('/', [TraineeCollaboratorController::class, 'store']);
        Route::get('{traineeCollaborator}', [TraineeCollaboratorController::class, 'show']);
        Route::put('{traineeCollaborator}', [TraineeCollaboratorController::class, 'update']);
        Route::delete('{traineeCollaborator}', [TraineeCollaboratorController::class, 'destroy']);
        Route::post('/bulk-delete', [TraineeCollaboratorController::class, 'bulkDelete']);
        Route::post('/{traineeCollaborator}/restore', [TraineeCollaboratorController::class, 'restore']);
    });
    Route::get('externals/form-options', [ExternalController::class, 'getFormOptions']);
    Route::post('external-trainers/toggle-activation', [ExternalTrainerController::class, 'toggleActivation']);
    Route::post('externals/toggle-activation', [ExternalController::class, 'toggleActivation']);
    Route::apiResource('externals', ExternalController::class);

    Route::prefix('internal-trainers')->group(function () {
        Route::bind('internalTrainer', function ($value) {
            return InternalTrainer::withTrashed()->findOrFail($value);
        });

        Route::get('/', [InternalTrainerController::class, 'index']);
        Route::get('/options', [InternalTrainerController::class, 'options']);
        Route::post('/', [InternalTrainerController::class, 'store']);
        Route::get('{internalTrainer}', [InternalTrainerController::class, 'show']);
        Route::put('{internalTrainer}', [InternalTrainerController::class, 'update']);
        Route::delete('{internalTrainer}', [InternalTrainerController::class, 'destroy']);
        Route::post('/bulk-delete', [InternalTrainerController::class, 'bulkDestroy']);
        Route::post('/{internalTrainer}/restore', [InternalTrainerController::class, 'restore']);
    });

    Route::prefix('job-postings')->group(function () {
        Route::bind('job_posting', function ($value) {
            return JobPosting::withTrashed()->findOrFail($value);
        });
        Route::get('/', [JobPostingController::class, 'index']);
        Route::post('/', [JobPostingController::class, 'store']);
        Route::get('{jobPosting}', [JobPostingController::class, 'show']);
        Route::put('{jobPosting}', [JobPostingController::class, 'update']);
        Route::delete('{jobPosting}', [JobPostingController::class, 'destroy']);
        Route::post('bulk-delete', [JobPostingController::class, 'bulkDestroy']);
        Route::put('{jobPosting}/restore', [JobPostingController::class, 'restore']);
    });

    Route::get('external-trainers/cabinet-options', [ExternalTrainerController::class, 'cabinetOptions']);
    Route::patch('external-trainers/{externalTrainer}/toggle-activation', [ExternalTrainerController::class, 'toggleActivation']);
    Route::apiResource('external-trainers', ExternalTrainerController::class);

    Route::get('medical-records/summary', [MedicalRecordController::class, 'summary']);
    Route::get('medical-records/meta', [MedicalRecordController::class, 'enums']);
    Route::prefix('medical-record')->group(function () {
        Route::post('/bulk-delete', [MedicalRecordController::class, 'bulkDelete']);
        Route::get('/', [MedicalRecordController::class, 'index']);
        Route::get('/{medicalRecord}', [MedicalRecordController::class, 'show']);
        Route::post('/', [MedicalRecordController::class, 'store']);
        Route::post('/{medicalRecord}', [MedicalRecordController::class, 'update']);
        Route::delete('/{medicalRecord}', [MedicalRecordController::class, 'destroy']);
        Route::post('/{id}/restore', [MedicalRecordController::class, 'restore']);
    });

    Route::get('hr/evaluations/meta', [EvaluationHrController::class, 'enums']);
    Route::get('hr/evaluations/list', [EvaluationHrController::class, 'getEvaluations']);
    Route::prefix('hr/evaluations')->group(function () {
        Route::post('/bulk-delete', [EvaluationHrController::class, 'bulkDelete']);
        Route::get('/', [EvaluationHrController::class, 'index']);
        Route::get('/{evaluationHr}', [EvaluationHrController::class, 'show']);
        Route::post('/', [EvaluationHrController::class, 'store']);
        Route::post('/{evaluationHr}', [EvaluationHrController::class, 'update']);
        Route::delete('/{evaluationHr}', [EvaluationHrController::class, 'destroy']);
        Route::post('/{id}/restore', [EvaluationHrController::class, 'restore']);
    });

    Route::prefix('expense-notes')->group(function () {
        Route::bind('expenseNote', function ($value) {
            return ExpenseNote::withTrashed()->findOrFail($value);
        });

        Route::get('/', [ExpenseNoteController::class, 'index']);
        Route::get('/options', [ExpenseNoteController::class, 'options']);
        Route::post('/', [ExpenseNoteController::class, 'store']);
        Route::get('{expenseNote}', [ExpenseNoteController::class, 'show']);
        Route::put('{expenseNote}', [ExpenseNoteController::class, 'update']);
        Route::delete('{expenseNote}', [ExpenseNoteController::class, 'destroy']);
        Route::post('/bulk-delete', [ExpenseNoteController::class, 'bulkDelete']);
        Route::post('/{expenseNote}/restore', [ExpenseNoteController::class, 'restore']);
        Route::put('/{expenseNote}/status', [ExpenseNoteController::class, 'updateStatus']);
    });

    Route::get('competency-grids/options', [CompetencyGridController::class, 'options']);
    Route::post('competency-grids/bulk-delete', [CompetencyGridController::class, 'bulkDestroy']);
    Route::post('competency-grids/{id}/restore', [CompetencyGridController::class, 'restore']);
    Route::apiResource('competency-grids', CompetencyGridController::class);

    Route::post('competency-criteria/toggle-activation', [CompetencyCriterionController::class, 'toggleActivation']);
    Route::apiResource('competency-criteria', CompetencyCriterionController::class);

    Route::prefix('modules')->group(function () {
        Route::bind('module', function ($value) {
            return Module::withTrashed()->findOrFail($value);
        });

        Route::get('/', [ModuleController::class, 'index']);
        Route::get('/options', [ModuleController::class, 'options']);
        Route::post('/', [ModuleController::class, 'store']);
        Route::get('{module}', [ModuleController::class, 'show']);
        Route::put('{module}', [ModuleController::class, 'update']);
        Route::delete('{module}', [ModuleController::class, 'destroy']);
        Route::post('/bulk-delete', [ModuleController::class, 'bulkDestroy']);
        Route::post('/{module}/restore', [ModuleController::class, 'restore']);
    });

    Route::prefix('candidates')->group(function () {
        Route::bind('candidate', function ($value) {
            return Candidate::withTrashed()->findOrFail($value);
        });
        Route::get('candidateSources', [CandidateController::class, 'getCandidateSources']);
        Route::get('candidateStatuses', [CandidateController::class, 'getCandidateStatus']);
        Route::get('/', [CandidateController::class, 'index']);
        Route::get('{candidate}', [CandidateController::class, 'show']);
        Route::delete('{candidate}', [CandidateController::class, 'destroy']);
        Route::post('bulk-delete', [CandidateController::class, 'bulkDestroy']);
        Route::put('/{candidate}/restore', [CandidateController::class, 'restore']);
        Route::post('/', [CandidateController::class, 'store']);
        Route::put('/{candidate}', [CandidateController::class, 'update']);
        Route::patch('/{candidate}/status', [CandidateController::class, 'changeStatus']);
        Route::post('/{candidate}/hire', [CollaboratorController::class, 'hire']);
    });

    Route::prefix('module-evaluations')->name('module-evaluations.')->group(function () {
        Route::get('/options', [ModuleEvaluationController::class, 'options'])->name('options');
        Route::post('/bulk-delete', [ModuleEvaluationController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/{id}/restore', [ModuleEvaluationController::class, 'restore'])->name('restore');
    });

    Route::apiResource('module-evaluations', ModuleEvaluationController::class);

    Route::get('training-sessions/form-options', [TrainingSessionController::class, 'getFormOptions']);
    Route::post('training-sessions/toggle-activation', [TrainingSessionController::class, 'toggleActivation']);
    Route::apiResource('training-sessions', TrainingSessionController::class);

    //Contract
    Route::get('/contracts/{id}/download', [ContractController::class, 'generate']);

    Route::get('assurances/form-options', [AssuranceController::class, 'getFormOptions']);
    Route::post('assurances/toggle-activation', [AssuranceController::class, 'toggleActivation']);
    Route::apiResource('assurances', AssuranceController::class);

    Route::get('assurances/export/uninsured', [AssuranceController::class, 'exportUninsured']);
    Route::post('assurances/import', [AssuranceController::class, 'import']);

    Route::post('compagnie-assurances/toggle-activation', [CompagnieAssuranceController::class, 'toggleActivation']);
    Route::apiResource('compagnie-assurances', CompagnieAssuranceController::class);

    Route::prefix('routes')->group(function () {
        Route::bind('route', function ($value) {
            return RouteModel::withTrashed()->findOrFail($value);
        });

        Route::get('/', [RouteController::class, 'index']);
        Route::get('/options', [RouteController::class, 'options']);
        Route::post('/', [RouteController::class, 'store']);
        Route::get('{route}', [RouteController::class, 'show']);
        Route::put('{route}', [RouteController::class, 'update']);
        Route::delete('{route}', [RouteController::class, 'destroy']);
        Route::post('/bulk-delete', [RouteController::class, 'bulkDestroy']);
        Route::post('/{route}/restore', [RouteController::class, 'restore']);
    });

    Route::prefix('/contacts')->group(function () {
        Route::get('/meta', [ContactPersonController::class, 'enums']);
        Route::post('/bulk-delete', [ContactPersonController::class, 'bulkDelete']);
        Route::get('/', [ContactPersonController::class, 'index']);
        Route::get('/list', [ContactPersonController::class, 'getContacts']);
        Route::get('/{contact}', [ContactPersonController::class, 'show']);
        Route::post('/', [ContactPersonController::class, 'store']);
        Route::post('/{contact}', [ContactPersonController::class, 'update']);
        Route::delete('/{contact}', [ContactPersonController::class, 'destroy']);
        Route::post('/{id}/restore', [ContactPersonController::class, 'restore']);
    });

    Route::apiResource('general-accounts', GeneralAccountController::class);
    Route::post('general-accounts/bulk-delete', [GeneralAccountController::class, 'bulkDestroy']);
    Route::post('general-accounts/{general_account}/restore', [GeneralAccountController::class, 'restore'])->withTrashed();

    // Third-Party Accounts
    Route::prefix('third-party-accounts')->group(function () {
        Route::get('/options', [ThirdPartyAccountController::class, 'options']);
        Route::post('/bulk-delete', [ThirdPartyAccountController::class, 'bulkDestroy']);
        Route::post('/{third_party_account}/restore', [ThirdPartyAccountController::class, 'restore'])->withTrashed();
    });
    Route::apiResource('third-party-accounts', ThirdPartyAccountController::class);

    Route::get('cheques/create', [ChequeController::class, 'create'])->name('cheques.createOptions');
    Route::post('cheques/toggle-activation', [ChequeController::class, 'toggleActivation']);
    Route::apiResource('cheques', ChequeController::class);

    Route::prefix('/payments')->group(function () {
        Route::get('/meta', [PaymentController::class, 'enums']);
        Route::get('/invoices-by-project/{projectId}', [PaymentController::class, 'getInvoicesByProject']);
        Route::get('/expense-notes-by-project/{projectId}', [PaymentController::class, 'getExpenseNotesByProject']);
        Route::post('/bulk-delete', [PaymentController::class, 'bulkDelete']);
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/list', [PaymentController::class, 'getPayments']);
        Route::get('/{payment}', [PaymentController::class, 'show']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::put('/{payment}', [PaymentController::class, 'update']);
        Route::delete('/{payment}', [PaymentController::class, 'destroy']);
        Route::post('/{id}/restore', [PaymentController::class, 'restore']);
    });

    // ETEBAC File Generation Routes (auto-detects bank type from bank_code: 310=TGR, 022=SG)
    Route::prefix('/etebac')->group(function () {
        // Single payment endpoint (for row-level action buttons)
        Route::post('/payment/{paymentId}', [EtebacController::class, 'generateForPayment']);
        
        // Batch endpoints - auto-detect bank type
        Route::post('/generate', [EtebacController::class, 'generateEtebac']);
        Route::post('/ordre-virement', [EtebacController::class, 'generateOrdreVirement']);
        Route::post('/generate-all', [EtebacController::class, 'generateAll']);
        
        // Legacy endpoints (still work, redirect to auto-detection)
        Route::post('/generate/sg', [EtebacController::class, 'generateEtebacSg']);
        Route::post('/generate/tgr', [EtebacController::class, 'generateEtebacTgr']);
        Route::post('/ordre-virement/sg', [EtebacController::class, 'generateOrdreVirementSg']);
        Route::post('/ordre-virement/tgr', [EtebacController::class, 'generateOrdreVirementTgr']);
        
        // Download endpoints
        Route::get('/download/{filename}', [EtebacController::class, 'downloadEtebac']);
        Route::get('/ordre-virement/download/{filename}', [EtebacController::class, 'downloadOrdreVirement']);
    });

    Route::prefix('/financial-resources')->group(function () {
        Route::get('/meta', [FinancialResourceController::class, 'enums']);
        Route::post('/bulk-delete', [FinancialResourceController::class, 'bulkDelete']);
        Route::get('/', [FinancialResourceController::class, 'index']);
        Route::get('/list', [FinancialResourceController::class, 'getFinancialResources']);
        Route::get('/{financialResource}', [FinancialResourceController::class, 'show']);
        Route::post('/', [FinancialResourceController::class, 'store']);
        Route::post('/{financialResource}', [FinancialResourceController::class, 'update']);
        Route::delete('/{financialResource}', [FinancialResourceController::class, 'destroy']);
        Route::post('/{id}/restore', [FinancialResourceController::class, 'restore']);
    });

    Route::prefix('conventions')->group(function () {
        Route::bind('convention', function ($value) {
            return \App\Models\Convention::withTrashed()->findOrFail($value);
        });
        Route::get('{conventions}/document', [ConventionController::class, 'showDocument']);

        Route::get('/', [ConventionController::class, 'index']);
        Route::post('/', [ConventionController::class, 'store']);
        Route::get('/options', [ConventionController::class, 'getConventionOptions']);
        Route::get('getDashboardChartData', [ConventionController::class, 'getDashboardChartData']);
        Route::get('{id}', [ConventionController::class, 'show']);
        Route::put('{id}', [ConventionController::class, 'update']);
        Route::delete('{id}', [ConventionController::class, 'destroy']);
        Route::post('bulk-delete', [ConventionController::class, 'bulkDelete']);
        Route::post('{id}/restore', [ConventionController::class, 'restore']);
        Route::get('/conventions/{id}/document', [ConventionController::class, 'downloadDocument'])->name('conventions.downloadDocument');
        Route::post('{convention}/validate', [ConventionController::class, 'validateConvention']);
    });

    Route::prefix('grants')->group(function () {
        Route::bind('grant', function ($value) {
            return Grant::withTrashed()->find($value);
        });
        Route::get('/meta', [GrantController::class, 'enums']);
        Route::get('/', [GrantController::class, 'index']);
        Route::post('/', [GrantController::class, 'store']);
        Route::get('{id}', [GrantController::class, 'show']);
        Route::put('{id}', [GrantController::class, 'update']);
        Route::delete('{id}', [GrantController::class, 'destroy']);
        Route::post('bulk-delete', [GrantController::class, 'bulkDelete']);
        Route::put('{id}/restore', [GrantController::class, 'restore']);
    });
    // Financial Installments
    Route::prefix('financial-installments')->group(function () {
        Route::get('/{id}/document', [FinancialInstallmentController::class, 'downloadDocument']);
        Route::bind('financial_installment', function ($value) {
            return \App\Models\FinancialInstallment::withTrashed()->findOrFail($value);
        });
        Route::get('/options', [FinancialInstallmentController::class, 'options']);
    });
    Route::apiResource('financial-installments', FinancialInstallmentController::class)->parameters([
        'financial-installments' => 'financial_installment'
    ]);
    Route::post('financial-installments/{financial_installment}/validate', [FinancialInstallmentController::class, 'validateInstallment']);
    Route::prefix('/checkouts')->group(function () {
        Route::get('/meta', [CheckoutController::class, 'enums']);
        Route::post('/bulk-delete', [CheckoutController::class, 'bulkDelete']);
        Route::get('/', [CheckoutController::class, 'index']);
        Route::get('/list', [CheckoutController::class, 'getCheckouts']);
        Route::get('/{checkout}', [CheckoutController::class, 'show']);
        Route::post('/', [CheckoutController::class, 'store']);
        Route::post('/{checkout}', [CheckoutController::class, 'update']);
        Route::delete('/{checkout}', [CheckoutController::class, 'destroy']);
        Route::post('/{id}/restore', [CheckoutController::class, 'restore']);
    });
    Route::prefix('/advances')->group(function () {
        Route::get('/meta', [AdvanceController::class, 'enums']);
        Route::post('/bulk-delete', [AdvanceController::class, 'bulkDelete']);
        Route::get('/', [AdvanceController::class, 'index']);
        Route::get('/list', [AdvanceController::class, 'getAdvances']);
        Route::get('/{advance}', [AdvanceController::class, 'show']);
        Route::post('/', [AdvanceController::class, 'store']);
        Route::post('/{advance}', [AdvanceController::class, 'update']);

        Route::post('/{id}/restore', [AdvanceController::class, 'restore']);
    });

    Route::patch('service-provisions/toggle-activation', [ServiceProvisionController::class, 'toggleActivation'])
        ->name('service-provisions.toggle-activation');
    Route::get('service-provisions/create', [ServiceProvisionController::class, 'create'])
        ->name('service-provisions.create');
    Route::apiResource('service-provisions', ServiceProvisionController::class)->except(['destroy']);

    Route::prefix('payrolls')->group(function () {
        // Static routes FIRST (before {payroll} wildcard)
        Route::get('options', [PayrollController::class, 'options']);
        Route::get('/', [PayrollController::class, 'index']);
        Route::post('/', [PayrollController::class, 'store']);
        Route::post('bulk-delete', [PayrollController::class, 'bulkDelete']);
        Route::post('hr-data-preview', [PayrollController::class, 'getHrDataPreview']); // Preview before creating payroll
        
        // Dynamic routes with {payroll} parameter AFTER static routes
        Route::get('{payroll}', [PayrollController::class, 'show']);
        Route::get('{payroll}/hr-data', [PayrollController::class, 'getHrData']);
        Route::post('{payroll}/populate-hr', [PayrollController::class, 'populateFromHr']);
        Route::match(['put', 'patch'], '{payroll}', [PayrollController::class, 'update']);
        Route::delete('{payroll}', [PayrollController::class, 'destroy']);
        Route::post('{id}/restore', [PayrollController::class, 'restore']);
    });

    Route::prefix('places')->group(function () {
        Route::bind('place', function ($value) {
            return Place::withTrashed()->find($value);
        });
        Route::get('options', [PlaceController::class, 'options']);
        Route::get('/', [PlaceController::class, 'index']);
        Route::post('/', [PlaceController::class, 'store']);
        Route::get('{place}', [PlaceController::class, 'show']);
        Route::match(['put', 'patch'], '{place}', [PlaceController::class, 'update']);
        Route::delete('{place}', [PlaceController::class, 'destroy']);
        Route::post('bulk-delete', [PlaceController::class, 'bulkDelete']);
        Route::post('{place}/restore', [PlaceController::class, 'restore']);
    });
    Route::get('/partial-receipts/{id}/pdf-preview', [PartialReceiptController::class, 'pdfPreview']);
    Route::get('/partial-receipts/{id}/pdf-download', [PartialReceiptController::class, 'pdfDownload']);
    Route::get('/partial-receipts/form-options', [PartialReceiptController::class, 'getFormOptions']);
    Route::delete('/partial-receipts/bulk-delete', [PartialReceiptController::class, 'bulkDelete']);
    Route::post('/partial-receipts/{id}/restore', [PartialReceiptController::class, 'restore'])
    ->withTrashed();
    Route::apiResource('/partial-receipts', PartialReceiptController::class);
    // --- Delivery Receipts ---
    Route::apiResource('delivery-receipts', DeliveryReceiptController::class);
    Route::post('delivery-receipts/{id}/restore', [DeliveryReceiptController::class, 'restore'])->withTrashed();
    Route::post('delivery-receipts/bulk-delete', [DeliveryReceiptController::class, 'bulkDelete']);

    // --- PV Provisoire ---
    Route::apiResource('provisional-acceptances', ProvisionalAcceptanceController::class);
    Route::post('provisional-acceptances/{id}/restore', [ProvisionalAcceptanceController::class, 'restore'])
        ->name('provisional-acceptances.restore')
        ->withTrashed();
    Route::post('provisional-acceptances/bulk-delete', [ProvisionalAcceptanceController::class, 'bulkDelete'])
        ->name('provisional-acceptances.bulk-delete');
        Route::get('provisional-acceptances/{id}/pdf-preview', [ProvisionalAcceptanceController::class, 'previewPdf']);
Route::get('provisional-acceptances/{id}/pdf-download', [ProvisionalAcceptanceController::class, 'downloadPdf']);

    // --- CRUD pour PV Définitif ---
    Route::apiResource('final-acceptances', FinalAcceptanceController::class);
    Route::post('final-acceptances/{id}/restore', [FinalAcceptanceController::class, 'restore'])
        ->name('final-acceptances.restore')
        ->withTrashed();
    Route::post('final-acceptances/bulk-delete', [FinalAcceptanceController::class, 'bulkDelete'])
        ->name('final-acceptances.bulk-delete');
    
    Route::get('final-acceptances/{id}/pdf-preview', [FinalAcceptanceController::class, 'previewPdf']);
    Route::get('final-acceptances/{id}/pdf-download', [FinalAcceptanceController::class, 'downloadPdf']);


    Route::prefix('budget-line-projects')->group(function(){
        Route::get('/selectable', [BudgetLineProjectController::class, 'getSelectableBudgetLines']);
    Route::get('/', [BudgetLineProjectController::class, 'index']);
    Route::post('/', [BudgetLineProjectController::class, 'store']);

    Route::get('{bdugetLineProject}', [BudgetLineProjectController::class, 'show']);
    Route::put('{id}', [BudgetLineProjectController::class, 'update']);
    Route::get('{budgetLineProjectId}/partners',[BudgetLineProjectController::class,'getBudgetLinePartners']);
    Route::delete('{bdugetLineProject}', [BudgetLineProjectController::class, 'destroy']);
    Route::post('bulk-delete', [BudgetLineProjectController::class, 'bulkDelete']);
    Route::post('{id}/restore', [BudgetLineProjectController::class, 'restore']);

    });

Route::prefix('delivery-orders')->group(function () {
    Route::get('/', [DeliveryOrderController::class, 'index']);
    Route::post('/', [DeliveryOrderController::class, 'store']);
    Route::get('{deliveryOrder}', [DeliveryOrderController::class, 'show']);
    Route::put('{deliveryOrder}', [DeliveryOrderController::class, 'update']);
    Route::delete('{deliveryOrder}', [DeliveryOrderController::class, 'destroy']);
    Route::post('bulk-delete', [DeliveryOrderController::class, 'bulkDelete']);
    Route::post('{id}/restore', [DeliveryOrderController::class, 'restore']);
    Route::get('/purchase-order/{purchaseOrderId}', [DeliveryOrderController::class, 'getPurchaseOrderArticles']);
    Route::patch('/{deliveryOrder}/validate', [DeliveryOrderController::class, 'validateDeliveryOrder']);
});

Route::prefix('calls-for-projects')->group(function () {
    Route::get('/', [CallsForProjectsController::class, 'index']);
    Route::post('/', [CallsForProjectsController::class, 'store']);
    Route::get('{callForProject}', [CallsForProjectsController::class, 'show']);
    Route::put('{callForProject}', [CallsForProjectsController::class, 'update']);
    Route::delete('{callForProject}', [CallsForProjectsController::class, 'destroy']);
    Route::post('bulk-delete', [CallsForProjectsController::class, 'bulkDelete']);
    Route::post('{id}/restore', [CallsForProjectsController::class, 'restore']);
    Route::patch('{id}/validate', [CallsForProjectsController::class, 'validateStatus']);
});

Route::prefix('prospections')->group(function () {
    Route::get('/create', [ProspectionController::class, 'create']);
    Route::get('/', [ProspectionController::class, 'index']);
    // Legacy alias used by frontend (POST /prospections/add)
    Route::post('/add', [ProspectionController::class, 'store']);
    Route::post('/', [ProspectionController::class, 'store']);
    Route::post('/toggle-activation', [ProspectionController::class, 'toggleActivation']);

    // === VALIDATION WORKFLOW ROUTES ===
    Route::post('/{prospection}/submit-validation', [ProspectionController::class, 'submitForValidation']);
    Route::post('/{prospection}/review-supervisor', [ProspectionController::class, 'reviewAsSupervisor']);
    Route::post('/{prospection}/review-operational', [ProspectionController::class, 'reviewAsOperational']);
    Route::post('/{prospection}/review-regional', [ProspectionController::class, 'reviewAsRegional']);
    Route::post('/{prospection}/review-national', [ProspectionController::class, 'reviewAsNational']);
    Route::post('/{prospection}/reopen', [ProspectionController::class, 'reopen']);

    Route::get('/{prospection}', [ProspectionController::class, 'show']);
    Route::put('/{prospection}', [ProspectionController::class, 'update']);
    Route::delete('/{prospection}', [ProspectionController::class, 'destroy']);
    Route::get('/deleted/{id}', [ProspectionController::class, 'findDeletedById']);
    // Soft delete
    Route::patch('/restore/{id}', [ProspectionController::class, 'restore']);
    Route::delete('/bulk', [ProspectionController::class, 'bulkDelete']);
});

Route::prefix('program-types')->group(function () {
    Route::get('/', [ProgramTypeController::class, 'index']);
    Route::post('/', [ProgramTypeController::class, 'store']);
    Route::get('{programType}', [ProgramTypeController::class, 'show']);
    Route::put('{programType}', [ProgramTypeController::class, 'update']);
    Route::delete('{programType}', [ProgramTypeController::class, 'destroy']);
    Route::post('bulk-delete', [ProgramTypeController::class, 'bulkDelete']);
    Route::post('{id}/restore', [ProgramTypeController::class, 'restore']);
});

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/mark-as-read', [NotificationController::class, 'markMultipleAsRead']);
        Route::patch('/{notificationRecipient}/mark-as-read', [NotificationController::class, 'markAsRead'])
            ->where('notificationRecipient', '[0-9]+');
    });

Route::get('/test-notification', [TestController::class, 'testNotification']);
Route::get('/send-test-email', [\App\Http\Controllers\Api\TestController::class, 'send']);
Route::get('/superior-info',[CollaboratorController::class, 'getSuperiorInfo']);

Route::prefix('expense-reports')->group(function () {
    Route::get('options', [ExpenseReportController::class, 'options']);
    Route::post('bulk-delete', [ExpenseReportController::class, 'bulkDelete']);
    Route::get('/', [ExpenseReportController::class, 'index']);
    Route::post('/', [ExpenseReportController::class, 'store']);

    Route::post('/{id}/submit', [ExpenseReportController::class, 'submit']);
    Route::post('/{id}/validate-manager', [ExpenseReportController::class, 'validateManager']);
    Route::post('/{id}/validate-treasury', [ExpenseReportController::class, 'validateTreasury']);
    Route::post('/{id}/validate-accounting', [ExpenseReportController::class, 'validateAccounting']);
    Route::post('/{id}/reject', [ExpenseReportController::class, 'reject']);


    Route::post('{id}/submit', [ExpenseReportController::class, 'submit']);
    Route::post('{id}/restore', [ExpenseReportController::class, 'restore']);
    Route::get('{id}', [ExpenseReportController::class, 'show']);
    Route::put('{id}', [ExpenseReportController::class, 'update']);
    Route::delete('{id}', [ExpenseReportController::class, 'destroy']);

    Route::get('projects/{projectId}/advances', [ExpenseReportController::class, 'getAdvancesByProject']);
    Route::get('projects/{projectId}/mission-orders', [ExpenseReportController::class, 'getMissionOrdersByProject']);

    Route::post('{expenseReport}/lines', [ExpenseLineController::class, 'store']);
    Route::put('{expenseReport}/expense-lines/{expenseLine}', [ExpenseLineController::class, 'update']);
    Route::delete('{expenseReport}/expense-lines/{expenseLine}', [ExpenseLineController::class, 'destroy']);
});

Route::get('transfer-orders/options', [TransferOrderController::class, 'options']);

Route::post('transfer-orders/bulk-delete', [TransferOrderController::class, 'bulkDelete']);
Route::post('transfer-orders/{transferOrder}/send', [TransferOrderController::class, 'send']);
Route::post('transfer-orders/{transferOrder}/validate', [TransferOrderController::class, 'validateOrder']);
Route::post('transfer-orders/{transferOrder}/reject', [TransferOrderController::class, 'reject']);

Route::apiResource('transfer-orders', TransferOrderController::class);


Route::get('etbac-files/options', [EtbacFileController::class, 'options']);
Route::get('etbac-files/eligible-transfer-orders', [EtbacFileController::class, 'getEligibleTransferOrders']);
Route::post('etbac-files/{etbacFile}/attach-transfer-orders', [EtbacFileController::class, 'attachTransferOrders']);
Route::post('etbac-files/{etbacFile}/detach-transfer-orders', [EtbacFileController::class, 'detachTransferOrders']);
Route::apiResource('etbac-files', EtbacFileController::class);

Route::prefix('programme-pedagogique-e2cng')->group(function () {
    Route::post('/bulk-delete', [ProgrammePedagogiqueE2CNGController::class, 'bulkDelete']);
    Route::post('/{id}/restore', [ProgrammePedagogiqueE2CNGController::class, 'restore']);
    Route::get('/options', [ProgrammePedagogiqueE2CNGController::class, 'options']);
    Route::apiResource('', ProgrammePedagogiqueE2CNGController::class)->parameters(['' => 'programme-pedagogique-e2cng']);
});
Route::prefix('teacher-evaluations')->group(function(){
    Route::get('/', [TeacherEvaluationController::class, 'index']);
    Route::post('/', [TeacherEvaluationController::class, 'store']);
    Route::get('{teacherEvaluation}', [TeacherEvaluationController::class, 'show']);
    Route::put('{teacherEvaluation}', [TeacherEvaluationController::class, 'update']);
    Route::delete('{teacherEvaluation}', [TeacherEvaluationController::class, 'destroy']);
    Route::post('bulk-delete', [TeacherEvaluationController::class, 'bulkDelete']);
    Route::post('{id}/restore', [TeacherEvaluationController::class, 'restore']);
    Route::patch('{id}/status', [TeacherEvaluationController::class, 'updateStatus']);
});

// Dashboard Routes
Route::prefix('dashboard')->group(function () {
    Route::get('/types', [DashboardController::class, 'getAvailableTypes']);
    Route::get('/multiple', [DashboardController::class, 'getMultipleDashboards']);
    Route::get('/{type}', [DashboardController::class, 'getDashboard']);
});

// KPI Charts Routes
Route::prefix('kpi-charts')->group(function () {
    Route::get('/', [KpiChartController::class, 'getAllKpis']);
    Route::get('/available', [KpiChartController::class, 'getAvailableKpis']);
    Route::get('/summary', [KpiChartController::class, 'getKpiSummary']);
    Route::get('/{kpi}', [KpiChartController::class, 'getKpiChart']);
});

// Partnership Dashboard Routes
Route::prefix('partnership-dashboard')->group(function () {
    Route::get('/available', [PartnershipDashboardController::class, 'getAvailableKpis']);
    Route::get('/all', [PartnershipDashboardController::class, 'getAllKpis']);
    Route::get('/active-projects', [PartnershipDashboardController::class, 'getActiveProjects']);
    Route::get('/convention-rate', [PartnershipDashboardController::class, 'getConventionRate']);
    Route::get('/total-grants', [PartnershipDashboardController::class, 'getTotalGrants']);
    Route::get('/grant-reception-rate', [PartnershipDashboardController::class, 'getGrantReceptionRate']);
    Route::get('/partners-by-type', [PartnershipDashboardController::class, 'getPartnersByType']);
    Route::get('/projects-by-zone', [PartnershipDashboardController::class, 'getProjectsByZone']);
    Route::get('/projects-by-phase', [PartnershipDashboardController::class, 'getProjectsByPhase']);
    Route::get('/average-signing-delay', [PartnershipDashboardController::class, 'getAverageSigningDelay']);
    Route::get('/task-delay-rate', [PartnershipDashboardController::class, 'getTaskDelayRate']);
    Route::get('/completed-evaluations', [PartnershipDashboardController::class, 'getCompletedEvaluations']);
    Route::get('/average-partner-score', [PartnershipDashboardController::class, 'getAveragePartnerScore']);
    Route::get('/unsubscribe-rate', [PartnershipDashboardController::class, 'getUnsubscribeRate']);
});


Route::get('/purchase-lists/articles-by-product/{productId}', [PurchaseListController::class, 'getArticlesByProduct']);
Route::get('/quotes/{quoteId}/purchase-list', [PurchaseOrderController::class, 'getPurchaseListByQuote']);
Route::get('/purchase-requests/{id}/products', [PurchaseOrderController::class, 'purchaseRequestProducts']);
Route::get('/purchase-requests/{id}/articles', [PurchaseOrderController::class, 'purchaseRequestArticles']);
Route::get('/quote/{quoteId}/details', [PurchaseOrderController::class, 'getQuoteDetails']);


});

