<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingGroupRequest;
use App\Http\Requests\UpdateTrainingGroupRequest;
use App\Http\Resources\TrainingGroupCollection;
use App\Http\Resources\TrainingGroupResource;
use App\Models\Candidate;
use App\Models\Collaborator;
use App\Models\External;
use App\Models\Training;
use App\Models\TrainingGroup;
use App\Services\TrainingGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

/**
 * class TrainingGroupController
 */
class TrainingGroupController extends Controller
{
    /**
     * @param TrainingGroupService $trainingGroupService
     */
    public function __construct(public TrainingGroupService $trainingGroupService)
    {
        //$this->authorizeResource(TrainingGroup::class, 'trainingGroup');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $paginator = $this->trainingGroupService->getAll($request);
            $resource = new TrainingGroupCollection($paginator);
            return $resource->response()->setStatusCode(Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json(
                ['error' => 'Erreur dans le serveur! ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param StoreTrainingGroupRequest $request
     * @return JsonResponse
     */
    public function store(StoreTrainingGroupRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $group = $this->trainingGroupService->createWithParticipants($data);
            return response()->json([
                'message' => 'Groupe de formation créé avec succès',
                'training_group' => $group,
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function show(TrainingGroup $trainingGroup): JsonResponse
    {
        try {
            $group = $this->trainingGroupService->show($trainingGroup->id);
            return (new TrainingGroupResource($group))
                ->additional(['message' => 'Groupe de formation récupéré avec succès'])
                ->response();
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur lors de la récupération du groupe: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateTrainingGroupRequest $request
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function update(UpdateTrainingGroupRequest $request, TrainingGroup $trainingGroup): JsonResponse
    {
        try {
            $data = $request->validated();
            $group = $this->trainingGroupService->updateWithParticipants($trainingGroup->id, $data);
            return response()->json([
                'message' => 'Groupe de formation mis à jour avec succès',
                'training_group' => $group,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du groupe: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return JsonResponse
     */
    public function destroy(TrainingGroup $trainingGroup): JsonResponse
    {
        try {
            $this->trainingGroupService->delete($trainingGroup->id);
            return response()->json([
                'message' => 'Groupe de formation supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur lors de la suppression du groupe: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            //$this->authorize('delete', TrainingGroup::class);
            $ids = $request->input('ids', []);
            $this->trainingGroupService->bulkDestroy($ids);
            return response()->json([
                'message' => count($ids) . ' groupe(s) de formation supprimé(s) avec succès !'
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur lors de la suppression des groupes: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $withTrashed = TrainingGroup::withTrashed()->findOrFail($id);
            $group = $this->trainingGroupService->restore($withTrashed->id);
            return response()->json([
                'message' => 'Groupe de formation restauré avec succès',
                'training_group' => $group,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur lors de la restauration du groupe: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            //$this->authorize('create', TrainingGroup::class);
            return response()->json([
                'trainings' => Training::all('id', 'title'),
                'collaborators' => Collaborator::all('id', 'first_name', 'last_name'),
                'statuses' => [
                    'active'    => 'Actif',
                    'closed'    => 'Fermé',
                    'cancelled' => 'Annulé',
                ],
                'candidates' => Candidate::all(['id','first_name','last_name']),
                'externals' => External::all(['id','full_name']),
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchByCin(Request $request): JsonResponse
    {
        $cin = $request->input('cin');
        if (empty($cin)) {
            return response()->json(['message' => 'Le CIN est requis'], Response::HTTP_BAD_REQUEST);
        }

        $participant = null;
        $type = null;

        if ($collaborator = Collaborator::with('region', 'projects', 'position')->where('cin', $cin)->first()) {
            $participant = $collaborator;
            $type = 'collaborator';
        } elseif ($candidate = Candidate::where('cin', $cin)->first()) {
            $participant = $candidate;
            $type = 'candidate';
        } elseif ($external = External::where('full_name', $cin)->first()) {
            $participant = $external;
            $type = 'external';
        }

        if ($participant) {
            return response()->json(['type' => $type, 'participant' => $participant]);
        }

        return response()->json(['error' => 'Participant not found'], Response::HTTP_NOT_FOUND);
    }
}
