<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CollaboratorStatusResource;
use App\Models\CollaboratorStatus;
use App\Services\CollaboratorStatusService;
use App\Http\Requests\StoreCollaboratorStatusRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class CollaboratorStatusController
 * @package App\Http\Controllers\Api
 */
class CollaboratorStatusController extends Controller
{
    protected CollaboratorStatusService $collaboratorStatusService;

    public function __construct(CollaboratorStatusService $collaboratorStatusService)
    {
        $this->collaboratorStatusService = $collaboratorStatusService;
    }

    /**
     * Get all collaborator statuses, optionally with soft-deleted ones.
     */
    public function index(Request $request)
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $statuses = $withTrashed
                ? $this->collaboratorStatusService->getAllWithTrashed()
                : $this->collaboratorStatusService->getAll();

            return response()->json([
                'data' => CollaboratorStatusResource::collection($statuses),
                'pagination' => [
                    'total' => $statuses->total(),
                    'count' => $statuses->count(),
                    'per_page' => $statuses->perPage(),
                    'current_page' => $statuses->currentPage(),
                    'total_pages' => $statuses->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show a specific collaborator status by ID.
     */
    public function show(string $id)
    {
        try {
            $status = $this->collaboratorStatusService->findCollaboratorStatusById($id);
            return response()->json(new CollaboratorStatusResource($status));
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Status not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Create a new collaborator status.
     */
    public function store(StoreCollaboratorStatusRequest $request)
    {
        try {
            $status = $this->collaboratorStatusService->create($request->validated());
            return response()->json(new CollaboratorStatusResource($status), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing collaborator status.
     */
    public function update(StoreCollaboratorStatusRequest $request, string $id)
    {
        try {
            $status = $this->collaboratorStatusService->update($id, $request->validated());
            return response()->json(new CollaboratorStatusResource($status));
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Status not found'], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a collaborator status.
     */
    public function destroy(string $id)
    {
        try {
            $this->collaboratorStatusService->delete($id);
            return response()->json(['message' => 'Status deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Status not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete collaborator statuses.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['message' => 'No statuses selected for deletion.'], Response::HTTP_BAD_REQUEST);
            }
            $deletedCount = $this->collaboratorStatusService->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount collaborator status(es) deleted successfully."], Response::HTTP_OK);
        } catch (ConflictHttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(string $id): JsonResponse
    {
        try {
            $collaboratorStat=$this->collaboratorStatusService->restore($id);
            return response()->json([
                'message' => 'Statut collaborateur restaurer.',
                'restored' => $collaboratorStat
            ],Response::HTTP_OK);
        }catch (Exception $e){
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }

    }
}
