<?php

namespace App\Services;


use App\Models\PurchaseRequest;

class PurchaseRequestService
{

    public function list(array $filters)
    {
        return $this->repo->allWithFilters($filters);
    }

    public function create(array $data)
    {
        $data['code'] = $this->generateReference($data['department_id']);
        $data['created_at_date'] = now()->format('Y-m-d');
        return $this->repo->create($data);
    }


    public function update(PurchaseRequest $header, array $data)
    {
        return $this->repo->update($header, $data);
    }

    public function delete(PurchaseRequest $header)
    {
        return $this->repo->delete($header);
    }

    public function generatePdf(int $id): string
    {
        $purchaseRequest = PurchaseRequest::with([
            'products.product',
            'products.category',
            'products.budgetLine',
            'department',
            'project',
            'project.budgetLines.category',
            'project.budgetLines.projects',
            'user',
            'user.collaborator.superior',
        ])->findOrFail($id);

        $html = view('pdfs.purchase-request', [
            'purchaseRequest' => $purchaseRequest
        ])->render();
        $directory = storage_path('app/public/purchase-requests');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        $path = storage_path(
            "app/public/purchase-requests/purchase-request-{$purchaseRequest->id}.pdf"
        );
        \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($path);
        return $path;
    }
}
