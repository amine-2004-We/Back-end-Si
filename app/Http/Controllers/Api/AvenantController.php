<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AvenantResource;
use App\Services\AvenantService;
use App\Http\Requests\UpdateAvenantRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreAvenantRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Support\Facades\Storage;
use App\Models\Avenant; // Import the Avenant model

class AvenantController extends Controller
{
    protected AvenantService $avenantService;

    public function __construct(AvenantService $avenantService)
    {
        $this->avenantService = $avenantService;
    }

    /**
     * Summary of index
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Avenant::class); // Authorize viewAny
            $params = $request->all();
            $avenants = $this->avenantService->getFilteredAvenants($params);
            return response()->json([
                'data' => AvenantResource::collection($avenants),
                'pagination' => [
                    'total' => $avenants->total(),
                    'count' => $avenants->count(),
                    'per_page' => $avenants->perPage(),
                    'current_page' => $avenants->currentPage(),
                    'total_pages' => $avenants->lastPage(),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    public function show(int $id)
    {
        try {
            $avenant = $this->avenantService->show($id);
            $this->authorize('view', $avenant); // Authorize view
            return new AvenantResource($avenant);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Avenant not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Summary of store
     * @param StoreAvenantRequest $request
     * @return JsonResponse
     */
    public function store(StoreAvenantRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Avenant::class); // Authorize create
            $avenant = $this->avenantService->create($request->validated());
            return response()->json(new AvenantResource($avenant), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ConflictHttpException $e) {
            return response()->json(['error' => 'Conflict error: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the specified Avenant.
     *
     * @param UpdateAvenantRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAvenantRequest $request, int $id): JsonResponse
    {
        try {
             $avenant = $this->avenantService->show($id);
            $this->authorize('update', $avenant);  

            $data = $request->validated();
            $updatedAvenant = $this->avenantService->update($id, $data);
            return response()->json(new AvenantResource($updatedAvenant), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Avenant not found'], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error: ' . $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   
   public function destroy(int $id): JsonResponse
    {
        try {
             $avenant = Avenant::withTrashed()->findOrFail($id);
            $this->authorize('delete', $avenant); // Authorize delete

            $this->avenantService->delete($id);
            return response()->json(['message' => 'Avenant deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Avenant not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function restore(int $id): JsonResponse
    {
        try {
            // Fetch the soft-deleted model to authorize
            $avenant = Avenant::withTrashed()->findOrFail($id);
            $this->authorize('restore', $avenant); // Authorize restore

            $restoredAvenant = $this->avenantService->restore($id);
            return response()->json([
                'message' => 'Avenant restauré avec succès',
                'data' => new AvenantResource($restoredAvenant)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Avenant non trouvé'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
             $this->authorize('bulkDelete', Avenant::class);

            $ids = $request->input('ids');
            if (empty($ids)) {
                return response()->json(['error' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
            }
            $this->avenantService->bulkDelete($ids);
            return response()->json(['message' => 'Avenants deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'One or more Avenants not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Downloads a file from the server's storage directory.
     *
     *
     * @param  string  $fileName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadFile(string $fileName)
    {
        $this->authorize('viewAny', Avenant::class);

        $decodedFileName = urldecode($fileName);
        $sanitizedFileName = basename($decodedFileName);

        $filePath = 'avenants_documents/' . $sanitizedFileName;
        Log::info('Attempting to download file from path: ' . Storage::disk('public')->path($filePath));

        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download($filePath, $sanitizedFileName);
    }
}