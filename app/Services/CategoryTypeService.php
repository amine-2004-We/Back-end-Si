<?php

namespace App\Services;

use App\Models\CategoryType;
use App\Repositories\CategoryTypeRepository;
use Exception;
use HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
use Illuminate\Http\Response;

/**
 * class CategoryTypeService
 */
class CategoryTypeService
{
    /**
     * @var CategoryTypeRepository
     */
    protected CategoryTypeRepository $categoryTypeRepository;

    /**
     * @param CategoryTypeRepository $categoryTypeRepository
     */
    public function __construct(CategoryTypeRepository $categoryTypeRepository)
    {
        $this->categoryTypeRepository = $categoryTypeRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request)
    {
        $filters = $request->only(['name','per_page']);
        return $this->categoryTypeRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->categoryTypeRepository->allWithoutPagination();
    }

    /**
     * @param int $id
     * @return CategoryType
     */
    public function show(int $id): CategoryType
    {
        return $this->categoryTypeRepository->find($id);
    }

    /**
     * @param array $data
     * @return void
     */
    public function create(array $data): void
    {
        try {
            $this->categoryTypeRepository->create($data);
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la création du type de rubrique : " . $e->getMessage(), 0, $e);
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
            $this->categoryTypeRepository->update($data, $id);
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la mise à jour du type de rubrique : " . $e->getMessage(), 0, $e);
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
                $this->categoryTypeRepository->delete($id);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la suppression du type de rubrique : " . $e->getMessage(), 0, $e);
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
                $this->categoryTypeRepository->bulkDelete($ids);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la suppression multiple des types de rubriques : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        return $this->categoryTypeRepository->restore($id);
    }
}
