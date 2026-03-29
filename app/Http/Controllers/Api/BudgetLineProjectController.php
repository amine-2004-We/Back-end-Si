<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetLineProjectRequest;
use App\Http\Requests\UpdateBudgetLineProjectRequest;
use App\Models\BudgetLine;
use App\Models\BudgetLineProject;
use App\Services\BudgetLineProjectService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class BudgetLineProjectController extends Controller
{
    private $budgetLineProjectService;

    public function __construct(BudgetLineProjectService $budgetLineProjectService)
    {
        $this->budgetLineProjectService = $budgetLineProjectService;
    }
    public function index(Request $request)
    {
        try {
            $budgetLines = $this->budgetLineProjectService->getAll($request);
            return response()->json($budgetLines, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreBudgetLineProjectRequest $request)
    {
        try{
            $data = $request->validated();
            return response()->json($this->budgetLineProjectService->store($data), Response::HTTP_CREATED);
        }catch (Exception $e){
            return response()->json([
                'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateBudgetLineProjectRequest $request, $id)
    {
        try{
            $data = $request->validated();
            return response()->json($this->budgetLineProjectService->update($id, $data), Response::HTTP_OK);
        }catch (Exception $e){
            return response()->json([
                'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(BudgetLineProject $budgetLineProject)
    {
        try{
            return response()->json($this->budgetLineProjectService->delete($budgetLineProject->id), Response::HTTP_OK);
        }catch (Exception $e){
            return response()->json([
                'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(BudgetLineProject $budgetLineProject)
    {
        try{
            return response()->json($this->budgetLineProjectService->restore($budgetLineProject->id), Response::HTTP_OK);
        }catch (Exception $e){
            return response()->json([
                'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSelectableBudgetLines()
    {
        try{
            $budgetLines = BudgetLine::select('id','label','code')->get();
            return response()->json(
                [
                    'budgetLines' => $budgetLines
                ], Response::HTTP_OK);
        }catch (Exception $e){
            return response()->json([
                'message' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /***
     * @param int $budgetLineProjectId
     * @return JsonResponse
     */
    public function getBudgetLinePartners(int $budgetLineProjectId): JsonResponse
    {
        try {
            $partners = $this->budgetLineProjectService
                ->getBudgetLinePartners($budgetLineProjectId);

            return response()->json($partners, Response::HTTP_OK);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erreur dans le serveur !',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
