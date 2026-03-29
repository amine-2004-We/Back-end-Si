<?php

namespace App\Repositories;

use App\Constants\Role;
use App\Models\MissionOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class MissionOrderRepository
 */
class MissionOrderRepository
{
    /**
     * @return mixed
     */
    public function all(): mixed
    {
        return MissionOrder::paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator|mixed
     */
    public function withFilters(array $filters): mixed
    {
        $query = MissionOrder::query()
            ->with('project', 'collaborator', 'province.region')
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        $user = auth()->user();
        if ($user && !$user->hasRole(Role::ADMIN_SI)) {
            $collaborator = $user->collaborator;
            if ($collaborator) {
                $subordinateIds = $collaborator->subordinates()->pluck('id')->toArray();
                $allowedIds = array_merge([$collaborator->id], $subordinateIds);
                $query->whereIn('collaborator_id', $allowedIds);
            } else {
                $query->whereNull('id');
            }
        }

        if (!empty($filters['mission_order_id'])) {
            $query->where('id', '=', $filters['mission_order_id']);
        }

        if (!empty($filters['mission_order_code'])) {
            $query->where('mission_order_code', 'like', '%' . $filters['mission_order_code'] . '%');
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

        if (!empty($filters['mission_description'])) {
            $query->where('mission_description', 'like', '%' . $filters['mission_description'] . '%');
        }

        if (!empty($filters['objective'])) {
            $query->where('objective', 'like', '%' . $filters['objective'] . '%');
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', '=', $filters['project_id']);
        }

        if (!empty($filters['province_id'])) {
            $query->where('province_id', '=', $filters['province_id']);
        }

        if (!empty($filters['mission_type'])) {
            $query->where('mission_type', '=', $filters['mission_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        if (!empty($filters['is_advance_requested'])) {
            $query->where('is_advance_requested', '=', $filters['is_advance_requested']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['date_range_start'])) {
            $query->where('start_date', '>=', $filters['date_range_start']);
        }

        if (!empty($filters['date_range_end'])) {
            $query->where('end_date', '<=', $filters['date_range_end']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return MissionOrder::all('id', 'mission_description', 'start_date', 'end_date', 'mission_type', 'status');
    }

    /**
     * @param string $id
     * @return MissionOrder
     */
    public function find(string $id): MissionOrder
    {
        return MissionOrder::find($id);
    }

    /**
     * @param string $id
     * @return MissionOrder
     */
    public function findWithTrashed(string $id): MissionOrder
    {
        return MissionOrder::withTrashed()->find($id);
    }

    /**
     * @param string $id
     * @return MissionOrder
     */
    public function findWithRelations(string $id): MissionOrder
    {
        return MissionOrder::with('project', 'collaborator', 'province')->find($id);
    }

    /**
     * @param array $data
     * @return MissionOrder
     */
    public function create(array $data): MissionOrder
    {
        $missionOrder = new MissionOrder();
        $missionOrder->fill($data);
        $missionOrder->save();
        return $missionOrder;
    }

    /**
     * @param array $data
     * @param string $id
     * @return mixed
     */
    public function update(array $data, string $id): mixed
    {
        $missionOrder = $this->find($id);
        $missionOrder->update($data);
        return $missionOrder;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delete(string $id): mixed
    {
        $missionOrder = $this->find($id);
        $missionOrder->delete();
        return $missionOrder;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete(array $ids): mixed
    {
        return MissionOrder::destroy($ids);
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(string $id): mixed
    {
        $missionOrder = $this->findWithTrashed($id);

        if (!$missionOrder) {
            abort(404, 'Mission order not found.');
        }

        $missionOrder->restore();
        return $missionOrder;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function approve(string $id): mixed
    {
        $missionOrder = $this->find($id);
        $missionOrder->update(['status' => 'approved']);
        return $missionOrder;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function refuse(string $id): mixed
    {
        $missionOrder = $this->find($id);
        $missionOrder->update(['status' => 'refused']);
        return $missionOrder;
    }

    /**
     * @param string $collaboratorId
     * @return Collection
     */
    public function findByCollaborator(string $collaboratorId): Collection
    {
        return MissionOrder::where('collaborator_id', $collaboratorId)
            ->with('project', 'province')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param string $projectId
     * @return Collection
     */
    public function findByProject(string $projectId): Collection
    {
        return MissionOrder::where('project_id', $projectId)
            ->with('collaborator', 'province')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param string $status
     * @return Collection
     */
    public function findByStatus(string $status): Collection
    {
        return MissionOrder::where('status', $status)
            ->with('project', 'collaborator', 'province')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param string $missionType
     * @return Collection
     */
    public function findByMissionType(string $missionType): Collection
    {
        return MissionOrder::where('mission_type', $missionType)
            ->with('project', 'collaborator', 'province')
            ->orderByDesc('created_at')
            ->get();
    }
}
