<?php

namespace App\Services;

use App\Models\Bank;
use App\Repositories\BankRepository;
use Exception;
use Illuminate\Http\Request;

/**
 * class BudgetCategoryService
 */
class BankService
{
    /**
     * @var BankRepository
     */
    public BankRepository $bankRepository;

    /**
     * @param BankRepository $bankRepository
     */
    public function __construct(BankRepository $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request ): mixed
    {
        $filters = $request->only(['bank_id', 'is_active','name','currency','per_page','country']);
        return $this->bankRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->bankRepository->allWithoutPagination();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id): mixed
    {
        return $this->bankRepository->find($id);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->bankRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Bank
     */
    public function update(int $id,array $data)
    {
        return $this->bankRepository->update($data, $id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id): mixed
    {
        return $this->bankRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->bankRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        try {
            return $this->bankRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
