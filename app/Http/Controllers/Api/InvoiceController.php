<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUnreceivedInvoiceRequest;
use App\Http\Requests\StoreReceivedInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\DeleteInvoiceRequest;
use App\Models\Invoice;
use App\Models\ThirdPartyAccount;
use Exception;
use Illuminate\Http\Response;

/**
 * class InvoiceController
 */
class InvoiceController extends Controller
{
    /**
     * @var InvoiceService
     */
    protected InvoiceService $invoiceService;

    /**
     * @param InvoiceService $invoiceService
     */
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getAll($request);
            return response()->json($invoices);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des factures.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'statuses' => Invoice::STATUSES,
                'unreceived_invoices' => $this->invoiceService->getUnreceivedInvoicesOptions(),
                'accounting_accounts' => ThirdPartyAccount::query()
                    ->with('generalAccount')
                    ->select(['id','name','subdivision','general_account_id'])
                    ->orderBy('name')
                    ->get(),
                'delivery_receipts' => \App\Models\DeliveryReceipt::query()
                    ->select(['id', 'receipt_identifier', 'reception_date', 'status'])
                    ->orderByDesc('created_at')
                    ->get(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur dans la récupération des données: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->find($id);
            return response()->json($invoice);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la facture.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a new UNRECEIVED invoice.
     * @param StoreUnreceivedInvoiceRequest $request
     * @return JsonResponse
     */
    public function storeUnreceived(StoreUnreceivedInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->createUnreceived($request->validated());
            return response()->json($invoice, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la facture non parvenue.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a new RECEIVED invoice by updating unreceived ones.
     * @param StoreReceivedInvoiceRequest $request
     * @return JsonResponse
     */
    public function storeReceived(StoreReceivedInvoiceRequest $request): JsonResponse
    {
        try {
            $updatedCount = $this->invoiceService->processReceived($request->validated());
            return response()->json([
                'message' => $updatedCount . ' facture(s) marquée(s) comme reçue(s) avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du traitement de la facture reçue.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a general invoice.
     * @param UpdateInvoiceRequest $request
     * @param Invoice $invoice
     * @return JsonResponse
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->update($invoice->id, $request->validated(), $request);
            return response()->json($invoice);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la facture.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Invoice $invoice
     * @return JsonResponse
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            $this->invoiceService->delete($invoice->id);
            return response()->json(['message' => 'Facture supprimée avec succès.']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la facture.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteInvoiceRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(DeleteInvoiceRequest $request): JsonResponse
    {
        try {
            $this->invoiceService->bulkDestroy($request->validated()['ids']);
            return response()->json([
                'message' => 'Les factures sélectionnées ont été supprimées avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression multiple.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted invoice.
     */
    public function restore(Invoice $invoice): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->restore($invoice->id);
            return response()->json([
                'message' => 'Facture restaurée avec succès.',
                'invoice' => $invoice,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration de la facture.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

