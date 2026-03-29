<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentRepository
{
    /**
     * Get paginated list of Payments with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Payment::query()
            ->with(['project.projectBankAccount', 'invoices', 'expenseReports'])
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        // Handle soft delete filters
        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        // Apply filters
        if (!empty($filters['payment_number'])) {
            $query->where('payment_number', 'ilike', '%' . $filters['payment_number'] . '%');
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['payment_type'])) {
            $query->where('payment_type', $filters['payment_type']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all payments (only selected columns).
     *
     * @return Collection
     */
    public function allPayments(): Collection
    {
        return Payment::all([
            'id',
            'payment_number',
            'transaction_date',
            'due_date',
            'bill',
            'amount',
            'payment_method',
            'payment_status',
            'note',
            'created_by',
        ]);
    }

    /**
     * Find a Payment by ID, including soft deleted.
     *
     * @param int $id
     * @return Payment
     * @throws ModelNotFoundException
     */
    public function find(int $id): Payment
    {
        return Payment::withTrashed()->with(['project.projectBankAccount', 'invoices', 'expenseReports'])->findOrFail($id);
    }

    /**
     * Create a new Payment.
     *
     * @param array $data
     * @return Payment
     */
    public function create(array $data): Payment
    {
        $invoiceIds = $data['invoice_ids'] ?? [];
        $expenseReportIds = $data['expense_report_ids'] ?? [];
        unset($data['invoice_ids'], $data['expense_report_ids']);
        
        $payment = Payment::create($data);
        
        if (!empty($invoiceIds)) {
            $payment->invoices()->sync($invoiceIds);
        }
        
        if (!empty($expenseReportIds)) {
            $payment->expenseReports()->sync($expenseReportIds);
        }
        
        return $payment;
    }

    /**
     * Update a Payment by ID.
     *
     * @param int $id
     * @param array $data
     * @return Payment
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Payment
    {
        $payment = $this->find($id);
        $invoiceIds = $data['invoice_ids'] ?? null;
        $expenseReportIds = $data['expense_report_ids'] ?? null;
        unset($data['invoice_ids'], $data['expense_report_ids']);
        
        $payment->update($data);
        
        if ($invoiceIds !== null) {
            $payment->invoices()->sync($invoiceIds);
        }
        
        if ($expenseReportIds !== null) {
            $payment->expenseReports()->sync($expenseReportIds);
        }
        
        return $payment;
    }

    /**
     * Delete a Payment by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $payment = $this->find($id);
        return $payment->delete();
    }

    /**
     * Bulk delete Payments by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Payment::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Payment by ID.
     *
     * @param int $id
     * @return Payment
     */
    public function restore(int $id): Payment
    {
        $payment = Payment::onlyTrashed()->findOrFail($id);
        $payment->restore();
        return $payment;
    }
}
