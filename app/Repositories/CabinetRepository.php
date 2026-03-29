<?php

namespace App\Repositories;

use App\Models\Cabinet;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * class CabinetRepository
 */
class CabinetRepository
{
    /** @var Cabinet */
    protected Cabinet $model;

    public function __construct(Cabinet $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function withFilters(array $filters)
    {
        $query = $this->model->query()->with(['createdBy','trainings','externalTrainers','Place']);

        $query->when($filters['name'] ?? null, function ($q, $name) {
            return $q->where('name', 'like', '%' . $name . '%');
        });

        $query->when($filters['cabinet_id'] ?? null, function ($q, $cabinetId) {
            return $q->where('cabinet_id', 'like', '%' . $cabinetId . '%');
        });

        $query->when($filters['contact_email'] ?? null, function ($q, $email) {
            return $q->where('contact_email', 'like', '%' . $email . '%');
        });

        $query->when($filters['responsible_name'] ?? null, function ($q, $email) {
            return $q->where('responsible_name', 'like', '%' . $email . '%');
        });

        if(!empty($filters['average_rating'])){
            $query->where('average_rating','=',$filters['average_rating']);
        }


        if (isset($filters['is_active'])) {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            if (!$isActive) {
                $query->onlyTrashed();
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 10);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithoutPagination()
    {
        return $this->model->orderBy('name', 'asc')->get();
    }

    /**
     * @param int $id
     * @return Cabinet
     * @throws ModelNotFoundException
     */
    public function find(int $id): Cabinet
    {
        return $this->model->with(['createdBy', 'place'])->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Cabinet
     */
    public function create(array $data): Cabinet
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param int $id
     * @return Cabinet
     * @throws ModelNotFoundException
     */
    public function update(array $data, int $id): Cabinet
    {
        $cabinet = $this->find($id);
        $cabinet->update($data);
        return $cabinet;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return $this->model->destroy($id);
    }

    /**
     * @param array<int> $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->destroy($ids);
    }

    /**
     * @param int $id
     * @return Cabinet
     * @throws ModelNotFoundException
     */
    public function restore(int $id): Cabinet
    {
        $cabinet = $this->model->onlyTrashed()->findOrFail($id);
        $cabinet->restore();
        return $cabinet;
    }
}
