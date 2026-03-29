<?php

namespace App\Services;

use App\Models\ProjectType;
use App\Repositories\ProjectTypeRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * class ProjectTypeService
 */
class ProjectTypeService
{
    /** @var ProjectTypeRepository */
    protected ProjectTypeRepository $projectTypeRepository;

    /**
     * @param ProjectTypeRepository $projectTypeRepository
     */
    public function __construct(ProjectTypeRepository $projectTypeRepository)
    {
        $this->projectTypeRepository = $projectTypeRepository;
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only(['is_active','name','per_page']);
        return $this->projectTypeRepository->all($filters);
    }

    public function getAllWithoutPagination(): Collection
    {
        return $this->projectTypeRepository->getAllWithoutPagination();
    }

    /**
     * @return Collection
     */
    public function allTypes(): Collection
    {
        return $this->projectTypeRepository->allTypes();
    }

    /**
     * @param int $id
     * @return ProjectType
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProjectType
    {
        return $this->projectTypeRepository->find($id);
    }

    /**
     * @param array $data
     * @return ProjectType
     */
    public function create(array $data): ProjectType
    {
        return $this->projectTypeRepository->create($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return ProjectType
     */
    public function update($id, array $data): ProjectType
    {
        return $this->projectTypeRepository->update($id, $data);
    }

    /**
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->projectTypeRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->projectTypeRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return ProjectType
     * @throws ModelNotFoundException
     */
    public function restore(int $id): ProjectType
    {
        try{
            return $this->projectTypeRepository->restore($id);
        }catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
