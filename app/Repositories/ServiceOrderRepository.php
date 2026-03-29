<?php

namespace App\Repositories;

use App\Models\ServiceOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ServiceOrderRepository
{
    /**
     * @var ServiceOrder
     */
    protected ServiceOrder $model;

    public function __construct(ServiceOrder $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated service orders with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with($this->getRelations());

        // Filtres par source
        if (isset($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        if (isset($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id'])
                  ->where('source_type', 'purchase_order');
        }

        if (isset($filters['calltender_id'])) {
            $query->where('calltender_id', $filters['calltender_id'])
                  ->where('source_type', 'calltender');
        }

        // Filtre de statut (actif/trash/tous)
        if (($filters['status_filter'] ?? 'active') === 'all') {
            $query->withTrashed();
        } elseif (($filters['status_filter'] ?? 'active') === 'trashed') {
            $query->onlyTrashed();
        }

        // Autres filtres
        $query->when($filters['identifier'] ?? null, function ($q, $identifier) {
            return $q->where('service_order_identifier', 'like', '%' . $identifier . '%');
        });

        $query->when($filters['supplier_id'] ?? null, function ($q, $supplierId) {
            return $q->where('supplier_id', $supplierId);
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            return $q->where('status', $status);
        });

        $query->when($filters['supervisor_id'] ?? null, function ($q, $supervisorId) {
            return $q->where('supervisor_id', $supervisorId);
        });

        // Filtres de date
        if (isset($filters['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filters['start_date_from']);
        }

        if (isset($filters['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filters['start_date_to']);
        }

        // Tri
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Find a specific service order by its ID, including soft-deleted ones if needed.
     *
     * @param int $id
     * @param bool $withTrashed
     * @return ServiceOrder
     * @throws ModelNotFoundException
     */
    public function findById(int $id, bool $withTrashed = false): ServiceOrder
    {
        $query = $this->model->query()->with($this->getRelations());
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    /**
     * Create a new service order.
     * Assumes file path is already generated and passed in $data.
     *
     * @param array $data Must include 'signed_document_path'
     * @return ServiceOrder
     */
    public function create(array $data): ServiceOrder
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing service order.
     * Assumes new file path (if any) is passed in $data.
     * Does NOT handle deleting the old file here (Service should do that).
     *
     * @param int $id
     * @param array $data May include 'signed_document_path' if changed
     * @return ServiceOrder
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): ServiceOrder
    {
        $serviceOrder = $this->findById($id);
        $serviceOrder->update($data);
        return $serviceOrder->refresh();
    }

    /**
     * Soft delete a service order by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $serviceOrder = $this->findById($id);
            return $serviceOrder->delete();
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            Log::error("Error soft deleting ServiceOrder ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore a soft-deleted service order.
     *
     * @param int $id
     * @return ServiceOrder|null
     */
    public function restore(int $id): ?ServiceOrder
    {
        $serviceOrder = $this->model->onlyTrashed()->find($id);
        if ($serviceOrder && $serviceOrder->restore()) {
            return $serviceOrder->load($this->getRelations());
        }
        return null;
    }

    /**
     * Permanently delete a service order (use with caution).
     * Deletes the associated signed document file first.
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete(int $id): bool
    {
         $serviceOrder = $this->findById($id, true);
         if ($serviceOrder) {
             if ($serviceOrder->signed_document_path) {
                 Storage::disk('public')->delete($serviceOrder->signed_document_path);
             }
             return $serviceOrder->forceDelete();
         }
         return false;
    }

    /**
     * Delete only the signed document file associated with a service order.
     * Updates the record to remove the path.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSignedDocument(int $id): bool
    {
        try {
            $serviceOrder = $this->findById($id, true);
            if ($serviceOrder->signed_document_path) {
                if (Storage::disk('public')->delete($serviceOrder->signed_document_path)) {
                    $serviceOrder->signed_document_path = null;
                    return $serviceOrder->save();
                }
                Log::warning("Storage::delete failed for document of ServiceOrder ID {$id}");
                return false;
            }
            return true;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            Log::error("Error deleting document for ServiceOrder ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk delete service orders.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return ServiceOrder::destroy($ids);
    }

    /**
     * Get the appropriate relations to load based on source type.
     *
     * @return array
     */
    protected function getRelations(): array
    {
        return [
            'purchaseOrder:id,po_number,subject,status,supplier_id',
            'calltender:id,calltender_id,subject,status,supplier',
            'supplier:id,company_name,trade_name',
            'supervisor:id,name,email'
        ];
    }

    /**
     * Get service orders by purchase order.
     *
     * @param int $purchaseOrderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPurchaseOrder(int $purchaseOrderId)
    {
        return $this->model->where('purchase_order_id', $purchaseOrderId)
                           ->where('source_type', 'purchase_order')
                           ->with($this->getRelations())
                           ->get();
    }

    /**
     * Get service orders by calltender.
     *
     * @param int $calltenderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCalltender(int $calltenderId)
    {
        return $this->model->where('calltender_id', $calltenderId)
                           ->where('source_type', 'calltender')
                           ->with($this->getRelations())
                           ->get();
    }

    /**
     * Count service orders by source type.
     *
     * @return array
     */
    public function countBySourceType(): array
    {
        return [
            'purchase_order' => $this->model->where('source_type', 'purchase_order')->count(),
            'calltender' => $this->model->where('source_type', 'calltender')->count(),
            'total' => $this->model->count(),
        ];
    }

    /**
     * Find service order by identifier.
     *
     * @param string $identifier
     * @return ServiceOrder|null
     */
    public function findByIdentifier(string $identifier): ?ServiceOrder
    {
        return $this->model->where('service_order_identifier', $identifier)
                           ->with($this->getRelations())
                           ->first();
    }
}