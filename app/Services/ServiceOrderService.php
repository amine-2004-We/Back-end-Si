<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\PurchaseOrder;
use App\Models\Calltender;
use App\Repositories\ServiceOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ServiceOrderService
{
    /**
     * @var ServiceOrderRepository
     */
    protected ServiceOrderRepository $serviceOrderRepository;

    public function __construct(ServiceOrderRepository $serviceOrderRepository)
    {
        $this->serviceOrderRepository = $serviceOrderRepository;
    }

    /**
     * Get paginated service orders with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->serviceOrderRepository->getAllPaginated($filters, $perPage);
    }

    /**
     * Find a specific service order by its ID.
     *
     * @param int $id
     * @return ServiceOrder
     * @throws ModelNotFoundException
     */
    public function findById(int $id): ServiceOrder
    {
        return $this->serviceOrderRepository->findById($id);
    }

    /**
     * Create a new service order.
     * Generates identifier and handles file upload.
     *
     * @param array $data Validated data from request. Expects 'signed_document' as UploadedFile.
     * @return ServiceOrder
     * @throws \Exception
     */
    public function create(array $data): ServiceOrder
    {
        $sourceData = $this->determineSource($data);
        $data = array_merge($data, $sourceData);
        
        $data['service_order_identifier'] = $this->generateUniqueIdentifier(
            $data['source_type'] ?? null,
            $data['purchase_order_id'] ?? null,
            $data['calltender_id'] ?? null
        );

        $filePath = null;
        $file = $data['signed_document'] ?? null;
        if ($file instanceof UploadedFile) {
            try {
                $filePath = $file->store('service_orders/signed_documents', 'public');
                $data['signed_document_path'] = $filePath;
            } catch (\Exception $e) {
                Log::error("ServiceOrder file upload failed: " . $e->getMessage());
                throw new \Exception("File upload failed.");
            }
        } else {
            throw new \InvalidArgumentException("Signed document is required.");
        }
        unset($data['signed_document']);

        DB::beginTransaction();
        try {
            $serviceOrder = $this->serviceOrderRepository->create($data);
            DB::commit();
            return $serviceOrder;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating ServiceOrder: " . $e->getMessage());
            if ($filePath) {
                 Storage::disk('public')->delete($filePath);
            }
            throw $e;
        }
    }

    /**
     * Update an existing service order.
     * Handles file replacement.
     *
     * @param int $id
     * @param array $data Validated data from request. May contain 'signed_document' as UploadedFile.
     * @return ServiceOrder
     * @throws ModelNotFoundException|\Exception
     */
    public function update(int $id, array $data): ServiceOrder
    {
        $serviceOrder = $this->serviceOrderRepository->findById($id);
        $oldFilePath = $serviceOrder->signed_document_path;
        $newFilePath = null;

        if (isset($data['purchase_order_id']) || isset($data['calltender_id'])) {
            $sourceData = $this->determineSource($data);
            $data = array_merge($data, $sourceData);
        }

        $file = $data['signed_document'] ?? null;
        if ($file instanceof UploadedFile) {
            try {
                $newFilePath = $file->store('service_orders/signed_documents', 'public');
                $data['signed_document_path'] = $newFilePath;
            } catch (\Exception $e) {
                 Log::error("ServiceOrder file update failed: " . $e->getMessage());
                 throw new \Exception("File upload failed during update.");
            }
        }
        unset($data['signed_document']);
        unset($data['service_order_identifier']);

        DB::beginTransaction();
        try {
            $updatedServiceOrder = $this->serviceOrderRepository->update($id, $data);

            if ($newFilePath && $oldFilePath) {
                 Storage::disk('public')->delete($oldFilePath);
            }

            DB::commit();
            return $updatedServiceOrder;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating ServiceOrder ID {$id}: " . $e->getMessage());
            if ($newFilePath) {
                 Storage::disk('public')->delete($newFilePath);
            }
            throw $e;
        }
    }

    /**
     * Soft delete a service order.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->serviceOrderRepository->delete($id);
    }

    /**
     * Restore a soft-deleted service order.
     *
     * @param int $id
     * @return ServiceOrder|null
     */
    public function restore(int $id): ?ServiceOrder
    {
        return $this->serviceOrderRepository->restore($id);
    }

    /**
     * Delete only the signed document file for a service order.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSignedDocument(int $id): bool
    {
        return $this->serviceOrderRepository->deleteSignedDocument($id);
    }

    /**
     * Determine source type based on provided data.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function determineSource(array $data): array
    {
        $hasPurchaseOrder = !empty($data['purchase_order_id']);
        $hasCalltender = !empty($data['calltender_id']);

        if ($hasPurchaseOrder && $hasCalltender) {
            throw new \Exception('Vous ne pouvez pas sélectionner à la fois un bon de commande et un marché.');
        }

        if ($hasPurchaseOrder) {
            return [
                'source_type' => 'purchase_order',
                'calltender_id' => null,
            ];
        } elseif ($hasCalltender) {
            return [
                'source_type' => 'calltender',
                'purchase_order_id' => null,
            ];
        } else {
            throw new \Exception('Vous devez sélectionner soit un bon de commande, soit un marché.');
        }
    }

    /**
     * Generate a unique Service Order Identifier.
     * Format: OS-[SourceType]-[SourceIdentifier]-[Sequence]
     *
     * @param string|null $sourceType
     * @param int|null $purchaseOrderId
     * @param int|null $calltenderId
     * @return string
     */
    protected function generateUniqueIdentifier(
        ?string $sourceType = null,
        ?int $purchaseOrderId = null,
        ?int $calltenderId = null
    ): string {
        $sourceIdentifier = 'UNKNOWN';
        
        if ($sourceType === 'purchase_order' && $purchaseOrderId) {
            $sourceIdentifier = 'BC-' . $purchaseOrderId;
        } elseif ($sourceType === 'calltender' && $calltenderId) {
            $sourceIdentifier = 'M-' . $calltenderId;
        } else {

            if ($purchaseOrderId) {
                $sourceIdentifier = 'PO-' . $purchaseOrderId;
                $sourceType = 'purchase_order';
            } elseif ($calltenderId) {
                $sourceIdentifier = 'CT-' . $calltenderId;
                $sourceType = 'calltender';
            }
        }

        $prefix = "OS-{$sourceIdentifier}-";
        
        $latestOrder = ServiceOrder::where('service_order_identifier', 'like', $prefix . '%')
                                   ->orderBy('service_order_identifier', 'desc')
                                   ->withTrashed()
                                   ->first();

        $nextNumber = 1;
        if ($latestOrder) {
            $lastNumberPart = str_replace($prefix, '', $latestOrder->service_order_identifier);
            if (is_numeric($lastNumberPart)) {
                $nextNumber = intval($lastNumberPart) + 1;
            }
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Bulk delete service orders.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException("No IDs provided for deletion.");
        }

        DB::beginTransaction();
        try {
            $count = $this->serviceOrderRepository->bulkDelete($ids);
            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get available sources for service orders.
     *
     * @param string|null $search
     * @param string|null $type
     * @return array
     */
    public function getAvailableSources(?string $search = null, ?string $type = 'all'): array
    {
        $sources = [];

        if ($type === 'all' || $type === 'purchase_order') {
            $purchaseOrders = PurchaseOrder::query()
                ->when($search, function ($query) use ($search) {
                    $query->where('po_number', 'like', "%{$search}%")
                          ->orWhere('subject', 'like', "%{$search}%");
                })
                ->where('status', 'validated')
                ->limit(20)
                ->get(['id', 'po_number', 'subject', 'supplier_id'])
                ->map(function ($po) {
                    return [
                        'type' => 'purchase_order',
                        'id' => $po->id,
                        'reference' => $po->po_number,
                        'subject' => $po->subject,
                        'supplier_id' => $po->supplier_id,
                    ];
                })
                ->toArray();

            $sources = array_merge($sources, $purchaseOrders);
        }

        if ($type === 'all' || $type === 'calltender') {
            $calltenders = Calltender::query()
                ->when($search, function ($query) use ($search) {
                    $query->where('calltender_id', 'like', "%{$search}%")
                          ->orWhere('subject', 'like', "%{$search}%")
                          ->orWhere('supplier', 'like', "%{$search}%");
                })
                ->whereIn('status', ['Signé', 'En cours'])
                ->limit(20)
                ->get(['id', 'calltender_id', 'subject', 'supplier'])
                ->map(function ($ct) {
                    return [
                        'type' => 'calltender',
                        'id' => $ct->id,
                        'reference' => $ct->calltender_id,
                        'subject' => $ct->subject,
                        'supplier' => $ct->supplier,
                    ];
                })
                ->toArray();

            $sources = array_merge($sources, $calltenders);
        }

        return $sources;
    }
}