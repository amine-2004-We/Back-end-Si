<?php

namespace App\Services;

use App\Models\BudgetCategory;
use App\Repositories\BudgetCategoryRepository;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class BudgetCategoryService
 */
class BudgetCategoryService
{
    /**
     * @var BudgetCategoryRepository
     */
    public BudgetCategoryRepository $budgetCategoryRepository;

    /**
     * @param BudgetCategoryRepository $budgetCategoryRepository
     */
    public function __construct(BudgetCategoryRepository $budgetCategoryRepository)
    {
        $this->budgetCategoryRepository = $budgetCategoryRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request ): mixed
    {
        $filters = $request->only(['type', 'is_active','label','code','budgetary_area','per_page']);
        return $this->budgetCategoryRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->budgetCategoryRepository->allWithoutPagination();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id): mixed
    {
        return $this->budgetCategoryRepository->find($id);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->budgetCategoryRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return BudgetCategory
     */
    public function update(int $id,array $data)
    {
        return $this->budgetCategoryRepository->update($data, $id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id): mixed
    {
        return $this->budgetCategoryRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->budgetCategoryRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        try {
            return $this->budgetCategoryRepository->restore($id);
        } catch (ModelNotFoundException $e) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Rubrique budgétaire introuvable.');
        } catch (Exception $e) {
            throw new HttpException(Response::HTTP_CONFLICT, $e->getMessage() ?: 'Impossible de restaurer la rubrique budgétaire.');
        }
    }
}
