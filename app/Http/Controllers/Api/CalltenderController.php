<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CalltenderResource;
use App\Services\CalltenderService;
use App\Http\Requests\UpdateCalltenderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreCalltenderRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Support\Facades\Storage;


class CalltenderController extends Controller
{
    protected CalltenderService $calltenderService;

    public function __construct(CalltenderService $calltenderService)
    {
        $this->calltenderService = $calltenderService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->all();
            $calltenders = $this->calltenderService->getFilteredCalltenders($params);
            return response()->json([
                'data' => CalltenderResource::collection($calltenders),
                'pagination' => [
                    'total' => $calltenders->total(),
                    'count' => $calltenders->count(),
                    'per_page' => $calltenders->perPage(),
                    'current_page' => $calltenders->currentPage(),
                    'total_pages' => $calltenders->lastPage(),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        try {
            $calltender = $this->calltenderService->show($id);
            return new CalltenderResource($calltender);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Calltender not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreCalltenderRequest $request): JsonResponse
    {
        try {
            $calltender = $this->calltenderService->create($request->validated());
            return response()->json(new CalltenderResource($calltender), Response::HTTP_CREATED);
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

    public function update(UpdateCalltenderRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $calltender = $this->calltenderService->update($id, $data);
            return response()->json(new CalltenderResource($calltender), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Calltender not found'], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error: ' . $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->calltenderService->delete($id);
            return response()->json(['message' => 'Calltender deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Calltender not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $calltender = $this->calltenderService->restore($id);
            return response()->json([
                'message' => 'marche restaurée avec succès',
                'data' => new CalltenderResource($calltender)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'marche non trouvée'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids');
            if (empty($ids)) {
                return response()->json(['error' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
            }
            $this->calltenderService->bulkDelete($ids);
            return response()->json(['message' => 'Calltenders deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'One or more Calltenders not found'], Response::HTTP_NOT_FOUND);
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
        $decodedFileName = urldecode($fileName);
        $sanitizedFileName = basename($decodedFileName);

        $filePath = 'calltenders_conditions/' . $sanitizedFileName;
        Log::info('Attempting to download file from path: ' . Storage::disk('public')->path($filePath));

        // Check if the file exists on the public disk.
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download($filePath, $sanitizedFileName);
    }
}
