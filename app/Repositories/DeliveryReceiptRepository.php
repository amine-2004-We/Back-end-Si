<?php

namespace App\Repositories;

use App\Models\DeliveryReceipt;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryReceiptRepository
{
    /**
     * @var DeliveryReceipt
     */
    protected DeliveryReceipt $model;

    public function __construct(DeliveryReceipt $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new receipt with its associated items.
     *
     * @param array $receiptData Data for the delivery_receipts table.
     * @param array $itemsData Array of items, e.g., [['article_id' => 1, 'quantity_received' => 10], ...]
     * @return DeliveryReceipt
     */
    public function createWithItems(array $receiptData, array $itemsData): DeliveryReceipt
    {
        return DB::transaction(function () use ($receiptData, $itemsData) {

            $receipt = $this->model->create($receiptData);

            $itemsToInsert = array_map(function ($item) use ($receipt) {
                return array_merge($item, [
                    'delivery_receipt_id' => $receipt->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }, $itemsData);

            $receipt->items()->insert($itemsToInsert);

            return $receipt->load('items');
        });
    }

    /**
     * Find a specific receipt by its ID, including relations.
     *
     * @param int $id
     * @param bool $withTrashed
     * @return DeliveryReceipt
     * @throws ModelNotFoundException
     */
    public function findById(int $id, bool $withTrashed = false): DeliveryReceipt
    {
        $query = $this->model->query()->with([
            'items.article',
            'receiver',
            'deliveryOrder'
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Get paginated receipts with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with(['receiver','deliveryOrder']);

        if (($filters['status_filter'] ?? 'active') === 'all') {
            $query->withTrashed();
        } elseif (($filters['status_filter'] ?? 'active') === 'trashed') {
            $query->onlyTrashed();
        }

        $query->when($filters['identifier'] ?? null, function ($q, $identifier) {
            return $q->where('receipt_identifier', 'like', '%' . $identifier . '%');
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            return $q->where('status', $status);
        });

        $sortBy = $filters['sort_by'] ?? 'reception_date';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Update an existing receipt.
     * Note: This simple update only changes fields on the main table.
     * Updating items (quantity) would require a more complex 'updateWithItems' method.
     *
     * @param int $id
     * @param array $data Data to update (e.g., status, observations)
     * @return DeliveryReceipt
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): DeliveryReceipt
    {
        $receipt = $this->findById($id);

        unset($data['receipt_identifier']);

        $receipt->update($data);
        return $receipt->refresh();
    }

    /**
     * Soft delete a receipt by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $receipt = $this->findById($id);
            return $receipt->delete();
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            Log::error("Error soft deleting DeliveryReceipt ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore a soft-deleted receipt.
     *
     * @param int $id
     * @return DeliveryReceipt|null
     */
    public function restore(int $id): ?DeliveryReceipt
    {
        $receipt = $this->model->onlyTrashed()->find($id);
        if ($receipt && $receipt->restore()) {
            return $receipt->load(['items.article', 'receiver']);
        }
        return null;
    }

    /**
     * Synchronizes (updates, creates, or soft-deletes) items for a delivery receipt.
     *
     * @param DeliveryReceipt $receipt The receipt to update.
     * @param array $itemsData The *new* full array of items.
     */
    public function syncItems(DeliveryReceipt $receipt, array $itemsData): void
    {
        // Get the IDs of all articles being sent from the frontend
        $incomingArticleIds = collect($itemsData)->pluck('article_id')->toArray();

        // Get all *current* items (not soft-deleted)
        $existingItems = $receipt->items()->get();

        // 1. Soft Delete: Find existing items that ARE NOT in the new list
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->article_id, $incomingArticleIds)) {
                $existingItem->delete(); // This will now soft delete
            }
        }

        // 2. Update or Create: Go through the new list
        foreach ($itemsData as $itemData) {

            // Find the item (even if it was soft-deleted)
            $item = $receipt->items()
                            ->withTrashed()
                            ->where('article_id', $itemData['article_id'])
                            ->first();

            if ($item) {
                // Item exists, update it and restore it if it was deleted
                $item->update([
                    'quantity_received' => $itemData['quantity_received'],
                    'deleted_at' => null // This restores the item
                ]);
            } else {
                // Item does not exist at all, create it
                $receipt->items()->create($itemData);
            }
        }
    }

    /**
     * Soft delete multiple receipts by their IDs.
     *
     * @param array $ids
     * @return int Count of deleted items
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
