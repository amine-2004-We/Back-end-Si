<?php

namespace App\Services;

use App\Models\BudgetLine;
use App\Repositories\BudgetLigneRepository;
use Exception;
use HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
use Illuminate\Http\Response;

/**
 * class BudgetLigneService
 */
class BudgetLigneService
{
    /**
     * @var BudgetLigneRepository
     */
    protected BudgetLigneRepository $budgetLigneRepository;

    /**
     * @param BudgetLigneRepository $budgetLigneRepository
     */
    public function __construct(BudgetLigneRepository $budgetLigneRepository)
    {
        $this->budgetLigneRepository = $budgetLigneRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request)
    {
        $filters = $request->only(['code', 'partner_name','is_active','per_page','project_name']);
        return $this->budgetLigneRepository->getAllWithFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->budgetLigneRepository->allWithoutPagination();
    }

    /**
     * @param int $id
     * @return BudgetLine
     */
    public function show(int $id): BudgetLine
    {
        return $this->budgetLigneRepository->find($id);
    }

    /**
     * @param array $data
     * @param array $partners
     * @return void
     */
    public function create(array $data): void
    {
        try {
            DB::transaction(function () use ($data) {
                $this->budgetLigneRepository->create($data);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la création de la ligne budgétaire : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @param array $partners
     * @return void
     */
    public function update(int $id, array $data): void
    {
        try {
            DB::transaction(function () use ($id, $data) {
                $this->budgetLigneRepository->update($id, $data);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la mise à jour de la ligne budgétaire : " . $e->getMessage(), 0, $e);
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
                $this->budgetLigneRepository->delete($id);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la suppression de la ligne budgétaire : " . $e->getMessage(), 0, $e);
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
                $this->budgetLigneRepository->bulkDelete($ids);
            });
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la suppression multiple des lignes budgétaires : " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restoreBudgetLine(int $id): mixed
    {
        try {
            return $this->budgetLigneRepository->restore($id);
        } catch (ModelNotFoundException $e) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'La ligne budgétaire est introuvable !');
        } catch (Exception $e) {
            throw new HttpException(Response::HTTP_CONFLICT, $e->getMessage() ?: 'Conflit lors de la restauration.');
        }
    }
}
