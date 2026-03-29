<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use Illuminate\Http\Request;
use App\Services\DeliveryReceiptService;
use App\Http\Requests\StoreDeliveryReceiptRequest;
use App\Http\Requests\UpdateDeliveryReceiptRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Exception;

class DeliveryReceiptController extends Controller
{
    /**
     * @var DeliveryReceiptService
     */
    protected DeliveryReceiptService $deliveryReceiptService;

    public function __construct(DeliveryReceiptService $deliveryReceiptService)
    {
        $this->deliveryReceiptService = $deliveryReceiptService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->query();
            $perPage = $request->query('per_page', 15);
            $receipts = $this->deliveryReceiptService->getPaginated($filters, $perPage);
            return response()->json($receipts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve delivery receipts: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreDeliveryReceiptRequest $request
     * @return JsonResponse
     */
    public function store(StoreDeliveryReceiptRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $receipt = $this->deliveryReceiptService->create($validatedData);

            $receipt->load(['items.article', 'receiver', 'deliveryOrder']);

            return response()->json($receipt, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create delivery receipt: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @param DeliveryReceipt $deliveryReceipt
     * @return JsonResponse
     */
    public function show(DeliveryReceipt $deliveryReceipt): JsonResponse
    {
        try {
            $deliveryReceipt->load(['items.article', 'receiver', 'deliveryOrder']);
            return response()->json($deliveryReceipt, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve delivery receipt: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateDeliveryReceiptRequest $request
     * @param DeliveryReceipt $deliveryReceipt
     * @return JsonResponse
     */
    public function update(UpdateDeliveryReceiptRequest $request, DeliveryReceipt $deliveryReceipt): JsonResponse
    {
         try {
            $validatedData = $request->validated();
            $updatedReceipt = $this->deliveryReceiptService->update($deliveryReceipt->id, $validatedData);

            $updatedReceipt->load(['items.article', 'receiver', 'deliveryOrder']);

            return response()->json($updatedReceipt, Response::HTTP_OK);
         } catch (Exception $e) {
             return response()->json(['error' => 'Failed to update delivery receipt: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
         }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     * @param DeliveryReceipt $deliveryReceipt
     * @return JsonResponse
     */
    public function destroy(DeliveryReceipt $deliveryReceipt): JsonResponse
    {
        try {
            if ($this->deliveryReceiptService->delete($deliveryReceipt->id)) {
                 return response()->json(['message' => 'Delivery receipt soft deleted successfully.'], Response::HTTP_OK);
            } else {
                 return response()->json(['error' => 'Failed to soft delete delivery receipt.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred during deletion: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted resource.
     * @param int $id The ID of the delivery receipt to restore
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $restoredReceipt = $this->deliveryReceiptService->restore($id);
            if ($restoredReceipt) {
                return response()->json($restoredReceipt, Response::HTTP_OK);
            } else {
                return response()->json(['error' => 'Delivery receipt not found or could not be restored.'], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
             return response()->json(['error' => 'An error occurred during restoration: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle bulk soft deletion of resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:delivery_receipts,id',
        ]);

        try {
            $count = $this->deliveryReceiptService->bulkDelete($request->input('ids'));
            return response()->json([
                'message' => $count . ' bon(s) de réception ont été supprimé(s).'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression en masse: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
