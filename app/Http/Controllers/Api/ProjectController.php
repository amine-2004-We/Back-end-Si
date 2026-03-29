<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyProjectRequest;
use App\Http\Requests\StoreProjectCollaboratorsRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Project;
use App\Models\ProjectPartner;
use App\Models\ClassResource;
use App\Models\Region;
use App\Models\Province;
use App\Services\PartnerService;
use App\Services\ProjectService;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\InterventionAxis;
use App\Services\ProjectBankAccountService;
use App\Services\ProjectStatusService;
use App\Services\ProjectTypeService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    const NATURE_OPTIONS = ['Public', 'Privée'];

    protected ProjectService $projectService;
    protected ProjectTypeService $projectTypeService;
    protected ProjectStatusService $projectStatusService;
    protected UserService $userService;
    protected ProjectBankAccountService $projectBankAccountService;
    protected PartnerService  $partnerService;

    public function __construct(
        ProjectBankAccountService $projectBankAccountService,
        UserService $userService,
        ProjectService $projectService,
        ProjectTypeService $projectTypeService,
        ProjectStatusService $projectStatusService,
        PartnerService  $partnerService
    ) {
        $this->projectService = $projectService;
        $this->projectTypeService = $projectTypeService;
        $this->projectStatusService = $projectStatusService;
        $this->userService = $userService;
        $this->projectBankAccountService = $projectBankAccountService;
        $this->partnerService = $partnerService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $projects = $this->projectService->getProjects($request);
            return response()->json($projects, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Erreur dans le serveur !'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function formOptions(): JsonResponse{
        try {
            $projectTypes = $this->projectTypeService->getAllWithoutPagination();
            $projectStatuses = $this->projectStatusService->getAllWithoutPagination();
            $users = $this->userService->getAllWithoutPagination();
            $bankAccounts = $this->projectBankAccountService->getAllWithoutPagination();
            
            // Get all partners with pagination (with a very high per_page to get all in one request)
            $partners = $this->partnerService->getAllPartners(['per_page' => 10000]);
            
            $interventionAxes = InterventionAxis::select('id', 'name', 'code')->get();

            $regions = Region::select('id', 'name')->get();
            $provinces = Province::select('id', 'name', 'region_id')->get();

            return response()->json([
                'bank_accounts' => $bankAccounts,
                'project_nature_options' => static::NATURE_OPTIONS,
                'project_types' => $projectTypes,
                'project_statuses' => $projectStatuses,
                'intervention_axes' => $interventionAxes,
                'regions' => $regions,
                'provinces' => $provinces,
                'users' => $users,
                'partners' => $partners,
                'partner_roles' => ProjectPartner::PARTNER_ROLES,
                'program',
                'programType'

            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Erreur inconnue dans le serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['created_by_id'] = auth()->id();

            $partners = $validatedData['partners'] ?? [];
            unset($validatedData['partners']);

            $projectNotes = array_key_exists('notes', $validatedData) ? $validatedData['notes'] : null;
            if (!is_null($projectNotes)) {
                \Log::info('notes received in controller', ['notes' => $projectNotes]);
            }
            unset($validatedData['notes']);

            $result = $this->projectService->createProjectWithAutomaticTasks($validatedData, $partners, $projectNotes);

            return response()->json([
                'success' => true,
                'message' => 'Projet créé avec succès.',

                 'project' => $result['project']->load([
                    'createdBy',
                    'projectBankAccount',
                    'responsible',
                    'partners',
                    'projectStatus',
                    'projectType',
                    'interventionAxis',
                    'region',
                    'province',
                    'notes.user'
                ]),

                 'tasks' => $result['tasks']
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            Log::error('Erreur création projet : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la création du projet.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $project = $this->projectService->getProject($id);

            return response()->json(
                $project->load([
                    'createdBy',
                    'projectBankAccount',
                    'partners',
                    'responsible',
                    'projectType',
                    'projectStatus',
                    'financialInstallments',
                    'interventionAxis',
                    'region',
                    'province',
                    'notes.user'
                ])
            , Response::HTTP_OK);
        } catch (Exception $e){
            return response()->json(['error' => $e->getMessage(), 'message' => 'Projet Introuvable!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(UpdateProjectRequest $request, int $id): JsonResponse
{
    try {
        $validatedData = $request->validated();
        $partners = $validatedData['partners'] ?? [];
        unset($validatedData['partners']);

        $projectNotes = $validatedData['notes'] ?? [];
        \Log::info('notes received in controller', ['notes' => $projectNotes]);
        unset($validatedData['notes']);

        // updateProject now returns an array with 'project' and 'tasks'
        $result = $this->projectService->updateProject(
            $id,
            $validatedData,
            $partners,
            $projectNotes
        );

        // Extract the project from the result
        $updatedProject = $result['project'];
        $tasksGenerated = $result['tasks'];

        // Log if tasks were generated
        if (!empty($tasksGenerated)) {
            \Log::info('Tasks automatically generated after project update', [
                'project_id' => $updatedProject->id,
                'task_count' => count($tasksGenerated)
            ]);
        }

        return response()->json([
            'message' => 'Projet mis à jour avec succès.',
            'project' => $updatedProject->load([
                'responsible',
                'createdBy',
                'projectBankAccount',
                'partners',
                'region',
                'province',
                'notes.user'
            ]),
            'tasks_generated' => !empty($tasksGenerated),
            'task_count' => count($tasksGenerated)
        ], Response::HTTP_OK);
    } catch (Exception $e) {
        Log::error('Error updating project: ' . $e->getMessage());
        return response()->json([
            'message' => 'Échec de la mise à jour du projet.', 
            'error' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    public function destroy(DestroyProjectRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $deletedCount = $this->projectService->deleteProjects($validated['project_ids']);

            if($deletedCount == 0){
                return response()->json(['error' => 'No item found', 'message' => 'Aucun projet trouvé pour la suppression.'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['message' => "{$deletedCount} projet(s) supprimé(s) avec succès."], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Échec de la suppression des projets.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $project = $this->projectService->restoreProject($id);
            return response()->json(['message' => 'Projet restauré avec succès.', 'project' => $project], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Échec de la restauration du projet.', 'error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    public function getCollaboratorsByProject(int $id): JsonResponse
    {
        $project = $this->projectService->getWithCollaborator($id);
        if (!$project) {
            return response()->json(['message' => 'Projet non trouvé'], 404);
        }
        return response()->json(['project_id' => $project->id, 'collaborators' => $project->collaborators()->get()]);
    }

    public function getUnitsByProject(int $id): JsonResponse
    {
        try {
            // Récupérer toutes les ressources de classe liées à ce projet
            $classResources = ClassResource::where('project_id', $id)
                                ->with(['projectClass' => function($query) {
                                    $query->with(['unit' => function($q) {
                                        $q->with(['site' => function($sq) {
                                            $sq->with(['commune' => function($cq) {
                                                $cq->with(['cercle' => function($ciq) {
                                                    $ciq->with(['province' => function($pq) {
                                                        $pq->with('region');
                                                    }]);
                                                }]);
                                            }, 'douar']);
                                        }, 'educator', 'creator']);
                                    }]);
                                    // Charger le level avec eager loading
                                    $query->with('level');
                                    // Charger les classResources avec eager loading
                                    $query->with('classResources');
                                }])
                                ->get();

            // Grouper les classes par unité (ou retourner un tableau vide si aucune ressource)
            $unitGroups = $classResources->isEmpty() 
                ? []
                : $classResources
                    ->groupBy('projectClass.unit_id')
                    ->map(function($group) {
                        $unit = $group->first()->projectClass->unit;
                        return [
                            ...$unit->toArray(),
                            'classes_in_project' => $group->map(function($resource) {
                                $projectClass = $resource->projectClass;
                                
                                // Récupérer le niveau directement de la BD
                                $level = \App\Models\Level::find($projectClass->levels_id);
                                $levelName = $level?->title ?? 'N/A';
                                
                                // Récupérer l'éducatrice ressource favorite
                                $favoriteResource = \App\Models\ClassResource::where('class_id', $projectClass->id)
                                    ->where('isFavorite', true)
                                    ->with('collaborator')
                                    ->first();
                                
                                $favoriteEducator = null;
                                if ($favoriteResource && $favoriteResource->collaborator) {
                                    $favoriteEducator = trim(
                                        ($favoriteResource->collaborator->first_name ?? '') . ' ' . 
                                        ($favoriteResource->collaborator->last_name ?? '')
                                    ) ?: null;
                                }
                                
                                return [
                                    'id' => $projectClass->id,
                                    'name' => $projectClass->class_name ?? 'N/A',
                                    'level' => $levelName,
                                    'internal_code' => $projectClass->internal_class_code ?? $projectClass->class_code ?? null,
                                    'favorite_educator' => $favoriteEducator,
                                    'current_workforce' => $projectClass->current_workforce ?? 0,
                                ];
                            })->toArray(),
                            'class_count' => $group->count()
                        ];
                    })
                    ->values();

            return response()->json([
                'project_id' => $id,
                'units' => $unitGroups
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Erreur lors de la récupération des unités.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function assignCollaborators(StoreProjectCollaboratorsRequest $request, int $id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $this->projectService->assignCollaborators($project, $request->input('collaborators'));
        return response()->json(['message' => 'Collaborateurs assignés avec succès.', 'collaborators' => $project->collaborators()->get()]);
    }

    public function removeCollaborator(int $projectId, int $collaboratorId): JsonResponse
    {
        try {
            $this->projectService->removeCollaborator($projectId, $collaboratorId);
            return response()->json(['message' => 'Suppression du collaborateur réussie.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Erreur de suppression du collaborateur.'], 500);
        }
    }

    public function showBudgetCategories(int $projectId): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectWithBudgetCategories($projectId);
            return response()->json(['project' => $project->load(['budgetCategories'])], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Projet Introuvable!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function showBudgetLinesByCategory(int $projectId, int $categoryId): JsonResponse
    {
        try {
            $data = $this->projectService->getBudgetLinesByCategory($projectId, $categoryId);
            return response()->json($data, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Données introuvables!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function getProjectBudgetLines(int $projectId): JsonResponse
    {
        try{
            $budgetLines = $this->projectService->getProjectBudgetLines($projectId);
            return response()->json(['budgetLines' => $budgetLines], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Données introuvables!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function getProjectBudgetCategoriesWithAggregates(int $projectId): JsonResponse
    {
        try {
            $data = $this->projectService->getProjectBudgetCategoriesWithAggregatesFormatted($projectId);
            return response()->json($data, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'message' => 'Projet Introuvable!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function getProjectPartners(Project $project): JsonResponse
    {
        try {
            $project->load('partners');
            return response()->json(['data' => PartnerResource::collection($project->partners)], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
 * Regenerate tasks for a project
 * Useful for fixing projects that should have tasks but don't
 */
public function regenerateTasks(int $id): JsonResponse
{
    try {
        $result = $this->projectService->regenerateTasks($id, false);
        
        return response()->json([
            'message' => 'Tasks regenerated successfully.',
            'project' => $result['project'],
            'tasks' => $result['tasks']
        ], Response::HTTP_OK);
    } catch (Exception $e) {
        Log::error('Error regenerating tasks: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to regenerate tasks.', 
            'error' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}
