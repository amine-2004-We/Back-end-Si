<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ProgramType;
use App\Http\Resources\ProgramTypeResource;
use Illuminate\Http\Request;
use App\Services\ProgramTypeService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Exception;


class ProgramTypeController extends Controller
{
    //

    protected ProgramTypeService $programTypeService;
    public function __construct(ProgramTypeService $programTypeService)
    {
        $this->programTypeService = $programTypeService;
    }

    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $programTypes = $this->programTypeService->getAll($request);

            return response()->json(ProgramTypeResource::collection($programTypes), Response::HTTP_OK);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    
}

    public function show(int $id): JsonResponse
    {
        try {
            $programType = $this->programTypeService->getById($id);

            return response()->json(new ProgramTypeResource($programType), Response::HTTP_OK);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            $programType = $this->programTypeService->create($data);

            return response()->json(new ProgramTypeResource($programType), Response::HTTP_CREATED);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $programType = $this->programTypeService->update($request, $id);

            return response()->json(new ProgramTypeResource($programType), Response::HTTP_OK);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->programTypeService->delete($id);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $programType = $this->programTypeService->restore($id);

            return response()->json(new ProgramTypeResource($programType), Response::HTTP_OK);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
