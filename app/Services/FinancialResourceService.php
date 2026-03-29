<?php

namespace App\Services;

use App\Models\FinancialResource;
use App\Repositories\FinancialResourceRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class FinancialResourceService
{
    /** @var FinancialResourceRepository */
    protected FinancialResourceRepository $financialResourceRepository;

    /**
     * @param FinancialResourceRepository $financialResourceRepository
     */
    public function __construct(FinancialResourceRepository $financialResourceRepository)
    {
        $this->financialResourceRepository = $financialResourceRepository;
    }

    /**
     * Get all financial resources with filters and pagination.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'financial_resources_code',
            'financial_resources_type',
            'partner_id',
            'project_id',
            'currency',
            'financial_type',
            'financial_status',
            'created_by',
            'per_page'
        ]);

        return $this->financialResourceRepository->all($filters);
    }

    /**
     * Get all financial resources with basic fields only.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return $this->financialResourceRepository->allBasic();
    }

    /**
     * Find a single financial resource by ID.
     *
     * @param int $id
     * @return FinancialResource
     * @throws ModelNotFoundException
     */
    public function find(int $id): FinancialResource
    {
        return $this->financialResourceRepository->find($id);
    }

    /**
     * Create a new financial resource.
     *
     * @param array $data
     * @return FinancialResource
     */
    public function create(array $data): FinancialResource
    {
        return $this->financialResourceRepository->create($data);
    }

    /**
     * Update a financial resource by ID.
     *
     * @param int $id
     * @param array $data
     * @return FinancialResource
     */
    public function update(int $id, array $data): FinancialResource
    {
        return $this->financialResourceRepository->update($id, $data);
    }

    /**
     * Delete a financial resource by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->financialResourceRepository->delete($id);
    }

    /**
     * Bulk delete multiple financial resources by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->financialResourceRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted financial resource by ID.
     *
     * @param int $id
     * @return FinancialResource
     * @throws ModelNotFoundException
     */
    public function restore(int $id): FinancialResource
    {
        try {
            return $this->financialResourceRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
