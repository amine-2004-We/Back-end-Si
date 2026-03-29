<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProspectionRequest;
use App\Http\Requests\UpdateProspectionRequest;
use App\Http\Resources\ProspectionResource;
use App\Models\Prospection;
use App\Models\Program;
use App\Models\Site;
use App\Services\ProspectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Exception;


class ProspectionController extends Controller
{
    protected ProspectionService $prospectionService;

    public function __construct(ProspectionService $prospectionService)
    {
        $this->prospectionService = $prospectionService;
        // $this->authorizeResource(Prospection::class, 'prospection');
    }

    // LIST + FILTER
    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');

            $prospections = $withTrashed
                ? $this->prospectionService->getTrashed($request->all())
                : $this->prospectionService->getFiltered($request->all());

            return response()->json([
                'data' => ProspectionResource::collection($prospections),
                'pagination' => [
                    'total' => $prospections->total(),
                    'count' => $prospections->count(),
                    'per_page' => $prospections->perPage(),
                    'current_page' => $prospections->currentPage(),
                    'total_pages' => $prospections->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // FETCH NO PAGINATION
    public function fetchProspection(): JsonResponse
    {
        try {
            return response()->json([
                'data' => ProspectionResource::collection($this->prospectionService->getAll())
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // SHOW
    public function show(Prospection $prospection): JsonResponse
    {
        try {
            return response()->json(
                new ProspectionResource(
                    $this->prospectionService->findProspectionById($prospection->id)
                )
            );
        } catch (Exception $e) {
            return response()->json(['error' => 'Prospection not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    // SHOW TRASHED
    public function showDeletedProspection(int $id): JsonResponse
    {
        try {
            $prospection = $this->prospectionService->findDeletedProspectionById($id);
            return response()->json(new ProspectionResource($prospection));
        } catch (Exception $e) {
            return response()->json(['error' => 'Prospection not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    // CREATE (show form data for creation)
    public function create(): JsonResponse
    {
        return response()->json([
            'programs' => Program::select('id', 'title', 'code')->get(),
            'sites' => Site::select('id', 'name')->get(),
            'douar_access_options' => Prospection::douarAccessOptions(),
            'douar_access_labels' => Prospection::douarAccessLabels(),
            'main_language_options' => Prospection::mainLanguageOptions(),
            'main_language_labels' => Prospection::mainLanguageLabels(),
            'association_activity_type_options' => Prospection::associationActivityTypeOptions(),
            'association_activity_type_labels' => Prospection::associationActivityTypeLabels(),
            'owner_type_options' => Prospection::ownerTypeOptions(),
            'owner_type_labels' => Prospection::ownerTypeLabels(),
            'owner_status_options' => Prospection::ownerStatusOptions(),
            'owner_status_labels' => Prospection::ownerStatusLabels(),
            'manager_status_options' => Prospection::managerStatusOptions(),
            'manager_status_labels' => Prospection::managerStatusLabels(),
            'manager_structure_options' => Prospection::managerStructureOptions(),
            'manager_structure_labels' => Prospection::managerStructureLabels(),
            'condition_options' => Prospection::conditionOptions(),
            'condition_labels' => Prospection::conditionLabels(),
            'decision_options' => Prospection::decisionOptions(),
            'decision_labels' => Prospection::decisionLabels(),
        ]);
    }

    // STORE
    public function store(StoreProspectionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            // Assign the authenticated collaborator as prospector
            $user = Auth::user();
            $data['prospector_id'] = $user && $user->collaborator ? $user->collaborator->id : null;
            
           $prospection = $this->prospectionService->create($data);

            return response()->json(
                new ProspectionResource($prospection),
                Response::HTTP_CREATED
            );
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // UPDATE
    public function update(UpdateProspectionRequest $request, Prospection $prospection): JsonResponse
    {
        try {
            $updated = $this->prospectionService->update($prospection->id, $request->validated());
            return response()->json(new ProspectionResource($updated));
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Prospection not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // DELETE (soft delete)
    public function destroy(Prospection $prospection): JsonResponse
    {
        try {
            $this->prospectionService->delete($prospection->id);
            return response()->json(['message' => 'Prospection deleted.'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Prospection not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // BULK DELETE
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:prospections,id',
        ]);

        $count = $this->prospectionService->bulkDelete($validated['ids']);
        return response()->json(['message' => "$count prospections deleted."]);
    }

    // RESTORE
    public function restore(int $id): JsonResponse
    {
        try {
            $prospection = $this->prospectionService->restore($id);
            return response()->json([
                'message' => 'Prospection restored.',
                'data' => new ProspectionResource($prospection)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Prospection not found or already restored.'], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error restoring: ' . $e->getMessage()
            ], 500);
        }
    }

    // TOGGLE ACTIVATION (soft delete / restore)
    public function toggleActivation(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer|exists:prospections,id',
            ]);

            $ids = $validated['ids'];
            $results = [];

            foreach ($ids as $id) {
                try {
                    $prospection = Prospection::withTrashed()->findOrFail($id);
                    
                    if ($prospection->deleted_at === null) {
                        // Soft delete
                        $prospection->delete();
                        $results[$id] = [
                            'status' => 'success',
                            'message' => 'Prospection deactivated (deleted)',
                            'action' => 'deactivated',
                            'deleted_at' => $prospection->deleted_at,
                        ];
                    } else {
                        // Restore
                        $prospection->restore();
                        $results[$id] = [
                            'status' => 'success',
                            'message' => 'Prospection activated (restored)',
                            'action' => 'activated',
                            'deleted_at' => $prospection->deleted_at,
                        ];
                    }
                } catch (ModelNotFoundException $e) {
                    $results[$id] = [
                        'status' => 'error',
                        'message' => 'Prospection not found',
                    ];
                }
            }

            return response()->json([
                'message' => 'Toggle activation completed',
                'results' => $results,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // === VALIDATION WORKFLOW METHODS ===

    /**
     * Submit prospection for validation
     * Sets initial prospector_role_type and moves to first pending status
     */
    public function submitForValidation(Request $request, Prospection $prospection): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('update', $prospection);

            // Only draft prospections can be submitted
            if ($prospection->validation_status !== 'draft') {
                return response()->json([
                    'error' => 'Invalid operation',
                    'message' => 'Only draft prospections can be submitted for validation',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Determine prospector role type
            $roleType = $prospection->determineProspectorRoleType();
            $prospection->prospector_role_type = $roleType;

            // Determine next status based on role type
            $nextStatus = $prospection->getNextValidationLevel();
            if (!$nextStatus) {
                return response()->json([
                    'error' => 'Invalid operation',
                    'message' => 'Could not determine next validation level',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $prospection->validation_status = $nextStatus;
            $prospection->save();

            return response()->json([
                'message' => 'Prospection submitted for validation',
                'data' => new ProspectionResource($prospection->fresh()),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not authorized to perform this action',
            ], Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate at supervisor level (for teacher prospectors)
     */
    public function reviewAsSupervisor(Request $request, Prospection $prospection): JsonResponse
    {
        try {
            $this->authorize('reviewAsSupervisor', $prospection);

            $validated = $request->validate([
                'observations' => 'required|string|max:1000',
                'action' => 'required|in:approve,reject',
                'rejection_reason' => 'required_if:action,reject|string|max:1000',
            ]);

            $prospection->supervisor_observations = $validated['observations'];
            $prospection->supervisor_reviewer_id = Auth::id();
            $prospection->supervisor_reviewed_at = now();

            if ($validated['action'] === 'reject') {
                $prospection->validation_status = 'rejected';
                $prospection->is_rejected = true;
                $prospection->rejected_at = now();
                $prospection->rejection_reason = $validated['rejection_reason'];
            } else {
                $nextStatus = $prospection->getNextValidationLevel();
                $prospection->validation_status = $nextStatus;
            }

            $prospection->save();

            return response()->json([
                'message' => $validated['action'] === 'reject' ? 'Prospection rejected' : 'Supervisor review completed',
                'data' => new ProspectionResource($prospection->fresh()),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not authorized to review this prospection at supervisor level',
            ], Response::HTTP_FORBIDDEN);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate at operational level
     */
    public function reviewAsOperational(Request $request, Prospection $prospection): JsonResponse
    {
        try {
            $this->authorize('reviewAsOperational', $prospection);

            $validated = $request->validate([
                'observations' => 'required|string|max:1000',
                'action' => 'required|in:approve,reject',
                'rejection_reason' => 'required_if:action,reject|string|max:1000',
            ]);

            $prospection->operational_observations = $validated['observations'];
            $prospection->operational_reviewer_id = Auth::id();
            $prospection->operational_reviewed_at = now();

            if ($validated['action'] === 'reject') {
                $prospection->validation_status = 'rejected';
                $prospection->is_rejected = true;
                $prospection->rejected_at = now();
                $prospection->rejection_reason = $validated['rejection_reason'];
            } else {
                $nextStatus = $prospection->getNextValidationLevel();
                $prospection->validation_status = $nextStatus;
            }

            $prospection->save();

            return response()->json([
                'message' => $validated['action'] === 'reject' ? 'Prospection rejected' : 'Operational review completed',
                'data' => new ProspectionResource($prospection->fresh()),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not authorized to review this prospection at operational level',
            ], Response::HTTP_FORBIDDEN);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate at regional level
     */
    public function reviewAsRegional(Request $request, Prospection $prospection): JsonResponse
    {
        try {
            $this->authorize('reviewAsRegional', $prospection);

            $validated = $request->validate([
                'observations' => 'required|string|max:1000',
                'action' => 'required|in:approve,reject',
                'rejection_reason' => 'required_if:action,reject|string|max:1000',
            ]);

            $prospection->regional_observations = $validated['observations'];
            $prospection->regional_reviewer_id = Auth::id();
            $prospection->regional_reviewed_at = now();

            if ($validated['action'] === 'reject') {
                $prospection->validation_status = 'rejected';
                $prospection->is_rejected = true;
                $prospection->rejected_at = now();
                $prospection->rejection_reason = $validated['rejection_reason'];
            } else {
                $nextStatus = $prospection->getNextValidationLevel();
                $prospection->validation_status = $nextStatus;
            }

            $prospection->save();

            return response()->json([
                'message' => $validated['action'] === 'reject' ? 'Prospection rejected' : 'Regional review completed',
                'data' => new ProspectionResource($prospection->fresh()),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not authorized to review this prospection at regional level',
            ], Response::HTTP_FORBIDDEN);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Final validation at national level
     */
    public function reviewAsNational(Request $request, Prospection $prospection): JsonResponse
    {
        try {
            $this->authorize('reviewAsNational', $prospection);

            $validated = $request->validate([
                'observations' => 'required|string|max:1000',
                'action' => 'required|in:approve,reject',
                'rejection_reason' => 'required_if:action,reject|string|max:1000',
            ]);

            $prospection->national_observations = $validated['observations'];
            $prospection->national_reviewer_id = Auth::id();
            $prospection->national_reviewed_at = now();

            if ($validated['action'] === 'reject') {
                $prospection->validation_status = 'rejected';
                $prospection->is_rejected = true;
                $prospection->rejected_at = now();
                $prospection->rejection_reason = $validated['rejection_reason'];
            } else {
                // Final validation
                $prospection->validation_status = 'validated';
            }

            $prospection->save();

            return response()->json([
                'message' => $validated['action'] === 'reject' ? 'Prospection rejected' : 'Prospection validated',
                'data' => new ProspectionResource($prospection->fresh()),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not authorized to perform final validation',
            ], Response::HTTP_FORBIDDEN);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reopen a rejected prospection for revision
     */
    public function reopen(Request $request, Prospection $prospection): JsonResponse
    {
        try {
            $this->authorize('reopen', $prospection);

            if (!$prospection->is_rejected || $prospection->validation_status !== 'rejected') {
                return response()->json([
                    'error' => 'Invalid operation',
                    'message' => 'Only rejected prospections can be reopened',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Reset to draft for resubmission
            $prospection->validation_status = 'draft';
            $prospection->is_rejected = false;
            $prospection->rejected_at = null;
            $prospection->rejection_reason = null;
            $prospection->prospector_role_type = null;

            // Clear all reviewer data
            $prospection->supervisor_reviewer_id = null;
            $prospection->supervisor_reviewed_at = null;
            $prospection->operational_reviewer_id = null;
            $prospection->operational_reviewed_at = null;
            $prospection->regional_reviewer_id = null;
            $prospection->regional_reviewed_at = null;
            $prospection->national_reviewer_id = null;
            $prospection->national_reviewed_at = null;

            // Clear observations
            $prospection->supervisor_observations = null;
            $prospection->operational_observations = null;
            $prospection->regional_observations = null;
            $prospection->national_observations = null;

            $prospection->save();

            return response()->json([
                'message' => 'Prospection reopened for revision',
                'data' => new ProspectionResource($prospection->fresh()),
            ], Response::HTTP_OK);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only the prospection creator can reopen it',
            ], Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

