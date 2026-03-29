<?php

namespace App\Services;

use App\Repositories\FinancialInstallmentRepository;
use App\Models\FinancialInstallment;
use Illuminate\Support\Facades\Storage;

class FinancialInstallmentService
{
    protected FinancialInstallmentRepository $repo;

    public function __construct(FinancialInstallmentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list(array $filters = [])
    {
        return $this->repo->all($filters);
    }

    public function find(int $id): FinancialInstallment
    {
        return $this->repo->find($id);
    }

    public function create(array $data): FinancialInstallment
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): FinancialInstallment
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id): int
    {
        return $this->repo->delete($id);
    }

    /**
     * Prepare the download for a proof document.
     */
    public function downloadDocument(int $id): array
    {
        $installment = $this->repo->find($id);
        $path = $installment->proof_document;

        if (!$path || !Storage::disk('private')->exists($path)) {
            throw new \Exception("Document not found for this installment.", 404);
        }

        return [
            'path' => $path,
            'name' => basename($path),
            'mime' => Storage::disk('private')->mimeType($path)
        ];
    }
}
