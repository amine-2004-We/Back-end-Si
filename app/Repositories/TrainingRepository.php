<?php

namespace App\Repositories;

use App\Models\Training;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * class TrainingRepository
 */
class TrainingRepository
{
    /** @var Training */
    protected Training $model;

    public function __construct(Training $model)
    {
        $this->model = $model;
    }

    /**
     * Get all trainings with filters and pagination.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function withFilters(array $filters)
    {
        $query = $this->model->query()->with(['responsible', 'cabinet', 'createdBy','modules','trainingGroups','modules.sessions']);

        $query->when($filters['title'] ?? null, function ($q, $title) {
            return $q->where('title', 'like', '%' . $title . '%');
        });

        $query->when($filters['training_type'] ?? null, function ($q, $type) {
            return $q->where('training_type', $type);
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            return $q->where('status', $status);
        });

        $query->when($filters['responsible_id'] ?? null, function ($q, $responsibleId) {
            return $q->where('responsible_id', $responsibleId);
        });

        $query->when($filters['cabinet_id'] ?? null, function ($q, $cabinetId) {
            return $q->where('cabinet_id', $cabinetId);
        });

        $query->when($filters['target_audience'] ?? null,function($q,$target_audience){
            return $q->where('target_audience',$target_audience);
        });

        if (isset($filters['is_active'])) {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            if (!$isActive) {
                $query->onlyTrashed();
            }
        }

        return $query->orderBy('start_date', 'desc')->paginate($filters['per_page'] ?? 10);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithoutPagination()
    {
        return $this->model->orderBy('title', 'asc')->get();
    }

    /**
     * @param int $id
     * @return Training
     * @throws ModelNotFoundException
     */
    public function find(int $id): Training
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Training
     */
    public function create(array $data): Training
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param int $id
     * @return Training
     * @throws ModelNotFoundException
     */
    public function update(array $data, int $id): Training
    {
        $training = $this->find($id);
        $training->update($data);
        return $training;
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
     * @return Training
     * @throws ModelNotFoundException
     */
    public function restore(int $id): Training
    {
        $training = $this->model->onlyTrashed()->findOrFail($id);
        $training->restore();
        return $training;
    }
}
