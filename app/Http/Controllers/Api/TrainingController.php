<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Http\Requests\DeleteTrainingRequest;
use App\Http\Resources\Options\TrainerOptionsResource;
use App\Models\Cabinet;
use App\Models\Collaborator;
use App\Models\CompetencyGrid;
use App\Models\ExternalTrainer;
use App\Models\Site;
use App\Models\Trainer;
use App\Models\TrainingGroup;
use App\Services\TrainingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class TrainingController
 */
class TrainingController extends Controller
{
    /**
     * @var TrainingService
     */
    public TrainingService $trainingService;

    /**
     * @param TrainingService $trainingService
     */
    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $trainings = $this->trainingService->getAll($request);

            return response()->json($trainings, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreTrainingRequest $request
     * @return JsonResponse
     */
    public function store(StoreTrainingRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $training = $this->trainingService
                ->createWithChildren($data, $request)   // << changed
                ->load(['responsible', 'cabinet', 'createdBy']);

            return response()->json([
                'message'  => 'Formation créée avec succès',
                'training' => $training,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $training = $this->trainingService->find($id);
            $training->load(['responsible', 'cabinet', 'createdBy','modules','modules.sessions']);

            return response()->json([
                'message' => 'Formation récupérée avec succès',
                'training' => $training,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de la formation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateTrainingRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateTrainingRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $training = $this->trainingService
                ->updateWithChildren($id, $data, $request)  // << changed
                ->load(['responsible', 'cabinet', 'createdBy']);

            return response()->json([
                'message'  => 'Formation mise à jour avec succès',
                'training' => $training,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la formation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->trainingService->delete($id);

            return response()->json([
                'message' => 'Formation supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression de la formation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteTrainingRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DeleteTrainingRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $ids = $validated['ids'] ?? [];

            $count = $this->trainingService->bulkDestroy($ids);

            return response()->json([
                'message' => $count . ' formation(s) supprimée(s) avec succès !'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression des formations: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $training = $this->trainingService->restore($id);

            return response()->json([
                'message' => 'Formation restaurée avec succès',
                'training' => $training,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la restauration de la formation: ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            $trainers = Trainer::query()
            ->with([
                'internalTrainer.collaborator:id,first_name,last_name,collaborator_code',
                'externalTrainer:id,trainer_id,full_name,trainer_identifier',
            ])
            ->where(function ($q) {
                $q->whereHas('internalTrainer.collaborator')
                ->orWhereHas('externalTrainer');
            })
            ->get();

            $trainerOptions = TrainerOptionsResource::collection($trainers)->resolve();
            $trainerOptions = array_values(array_filter(
                $trainerOptions,
                fn ($t) => isset($t['label']) && trim($t['label']) !== ''
            ));
            $collaborators = Collaborator::select('id', 'first_name', 'last_name')->get()->map(fn($c) => [
            'id' => $c->id,
            'label' => "{$c->first_name} {$c->last_name} (Interne)",
            'type' => Collaborator::class
        ]);

            $externalTrainers = ExternalTrainer::select('id', 'full_name')->get()->map(fn($e) => [
                'id' => $e->id,
                'label' => "{$e->full_name} (Externe)",
                'type' => ExternalTrainer::class
            ]);
                return response()->json([
                'training_types' => [
                    'initial'    => 'Initiale',
                    'continuous' => 'Continue',
                    'monthly'    => 'Mensuelle',
                ],
                'statuses' => [
                    'planned'     => 'Planifiée',
                    'in_progress' => 'En cours',
                    'completed'   => 'Terminée',
                    'cancelled'   => 'Annulée',
                ],
                'target_audiences' => [
                    'candidates'    => 'Candidats',
                    'collaborators' => 'Collaborateurs',
                    'externals'     => 'Externes',
                ],
                'responsibles' => Collaborator::select('id', 'last_name', 'first_name')->get(),
                'training_groups' => TrainingGroup::select('id', 'title')->where('status','active')->get(),
                'animators' => $collaborators->concat($externalTrainers),

                'competency_grids' => CompetencyGrid::select('id', 'title','code')->get(),
                'cabinets'     => Cabinet::select('id', 'name')->get(),
                'trainers' => $trainerOptions,
                'sites' => Site::select('id', 'name')->get(),
                'formation_types' => \App\Enums\TrainingModuleFormat::options(),
                'module_statuses' => \App\Enums\TrainingModuleStatus::options(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
