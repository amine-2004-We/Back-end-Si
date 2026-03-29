<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the payments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payments = $this->paymentService->getAll($request);
            return response()->json($payments, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a basic listing of payments (id, payment_number, amount, etc).
     */
    public function getPayments(): JsonResponse
    {
        try {
            $payments = $this->paymentService->allPayments();
            return response()->json($payments, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): JsonResponse
    {
        try {
            $paymentItem = $this->paymentService->find($payment->id);
            return response()->json(new PaymentRequest($paymentItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du paiement : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created payment.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $paymentItem = $this->paymentService->createWithInvoices($validated);
            return response()->json(new PaymentRequest($paymentItem), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du paiement : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get invoices by project ID with total amounts.
     * Only returns validated invoices (not yet paid)
     */
    public function getInvoicesByProject(int $projectId): JsonResponse
    {
        try {
            $invoices = Invoice::whereHas('purchaseOrder.purchaseRequests', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
                ->whereIn('status', [
                    Invoice::STATUS_VALIDATED_TREASURY,
                    Invoice::STATUS_VALIDATED_ACCOUNTING_1,
                    Invoice::STATUS_VALIDATED_CG_2,
                ])
                ->with('purchaseOrder')
                ->select('id', 'invoice_number', 'invoice_date', 'due_date', 'total', 'purchase_order_id', 'status')
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'due_date' => $invoice->due_date,
                        'total' => $invoice->total ?? $invoice->purchaseOrder?->total_amount_ttc,
                        'status' => $invoice->status,
                    ];
                });

            return response()->json($invoices, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified payment.
     */
    public function update(StorePaymentRequest $request, Payment $payment): JsonResponse
    {
        try {
            $validated = $request->validated();
            $paymentItem = $this->paymentService->update($payment->id, $validated);
            return response()->json(new PaymentRequest($paymentItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du paiement : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        try {
            $this->paymentService->delete($payment->id);
            return response()->json([
                'message' => 'Le paiement a été supprimé avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du paiement : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete payments.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:payments,id',
            ]);

            $this->paymentService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des paiements : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted payment.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $paymentItem = $this->paymentService->restore($id);
            return response()->json(new PaymentRequest($paymentItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du paiement : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get enum values for payments and projects.
     */
    public function enums(): JsonResponse
    {
        try {
            $projects = \App\Models\Project::select('id', 'project_name', 'project_code')
                ->orderBy('project_name')
                ->get();

            return response()->json([
                'payment_methods' => PaymentMethodEnum::options(),
                'payment_statuses' => PaymentStatusEnum::options(),
                'projects' => $projects,
                'payment_types' => [
                    ['value' => 'invoice', 'label' => 'Facture'],
                    ['value' => 'expense_report', 'label' => 'Note de frais'],
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get expense reports by project ID.
     * Only returns validated expense reports (not yet paid)
     */
    public function getExpenseNotesByProject(int $projectId): JsonResponse
    {
        try {
            $expenseReports = \App\Models\ExpenseReport::where('project_id', $projectId)
                ->whereIn('status', [
                    \App\Models\ExpenseReport::STATUS_VALIDATED_MANAGER,
                    \App\Models\ExpenseReport::STATUS_VALIDATED_TREASURY,
                    \App\Models\ExpenseReport::STATUS_VALIDATED_ACCOUNTING,
                ])
                ->with('createdBy')
                ->get()
                ->map(function ($expenseReport) {
                    return [
                        'id' => $expenseReport->id,
                        'total_amount' => $expenseReport->total_amount,
                        'status' => $expenseReport->status,
                        'created_at' => $expenseReport->created_at,
                    ];
                });

            return response()->json($expenseReports, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
