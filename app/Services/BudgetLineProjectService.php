<?php

namespace App\Services;

use App\Repositories\BudgetLineProjectRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * class BudgetLineProjectService
 */
class BudgetLineProjectService
{
    protected $budgetLineProjectRepository;

    public function __construct(BudgetLineProjectRepository $budgetLineProjectRepository)
    {
        $this->budgetLineProjectRepository = $budgetLineProjectRepository;
    }

    public function getAll(Request $request){
        $filters = $request->only(['code', 'partner_name','is_active','per_page','project_name']);
        return $this->budgetLineProjectRepository->withFilters($filters);
    }

    public function store(array $data){
        try {
            return $this->budgetLineProjectRepository->store($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, array $data){
        try {
            return $this->budgetLineProjectRepository->update($id, $data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $id){
        try {
            return $this->budgetLineProjectRepository->delete($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function bulkDelete(array $ids){
        try {
            return $this->budgetLineProjectRepository->bulkDelete($ids);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function restore(int $id){
        try {
            return $this->budgetLineProjectRepository->restore($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /***
     * @param int $budgetLineProjectId
     * @return Collection
     */
    public function getBudgetLinePartners(int $budgetLineProjectId): Collection
    {
        return $this->budgetLineProjectRepository
            ->getBudgetLinePartners($budgetLineProjectId);
    }

}
