<?php

namespace App\Repositories;

use App\Models\ServiceProvision;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 *class ServiceProvisionRepository
 */
class ServiceProvisionRepository
{
    /**
     * @var array|string[]
     */
    protected array $relations = [
        'budgetLine.project',
        'createdBy'
    ];

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ServiceProvision::query()->withTrashed()->with($this->relations);

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('reference', 'like', $searchTerm)
                  ->orWhere('supplier', 'like', $searchTerm);
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['budget_line_id']) && $filters['budget_line_id'] !== 'null') {
            $query->where('budget_line_id', $filters['budget_line_id']);
        }

        if (isset($filters['status_filter'])) {
            if ($filters['status_filter'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status_filter'] === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        } else {
            $query->whereNull('deleted_at');
        }

        $sortBy = $filters['sort_by'] ?? 'provision_date';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * @param int $id
     * @return ServiceProvision|null
     */
    public function findById(int $id): ?ServiceProvision
    {
        return ServiceProvision::withTrashed()->with($this->relations)->find($id);
    }

    /**
     * @param array $data
     * @return ServiceProvision
     */
    public function create(array $data): ServiceProvision
    {
        return ServiceProvision::create($data);
    }

    /**
     * @param ServiceProvision $serviceProvision
     * @param array $data
     * @return bool
     */
    public function update(ServiceProvision $serviceProvision, array $data): bool
    {
        return $serviceProvision->update($data);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $provision = ServiceProvision::withTrashed()->find($id);

            if (!$provision) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Prestation non trouvée.'];
                continue;
            }

            try {
                if ($provision->trashed()) {
                    $provision->restore();
                    $message = 'Prestation restaurée avec succès.';
                } else {
                    $provision->delete();
                    $message = 'Prestation désactivée avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (\Exception $e) {
                Log::error("Error toggling activation for service provision ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur lors du changement de statut.'];
            }
        }
        return $results;
    }
}
