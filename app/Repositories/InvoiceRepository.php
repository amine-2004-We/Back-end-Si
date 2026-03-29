<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class InvoiceRepository
 */
class InvoiceRepository
{
    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function withFilters(array $filters): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with(['accountingAccount'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }
        
        if (isset($filters['is_received'])) {
            if ($filters['is_received'] === 'true') {
                $query->where('status', '!=', Invoice::STATUS_UNRECEIVED);
            } elseif ($filters['is_received'] === 'false') {
                $query->where('status', '=', Invoice::STATUS_UNRECEIVED);
            }
        }

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        if (!empty($filters['subject'])) {
            $query->where('subject', 'like', '%' . $filters['subject'] . '%');
        }
        
        if (!empty($filters['supplier_invoice_number'])) {
            $query->where('supplier_invoice_number', 'LIKE', '%' . $filters['supplier_invoice_number'] . '%');
        }

        $perPage = !empty($filters['per_page']) ? (int)$filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get a list of invoices that are marked as "Non parvenue".
     *
     * @return Collection
     */
    public function getUnreceivedInvoices(): Collection
    {
        return Invoice::where('status', Invoice::STATUS_UNRECEIVED)
                      ->whereNull('deleted_at')
                      ->select('id', 'invoice_number', 'subject')
                      ->orderBy('invoice_number')
                      ->get();
    }
    
    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return Invoice::select('id', 'invoice_number', 'status', 'due_date', 'supplier_invoice_number', 'reception_date')->get();
    }

    /**
     * @param int $id
     * @return Invoice
     */
    public function find(int $id): Invoice
    {
        return Invoice::with('accountingAccount')->findOrFail($id);
    }

    /**
     * @param int $id
     * @return Invoice
     */
    public function findWithTrashed(int $id): Invoice
    {
        return Invoice::withTrashed()->with('accountingAccount')->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    /**
     * @param array $data
     * @param int $id
     * @return Invoice
     */
    public function update(array $data, int $id): Invoice
    {
        $invoice = $this->find($id);
        $invoice->update($data);
        return $invoice;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Invoice::destroy($id) > 0;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Invoice::destroy($ids);
    }

    /**
     * @param int $id
     * @return Invoice
     * @throws ModelNotFoundException
     */
    public function restore(int $id): Invoice
    {
        $invoice = $this->findWithTrashed($id);

        if (!$invoice) {
            abort(404, 'Facture introuvable.');
        }

        if (Invoice::where('invoice_number', $invoice->invoice_number)->whereNull('deleted_at')->exists()) {
            abort(409, 'Conflit : le numéro de facture est déjà utilisé.');
        }

        $invoice->restore();
        return $invoice;
    }
}

