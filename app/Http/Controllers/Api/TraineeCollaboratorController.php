<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTraineeCollaboratorRequest;
use App\Http\Requests\UpdateTraineeCollaboratorForTrainingRequest;
use App\Models\Collaborator;
use App\Models\TraineeCollaborator;
use App\Models\Training;
use App\Models\TrainingGroup;
use App\Services\TraineeCollaboratorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * class TraineeCollaboratorController
 */
class TraineeCollaboratorController extends Controller
{
    /**
     * @var TraineeCollaboratorService
     */
    public TraineeCollaboratorService $tcService;

    /**
     * @param TraineeCollaboratorService $tcService
     */
    public function __construct(TraineeCollaboratorService $tcService)
    {
        $this->tcService = $tcService;
        $this->authorizeResource(TraineeCollaborator::class, 'traineeCollaborator');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $list = $this->tcService->getTraineeCollaborators($request);
            return response()->json($list, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération de la liste: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreTraineeCollaboratorRequest $request
     * @return JsonResponse
     */
    public function store(StoreTraineeCollaboratorRequest $request): JsonResponse
    {
        try {
            $tc = $this->tcService->createBasic(
                payload: $request->validated(),
                createdByUserId: auth()->id()
            );

            return response()->json([
                'message' => 'Stagiaire-collaborateur créé avec succès',
                'trainee_collaborator' => $tc,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param Training $training
     * @param TraineeCollaborator $traineeCollaborator
     * @return JsonResponse
     */
    public function show(TraineeCollaborator $traineeCollaborator): JsonResponse
    {
        try {
            $tc = $this->tcService->getWithRelations($traineeCollaborator->id);
            return response()->json([
                'message' => 'Stagiaire-collaborateur récupéré avec succès',
                'trainee_collaborator' => $tc,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => 'Stagiaire-collaborateur non trouvée '], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param UpdateTraineeCollaboratorForTrainingRequest $request
     * @param TraineeCollaborator $traineeCollaborator
     * @return JsonResponse
     */
    public function update(UpdateTraineeCollaboratorForTrainingRequest $request, TraineeCollaborator $traineeCollaborator): JsonResponse
    {
        try {
            $tc = $this->tcService->updateBasic(
                tc: $traineeCollaborator,
                payload: $request->validated()
            );

            return response()->json([
                'message' => 'Stagiaire-collaborateur mis à jour avec succès',
                'trainee_collaborator' => $tc,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param TraineeCollaborator $traineeCollaborator
     * @return JsonResponse
     */
    public function destroy( TraineeCollaborator $traineeCollaborator): JsonResponse
    {
        try {
            $this->tcService->deleteMany([$traineeCollaborator->id]);
            return response()->json([
                'message' => 'Stagiaire-collaborateur supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param Request $request
     * @param Training $training
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $this->authorize('delete', TraineeCollaborator::class);

            $ids = $request->input('ids', []);
            $deleted = $this->tcService->deleteMany($ids);

            return response()->json([
                'message' => $deleted . ' enregistrement(s) supprimé(s) avec succès !'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression en masse: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param TraineeCollaborator $tc
     * @return JsonResponse
     */
    public function restore(TraineeCollaborator $tc): JsonResponse
    {
        try {
            $tc = $this->tcService->restore($tc->id);

            return response()->json([
                'message' => 'Stagiaire-collaborateur restauré avec succès',
                'trainee_collaborator' => $tc,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @param Training $training
     * @return JsonResponse
     */
    public function options(Training $training): JsonResponse
    {
        try {
            $this->authorize('create', TraineeCollaborator::class);

            return response()->json([
               'trainings' => Training::whereIn('training_type', ['continuous', 'monthly'])
               ->select('id', 'title','training_type')
               ->orderBy('title')
               ->get(),
                'collaborators' => Collaborator::query()
                    ->select('id','collaborator_code', 'first_name', 'last_name')
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get(),
                'training_groups' => TrainingGroup::query()
                    ->select('id', 'title')
                    ->with('training')
                    ->orderBy('title')
                    ->get(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => "Erreur lors de la récupération des options: " . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
