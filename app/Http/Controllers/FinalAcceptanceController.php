<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FinalAcceptanceService;
use App\Http\Requests\StoreFinalAcceptanceRequest;
use App\Http\Requests\UpdateFinalAcceptanceRequest;
use App\HttpKtp\Requests\UpdateFinalAcceptanceRequest as RequestsUpdateFinalAcceptanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Services\pdfs\FinalAcceptancePdfService;

class FinalAcceptanceController extends Controller
{
    protected FinalAcceptanceService $service;

    public function __construct(FinalAcceptanceService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->query();
            $perPage = $request->query('per_page', 15);
            $receipts = $this->service->getPaginated($filters, $perPage);
            return response()->json($receipts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors   la récupération des données.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreFinalAcceptanceRequest $request
     * @return JsonResponse
     */
    public function store(StoreFinalAcceptanceRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $pv = $this->service->create($validatedData);
            return response()->json($pv, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $pv = $this->service->findById($id);
            return response()->json($pv, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'PV Définitif non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur serveur.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateFinalAcceptanceRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateFinalAcceptanceRequest $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $pv = $this->service->update($id, $validatedData);
            return response()->json($pv, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'PV Définitif non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            if ($this->service->delete($id)) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
            return response()->json(['message' => 'PV Définitif non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $pv = $this->service->restore($id);
            if ($pv) {
                return response()->json($pv, Response::HTTP_OK);
            }
            return response()->json(['message' => 'PV Définitif non trouvé dans la corbeille.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la restauration.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        try {
            $count = $this->service->bulkDelete($request->input('ids'));
            return response()->json(['message' => "{$count} élément(s) supprimé(s)."], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression en masse.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
 * Prévisualisation du PDF du PV de réception définitive.
 */
public function previewPdf($id)
{
    $pdfService = new FinalAcceptancePdfService();
    $path = $pdfService->generatePdf($id);
    return response()->file($path);
}

/**
 * Téléchargement du PDF du PV de réception définitive.
 */
public function downloadPdf($id)
{
    $pdfService = new FinalAcceptancePdfService();
    $path = $pdfService->generatePdf($id);
    return response()->download($path);
}

}
