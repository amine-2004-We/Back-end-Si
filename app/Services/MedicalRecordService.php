<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Repositories\MedicalRecordRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MedicalRecordService
{
    /** @var MedicalRecordRepository */
    protected MedicalRecordRepository $medicalRecordRepository;

    /**
     * @param MedicalRecordRepository $medicalRecordRepository
     */
    public function __construct(MedicalRecordRepository $medicalRecordRepository)
    {
        $this->medicalRecordRepository = $medicalRecordRepository;
    }

    /**
     * Get all medical records with filters and pagination.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'medical_records_types',
            'collaborator_id',
            'processing_status',
            'declaration_number',
            'consultation_date',
            'per_page'
        ]);

        return $this->medicalRecordRepository->all($filters);
    }

    /**
     * Get all medical records with a summary of fields.
     *
     * @return Collection
     */
    public function allSummary(): Collection
    {
        return $this->medicalRecordRepository->allSummary();
    }

    /**
     * Find a single medical record by ID.
     *
     * @param int $id
     * @return MedicalRecord
     * @throws ModelNotFoundException
     */
    public function find(int $id): MedicalRecord
    {
        return $this->medicalRecordRepository->find($id);
    }

    /**
     * Create a new medical record.
     *
     * @param array $data
     * @return MedicalRecord
     */
    public function create(array $data): MedicalRecord
    {
        return $this->medicalRecordRepository->create($data);
    }

    /**
     * Update a medical record by ID.
     *
     * @param int $id
     * @param array $data
     * @return MedicalRecord
     */
    public function update(int $id, array $data): MedicalRecord
    {
        return $this->medicalRecordRepository->update($id, $data);
    }

    /**
     * Delete a medical record by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->medicalRecordRepository->delete($id);
    }

    /**
     * Bulk delete multiple medical records by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->medicalRecordRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted medical record by ID.
     *
     * @param int $id
     * @return MedicalRecord
     * @throws ModelNotFoundException
     */
    public function restore(int $id): MedicalRecord
    {
        try {
            return $this->medicalRecordRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
