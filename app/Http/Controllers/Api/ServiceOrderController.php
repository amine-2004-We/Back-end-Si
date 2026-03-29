<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use App\Services\pdfs\ServiceOrderPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;

class ServiceOrderController extends Controller
{
    /**
     * @var ServiceOrderService
     */
    protected ServiceOrderService $serviceOrderService;

    public function __construct(ServiceOrderService $serviceOrderService)
    {
        $this->serviceOrderService = $serviceOrderService;
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
            $serviceOrders = $this->serviceOrderService->getPaginated($filters, $perPage);
            return response()->json($serviceOrders, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve service orders: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreServiceOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceOrderRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Pass the UploadedFile object directly to the service
            if ($request->hasFile('signed_document')) {
                $validatedData['signed_document'] = $request->file('signed_document');
            }
            
            $serviceOrder = $this->serviceOrderService->create($validatedData);
            
            // Charger les relations appropriées
            $serviceOrder = $this->loadRelations($serviceOrder);
            
            return response()->json($serviceOrder, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create service order: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @param ServiceOrder $serviceOrder
     * @return JsonResponse
     */
    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            $serviceOrder = $this->loadRelations($serviceOrder);
            return response()->json($serviceOrder, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve service order: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateServiceOrderRequest $request
     * @param ServiceOrder $serviceOrder
     * @return JsonResponse
     */
    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('signed_document')) {
                $validatedData['signed_document'] = $request->file('signed_document');
            }

            $updatedOrder = $this->serviceOrderService->update($serviceOrder->id, $validatedData);
            $updatedOrder = $this->loadRelations($updatedOrder);
            
            return response()->json($updatedOrder, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update service order: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     * @param ServiceOrder $serviceOrder
     * @return JsonResponse
     */
    public function destroy(ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            if ($this->serviceOrderService->delete($serviceOrder->id)) {
                 return response()->json(['message' => 'Service order soft deleted successfully.'], Response::HTTP_OK);
            } else {
                 return response()->json(['error' => 'Failed to soft delete service order.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred during deletion: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted resource.
     * @param int $id The ID of the service order to restore
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $restoredOrder = $this->serviceOrderService->restore($id);
            if ($restoredOrder) {
                $restoredOrder = $this->loadRelations($restoredOrder);
                return response()->json($restoredOrder, Response::HTTP_OK);
            } else {
                return response()->json(['error' => 'Service order not found or could not be restored.'], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
             return response()->json(['error' => 'An error occurred during restoration: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete the associated signed document for the specified resource.
     * @param ServiceOrder $serviceOrder
     * @return JsonResponse
     */
    public function deleteDocument(ServiceOrder $serviceOrder): JsonResponse
    {
         try {
             if ($this->serviceOrderService->deleteSignedDocument($serviceOrder->id)) {
                 return response()->json(['message' => 'Document deleted successfully.'], Response::HTTP_OK);
             } else {
                 return response()->json(['error' => 'Failed to delete document or no document existed.'], Response::HTTP_BAD_REQUEST);
             }
         } catch (Exception $e) {
             return response()->json(['error' => 'An error occurred deleting the document: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
         }
    }

    /**
     * Bulk delete service orders.
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:service_orders,id'
        ]);

        $ids = $validated['ids'];

        $count = ServiceOrder::destroy($ids);

        return response()->json([
            'message' => $count . ' service order(s) deleted successfully.'
        ], 200);
    }

    /**
     * Get available sources for creating service orders.
     * @param Request $request
     * @return JsonResponse
     */
        public function getSources(Request $request): JsonResponse
    {
        try {
            $type = $request->query('type'); 
            
            if (!$type || !in_array($type, ['purchase_order', 'calltender'])) {
                return response()->json([
                    'error' => 'Type parameter is required and must be purchase_order or calltender'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            if ($type === 'purchase_order') {
                $sources = \App\Models\PurchaseOrder::query()
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'po_number', 'subject', 'supplier_id', 'status'])
                    ->map(function ($po) {
                        return [
                            'type' => 'purchase_order',
                            'id' => $po->id,
                            'reference' => $po->po_number,
                            'subject' => $po->subject,
                            'supplier_id' => $po->supplier_id,
                            'status' => $po->status,
                        ];
                    })
                    ->values()
                    ->toArray();
            } else {
                $sources = \App\Models\Calltender::query()
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'calltender_id', 'subject', 'supplier', 'status'])
                    ->map(function ($ct) {
                        return [
                            'type' => 'calltender',
                            'id' => $ct->id,
                            'reference' => $ct->calltender_id,
                            'subject' => $ct->subject,
                            'supplier' => $ct->supplier,
                            'status' => $ct->status,
                        ];
                    })
                    ->values()
                    ->toArray();
            }
            
            return response()->json($sources, Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Error in getSources: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve sources',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Preview the PDF for a Service Order.
     * @param int $id
     * @param ServiceOrderPdfService $pdfService
     * @return \Illuminate\Http\Response
     */
    public function pdfPreview($id, ServiceOrderPdfService $pdfService)
    {
        $path = $pdfService->generatePdf($id);
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="service-order-preview.pdf"',
        ]);
    }

    /**
     * Download the PDF for a Service Order.
     * @param int $id
     * @param ServiceOrderPdfService $pdfService
     * @return \Illuminate\Http\Response
     */
    public function pdfDownload($id, ServiceOrderPdfService $pdfService)
    {
        $path = $pdfService->generatePdf($id);
        return response()->download($path, 'service-order-' . $id . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Load appropriate relations based on source type.
     * @param ServiceOrder $serviceOrder
     * @return ServiceOrder
     */
    private function loadRelations(ServiceOrder $serviceOrder): ServiceOrder
    {
        if ($serviceOrder->source_type === 'purchase_order') {
            $serviceOrder->load([
                'purchaseOrder:id,po_number,subject,status,supplier_id',
                'supplier:id,company_name,trade_name',
                'supervisor:id,name,email'
            ]);
        } elseif ($serviceOrder->source_type === 'calltender') {
            $serviceOrder->load([
                'calltender:id,calltender_id,subject,status,supplier',
                'supplier:id,company_name,trade_name',
                'supervisor:id,name,email'
            ]);
        } else {
            $serviceOrder->load(['supplier:id,company_name,trade_name', 'supervisor:id,name,email']);
        }
        
        return $serviceOrder;
    }
}