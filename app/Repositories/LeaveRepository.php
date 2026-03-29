<?php

namespace App\Repositories;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRepository
{
    /***
     * @param User $user
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllLeaves(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Leave::query()
            ->with(['LeaveType', 'collaborator.superior']);

        if (!$user->hasRole('RH (Ressources Humaines)')) {

            $collaboratorId = optional($user->collaborator)->id;

            if (!$collaboratorId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($collaboratorId) {
                    $q->where('collaborator_id', $collaboratorId)
                        ->orWhereHas('collaborator', function ($subQ) use ($collaboratorId) {
                            $subQ->where('hierarchical_superior', $collaboratorId);
                        });
                });
            }
        }

        if (isset($filters['activation_status']) && $filters['activation_status'] !== 'active') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['reason'])) {
            $query->where('reason', 'like', '%' . $filters['reason'] . '%');
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (($filters['activation_status'] ?? null) === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /***
     * @param User $user
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllTrashedLeaves(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Leave::onlyTrashed()
            ->with(['LeaveType', 'collaborator.superior']);

        if (!$user->hasRole('RH (Ressources Humaines)')) {

            $collaboratorId = optional($user->collaborator)->id;

            if (!$collaboratorId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($collaboratorId) {
                    $q->where('collaborator_id', $collaboratorId)
                        ->orWhereHas('collaborator', function ($subQ) use ($collaboratorId) {
                            $subQ->where('hierarchical_superior', $collaboratorId);
                        });
                });
            }
        }

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['reason'])) {
            $query->where('reason', 'like', '%' . $filters['reason'] . '%');
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /***
     * @param int $collaboratorId
     * @return Leave
     */
    public function show(int $collaboratorId): Leave
    {
        return Leave::query()->findOrFail($collaboratorId);
    }

    /***
     * @param array $data
     * @return Leave
     */
    public function store(array $data): Leave
    {
        return Leave::query()->create($data);
    }

    /***
     * @param string $leaveId
     * @param array $data
     * @return Leave
     */
    public function update(string $leaveId, array $data): Leave{
        $leave = Leave::query()->findOrFail($leaveId);
        $leave->update($data);
        return $leave;
    }

    /***
     * @param string $leaveId
     * @return bool
     */
    public function delete(string $leaveId): bool
    {
        $leave = Leave::query()->findOrFail($leaveId);
        return $leave->delete();
    }

    /***
     * @param string $leaveId
     * @return bool
     */
    public function restore(string $leaveId): bool
    {
        $leave = Leave::onlyTrashed()->findOrFail($leaveId);
        return $leave->restore();
    }

    /***
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids): bool
    {
        return Leave::query()->whereIn('id', $ids)->delete();
    }
}
