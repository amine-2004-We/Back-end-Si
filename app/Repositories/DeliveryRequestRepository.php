<?php

namespace App\Repositories;

use App\Models\DeliveryRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DeliveryRequestRepository
{
    /**
     * Get paginated list of DeliveryRequests with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = DeliveryRequest::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        isset($filters['is_active']) && $filters['is_active'] === 'false'
            ? $query->onlyTrashed() : $query->withoutTrashed();

        if (!empty($filters['code'])) {
            $query->where('code', 'ilike', '%' . $filters['code'] . '%');
        }
        if (!empty($filters['purchase_request_id'])) {
            $query->where('purchase_request_id', $filters['purchase_request_id']);
        }
        if (!empty($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }
        if (!empty($filters['applicant'])) {
            $query->where('applicant', $filters['applicant']);
        }
        if (!empty($filters['recipient'])) {
            $query->where('recipient', $filters['recipient']);
        }
        if (!empty($filters['recipient_contact'])) {
            $query->where('recipient_contact', 'ilike', '%' . $filters['recipient_contact'] . '%');
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        if (!empty($filters['request_date_from'])) {
            $query->where('request_date', '>=', $filters['request_date_from']);
        }
        if (!empty($filters['request_date_to'])) {
            $query->where('request_date', '<=', $filters['request_date_to']);
        }
        if (!empty($filters['delivery_date_from'])) {
            $query->where('delivery_date', '>=', $filters['delivery_date_from']);
        }
        if (!empty($filters['delivery_date_to'])) {
            $query->where('delivery_date', '<=', $filters['delivery_date_to']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all delivery requests with only id and basic fields.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return DeliveryRequest::all([
            'id',
            'code',
            'purchase_request_id',
            'purchase_order_id',
            'applicant',
            'recipient', 
            'recipient_contact', 
            'request_date',
            'delivery_date',
            'priority',
            'status',
        ]);
    }

    /**
     * Find a DeliveryRequest by ID, including soft deleted.
     *
     * @param int $id
     * @return DeliveryRequest
     * @throws ModelNotFoundException
     */
    public function find(int $id): DeliveryRequest
    {
        return DeliveryRequest::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new DeliveryRequest.
     *
     * @param array $data
     * @return DeliveryRequest
     */
    public function create(array $data): DeliveryRequest
    {
        return DeliveryRequest::create($data);
    }

    /**
     * Update a DeliveryRequest by ID.
     *
     * @param int $id
     * @param array $data
     * @return DeliveryRequest
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): DeliveryRequest
    {
        $request = $this->find($id);
        $request->update($data);
        return $request;
    }

    /**
     * Delete a DeliveryRequest by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $request = $this->find($id);
        return $request->delete();
    }

    /**
     * Bulk delete DeliveryRequests by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return DeliveryRequest::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted DeliveryRequest by ID.
     *
     * @param int $id
     * @return DeliveryRequest
     */
    public function restore(int $id): DeliveryRequest
    {
        $request = DeliveryRequest::onlyTrashed()->findOrFail($id);
        $request->restore();
        return $request;
    }

    /**
     * Create a new delivery request with its associated items.
     *
     * @param array $requestData Data for the delivery_requests table.
     * @param array $itemsData Array of items, e.g., [['article_id' => 1, 'quantity_requested' => 10], ...]
     * @return DeliveryRequest
     */
    public function createWithItems(array $requestData, array $itemsData): DeliveryRequest
    {
        return DB::transaction(function () use ($requestData, $itemsData) {
            $deliveryRequest = DeliveryRequest::create($requestData);

            $itemsToInsert = array_map(function ($item) use ($deliveryRequest) {
                return array_merge($item, [
                    'delivery_request_id' => $deliveryRequest->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, $itemsData);

            $deliveryRequest->items()->insert($itemsToInsert);

            return $deliveryRequest->load('items');
        });
    }

    /**
     * Update an existing delivery request and its associated items.
     *
     * @param int $id
     * @param array $requestData Data for the delivery_requests table.
     * @param array $itemsData Array of items, e.g., [['article_id' => 1, 'quantity_requested' => 10], ...]
     * @return DeliveryRequest
     */
    public function updateWithItems(int $id, array $requestData, array $itemsData): DeliveryRequest
    {
        return DB::transaction(function () use ($id, $requestData, $itemsData) {
            $deliveryRequest = DeliveryRequest::findOrFail($id);

            $deliveryRequest->update($requestData);

            $itemsToInsert = array_map(function ($item) use ($deliveryRequest) {
                return array_merge($item, [
                    'delivery_request_id' => $deliveryRequest->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, $itemsData);

            $deliveryRequest->items()->delete();
            $deliveryRequest->items()->insert($itemsToInsert);

            return $deliveryRequest->load('items');
        });
    }
}