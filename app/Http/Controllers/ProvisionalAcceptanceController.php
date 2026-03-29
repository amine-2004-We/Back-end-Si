<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProvisionalAcceptanceRequest;
use App\Http\Requests\UpdateProvisionalAcceptanceRequest;
use App\Services\ProvisionalAcceptanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\ProvisionalAcceptance;
use App\Services\pdfs\ProvisionalAcceptancePdfService;

class ProvisionalAcceptanceController extends Controller
{
    protected ProvisionalAcceptanceService $service;

    public function __construct(ProvisionalAcceptanceService $service)
    {
        $this->service = $service;
    }

    /**
     * Affiche une liste paginée.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->query();
            $perPage = $request->query('per_page', 15);
            $receipts = $this->service->getPaginated($filters, $perPage);
            return response()->json($receipts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des données.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crée un nouveau PV Provisoire.
     */
    public function store(StoreProvisionalAcceptanceRequest $request): JsonResponse
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
     * Affiche un PV Provisoire spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $pv = $this->service->findById($id);
            return response()->json($pv, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'PV Provisoire non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur serveur.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Met à jour un PV Provisoire.
     */
    public function update(UpdateProvisionalAcceptanceRequest $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $pv = $this->service->update($id, $validatedData);
            return response()->json($pv, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'PV Provisoire non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Soft-delete un PV Provisoire.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            if ($this->service->delete($id)) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
            return response()->json(['message' => 'PV Provisoire non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restaure un PV Provisoire.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $pv = $this->service->restore($id);
            if ($pv) {
                return response()->json($pv, Response::HTTP_OK);
            }
            return response()->json(['message' => 'PV Provisoire non trouvé dans la corbeille.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la restauration.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gère la suppression en masse.
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
     * Prévisualisation du PDF du PV Provisoire.
     */
    public function previewPdf($id)
{
    $pdfService = new ProvisionalAcceptancePdfService();
    $path = $pdfService->generatePdf($id);
    return response()->file($path);
}

/**
 * Téléchargement du PDF du PV Provisoire.
 */
public function downloadPdf($id)
{
    $pdfService = new ProvisionalAcceptancePdfService();
    $path = $pdfService->generatePdf($id);
    return response()->download($path);
}
}
