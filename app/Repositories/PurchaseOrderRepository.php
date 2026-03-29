<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Database\Eloquent\Collection;

/**
 * class PurchaseOrderRepository
 */
class PurchaseOrderRepository
{
    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function all(): mixed
    {
        return PurchaseOrder::with(['quote', 'supplier', 'issuer', 'purchaseRequest'])
            ->paginate(10);
    }

    /**
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function withFilters(array $filters): mixed
    {
        $query = PurchaseOrder::query()
            ->with(['quote', 'supplier', 'issuer', 'purchaseRequest'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['po_number'])) {
            $query->where('po_number', 'like', '%' . $filters['po_number'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }

        if (!empty($filters['quote_id'])) {
            $query->where('quote_id', (int) $filters['quote_id']);
        }

        if (!empty($filters['issuer_id'])) {
            $query->where('issuer_id', (int) $filters['issuer_id']);
        }

        if (!empty($filters['issue_date_from'])) {
            $query->whereDate('issue_date', '>=', $filters['issue_date_from']);
        }

        if (!empty($filters['issue_date_to'])) {
            $query->whereDate('issue_date', '<=', $filters['issue_date_to']);
        }

        if (array_key_exists('is_active', $filters)) {
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

        if(!empty($filters['currency'])) {
            $query->where('currency', '=',  $filters['currency']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, PurchaseOrder>
     */
    public function allWithoutPagination(): Collection
    {
        return PurchaseOrder::all([
            'id',
            'po_number',
            'status',
            'supplier_id',
            'quote_id',
            'issuer_id',
            'issue_date',
            'total_amount_ttc',
            'currency',
            'payment_method',
        ]);
    }

    /**
     * @param int $id
     * @return PurchaseOrder|\Illuminate\Database\Eloquent\Builder<PurchaseOrder>
     */
    public function find(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::find($id);
    }

    /**
     * @param int $id
     * @return mixed|null
     */
    public function findWithTrashed(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return PurchaseOrder
     */
    public function create(array $data): PurchaseOrder
    {
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->fill($data);
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    /**
     * @param array $data
     * @param int $id
     * @return PurchaseOrder
     */
    public function update(array $data, int $id): mixed
    {
        $purchaseOrder = $this->find($id);

        if (!$purchaseOrder) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Purchase Order with ID {$id} not found."
            );
        }
        $purchaseOrder->update($data);
        return $purchaseOrder;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $purchaseOrder = $this->find($id);

        if (!$purchaseOrder) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Purchase Order with ID {$id} not found."
            );
        }

        return $purchaseOrder->delete() ? 1 : 0;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): mixed
    {
        return PurchaseOrder::destroy($ids);
    }

    /**
     * @param int $id
     * @return PurchaseOrder
     */
    public function restore(int $id): mixed
    {
        $purchaseOrder = $this->findWithTrashed($id);

        if (!$purchaseOrder) {
            abort(404, 'Purchase Order not found.');
        }

        $purchaseOrder->restore();

        return $purchaseOrder;
    }

    /***
     * @param int $purchaseOrderId
     * @return Collection
     */
    public function getPurchaseOrderLines(int $purchaseOrderId): Collection
    {
        return PurchaseOrderLine::where('purchase_order_id', $purchaseOrderId)
            ->with('purchaseOrder')
            ->get();
    }
}
