<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCabinetRequest;
use App\Http\Requests\UpdateCabinetRequest;
use App\Http\Requests\DeleteCabinetRequest; // Assuming this request exists for bulk deletes
use App\Models\Cabinet;
use App\Models\Collaborator;
use App\Services\CabinetService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CabinetController extends Controller
{
    /** @var CabinetService */
    protected CabinetService $cabinetService;

    public function __construct(CabinetService $cabinetService)
    {
        $this->cabinetService = $cabinetService;
        // $this->authorizeResource(Cabinet::class, 'cabinet');
    }

    /**

     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cabinets = $this->cabinetService->getAll($request);

            return response()->json($cabinets, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreCabinetRequest $request
     * @return JsonResponse
     */
    public function store(StoreCabinetRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $cabinet = $this->cabinetService
                ->create($data, $request)
                ->load(['responsible', 'createdBy']);

            return response()->json([
                'message' => 'Cabinet créé avec succès',
                'cabinet' => $cabinet,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Cabinet $cabinet
     * @return JsonResponse
     */
    public function show(Cabinet $cabinet): JsonResponse
    {
        try {
            $cabinet = $this->cabinetService->find($cabinet->id);
            $cabinet->load(['createdBy']);

            return response()->json([
                'message' => 'Cabinet récupéré avec succès',
                'cabinet' => $cabinet,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du cabinet: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateCabinetRequest $request
     * @param Cabinet $cabinet
     * @return JsonResponse
     */
    public function update(UpdateCabinetRequest $request, Cabinet $cabinet): JsonResponse
    {
        try {
            $data = $request->validated();

            $cabinet = $this->cabinetService
                ->update($cabinet->id, $data, $request)
                ->load(['createdBy']);

            return response()->json([
                'message' => 'Cabinet mis à jour avec succès',
                'cabinet' => $cabinet,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du cabinet: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Cabinet $cabinet
     * @return JsonResponse
     */
    public function destroy(Cabinet $cabinet): JsonResponse
    {
        try {
            $this->cabinetService->delete($cabinet->id);

            return response()->json([
                'message' => 'Cabinet supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression du cabinet: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteCabinetRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DeleteCabinetRequest $request): JsonResponse
    {
        try {
            $this->authorize('bulkDelete', Cabinet::class);
            $validated = $request->validated();
            $ids = $validated['ids'] ?? [];

            $count = $this->cabinetService->bulkDestroy($ids);

            return response()->json([
                'message' => $count . ' cabinet(s) supprimé(s) avec succès !'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression des cabinets: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Cabinet $cabinet
     * @return JsonResponse
     */
    public function restore(Cabinet $cabinet): JsonResponse
    {
        try {
            $cabinet = $this->cabinetService->restore($cabinet->id);

            return response()->json([
                'message' => 'Cabinet restauré avec succès',
                'cabinet' => $cabinet,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la restauration du cabinet: ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'average_ratings' => [
                    'Très satisfaisant' => 'Très satisfaisant',
                    'Moyen' => 'Moyen',
                    'Faible' => 'Faible',
                ]
        ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
