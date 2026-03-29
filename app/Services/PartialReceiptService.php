<?php

namespace App\Services;

use App\Repositories\PartialReceiptRepository;
use App\Enums\PartialReceiptStatusEnum;
use App\Models\DeliveryReceipt;
use App\Services\pdfs\PartialReceiptPdfService;

class PartialReceiptService
{
    protected PartialReceiptRepository $partialReceiptRepository;
    protected PartialReceiptPdfService $pdfService;

    public function __construct(PartialReceiptRepository $partialReceiptRepository, PartialReceiptPdfService $pdfService)
    {
        $this->partialReceiptRepository = $partialReceiptRepository;
        $this->pdfService = $pdfService;
    }
    public function generatePdf(int $id): string
    {
        return $this->pdfService->generatePdf($id);
    }

    public function getFilteredPartialReceipts(array $params)
    {
        return $this->partialReceiptRepository->getFiltered($params);
    }

    public function create(array $data)
    {
        $deliveryReceiptIds = $data['delivery_receipt_ids'] ?? [];
        unset($data['delivery_receipt_ids']);
        $partialReceipt = $this->partialReceiptRepository->create($data);
        if (!empty($deliveryReceiptIds)) {
            $partialReceipt->deliveryReceipts()->sync($deliveryReceiptIds);
        }
        return $partialReceipt->load('deliveryReceipts');
    }

    public function show(int $id, bool $withTrashed = false)
    {
        return $this->partialReceiptRepository->find($id, $withTrashed);
    }

    public function update(int $id, array $data)
    {
        $deliveryReceiptIds = $data['delivery_receipt_ids'] ?? null;
        unset($data['delivery_receipt_ids']);
        $partialReceipt = $this->partialReceiptRepository->update($id, $data);
        if (is_array($deliveryReceiptIds)) {
            $partialReceipt->deliveryReceipts()->sync($deliveryReceiptIds);
        }
        return $partialReceipt->load('deliveryReceipts');
    }

    public function delete(int $id)
    {
        return $this->partialReceiptRepository->delete($id);
    }

    public function restore(int $id)
    {
        return $this->partialReceiptRepository->restore($id);
    }

    public function bulkDelete(array $ids)
    {
        return $this->partialReceiptRepository->bulkDelete($ids);
    }

    /**
     * Get all data needed for the form.
     * This now provides Delivery Receipts instead of POs, Collabs, and Articles.
     */
    public function getFormOptions(): array
    {
        $deliveryReceipts = DeliveryReceipt::with('receiver')  
                                       ->orderBy('receipt_identifier', 'desc')
                                       ->get()
                                       ->map(fn($dr) => [
                                           'id' => $dr->id,
                                           'name' => $dr->receipt_identifier . ($dr->receiver ? ' (Reçu par: ' . $dr->receiver->name . ')': ''),
                                       ]);

        $statuses = PartialReceiptStatusEnum::values();

        return [
            'delivery_receipts' => $deliveryReceipts, 
            'statuses' => $statuses,
        ];
    }
}
