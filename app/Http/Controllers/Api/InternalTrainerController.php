<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInternalTrainerRequest;
use App\Http\Requests\UpdateInternalTrainerRequest;
use App\Http\Requests\DeleteInternalTrainerRequest;
use App\Models\Collaborator;
use App\Models\InternalTrainer;
use App\Services\InternalTrainerService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class InternalTrainerController
 */
class InternalTrainerController extends Controller
{
    /**
     * @var InternalTrainerService
     */
    public InternalTrainerService $internalTrainerService;

    /**
     * @param InternalTrainerService $internalTrainerService
     */
    public function __construct(InternalTrainerService $internalTrainerService)
    {
        $this->internalTrainerService = $internalTrainerService;
        $this->authorizeResource(InternalTrainer::class, 'internalTrainer');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $internalTrainers = $this->internalTrainerService->getAll($request);
            return response()->json($internalTrainers, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreInternalTrainerRequest $request
     * @return JsonResponse
     */
    public function store(StoreInternalTrainerRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $internalTrainer = $this->internalTrainerService->create($data);
            return response()->json([
                'message' => 'Formateur interne créé avec succès',
                'internal_trainer' => $internalTrainer,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param InternalTrainer $internalTrainer
     * @return JsonResponse
     */
    public function show(InternalTrainer $internalTrainer): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Formateur interne récupéré avec succès',
                'internal_trainer' => $this->internalTrainerService->show($internalTrainer->id),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération du formateur interne: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateInternalTrainerRequest $request
     * @param InternalTrainer $internalTrainer
     * @return JsonResponse
     */
    public function update(UpdateInternalTrainerRequest $request, InternalTrainer $internalTrainer): JsonResponse
    {
        try {
            $data = $request->validated();
            $updatedTrainer = $this->internalTrainerService->update($internalTrainer->id, $data);
            return response()->json([
                'message' => 'Formateur interne mis à jour avec succès',
                'internal_trainer' => $updatedTrainer,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du formateur interne: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param InternalTrainer $internalTrainer
     * @return JsonResponse
     */
    public function destroy(InternalTrainer $internalTrainer): JsonResponse
    {
        try {
            $this->internalTrainerService->delete($internalTrainer->id);
            return response()->json([
                'message' => 'Formateur interne supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression du formateur interne: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteInternalTrainerRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DeleteInternalTrainerRequest $request): JsonResponse
    {
        try {
            $this->authorize('delete', InternalTrainer::class);
            $ids = $request->validated()['ids'];
            $count = $this->internalTrainerService->bulkDestroy($ids);
            return response()->json([
                'message' => $count . ' formateur(s) interne(s) supprimé(s) avec succès !'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression des formateurs internes: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param InternalTrainer $internalTrainer
     * @return JsonResponse
     */
    public function restore(InternalTrainer $internalTrainer): JsonResponse
    {
        try {
            $restoredTrainer = $this->internalTrainerService->restore($internalTrainer->id);
            return response()->json([
                'message' => 'Formateur interne restauré avec succès',
                'internal_trainer' => $restoredTrainer,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration du formateur interne: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'collaborators' => Collaborator::select('id', 'first_name', 'last_name')->get(),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
