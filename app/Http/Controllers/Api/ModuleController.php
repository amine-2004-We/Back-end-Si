<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteModuleRequest;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Module;
use App\Models\Training;
use App\Models\Trainer;
use App\Models\CompetencyGrid;
use App\Services\ModuleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModuleController extends Controller
{
    /** @var ModuleService */
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
        $this->authorizeResource(Module::class, 'module');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $modules = $this->moduleService->getAll($request);
            return response()->json($modules, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreModuleRequest $request
     * @return JsonResponse
     */
    public function store(StoreModuleRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $module = $this->moduleService->create($data, $request);
            return response()->json([
                'message' => 'Module créé avec succès',
                'module' => $module,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Module $module
     * @return JsonResponse
     */
    public function show(Module $module): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Module récupéré avec succès',
                'module' => $module->load(['training', 'trainer.internalTrainer.collaborator', 'competencyGrid', 'creator']),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateModuleRequest $request
     * @param Module $module
     * @return JsonResponse
     */
    public function update(UpdateModuleRequest $request, Module $module): JsonResponse
    {
        try {
            $data = $request->validated();
            $updatedModule = $this->moduleService->update($data, $module, $request);
            return response()->json([
                'message' => 'Module mis à jour avec succès',
                'module' => $updatedModule,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Module $module
     * @return JsonResponse
     */
    public function destroy(Module $module): JsonResponse
    {
        try {
            $this->moduleService->delete($module->id);
            return response()->json(['message' => 'Module supprimé avec succès'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteModuleRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DeleteModuleRequest $request): JsonResponse
    {
        // Manually authorize the bulk delete action using the policy
        $this->authorize('deleteAny', Module::class);
        try {
            $validated = $request->validated();
            $ids = $validated['ids'];
            $count = $this->moduleService->bulkDestroy($ids);
            return response()->json(['message' => $count . ' module(s) supprimé(s) avec succès !'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression en masse: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Module $module
     * @return JsonResponse
     */
    public function restore(Module $module): JsonResponse
    {
        $this->authorize('restore', $module);
        try {
            $restoredModule = $this->moduleService->restore($module->id);
            return response()->json([
                'message' => 'Module restauré avec succès',
                'module' => $restoredModule,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        $this->authorize('viewAny', Module::class);
        try {
            return response()->json([
                'trainings' => Training::select('id', 'title')->whereIn('training_type', ['continuous', 'monthly'])->get(),
                'trainers' => Trainer::with('internalTrainer.collaborator:id,first_name,last_name','externalTrainer')->get()->map(function($trainer) {
                    if ($trainer->internalTrainer && $trainer->internalTrainer->collaborator) {
                        return [
                            'id' => $trainer->id,
                            'name' => $trainer->internalTrainer->collaborator->first_name . ' ' . $trainer->internalTrainer->collaborator->last_name,
                            'type' => 'Interne'
                        ];
                    }
                    if ($trainer->externalTrainer) {
                        return [
                            'id' => $trainer->id,
                            'name' => $trainer->externalTrainer->full_name,
                            'type' => 'Externe'
                        ];
                    }
                    return null;
                })->filter()->values(),
                'competency_grids' => CompetencyGrid::select('id', 'title')->get(),
                'formation_types' => \App\Enums\TrainingModuleFormat::options(),
                'statuses' => \App\Enums\TrainingModuleStatus::options(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

