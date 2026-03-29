<?php

namespace App\Repositories;

use App\Models\FinancialResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FinancialResourceRepository
{
    /**
     * Get paginated list of FinancialResources with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = FinancialResource::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        // Handle soft delete filters
        isset($filters['is_active']) && $filters['is_active'] === 'false'
            ? $query->onlyTrashed() : $query->withoutTrashed();

        // Apply filters
        if (!empty($filters['financial_resources_code'])) {
            $query->where('financial_resources_code', 'ilike', '%' . $filters['financial_resources_code'] . '%');
        }
        if (!empty($filters['financial_resources_type'])) {
            $query->where('financial_resources_type', $filters['financial_resources_type']);
        }
        if (!empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }
        if (!empty($filters['financial_type'])) {
            $query->where('financial_type', $filters['financial_type']);
        }
        if (!empty($filters['financial_status'])) {
            $query->where('financial_status', $filters['financial_status']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all financial resources with only id and basic fields.
     *
     * @return Collection
     */
    public function allBasic(): Collection
    {
        return FinancialResource::all([
            'id',
            'financial_resources_code',
            'financial_resources_type',
            'partner_id',
            'project_id',
            'amount_received',
            'currency',
            'financial_type',
            'financial_status',
        ]);
    }

    /**
     * Find a FinancialResource by ID, including soft deleted.
     *
     * @param int $id
     * @return FinancialResource
     * @throws ModelNotFoundException
     */
    public function find(int $id): FinancialResource
    {
        return FinancialResource::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new FinancialResource.
     *
     * @param array $data
     * @return FinancialResource
     */
    public function create(array $data): FinancialResource
    {
        return FinancialResource::create($data);
    }

    /**
     * Update a FinancialResource by ID.
     *
     * @param int $id
     * @param array $data
     * @return FinancialResource
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): FinancialResource
    {
        $resource = $this->find($id);
        $resource->update($data);
        return $resource;
    }

    /**
     * Delete a FinancialResource by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $resource = $this->find($id);
        return $resource->delete();
    }

    /**
     * Bulk delete FinancialResources by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return FinancialResource::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted FinancialResource by ID.
     *
     * @param int $id
     * @return FinancialResource
     */
    public function restore(int $id): FinancialResource
    {
        $resource = FinancialResource::onlyTrashed()->findOrFail($id);
        $resource->restore();
        return $resource;
    }
}
