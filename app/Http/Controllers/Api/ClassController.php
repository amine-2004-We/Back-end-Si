<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Http\Resources\ClassResource;
use App\Models\ProjectClass;
use App\Services\ClassService;
use App\Services\ClassOperationalTrackingService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ClassController extends Controller
{
    protected ClassService $classService;
    protected TaskService $taskService;
    protected ClassOperationalTrackingService $trackingService;

    public function __construct(
        ClassService $classService,
        TaskService $taskService,
        ClassOperationalTrackingService $trackingService
    ) {
        $this->classService = $classService;
        $this->taskService = $taskService;
        $this->trackingService = $trackingService;
        // $this->authorizeResource(ProjectClass::class, 'project_class');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $filters = $request->except('with_trashed');

            if ($withTrashed) {
                $classes = $this->classService->getAllWithTrashed($filters);
            } else {
                $classes = $this->classService->getAll($filters);
            }

            return response()->json([
                'data' => ClassResource::collection($classes),
                'pagination' => [
                    'total' => $classes->total(),
                    'count' => $classes->count(),
                    'per_page' => $classes->perPage(),
                    'current_page' => $classes->currentPage(),
                    'total_pages' => $classes->lastPage(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $class = $this->classService->show($id);
            return response()->json(new ClassResource($class));
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Class not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function store(StoreClassRequest $request): JsonResponse
    {
        try {
            $class = $this->classService->create($request);

            return response()->json([
                'success' => true,
                'message' => 'Class created successfully (tasks generated automatically by Observer).',
                'class' => new ClassResource($class),
            ], Response::HTTP_CREATED);
        }

        catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(StoreClassRequest $request,ProjectClass $projectClass): JsonResponse
    {
        try {
            // $this->authorize('update', $projectClass);
            $class = $this->classService->update($projectClass->id, $request);
            return response()->json(new ClassResource($class));
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Class not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'.$e], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ProjectClass $projectClass): JsonResponse
    {
        try {
            $this->classService->delete($projectClass->id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Class not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $class = $this->classService->restore($id);
            return response()->json([
                'message' => 'Class successfully restored.',
                'data' => new ClassResource($class),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => 'Class not found or not deleted.',
            ], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:class,id',
        ]);

        try {
            $deletedCount = $this->classService->bulkDelete($validated['ids']);
            return response()->json([
                'message' => "$deletedCount class(es) deleted successfully."
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /***
     * @param int $id
     * @return JsonResponse
     */
    public function getClassResources(int $id): JsonResponse
    {
        try {
            $resources = $this->classService->getResourcesWithClassId($id);
            return response()->json(["class_resources" => $resources]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getClassResourcesArchive(int $id): JsonResponse
    {
        try {
            $resources = $this->classService->getResourcesWithClassIdArchive($id);
            return response()->json(["class_resources" => $resources]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the operational state of a class.
     */
    public function updateState(Request $request, ProjectClass $projectClass): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_state' => 'required|string|in:Création,Transfert,Relocalisation',
                'transfer_to_project_id' => 'nullable|integer|exists:projects,id',
                'relocate_to_class_id' => 'nullable|integer|exists:class,id|not_in:' . $projectClass->id,
                'relocation_date' => 'nullable|date',
            ]);

            $updatedClass = $this->trackingService->updateState(
                $projectClass->id,
                $validated['class_state'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Class state updated successfully.',
                'data' => new ClassResource($updatedClass),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the operational status of a class.
     */
    public function updateStatus(Request $request, ProjectClass $projectClass): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_status_value' => 'required|string|in:Opérationnel,En arrêt,Résilié,Clôturé,Pérennisé',
                'status_change_date' => 'nullable|date',
                'status_change_reason' => 'nullable|string|max:1000',
                'perpetuation_project_id' => 'nullable|integer|exists:projects,id',
            ]);

            $updatedClass = $this->trackingService->updateStatus(
                $projectClass->id,
                $validated['class_status_value'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Class status updated successfully.',
                'data' => new ClassResource($updatedClass),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the status history for a class.
     */
    public function getStatusHistory(ProjectClass $projectClass): JsonResponse
    {
        try {
            $history = $this->trackingService->getHistory($projectClass->id);

            return response()->json([
                'data' => $history,
                'count' => $history->count(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get form options for class state and status dropdowns.
     */
    public function getOperationalOptions(): JsonResponse
    {
        try {
            return response()->json([
                'states' => ClassOperationalTrackingService::getStates(),
                'statuses' => ClassOperationalTrackingService::getStatuses(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
