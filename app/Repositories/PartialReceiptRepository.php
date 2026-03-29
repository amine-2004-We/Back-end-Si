<?php

namespace App\Repositories;

use App\Models\PartialReceipt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PartialReceiptRepository
{
    protected $relations = [
        'deliveryReceipts.receiver',
        'deliveryReceipts.items.article'
    ];

    /**
     * Get filtered partial receipts.
     */
    public function getFiltered(array $params): LengthAwarePaginator|Collection
    {
        $query = PartialReceipt::with($this->relations);

        if (!empty($params['filter']['activation_status'])) {
            $status = $params['filter']['activation_status'];
            if ($status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($status === 'deactivated') {
                $query->onlyTrashed();
            } elseif ($status === 'all') {
                $query->withTrashed();
            }
            unset($params['filter']['activation_status']);
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('pv_partial_id', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('observations', 'ilike', '%' . $searchTerm . '%')
                  
                  // Search through DeliveryReceipt for its identifier
                  ->orWhereHas('deliveryReceipts', function ($subQ) use ($searchTerm) {
                      $subQ->where('receipt_identifier', 'ilike', '%' . $searchTerm . '%');
                  })

                  // Search through DeliveryReceipt for the receiver's name
                  ->orWhereHas('deliveryReceipts.receiver', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'ilike', '%' . $searchTerm . '%');
                  })

                  // Search through DeliveryReceipt's items for article names
                  ->orWhereHas('deliveryReceipts.items.article', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'ilike', '%' . $searchTerm . '%'); 
                  });
            });
        }

        // Handle Specific Column Filters
        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                if (!empty($value)) {
                    // Adjust filter keys if they are now on related models
                    if ($key === 'purchase_order_id') {
                        $query->whereHas('deliveryReceipts.deliveryOrder', fn($q) => $q->where('purchase_order_id', $value));
                    } elseif ($key === 'receptionist_id') {
                        $query->whereHas('deliveryReceipts', fn($q) => $q->where('receiver_id', $value));
                    } else {
                         $query->where($key, $value);
                    }
                }
            }
        }

        // Handle Sorting
        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) ? $params['sort_direction'] : 'asc';
            // Add logic for sorting by related fields if needed
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle Pagination
        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;
        if ($perPage === -1) {
            return $query->get();
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new partial receipt.
     * Articles are no longer attached here.
     */
    public function create(array $data): PartialReceipt
{
    return DB::transaction(function () use ($data) {
        // 1. Création (déclenche l'Observer)
        $partialReceipt = PartialReceipt::create($data);

        // 2. Remplissage de la table pivot delivery_receipt_partial_receipt
        if (!empty($data['delivery_receipt_ids'])) {
            $partialReceipt->deliveryReceipts()->attach($data['delivery_receipt_ids']);
        }

        return $partialReceipt->load($this->relations);
    });
}

    /**
     * Find a partial receipt by its ID.
     */
    public function find(int $id, bool $withTrashed = false): ?PartialReceipt
    {
        $query = PartialReceipt::with($this->relations);
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    /**
     * Update a partial receipt.
     * Articles are no longer synced here.
     */
    public function update(int $id, array $data): PartialReceipt
{
    return DB::transaction(function () use ($id, $data) {
        $partialReceipt = $this->find($id);
        $partialReceipt->update($data);

        // Mise à jour de la table pivot (supprime les anciens liens et met les nouveaux)
        if (isset($data['delivery_receipt_ids'])) {
            $partialReceipt->deliveryReceipts()->sync($data['delivery_receipt_ids']);
        }

        return $partialReceipt->load($this->relations);
    });
}

    /**
     * Delete a partial receipt by its ID.
     */
    public function delete(int $id): bool
    {
        $partialReceipt = $this->find($id);
        return $partialReceipt->delete();
    }

    /**
     * Restore a soft-deleted partial receipt by its ID.
     */
    public function restore(int $id): PartialReceipt
    {
        $partialReceipt = $this->find($id, true); // Find with trashed
        $partialReceipt->restore();
        return $partialReceipt;
    }

    /**
     * Bulk delete partial receipts by their IDs.
     */
    public function bulkDelete(array $ids): int
    {
        return PartialReceipt::whereIn('id', $ids)->delete();
    }
}
