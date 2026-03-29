<?php

namespace App\Services;

use App\Models\MissionOrder;
use App\Repositories\MissionOrderRepository;
use Exception;
use Illuminate\Http\Request;

/**
 * class MissionOrderService
 */
class MissionOrderService
{
    /**
     * @var MissionOrderRepository
     */
    public MissionOrderRepository $missionOrderRepository;

    /**
     * @param MissionOrderRepository $missionOrderRepository
     */
    public function __construct(MissionOrderRepository $missionOrderRepository)
    {
        $this->missionOrderRepository = $missionOrderRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'mission_order_id',
            'mission_order_code',
            'is_active',
            'mission_description',
            'objective',
            'project_id',
            'collaborator_id',
            'province_id',
            'mission_type',
            'status',
            'is_advance_requested',
            'start_date',
            'end_date',
            'date_range_start',
            'date_range_end',
            'per_page'
        ]);
        return $this->missionOrderRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->missionOrderRepository->allWithoutPagination();
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function show(string $id): mixed
    {
        return $this->missionOrderRepository->find($id);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function showWithRelations(string $id): mixed
    {
        return $this->missionOrderRepository->findWithRelations($id);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed
    {
        return $this->missionOrderRepository->create($data);
    }

    /**
     * @param string $id
     * @param array $data
     * @return mixed
     */
    public function update(string $id, array $data): mixed
    {
        return $this->missionOrderRepository->update($data, $id);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delete(string $id): mixed
    {
        return $this->missionOrderRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->missionOrderRepository->bulkDelete($ids);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function restore(string $id): mixed
    {
        try {
            return $this->missionOrderRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function approve(string $id): mixed
    {
        return $this->missionOrderRepository->approve($id);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function refuse(string $id): mixed
    {
        return $this->missionOrderRepository->refuse($id);
    }

    /**
     * @param string $collaboratorId
     * @return mixed
     */
    public function getByCollaborator(string $collaboratorId): mixed
    {
        return $this->missionOrderRepository->findByCollaborator($collaboratorId);
    }

    /**
     * @param string $projectId
     * @return mixed
     */
    public function getByProject(string $projectId): mixed
    {
        return $this->missionOrderRepository->findByProject($projectId);
    }

    /**
     * @param string $status
     * @return mixed
     */
    public function getByStatus(string $status): mixed
    {
        return $this->missionOrderRepository->findByStatus($status);
    }

    /**
     * @param string $missionType
     * @return mixed
     */
    public function getByMissionType(string $missionType): mixed
    {
        return $this->missionOrderRepository->findByMissionType($missionType);
    }
}
