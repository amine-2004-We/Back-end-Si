<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Repositories\DeliveryReceiptRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DeliveryReceiptService
{
    /**
     * @var DeliveryReceiptRepository
     */
    protected DeliveryReceiptRepository $deliveryReceiptRepository;

    public function __construct(DeliveryReceiptRepository $deliveryReceiptRepository)
    {
        $this->deliveryReceiptRepository = $deliveryReceiptRepository;
    }

    /**
     * Get paginated delivery receipts.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->deliveryReceiptRepository->getAllPaginated($filters, $perPage);
    }

    /**
     * Find a specific receipt by its ID.
     *
     * @param int $id
     * @return DeliveryReceipt
     * @throws ModelNotFoundException
     */
    public function findById(int $id): DeliveryReceipt
    {
        return $this->deliveryReceiptRepository->findById($id);
    }

    /**
     * Create a new delivery receipt.
     *
     * @param array $data Validated data (must include 'order_id', 'receiver_id', 'reception_date', 'status', 'items')
     * @return DeliveryReceipt
     * @throws ValidationException|\Exception
     */
    public function create(array $data): DeliveryReceipt
    {
        // 1. Separate main data from items data
        $itemsData = $data['items'] ?? [];
        unset($data['items']);

        // 2. Validate quantities
        $this->validateItemQuantities($itemsData);

        // 3. Generate unique identifier
        $data['receipt_identifier'] = $this->generateUniqueIdentifier($data['delivery_order_id']);

        // 4. Create in repository (which handles the transaction)
        try {
            $receipt = $this->deliveryReceiptRepository->createWithItems($data, $itemsData);
            return $receipt;
        } catch (\Exception $e) {
            Log::error("Error creating DeliveryReceipt: " . $e->getMessage());
            throw new \Exception("Failed to create delivery receipt.");
        }
    }

    /**
     * Update an existing delivery receipt (e.g., status, observations).
     *
     * @param int $id
     * @param array $data Validated data (e.g., ['status' => '...'])
     * @return DeliveryReceipt
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): DeliveryReceipt
    {
        $itemsData = $data['items'] ?? null;
        unset($data['items']);

        if ($itemsData !== null) {
            $this->validateItemQuantities($itemsData);
        }

        $receipt = $this->deliveryReceiptRepository->findById($id);

        return DB::transaction(function () use ($receipt, $data, $itemsData) {
            if (!empty($data)) {
                $this->deliveryReceiptRepository->update($receipt->id, $data);
            }

            if ($itemsData !== null) {
                $this->deliveryReceiptRepository->syncItems($receipt, $itemsData);
            }

            return $receipt->refresh();
        });
    }

    /**
     * Soft delete a delivery receipt.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->deliveryReceiptRepository->delete($id);
    }

    /**
     * Restore a soft-deleted delivery receipt.
     *
     * @param int $id
     * @return DeliveryReceipt|null
     */
    public function restore(int $id): ?DeliveryReceipt
    {
        return $this->deliveryReceiptRepository->restore($id);
    }

    /**
     * Validate received quantities against the purchase order.
     *
     * @param int $orderId
     * @param array $itemsData
     * @throws ValidationException
     */
    protected function validateItemQuantities(array $itemsData): void
    {

        $errors = [];
        foreach ($itemsData as $index => $item) {
            if (empty($item['article_id'])) {
                $errors["items.{$index}.article_id"] = "Article ID is required.";
            }
            if (empty($item['quantity_received']) || !is_numeric($item['quantity_received']) || $item['quantity_received'] <= 0) {
                $errors["items.{$index}.quantity_received"] = "Quantity must be a number greater than 0.";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Generate a unique receipt identifier.
     * Rule: BR-[ID ODL]-[Numéro]
     *
     * @param int $orderId
     * @return string
     */
    protected function generateUniqueIdentifier(int $orderId): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = "BR-{$orderId}-";

        $latestReceipt = DeliveryReceipt::where('receipt_identifier', 'like', $prefix . '%')
                                        ->orderBy('receipt_identifier', 'desc')
                                        ->withTrashed()
                                        ->first();

                                        $nextNumber = 1;
                                        if ($latestReceipt) {
                                            $lastNumberPart = str_replace($prefix, '', $latestReceipt->receipt_identifier);
                                            if (is_numeric($lastNumberPart)) {
                                                $nextNumber = intval($lastNumberPart) + 1;
                                            }
                                        }

                                        $sequenceNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

                                        return $prefix . $sequenceNumber;
    }

    /**
     * Soft delete multiple delivery receipts.
     *
     * @param array $ids
     * @return int Count of deleted items
     */
    public function bulkDelete(array $ids): int
    {
        return $this->deliveryReceiptRepository->bulkDelete($ids);
    }
}
