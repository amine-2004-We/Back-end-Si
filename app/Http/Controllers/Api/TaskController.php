<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Handles API requests for managing tasks, activities, and events.
 */
class TaskController extends Controller
{
    /**
     * TaskController constructor.
     *
     * @param  TaskService  $taskService  The service for handling task business logic.
     */
    public function __construct(protected TaskService $taskService)
    {
        //$this->authorizeResource(Task::class, 'task');
    }

    /**
     * Display a paginated listing of tasks.
     *
     * @param  Request  $request  The request object containing filters and pagination.
     * @return JsonResponse A JSON response containing the paginated list of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getPaginatedTasks($request->all(), $request->get('per_page', 15));

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Retrieve the options required for the task creation form.
     *
     * @return JsonResponse A JSON response containing form options.
     */
    public function create(): JsonResponse
    {
        try {
            return response()->json($this->taskService->getFormOptions());
        } catch (Exception $e) {
            Log::error('Error getting task form options: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la récupération des options.'.$e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  StoreTaskRequest  $request  The request object with validated data.
     * @return JsonResponse A JSON response containing the created task resource.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['isProject'] = true;
            $task = $this->taskService->createTask($data);
            if (! $task) {
                return response()->json(['message' => 'Échec de la création de la tâche.'], 500);
            }

            return response()->json([
                'message' => 'Tâche créée avec succès.',
                'task' => TaskResource::make($task),
            ], HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Error in TaskController store method: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la création de la tâche.'], 500);
        }
    }

    /**
     * Display the specified task.
     *
     * @param  Task  $task  The task model instance.
     * @return JsonResponse A JSON response containing the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        $loadedTask = $this->taskService->findTask($task->id);

        return response()->json(TaskResource::make($loadedTask));
    }

    /**
     * Update the specified task in storage.
     *
     * @param  UpdateTaskRequest  $request  The request object with validated data.
     * @param  Task  $task  The task model instance to update.
     * @return JsonResponse A JSON response containing the updated task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $updatedTask = $this->taskService->updateTask($task, $request->validated());

            if (!$updatedTask) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de mettre à jour la tâche. Veuillez vérifier les données et réessayer.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tâche mise à jour avec succès.',
                'data' => $updatedTask
            ], 200);
        } catch (Exception $e) {
            Log::error('TaskController::update - Exception caught', [
                'task_id' => $task->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all tasks grouped by phase for a given project.
     */
    public function getTasksByPhase(int $projectId): JsonResponse
    {
        try {
            $tasksByPhase = $this->taskService->getTasksGroupedByPhase($projectId);

            return response()->json([
                'message' => 'Tâches regroupées par phase récupérées avec succès.',
                'data' => $tasksByPhase,
            ], HttpResponse::HTTP_OK);

        } catch (Exception $e) {
            Log::error('Error getting tasks by phase: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la récupération des tâches par phase.'], 500);
        }
    }

    /**
     * Remove the specified task from storage.
     *
     * @param  Task  $task  The task model instance to delete.
     * @return JsonResponse A JSON response confirming deletion.
     */
    public function destroy(Task $task): JsonResponse
    {
        $deleted = $this->taskService->deleteTask($task);
        if ($deleted) {
            return response()->json(['message' => 'Tâche supprimée avec succès.']);
        }

        return response()->json(['message' => 'Échec de la suppression de la tâche.'], 500);
    }

    /**
     * Toggle the activation status for a batch of tasks.
     *
     * @param  Request  $request  The request containing an array of task IDs.
     * @return JsonResponse A JSON response with the results of the operation.
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', Task::class);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:tasks,id',
        ]);

        try {
            $results = $this->taskService->toggleTaskActivation($request->input('ids'));

            return response()->json($results, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error toggling task activation: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors du changement de statut des tâches.'], 500);
        }
    }
    /**
     * Get all tasks grouped by phase for a given project.
     */
    public function getTasksGroupedByPedagogicalProject(int $projectId,int $collabId): JsonResponse
    {
        try {
            $tasksByPhase = $this->taskService->getTasksGroupedByPedagogicalProject($projectId,$collabId);

            return response()->json([
                'message' => 'Tâches regroupées par phase récupérées avec succès.',
                'data' => $tasksByPhase,
            ], HttpResponse::HTTP_OK);

        } catch (Exception $e) {
            Log::error('Error getting tasks by phase: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la récupération des tâches par phase.'], 500);
        }
    }
    /**
     * Get all tasks for a specific collaborator.
     *
     * @param int $collaboratorId
     * @return JsonResponse
     */
    public function getTasksByCollaborator(Request $request): JsonResponse
    {
        $collaboratorId = $request->get('collaborator_id');

        if (!$collaboratorId) {
            return response()->json(['error' => 'collaborator_id is required'], 422);
        }

        $tasks = $this->taskService->getPaginatedTasksByCollaborator(
            (int) $collaboratorId,
            $request->all(),
            $request->get('per_page', 15)
        );

        return TaskResource::collection($tasks)->response();
    }
    public function getPaginatedPreSchool(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getPaginatedPreSchool($request->all(), $request->get('per_page', 15));

        return TaskResource::collection($tasks)->response();
    }
    public function getPaginatedSmallSection(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getPaginatedSmallSection($request->all(), $request->get('per_page', 15));

        return TaskResource::collection($tasks)->response();
    }

    public function getPaginatedKader(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getPaginatedKader($request->all(), $request->get('per_page', 15));

        return TaskResource::collection($tasks)->response();
    }

    /***
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaginatedSupportPlan(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getPaginatedSupportPlan($request->all(), $request->get('per_page', 15));

        return TaskResource::collection($tasks)->response();
    }
}
