<?php

namespace App\Services;

use App\Models\BudgetLine;
use App\Models\ServiceProvision;
use App\Repositories\ServiceProvisionRepository;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ServiceProvisionService
{
    protected ServiceProvisionRepository $provisionRepository;

    public function __construct(ServiceProvisionRepository $provisionRepository)
    {
        $this->provisionRepository = $provisionRepository;
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedProvisions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->provisionRepository->getPaginated($filters, $perPage);
    }

    /**
     * @param int $id
     * @return ServiceProvision|null
     */
    public function getProvision(int $id): ?ServiceProvision
    {
        return $this->provisionRepository->findById($id);
    }

    /**
     * @param array $data
     * @param UploadedFile $file
     * @return ServiceProvision
     */
    public function createProvision(array $data, UploadedFile $file): ServiceProvision
    {
        return DB::transaction(function () use ($data, $file) {
            $budgetLine = BudgetLine::find($data['budget_line_id']);

            if (!$budgetLine) {
                throw new Exception("La ligne budgétaire sélectionnée n'existe pas.");
            }
            if ($budgetLine->remaining_amount < $data['amount']) {
                throw new Exception("Fonds insuffisants sur la ligne budgétaire.");
            }

            $path = $file->store('justifications/services', 'public');
            $data['justification_path'] = $path;

            $provision = $this->provisionRepository->create($data);

            $budgetLine->consumed_amount += $data['amount'];
            $budgetLine->remaining_amount -= $data['amount'];
            $budgetLine->save();

            return $provision;
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @param UploadedFile|null $file
     * @return ServiceProvision|null
     */
    public function updateProvision(int $id, array $data, ?UploadedFile $file): ?ServiceProvision
    {
        $provision = $this->provisionRepository->findById($id);
        if (!$provision) {
            return null;
        }

        DB::transaction(function () use ($provision, $data, $file) {
            $originalAmount = $provision->amount;
            $originalBudgetLine = $provision->budgetLine;

            if ($originalBudgetLine) {
                $originalBudgetLine->consumed_amount -= $originalAmount;
                $originalBudgetLine->remaining_amount += $originalAmount;
                $originalBudgetLine->save();
            }

            $newAmount = $data['amount'];
            $newBudgetLine = BudgetLine::find($data['budget_line_id'] ?? $originalBudgetLine->id);

            if ($newBudgetLine) {
                if ($newBudgetLine->remaining_amount < $newAmount) {
                    throw new Exception("Fonds insuffisants sur la nouvelle ligne budgétaire.");
                }
                $newBudgetLine->consumed_amount += $newAmount;
                $newBudgetLine->remaining_amount -= $newAmount;
                $newBudgetLine->save();
            }

            if ($file) {
                if ($provision->justification_path) {
                    Storage::disk('public')->delete($provision->justification_path);
                }
                $path = $file->store('justifications/services', 'public');
                $data['justification_path'] = $path;
            }

            $this->provisionRepository->update($provision, $data);
        });

        return $provision->fresh();
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleProvisionActivation(array $ids): array
    {
        return $this->provisionRepository->toggleActivation($ids);
    }
}
