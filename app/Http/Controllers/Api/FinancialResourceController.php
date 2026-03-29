<?php

namespace App\Http\Controllers\Api;

use App\Enums\CurrencyEnum;
use App\Enums\FinancialResourcesTypeEnum;
use App\Enums\FinancialStatusEnum;
use App\Enums\FinancialTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinancialResourceRequest;
use App\Http\Resources\FinancialResourceResources;
use App\Models\FinancialResource;
use App\Services\FinancialResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class FinancialResourceController extends Controller
{
    protected FinancialResourceService $financialResourceService;

    public function __construct(FinancialResourceService $financialResourceService)
    {
        $this->financialResourceService = $financialResourceService;
    }

    /**
     * Display a listing of the financial resources.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $resources = $this->financialResourceService->getAll($request);
            return response()->json($resources, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a basic listing (id, code, type, etc).
     */
    public function getFinancialResources(): JsonResponse
    {
        try {
            $resources = $this->financialResourceService->allBasic();
            return response()->json($resources, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified financial resource.
     */
    public function show(FinancialResource $financialResource): JsonResponse
    {
        try {
            $item = $this->financialResourceService->find($financialResource->id);
            return response()->json(new FinancialResourceResources($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la ressource financière : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created financial resource.
     */
    public function store(StoreFinancialResourceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_financial_ressources', $filename, 'public');
                $validated['attachment'] = $path;
            }
            $item = $this->financialResourceService->create($validated);
            return response()->json(new FinancialResourceResources($item), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la ressource financière : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified financial resource.
     */
    public function update(StoreFinancialResourceRequest $request, FinancialResource $financialResource): JsonResponse
    {
        try {
            $validated = $request->validated();
            $item = $this->financialResourceService->update($financialResource->id, $validated);
            return response()->json(new FinancialResourceResources($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la ressource financière : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified financial resource.
     */
    public function destroy(FinancialResource $financialResource): JsonResponse
    {
        try {
            $this->financialResourceService->delete($financialResource->id);
            return response()->json([
                'message' => 'La ressource financière a été supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la ressource financière : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete financial resources.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:financial_resources,id',
            ]);

            $this->financialResourceService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des ressources financières : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted financial resource.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $item = $this->financialResourceService->restore($id);
            return response()->json(new FinancialResourceResources($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration de la ressource financière : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get enums for dropdowns (currency, type, etc).
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'financial_resources_type' => FinancialResourcesTypeEnum::options(),
            'currency' => CurrencyEnum::options(),
            'financial_type' =>FinancialTypeEnum::options(),
            'financial_status'=>FinancialStatusEnum::options(),
        ]);
    }
}
