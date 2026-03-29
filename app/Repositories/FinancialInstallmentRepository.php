<?php

namespace App\Repositories;

use App\Models\FinancialInstallment;
use Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FinancialInstallmentRepository
{
    /**
     * Get paginated list of installments with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = FinancialInstallment::with('convention', 'receptions')->orderBy('created_at', 'desc');

        if (!empty($filters['convention_id'])) {
            $query->where('convention_id', (int) $filters['convention_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Find an installment by ID.
     *
     * @param int $id
     * @return FinancialInstallment
     */
    public function find(int $id): FinancialInstallment
    {
        return FinancialInstallment::with('convention', 'receptions')->findOrFail($id);
    }

    /**
     * Create an installment.
     *
     * @param array $data
     * @return FinancialInstallment
     */
    public function create(array $data): FinancialInstallment
    {
        $receptions = $data['receptions'] ?? [];
        unset($data['receptions']);
        if (empty($data['due_date'])) {
            $data['due_date'] = now()->toDateString();
        }

        $installment = FinancialInstallment::create($data);

        foreach ($receptions as $reception) {
            $installment->receptions()->create($reception);
        }

        return $installment->fresh('convention', 'receptions');
    }

    /**
     * Update an installment.
     *
     * @param int $id
     * @param array $data
     * @return FinancialInstallment
     */
    /**
     * Update an installment.
     *
     * @param int $id
     * @param array $data
     * @return FinancialInstallment
     */
    public function update(int $id, array $data): FinancialInstallment
    {
        return DB::transaction(function () use ($id, $data) {
            $installment = $this->find($id);

             if (isset($data['proof_document']) && $data['proof_document'] instanceof \Illuminate\Http\UploadedFile) {
                $data['proof_document'] = $data['proof_document']->store('installments', 'private');
            }

             $receptionsData = $data['receptions'] ?? null;
            unset($data['receptions']);

             $installment->update($data);

             if ($receptionsData !== null) {
                // Get current reception IDs
                $currentReceptionIds = $installment->receptions->pluck('id')->toArray();
                $incomingReceptionIds = [];

                foreach ($receptionsData as $receptionData) {
                    if (isset($receptionData['id'])) {
                         $incomingReceptionIds[] = $receptionData['id'];
                        $installment->receptions()->where('id', $receptionData['id'])->update($receptionData);
                    } else {
                         $newReception = $installment->receptions()->create($receptionData);
                        $incomingReceptionIds[] = $newReception->id;
                    }
                }

                 $receptionsToDelete = array_diff($currentReceptionIds, $incomingReceptionIds);
                if (!empty($receptionsToDelete)) {
                    $installment->receptions()->whereIn('id', $receptionsToDelete)->delete();
                }
            }

            return $installment->fresh('convention', 'receptions');
        });
    }

    /**
     * Delete an installment.
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $installment = $this->find($id);
        return $installment->delete();
    }
}
