<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;
use App\Http\Requests\DeleteAbsenceRequest; // Assumes this will be created for bulk deletes
use App\Models\Collaborator;
use App\Services\AbsenceService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class AbsenceController
 */
class AbsenceController extends Controller
{
    /**
     * @var AbsenceService
     */
    public AbsenceService $absenceService;

    /**
     * @param AbsenceService $absenceService
     */
    public function __construct(AbsenceService $absenceService)
    {
        $this->absenceService = $absenceService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $absences = $this->absenceService->getAll($request);
            return response()->json($absences, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur lors de la récupération des absences: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreAbsenceRequest $request
     * @return JsonResponse
     */
    public function store(StoreAbsenceRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $absence = $this->absenceService->create($data, $request);
            return response()->json([
                'message' => 'Absence créée avec succès',
                'absence' => $absence,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur lors de la création de l\'absence: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $absence = $this->absenceService->show($id);
            return response()->json([
                'message' => 'Absence récupérée avec succès',
                'absence' => $absence,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération de l\'absence: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateAbsenceRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAbsenceRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $absence = $this->absenceService->update($id, $data, $request);
            return response()->json([
                'message' => 'Absence mise à jour avec succès',
                'absence' => $absence,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'absence: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->absenceService->delete($id);
            return response()->json([
                'message' => 'Absence supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression de l\'absence: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteAbsenceRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(DeleteAbsenceRequest $request): JsonResponse
    {
        try {
            $ids = $request->validated()['ids'];
            $count = $this->absenceService->bulkDestroy($ids);
            return response()->json([
                'message' => $count . ' absence(s) supprimée(s) avec succès !'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression des absences: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $absence = $this->absenceService->restore($id);
            return response()->json([
                'message' => 'Absence restaurée avec succès',
                'absence' => $absence,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration de l\'absence: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'collaborators' => Collaborator::all('id', 'first_name', 'last_name'),
                'absence_types' => [
                    'Maladie' => 'Maladie',
                    'Éducatif' => 'Éducatif',
                    'Administratif' => 'Administratif',
                    'événements familiaux' => 'Événements familiaux',
                    'mesures disciplinaire' => 'Mesures disciplinaire',
                ],
                'absence_statuses' => [
                    'Justifié' => 'Justifié',
                    'Non justifié' => 'Non justifié',
                    'autorisé' => 'Autorisé',
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
