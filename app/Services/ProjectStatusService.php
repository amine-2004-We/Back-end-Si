<?php

namespace App\Services;

use App\Models\ProjectStatus;
use App\Repositories\ProjectStatusRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class ProjectStatusService
 */
class ProjectStatusService
{
    /**
     * @var ProjectStatusRepository
     */
    protected ProjectStatusRepository $projectStatusRepository;

    /**
     * @param ProjectStatusRepository $projectStatusRepository
     */
    public function __construct(ProjectStatusRepository $projectStatusRepository)
    {
        $this->projectStatusRepository = $projectStatusRepository;
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        $filters = $request->only(['is_active','name','per_page']);
        return $this->projectStatusRepository->all($filters);
    }

    public function getAllWithoutPagination(): Collection
    {
        return $this->projectStatusRepository->allWithoutPagination();
    }

    /**
     * @return Collection
     */
    public function getAllStatus(): Collection
    {
        return $this->projectStatusRepository->getAll();
    }

    /**
     * @param int $id
     * @return ProjectStatus
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProjectStatus
    {
        return $this->projectStatusRepository->find($id);
    }

    /**
     * @param array $data
     * @return ProjectStatus
     */

    public function create(array $data): ProjectStatus
    {
        return $this->projectStatusRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return ProjectStatus
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): ProjectStatus
    {
        return $this->projectStatusRepository->update($id, $data);
    }

    /**
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->projectStatusRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->projectStatusRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return ProjectStatus
     */
    public function restore(int $id)
    {
        try{
             return $this->projectStatusRepository->restore($id);
        }catch(Exception $e){
            abort(409,$e->getMessage());
        }
    }
}
