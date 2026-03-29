<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdvanceTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdvanceRequest;
use App\Http\Resources\AdvanceResource;
use App\Models\Advance;
use App\Services\AdvanceService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdvanceController extends Controller
{
    protected AdvanceService $advanceService;

    public function __construct(AdvanceService $advanceService)
    {
        $this->advanceService = $advanceService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $advances = $this->advanceService->getAll($request);
            return response()->json($advances, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAdvances(): JsonResponse
    {
        try {
            $advances = $this->advanceService->allBasic();
            return response()->json($advances, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Advance $advance): JsonResponse
    {
        try {
            $item = $this->advanceService->find($advance->id);
            return response()->json(new AdvanceResource($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de l’avance : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreAdvanceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $item = $this->advanceService->create($validated);
            return response()->json(new AdvanceResource($item), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l’avance : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(StoreAdvanceRequest $request, Advance $advance): JsonResponse
    {
        try {
            $validated = $request->validated();
            $item = $this->advanceService->update($advance->id, $validated);
            return response()->json(new AdvanceResource($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l’avance : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Advance $advance): JsonResponse
    {
        try {
            $this->advanceService->delete($advance->id);
            return response()->json([
                'message' => 'L’avance a été supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de l’avance : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:advances,id',
            ]);

            $this->advanceService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des avances : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $item = $this->advanceService->restore($id);
            return response()->json(new AdvanceResource($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration de l’avance : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get enums for dropdowns (advance types, proof status, etc).
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'advance_type' => AdvanceTypeEnum::options(),
        ]);
    }
}
