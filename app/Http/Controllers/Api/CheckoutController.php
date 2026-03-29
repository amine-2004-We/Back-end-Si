<?php

namespace App\Http\Controllers\Api;

use App\Enums\OperationTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckoutRequest;
use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class CheckoutController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $checkouts = $this->checkoutService->getAll($request);
            return response()->json($checkouts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCheckouts(): JsonResponse
    {
        try {
            $checkouts = $this->checkoutService->allBasic();
            return response()->json($checkouts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Checkout $checkout): JsonResponse
    {
        try {
            $item = $this->checkoutService->find($checkout->id);
            return response()->json(new CheckoutResource($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du checkout : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $item = $this->checkoutService->create($validated);
            return response()->json(new CheckoutResource($item), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du checkout : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(StoreCheckoutRequest $request, Checkout $checkout): JsonResponse
    {
        try {
            $validated = $request->validated();
            $item = $this->checkoutService->update($checkout->id, $validated);
            return response()->json(new CheckoutResource($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du checkout : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Checkout $checkout): JsonResponse
    {
        try {
            $this->checkoutService->delete($checkout->id);
            return response()->json([
                'message' => 'Le checkout a été supprimé avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du checkout : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:checkouts,id',
            ]);

            $this->checkoutService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des checkouts : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $item = $this->checkoutService->restore($id);
            return response()->json(new CheckoutResource($item), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du checkout : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get enums for dropdowns (currency, type, etc).
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'operation_type' => OperationTypeEnum::options(),
        ]);
    }
}
