<?php

namespace App\Repositories;

use App\Models\Advance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdvanceRepository
{
    /**
     * Get all advances with optional filters and pagination.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Advance::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        // Active / Inactive filter
        isset($filters['is_active']) && $filters['is_active'] === 'false'
            ? $query->onlyTrashed()
            : $query->withoutTrashed();

        // Filters
        if (!empty($filters['advance_code'])) {
            $query->where('advance_code', 'ilike', '%' . $filters['advance_code'] . '%');
        }
        if (!empty($filters['advance_type'])) {
            $query->where('advance_type', $filters['advance_type']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        if (!empty($filters['proof_status'])) {
            $query->where('proof_status', $filters['proof_status']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get a lightweight collection of advances.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return Advance::all([
            'id',
            'advance_code',
            'advance_type',
            'project_id',
            'collaborator_id',
            'advance_amount',
            'proof_expected',
            'proof_status',
        ]);
    }

    /**
     * Find an advance by ID.
     *
     * @param int $id
     * @return Advance
     */
    public function find(int $id): Advance
    {
        return Advance::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new advance.
     *
     * @param array $data
     * @return Advance
     */
    public function create(array $data): Advance
    {
        return Advance::create($data);
    }

    /**
     * Update an existing advance.
     *
     * @param int $id
     * @param array $data
     * @return Advance
     */
    public function update(int $id, array $data): Advance
    {
        $advance = $this->find($id);
        $advance->update($data);
        return $advance;
    }

    /**
     * Soft delete an advance.
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $advance = $this->find($id);
        return $advance->delete();
    }

    /**
     * Bulk delete multiple advances.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Advance::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted advance.
     *
     * @param int $id
     * @return Advance
     */
    public function restore(int $id): Advance
    {
        $advance = Advance::onlyTrashed()->findOrFail($id);
        $advance->restore();
        return $advance;
    }
}
