<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use App\Http\Requests\StoreCallForProjectRequest;
use App\Http\Requests\UpdateCallForProjectRequest;
use App\Models\CallForProject;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CallForProjectResource;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Services\CallForProjectService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;


class CallsForProjectsController extends Controller
{
    //
    protected CallForProjectService $callForProjectService;
     protected ProjectService $projectService;

    public function __construct(CallForProjectService $callForProjectService, ProjectService $projectService)
    {
        $this->callForProjectService = $callForProjectService;
        $this->projectService = $projectService;
        // $this->authorizeResource(CallForProject::class, 'callForProject');
    }


     /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->all();
            $callForProjects = $this->callForProjectService->getFilteredCallForProjects($params);
            return response()->json([
                'data' => CallForProjectResource::collection($callForProjects),
                'pagination' => [
                    'total' => $callForProjects->total(),
                    'count' => $callForProjects->count(),
                    'per_page' => $callForProjects->perPage(),
                    'current_page' => $callForProjects->currentPage(),
                    'total_pages' => $callForProjects->lastPage(),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
         * @param StoreCallForProjectRequest $request
         * @return JsonResponse
         */
        

        public function store(StoreCallForProjectRequest $request): JsonResponse
        {
            try {
                $callForProject = $this->callForProjectService->createCallForProject($request->validated());
                return response()->json(new CallForProjectResource($callForProject), Response::HTTP_CREATED);
            } catch (Exception $e) {
                return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

    }

    /**
     * @param CallForProject $callForProject
     * @return JsonResponse
     */

    public function show(CallForProject $callForProject): JsonResponse
    {
        try {
            return response()->json(new CallForProjectResource($callForProject), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateCallForProjectRequest $request
     * @return JsonResponse
     */

    public function update(UpdateCallForProjectRequest $request, CallForProject $callForProject): JsonResponse
    {
        try {
            $callForProject = $this->callForProjectService->updateCallForProject($request->validated(),$callForProject->id);

            return response()->json(new CallForProjectResource($callForProject), Response::HTTP_OK);
        }
        catch(ModelNotFoundException $e) {
            return response()->json(['error' => 'CallForProject not found'], Response::HTTP_NOT_FOUND);
        }
         catch (Exception $e) {
           
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param CallForProject $callForProject
     * @return JsonResponse
     */

    public function destroy(CallForProject $callForProject): JsonResponse
    {
        try {
            $this->callForProjectService->deleteCallForProject($callForProject->id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }
          catch(ModelNotFoundException $e) {
            return response()->json(['error' => 'CallForProject not found'], Response::HTTP_NOT_FOUND);
        }
         catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids');
            $this->callForProjectService->bulkDelete($ids);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */

    public function restore(int $id): JsonResponse
    {
        try {
        
            $this->callForProjectService->restoreCallForProject($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    /**
     * @param Request $request
     * @return boolean
     */

   public function validateStatus(int $id, Request $request)
    {
      
        $validated = $request->validate([
            'status' => 'required|string|in:Accepté,Refusé,Clôturé'
        ]);

        try {
         
            $this->callForProjectService->validateAndProcess(
                $id,
                $validated['status'],
                auth()->id()
            );

          
            return response()->json([
                'success' => true,
                'message' => 'Call for project status validated successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
       
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
           
            \Log::error('validateStatus failed at controller level', [
                'call_for_project_id' => $id,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }






}
