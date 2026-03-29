<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleEvaluationRequest;
use App\Http\Requests\UpdateModuleEvaluationRequest;
use App\Http\Requests\BulkDeleteModuleEvaluationsRequest;
use App\Models\ModuleEvaluation;
use App\Models\Module;
use App\Models\Participant;
use App\Models\Trainer;
use App\Models\CompetencyGrid;
use App\Services\ModuleEvaluationService;
use App\Enums\ModuleEvaluationStatus;
use App\Enums\ModuleEvaluationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ModuleEvaluationController extends Controller
{
    public function __construct(
        private readonly ModuleEvaluationService $service
    ) {
        // $this->authorizeResource(ModuleEvaluation::class, 'moduleEvaluation');
    }

    /**
     * GET /api/module-evaluations
     * Supports filters & pagination via query params.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'module_id',
                'participant_id',
                'trainer_id',
                'status',
                'evaluation_type',
                'evaluated_at',
                'per_page',
                'search',
                'is_active',
                'page',
            ]);

            $evaluations = $this->service->getAll($filters);

            return response()->json($evaluations, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/module-evaluations
     */
    public function store(StoreModuleEvaluationRequest $request): JsonResponse
    {
        try {
            $payload     = $request->validated();

            $evaluation = $this->service->create(
                data: $payload,
                request: $request
            );

            return response()->json([
                'message'           => 'Évaluation créée avec succès',
                'module_evaluation' => $evaluation,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /api/module-evaluations/{moduleEvaluation}
     */
    public function show(ModuleEvaluation $moduleEvaluation): JsonResponse
    {
        try {
            $eval = $this->service->get($moduleEvaluation->id);

            return response()->json([
                'message'           => 'Évaluation récupérée avec succès',
                'module_evaluation' => $eval,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PUT/PATCH /api/module-evaluations/{moduleEvaluation}
     */
    public function update(UpdateModuleEvaluationRequest $request, ModuleEvaluation $moduleEvaluation): JsonResponse
    {
        try {
            $payload = $request->validated();

            $eval = $this->service->update(
                id: $moduleEvaluation->id,
                data: $payload,
                request: $request
            );

            return response()->json([
                'message'           => 'Évaluation mise à jour avec succès',
                'module_evaluation' => $eval,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * DELETE /api/module-evaluations/{moduleEvaluation}
     * (Soft delete)
     */
    public function destroy(ModuleEvaluation $moduleEvaluation): JsonResponse
    {
        try {
            $this->service->deleteMany([$moduleEvaluation->id]);

            return response()->json([
                'message' => 'Évaluation supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/module-evaluations/bulk-delete
     * Body: { ids: number[] }
     */
    public function bulkDelete(BulkDeleteModuleEvaluationsRequest $request): JsonResponse
    {
        try {
            $ids   = $request->validated('ids');
            $count = $this->service->deleteMany($ids);

            return response()->json([
                'message' => $count.' évaluation(s) supprimée(s) avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression multiple: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/module-evaluations/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $withTrashed = ModuleEvaluation::withTrashed()->findOrFail($id);
            $eval = $this->service->restore($withTrashed->id);

            return response()->json([
                'message'           => 'Évaluation restaurée avec succès',
                'module_evaluation' => $eval,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la restauration: '.$e->getMessage(),
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * GET /api/module-evaluations/options
     * Return enums + select options.
     */
    public function options(): JsonResponse
    {
        try {
            $this->authorize('create', ModuleEvaluation::class);

            $modules = Module::query()
                ->select('id', 'title', 'module_id', 'training_id')
                ->with(['training:id,title,training_id'])
                ->orderByDesc('created_at')
                ->get()
                ->map(function (Module $m) {
                    return [
                        'id'             => $m->id,
                        'title'          => $m->title,
                        'module_id'      => $m->module_id,
                        'training'       => $m->relationLoaded('training') && $m->training
                            ? [
                                'id'          => $m->training->id,
                                'title'       => $m->training->title,
                                'training_id' => $m->training->training_id,
                            ]
                            : null,
                    ];
                });

            $trainers = Trainer::query()
                ->with(['internalTrainer.collaborator', 'externalTrainer'])
                ->where(function ($q) {
                    $q->whereHas('internalTrainer.collaborator')
                      ->orWhereHas('externalTrainer');
                })
                ->get()
                ->map(function (Trainer $t) {
                    $label = null;
                    if ($it = $t->internalTrainer?->collaborator) {
                        $label = trim(($it->collaborator_code ?? '') . ' — ' . ($it->first_name ?? '') . ' ' . ($it->last_name ?? ''));
                    } elseif ($et = $t->externalTrainer) {
                        $label = trim(($et->external_code ?? $et->trainer_identifier ?? '') . ' — ' . ($et->full_name ?? ''));
                    }
                    return ['id' => $t->id, 'label' => $label];
                })
                ->filter(fn ($t) => !empty($t['label']))
                ->values();

            $participants = Participant::query()
                ->with(['traineeCollaborator.collaborator', 'externalTrainee'])
                ->where(function ($q) {
                    $q->whereHas('traineeCollaborator.collaborator')
                      ->orWhereHas('externalTrainee');
                })
                ->get()
                ->map(function (Participant $p) {
                    $label = null;
                    if ($tc = $p->traineeCollaborator?->collaborator) {
                        $label = trim(($tc->collaborator_code ?? '') . ' — ' . ($tc->first_name ?? '') . ' ' . ($tc->last_name ?? ''));
                    } elseif ($ex = $p->externalTrainee) {
                        $label = trim(($ex->external_identifier ?? '[Externe]') . ' — ' . ($ex->full_name ?? ''));
                    }
                    return ['id' => $p->id, 'label' => $label];
                })
                ->filter(fn ($p) => !empty($p['label']))
                ->values();

            $grids = CompetencyGrid::query()
                ->select('id', 'title', 'code')
                ->orderBy('title')
                ->get();

            return response()->json([
                'statuses'          => ModuleEvaluationStatus::options(),
                'evaluation_types'  => ModuleEvaluationType::options(),
                'modules'           => $modules,
                'trainers'          => $trainers,
                'participants'      => $participants,
                'competency_grids'  => $grids,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des options: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

