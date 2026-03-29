<?php

namespace App\Repositories;

use App\Models\Absence;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class AbsenceRepository
 */
class AbsenceRepository
{
    /**
     * @return mixed
     */
    public function all(): mixed
    {
        return Absence::paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator|mixed
     */
    public function withFilters(array $filters): mixed
    {
        $query = Absence::query()
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at')
            ->with('collaborator');

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', '=', $filters['collaborator_id']);
        }

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

        if(!empty($filters['reason'])) {
            $query->where('reason', 'ilike', '%' . $filters['reason'] . '%');
        }

        if(!empty($filters['absence_type'])) {
            $query->where('absence_type', '=', $filters['absence_type']);
        }

        if(!empty($filters['absence_status'])){
            $query->where('absence_status','=', $filters['absence_status']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('start_date', $filters['start_date']);
        }

        if (!empty($filters['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filters['start_date_from']);
        }

        if (!empty($filters['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filters['start_date_to']);
        }

        if (!empty($filters['start_time'])) {
            $query->whereTime('start_time', $filters['start_time']);
        }
        if (!empty($filters['start_time_from'])) {
            $query->whereTime('start_time', '>=', $filters['start_time_from']);
        }
        if (!empty($filters['start_time_to'])) {
            $query->whereTime('start_time', '<=', $filters['start_time_to']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('end_date', $filters['end_date']);
        }
        if (!empty($filters['end_date_from'])) {
            $query->whereDate('end_date', '>=', $filters['end_date_from']);
        }
        if (!empty($filters['end_date_to'])) {
            $query->whereDate('end_date', '<=', $filters['end_date_to']);
        }

        if (!empty($filters['end_time'])) {
            $query->whereTime('end_time', $filters['end_time']);
        }
        if (!empty($filters['end_time_from'])) {
            $query->whereTime('end_time', '>=', $filters['end_time_from']);
        }
        if (!empty($filters['end_time_to'])) {
            $query->whereTime('end_time', '<=', $filters['end_time_to']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return Absence::all('id','collaborator_id','reason','absence_type', 'absence_status', 'start_date', 'start_time', 'end_date', 'end_time', 'justification_path');
    }

    /**
     * @param int $id
     * @return Absence
     */
    public function find(int $id): Absence
    {
        return Absence::with('collaborator')->findOrFail($id);
    }

    /**
     * @param int $id
     * @return Absence
     */
    public function findWithTrashed(int $id): Absence
    {
        return Absence::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return Absence
     */
    public function create(array $data): Absence
    {
        $absence = Absence::create($data);
        return $absence;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $absence = $this->find($id);
        $absence->update($data);
        return $absence;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $absence = $this->find($id);
        $absence->delete();
        return $absence;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete(array $ids): mixed
    {
        return Absence::destroy($ids);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(int $id): mixed
    {
        $absence = $this->findWithTrashed($id);

        if (!$absence) {
            abort(404, 'Absence not found.');
        }

        $absence->restore();
        return $absence;
    }
}
