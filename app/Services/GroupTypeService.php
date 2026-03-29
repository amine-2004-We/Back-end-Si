<?php

namespace App\Services;

use App\Models\GroupType;
use App\Repositories\GroupTypeRepository;
use Exception;
use HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
use Illuminate\Http\Response;

/**
 * class GroupTypeService
 */
class GroupTypeService
{
    /**
     * @var GroupTypeRepository
     */
    protected GroupTypeRepository $groupTypeRepository;

    /**
     * @param GroupTypeRepository $groupTypeRepository
     */
    public function __construct(GroupTypeRepository $groupTypeRepository)
    {
        $this->groupTypeRepository = $groupTypeRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request)
    {
        $filters = $request->only(['name','per_page']);
        return $this->groupTypeRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->groupTypeRepository->allWithoutPagination();
    }

    /**
     * @param int $id
     * @return GroupType
     */
    public function show(int $id): GroupType
    {
        return $this->groupTypeRepository->find($id);
    }

    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
        try {
            $this->groupTypeRepository->create($data);
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la création du type de groupe : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return void
     */
    public function update(int $id, array $data): void
    {
        try {
            $this->groupTypeRepository->update($data, $id);
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la mise à jour du type de groupe : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $this->groupTypeRepository->delete($id);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la suppression du type de groupe : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array $ids
     * @return void
     */
    public function bulkDelete(array $ids): void
    {
        try {
            DB::transaction(function () use ($ids) {
                $this->groupTypeRepository->bulkDelete($ids);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la suppression multiple des types de groupes : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        return $this->groupTypeRepository->restore($id);
    }
}
