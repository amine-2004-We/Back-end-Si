<?php

namespace App\Repositories;

use App\Models\MedicalRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MedicalRecordRepository
{
    /**
     * Get paginated list of Medical Records with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = MedicalRecord::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        // Handle soft delete filters
        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        // Apply filters
        if (!empty($filters['medical_records_types'])) {
            $query->where('medical_records_types', $filters['medical_records_types']);
        }

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }

        if (!empty($filters['processing_status'])) {
            $query->where('processing_status', $filters['processing_status']);
        }

        if (!empty($filters['declaration_number'])) {
            $query->where('declaration_number', 'like', '%' . $filters['declaration_number'] . '%');
        }

        if (!empty($filters['consultation_date'])) {
            $query->whereDate('consultation_date', $filters['consultation_date']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all medical records with selected fields.
     *
     * @return Collection
     */
    public function allSummary(): Collection
    {
        return MedicalRecord::select(
            'id',
            'collaborator_id',
            'medical_records_types',
            'consultation_date',
            'processing_status',
            'created_by'
        )->get();
    }

    /**
     * Find a Medical Record by ID, including soft deleted.
     *
     * @param int $id
     * @return MedicalRecord
     * @throws ModelNotFoundException
     */
    public function find(int $id): MedicalRecord
    {
        return MedicalRecord::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new Medical Record.
     *
     * @param array $data
     * @return MedicalRecord
     */
    public function create(array $data): MedicalRecord
    {
        return MedicalRecord::create($data);
    }

    /**
     * Update a Medical Record by ID.
     *
     * @param int $id
     * @param array $data
     * @return MedicalRecord
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): MedicalRecord
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    /**
     * Delete a Medical Record by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $record = $this->find($id);
        return $record->delete();
    }

    /**
     * Bulk delete Medical Records by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return MedicalRecord::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Medical Record by ID.
     *
     * @param int $id
     * @return MedicalRecord
     */
    public function restore(int $id): MedicalRecord
    {
        $record = MedicalRecord::onlyTrashed()->findOrFail($id);
        $record->restore();
        return $record;
    }
}
