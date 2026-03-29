<?php

namespace App\Services;

use App\Models\BudgetLineProject;
use App\Models\Invoice;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequestLine;
use App\Repositories\InvoiceRepository;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * class InvoiceService
 */
class InvoiceService
{
    use UploadFileTrait;

    protected InvoiceRepository $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Get all invoices with filters.
     */
    public function getAll(Request $request)
    {
        $filters = $request->only([
            'is_received', 'status', 'is_active', 'subject',
            'supplier_invoice_number', 'per_page'
        ]);

        return $this->invoiceRepository->withFilters($filters);
    }

    /**
     * Find an invoice by its ID.
     */
    public function find(int $id): Invoice
    {
        return $this->invoiceRepository->find($id);
    }

    /**
     * Create an unreceived invoice.
     */
    public function createUnreceived(array $data): Invoice
    {
        // Enforce the status for this workflow
        $data['status'] = Invoice::STATUS_UNRECEIVED;
        return $this->invoiceRepository->create($data);
    }

    /**
     * Process a batch of unreceived invoices to mark them as received.
     * This method now handles a nested array of invoice data.
     */
    public function processReceived(array $data): int
    {
        return DB::transaction(function () use ($data) {
            $updatedCount = 0;
            foreach ($data['invoices'] as $invoiceData) {
                $invoiceId = $invoiceData['unreceived_invoice_id'];
                unset($invoiceData['unreceived_invoice_id']);

                // Prepare the data for the update
                $updatePayload = $invoiceData;
                $updatePayload['status'] = Invoice::STATUS_PENDING_VALIDATION; // Set the new status

                $this->invoiceRepository->update($updatePayload, $invoiceId);
                $updatedCount++;
            }
            return $updatedCount;
        });
    }

    /**
     * Update a general invoice record.
     */
    public function update(int $id, array $data, Request $request): Invoice
    {
        $invoice = $this->invoiceRepository->find($id);
        $this->handleAttachmentUpdate($invoice, $data, $request);

        if ($request->status === Invoice::STATUS_ACCOUNTED) {

            $deliveryReceipt = $invoice->deliveryReceipt;
             // Ensure relationships are loaded
             $deliveryReceipt?->load('deliveryOrder.purchaseOrder');
             
             // Traverse to get PurchaseOrder
             $purchaseOrder = $deliveryReceipt?->deliveryOrder?->purchaseOrder;

            if ($purchaseOrder) {
                $purchaseOrder->load('purchaseRequest');
                $projectId = $purchaseOrder->purchaseRequest->project_id ?? null;
                if (!$projectId) {
                    throw new \Exception(
                        'Projet introuvable pour le bon de commande lié à la facture'
                    );
                }
                $poLines = PurchaseOrderLine::where(
                    'purchase_order_id',
                    $purchaseOrder->id
                )->get();

                foreach ($poLines as $poLine) {

                    $totalHt  = $poLine->quantity * $poLine->unit_price;
                    $tva      = $totalHt * $poLine->tva_rate;
                    $totalTtc = $totalHt + $tva;

                    $productId = $poLine->product_id;
                    if (!$productId) {
                        continue;
                    }

                    $prLine = PurchaseRequestLine::where('product_id', $productId)
                        ->latest()
                        ->first();

                    if (!$prLine || !$prLine->budget_line_id) {
                        continue;
                    }

                    $budgetLineProject = BudgetLineProject::where('budget_line_id', $prLine->budget_line_id)
                        ->where('project_id', $projectId)
                        ->first();

                    if (!$budgetLineProject) {
                        continue;
                    }

                    $budgetLineProject->decrement('engaged_amount', $totalTtc);
                    $budgetLineProject->increment('consumed_amount', $totalTtc);
                }
            }
        }

        $invoice->update($data);

        return $invoice;
    }

    /**
     * Soft delete an invoice.
     */
    public function delete(int $id): bool
    {
        return $this->invoiceRepository->delete($id);
    }

    /**
     * Bulk delete invoices.
     */
    public function bulkDestroy(array $ids): int
    {
        return $this->invoiceRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted invoice.
     */
    public function restore(int $id): Invoice
    {
        return $this->invoiceRepository->restore($id);
    }

    /**
     * Get a list of unreceived invoices for frontend options.
     */
    public function getUnreceivedInvoicesOptions(): Collection
    {
        return $this->invoiceRepository->getUnreceivedInvoices();
    }

    /**
     * Helper to manage attachment updates.
     */
    private function handleAttachmentUpdate(Invoice $invoice, array &$data, Request $request): void
    {
        if ($request->boolean('remove_attachment')) {
            if ($invoice->attachment_path) {
                $this->deletePublicFile($invoice->attachment_path);
            }
            $data['attachment_path'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($invoice->attachment_path) {
                $this->deletePublicFile($invoice->attachment_path);
            }
            $data['attachment_path'] = $this->uploadPublicFile($request->file('attachment'), 'invoices');
        }
    }
}

