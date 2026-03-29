<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyBudgetLineRequest;
use App\Http\Requests\StoreBudgetLineProjectRequest;
use App\Http\Requests\StoreBudgetLineRequest;
use App\Http\Requests\UpdateBudgetLineRequest;
use App\Models\BudgetLine;
use App\Models\Project;
use App\Services\BudgetCategoryService;
use App\Services\BudgetLigneService;
use App\Services\PartnerService;
use App\Services\ProjectService;
use HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * class BudgetLigneController
 */
class BudgetLigneController extends Controller
{
    /**
     * @var BudgetLigneService
     */
    protected BudgetLigneService $budgetLigneService;

    /**
     * @var BudgetCategoryService
     */
    protected BudgetCategoryService $budgetCategoryService;

    /**
     * @var PartnerService
     */
    protected PartnerService  $partnerService;

    /**
     * @var ProjectService
     */
    protected ProjectService $projectService;

    /**
     * @param BudgetLigneService $budgetLigneService
     */
    public function __construct(
        BudgetLigneService $budgetLigneService,
        BudgetCategoryService $budgetCategoryService,
        PartnerService  $partnerService,
        ProjectService $projectService
    )
    {
        $this->budgetLigneService = $budgetLigneService;
        $this->budgetCategoryService = $budgetCategoryService;
        $this->partnerService = $partnerService;
        $this->projectService = $projectService;
    }

    /**
     * @return JsonResponse
     */
        public function index(Request $request): JsonResponse
        {
            try {
                $budgetLines = $this->budgetLigneService->getAll($request);
                return response()->json($budgetLines, Response::HTTP_OK);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


    /**
     * @param StoreBudgetLineRequest $request
     * @return JsonResponse
     */
    public function store(StoreBudgetLineRequest $request): JsonResponse
    {
        try {
           $validated = $request->validated();


            $this->budgetLigneService->create($validated);

            return response()->json(['message' => 'Ligne budgétaire créée avec succès.'], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la ligne budgétaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param UpdateBudgetLineRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBudgetLineRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();


            $this->budgetLigneService->update($id, $validated);

            return response()->json(['message' => 'Ligne budgétaire mise à jour avec succès.']);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la ligne budgétaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id): mixed
    {
        try{
            $budgetLigne =  $this->budgetLigneService->show($id);

            return  response()->json($budgetLigne->load('partners','category'),Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Erreur Dans le serveur !'
            ], Response::HTTP_NOT_FOUND);
        }
    }
    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->budgetLigneService->delete($id);

            return response()->json(['message' => 'Ligne budgétaire supprimée avec succès.']);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la ligne budgétaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param DestroyBudgetLineRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DestroyBudgetLineRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->budgetLigneService->bulkDelete($validated['ids']);

            return response()->json(['message' => 'Lignes budgétaires supprimées avec succès.']);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression multiple.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try{
            $budgetCategories = $this->budgetCategoryService->getAllWithoutPagination();
            $partners = $this->partnerService->getAllWithoutPagination();
            $projects = Project::with('partners')->get();

            return response()->json([
                'budgetCategories' => $budgetCategories,
                'partners' => $partners,
                'projects' => $projects
            ]);
        }catch(Exception $e){
            return response()->json(['message' => 'Erreur dans la recuperation des donnees!']);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $budgetLine = $this->budgetLigneService->restoreBudgetLine($id);

            return response()->json([
                'message' => 'Ligne budgétaire restaurée avec succès.',
                'budgetLine' => $budgetLine
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Ligne budgétaire introuvable.'
            ], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur inattendue.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
