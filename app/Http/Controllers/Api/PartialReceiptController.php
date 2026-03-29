<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartialReceiptRequest;
use App\Http\Requests\UpdatePartialReceiptRequest;
use App\Http\Resources\PartialReceiptResource;
use App\Models\PartialReceipt;
use App\Services\PartialReceiptService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PartialReceiptController extends Controller
{
    protected PartialReceiptService $partialReceiptService;

    public function __construct(PartialReceiptService $partialReceiptService)
    {
        $this->partialReceiptService = $partialReceiptService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            // $this->authorize('viewAny', PartialReceipt::class);
            $params = $request->all();
            $receipts = $this->partialReceiptService->getFilteredPartialReceipts($params);
            
            if ($receipts instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                return response()->json([
                    'data' => PartialReceiptResource::collection($receipts),
                    'pagination' => [
                        'total' => $receipts->total(),
                        'count' => $receipts->count(),
                        'per_page' => $receipts->perPage(),
                        'current_page' => $receipts->currentPage(),
                        'total_pages' => $receipts->lastPage(),
                    ]
                ], Response::HTTP_OK);
            }

            return response()->json([
                'data' => PartialReceiptResource::collection($receipts)
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StorePartialReceiptRequest $request): JsonResponse
    {
        try {
            // $this->authorize('create', PartialReceipt::class);
            $receipt = $this->partialReceiptService->create($request->validated());
            return (new PartialReceiptResource($receipt))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $receipt = $this->partialReceiptService->show($id);
            // $this->authorize('view', $receipt);
            return (new PartialReceiptResource($receipt))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Not found: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(UpdatePartialReceiptRequest $request, int $id): JsonResponse
    {
        try {
            $receipt = $this->partialReceiptService->show($id);
            // $this->authorize('update', $receipt);
            
            $updatedReceipt = $this->partialReceiptService->update($id, $request->validated());
             // Return new resource, which will be wrapped in 'data' by default
            return (new PartialReceiptResource($updatedReceipt))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $receipt = $this->partialReceiptService->show($id, true); // Find with trashed
            // $this->authorize('delete', $receipt);
            
            $this->partialReceiptService->delete($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $receipt = $this->partialReceiptService->show($id, true); // Find with trashed
            // $this->authorize('restore', $receipt);
            
            $restoredReceipt = $this->partialReceiptService->restore($id);
             // Return new resource, which will be wrapped in 'data' by default
            return (new PartialReceiptResource($restoredReceipt))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            // $this->authorize('bulkDelete', PartialReceipt::class);
            $ids = $request->input('ids');
            if (empty($ids)) {
                return response()->json(['error' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
            }
            
            $this->partialReceiptService->bulkDelete($ids);
            return response()->json(['message' => 'Partial receipts deleted successfully'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all the data needed for the create/edit form dropdowns.
     */
    public function getFormOptions(): JsonResponse
    {
        try {
            // No authorization check needed for form options, or add one if required
            $options = $this->partialReceiptService->getFormOptions();
            return response()->json($options, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function pdfPreview($id)
    {
        $path = app(\App\Services\pdfs\PartialReceiptPdfService::class)->generatePdf($id);
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pv-partiel-'.$id.'.pdf"',
        ]);
    }

    /**
     * Téléchargement du PDF PV Partiel
     */
    public function pdfDownload($id)
    {
        $path = app(\App\Services\pdfs\PartialReceiptPdfService::class)->generatePdf($id);
        return response()->download($path, 'pv-partiel-'.$id.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
