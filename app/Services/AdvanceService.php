<?php

namespace App\Services;

use App\Models\Advance;
use App\Repositories\AdvanceRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class AdvanceService
{
    protected AdvanceRepository $advanceRepository;

    public function __construct(AdvanceRepository $advanceRepository)
    {
        $this->advanceRepository = $advanceRepository;
    }

    /**
     * Get paginated list of advances with filters.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'advance_code',
            'advance_type',
            'project_id',
            'collaborator_id',
            'created_by',
            'proof_status',
            'per_page',
        ]);

        return $this->advanceRepository->all($filters);
    }

    /**
     * Get a lightweight collection of advances.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return $this->advanceRepository->allBasic();
    }

    /**
     * Find an advance by ID.
     *
     * @param int $id
     * @return Advance
     */
    public function find(int $id): Advance
    {
        return $this->advanceRepository->find($id);
    }

    /**
     * Create a new advance.
     *
     * @param array $data
     * @return Advance
     */
    public function create(array $data): Advance
    {
        return $this->advanceRepository->create($data);
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
        return $this->advanceRepository->update($id, $data);
    }

    /**
     * Delete an advance (soft delete).
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return $this->advanceRepository->delete($id);
    }

    /**
     * Bulk delete multiple advances.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->advanceRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft deleted advance.
     *
     * @param int $id
     * @return Advance
     */
    public function restore(int $id): Advance
    {
        try {
            return $this->advanceRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
