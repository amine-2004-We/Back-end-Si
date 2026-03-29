<?php

namespace App\Services;

use App\Models\ExpenseReport;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /** @var PaymentRepository */
    protected PaymentRepository $paymentRepository;

    /**
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Get all payments with filters and pagination.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'payment_number',
            'payment_method',
            'payment_status',
            'created_by',
            'payment_type',
            'per_page'
        ]);

        return $this->paymentRepository->all($filters);
    }

    /**
     * Get all payments (only selected columns).
     *
     * @return Collection
     */
    public function allPayments(): Collection
    {
        return $this->paymentRepository->allPayments();
    }

    /**
     * Find a single payment by ID.
     *
     * @param int $id
     * @return Payment
     * @throws ModelNotFoundException
     */
    public function find(int $id): Payment
    {
        return $this->paymentRepository->find($id);
    }

    /**
     * Create a new payment with invoices or expense notes.
     *
     * @param array $data
     * @return Payment
     */
    public function createWithInvoices(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $paymentType = $data['payment_type'] ?? Payment::TYPE_INVOICE;
            $totalAmount = 0;

            if ($paymentType === Payment::TYPE_INVOICE) {
                // Handle invoice payment
                $invoiceIds = $data['invoice_ids'] ?? [];
                $invoices = Invoice::whereIn('id', $invoiceIds)->get();
                $totalAmount = $invoices->sum('total');
            } else {
                // Handle expense report payment
                $expenseReportIds = $data['expense_report_ids'] ?? [];
                $expenseReports = ExpenseReport::whereIn('id', $expenseReportIds)->get();
                $totalAmount = $expenseReports->sum('total_amount');
            }

            // Create payment
            $paymentData = [
                'transaction_date' => $data['transaction_date'],
                'due_date' => $data['due_date'] ?? null,
                'amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'],
                'note' => $data['note'] ?? null,
                'project_id' => $data['project_id'],
                'payment_type' => $paymentType,
                'created_by' => auth()->id(),
            ];

            $payment = $this->paymentRepository->create($paymentData);

            if ($paymentType === Payment::TYPE_INVOICE) {
                // Attach invoices to payment
                foreach ($invoices as $invoice) {
                    $payment->invoices()->attach($invoice->id, [
                        'amount' => $invoice->total,
                    ]);
                }
                
                // Mark invoices as paid if payment is validated
                if ($data['payment_status'] === 'Validé') {
                    Invoice::whereIn('id', $invoiceIds)->update(['status' => Invoice::STATUS_PAID]);
                }
                
                return $payment->load('invoices', 'project');
            } else {
                // Attach expense reports to payment
                foreach ($expenseReports as $expenseReport) {
                    $payment->expenseReports()->attach($expenseReport->id, [
                        'amount' => $expenseReport->total_amount,
                    ]);
                }
                
                // Mark expense reports as paid if payment is validated
                if ($data['payment_status'] === 'Validé') {
                    ExpenseReport::whereIn('id', $expenseReportIds)->update(['status' => ExpenseReport::STATUS_PAID]);
                }
                
                return $payment->load('expenseReports', 'project');
            }
        });
    }

    /**
     * Update a payment by ID.
     *
     * @param int $id
     * @param array $data
     * @return Payment
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Payment
    {
        return DB::transaction(function () use ($id, $data) {
            $payment = $this->paymentRepository->find($id);
            $paymentType = $data['payment_type'] ?? $payment->payment_type ?? Payment::TYPE_INVOICE;
            
            $totalAmount = 0;

            if ($paymentType === Payment::TYPE_INVOICE) {
                $invoiceIds = $data['invoice_ids'] ?? [];
                if (!empty($invoiceIds)) {
                    $invoices = Invoice::whereIn('id', $invoiceIds)->get();
                    $totalAmount = $invoices->sum('total');
                    
                    // Sync invoices
                    $syncData = [];
                    foreach ($invoices as $invoice) {
                        $syncData[$invoice->id] = ['amount' => $invoice->total];
                    }
                    $payment->invoices()->sync($syncData);
                    $payment->expenseReports()->detach(); // Remove any expense reports
                }
            } else {
                $expenseReportIds = $data['expense_report_ids'] ?? [];
                if (!empty($expenseReportIds)) {
                    $expenseReports = ExpenseReport::whereIn('id', $expenseReportIds)->get();
                    $totalAmount = $expenseReports->sum('total_amount');
                    
                    // Sync expense reports
                    $syncData = [];
                    foreach ($expenseReports as $expenseReport) {
                        $syncData[$expenseReport->id] = ['amount' => $expenseReport->total_amount];
                    }
                    $payment->expenseReports()->sync($syncData);
                    $payment->invoices()->detach(); // Remove any invoices
                }
            }

            // Update payment data
            $updateData = [
                'transaction_date' => $data['transaction_date'] ?? $payment->transaction_date,
                'due_date' => $data['due_date'] ?? $payment->due_date,
                'payment_method' => $data['payment_method'] ?? $payment->payment_method,
                'payment_status' => $data['payment_status'] ?? $payment->payment_status,
                'note' => $data['note'] ?? $payment->note,
                'project_id' => $data['project_id'] ?? $payment->project_id,
                'payment_type' => $paymentType,
            ];

            if ($totalAmount > 0) {
                $updateData['amount'] = $totalAmount;
            }

            $updatedPayment = $this->paymentRepository->update($id, $updateData);
            
            // Mark items as paid if payment status is Validé
            $newStatus = $data['payment_status'] ?? $payment->payment_status;
            if ($newStatus === 'Validé') {
                if ($paymentType === Payment::TYPE_INVOICE) {
                    $invoiceIds = $updatedPayment->invoices()->pluck('invoices.id')->toArray();
                    if (!empty($invoiceIds)) {
                        Invoice::whereIn('id', $invoiceIds)->update(['status' => Invoice::STATUS_PAID]);
                    }
                } else {
                    $expenseReportIds = $updatedPayment->expenseReports()->pluck('expense_reports.id')->toArray();
                    if (!empty($expenseReportIds)) {
                        ExpenseReport::whereIn('id', $expenseReportIds)->update(['status' => ExpenseReport::STATUS_PAID]);
                    }
                }
            }
            
            return $updatedPayment;
        });
    }

    /**
     * Delete a payment by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->paymentRepository->delete($id);
    }

    /**
     * Bulk delete multiple payments by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->paymentRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted payment by ID.
     *
     * @param int $id
     * @return Payment
     * @throws ModelNotFoundException
     */
    public function restore(int $id): Payment
    {
        try {
            return $this->paymentRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
