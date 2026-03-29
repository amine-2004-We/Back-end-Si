<?php

namespace App\Services;

use App\Models\DeliveryRequest;
use App\Repositories\DeliveryRequestRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class DeliveryRequestService
{
    /** @var DeliveryRequestRepository */
    protected DeliveryRequestRepository $deliveryRequestRepository;

    /**
     * @param DeliveryRequestRepository $deliveryRequestRepository
     */
    public function __construct(DeliveryRequestRepository $deliveryRequestRepository)
    {
        $this->deliveryRequestRepository = $deliveryRequestRepository;
    }

    /**
     * Get all delivery requests with filters and pagination.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'code',
            'purchase_request_id',
            'purchase_order_id',
            'applicant',
            'recipient', 
            'recipient_contact', 
            'priority',
            'status',
            'created_by',
            'superior_id',
            'request_date_from', 
            'request_date_to',
            'delivery_date_from',
            'delivery_date_to',
            'per_page'
        ]);

        // Eager load purchaseOrder and quote
        return DeliveryRequest::with(['purchaseOrder.quote', 'items.article.product','creator.collaborator.superior.user'])->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Get all delivery requests with basic fields only.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return $this->deliveryRequestRepository->allBasic();
    }

    /**
     * Find a single delivery request by ID.
     *
     * @param int $id
     * @return DeliveryRequest
     * @throws ModelNotFoundException
     */
    public function find(int $id): DeliveryRequest
    {
        // Eager load purchaseOrder and quote for single item
        return DeliveryRequest::with(['purchaseOrder.quote','items'])->findOrFail($id);
    }

    /**
     * Create a new delivery request with items.
     *
     * @param array $data
     * @return DeliveryRequest
     */
    public function create(array $data): DeliveryRequest
    {
        $itemsData = $data['items'] ?? [];
        unset($data['items']);

        return $this->deliveryRequestRepository->createWithItems($data, $itemsData);
    }

    /**
     * Update an existing delivery request with items.
     *
     * @param int $id
     * @param array $data
     * @return DeliveryRequest
     */
    public function update(int $id, array $data): DeliveryRequest
    {
        $itemsData = $data['items'] ?? [];
        unset($data['items']);

        return $this->deliveryRequestRepository->updateWithItems($id, $data, $itemsData);
    }

    /**
     * Delete a delivery request by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->deliveryRequestRepository->delete($id);
    }

    /**
     * Bulk delete multiple delivery requests by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->deliveryRequestRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted delivery request by ID.
     *
     * @param int $id
     * @return DeliveryRequest
     * @throws ModelNotFoundException
     */
    public function restore(int $id): DeliveryRequest
    {
        try {
            return $this->deliveryRequestRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}