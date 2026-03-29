<?php

namespace App\Repositories;

use App\Models\Cheque;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 *class ChequeRepository
 */
class ChequeRepository
{
    /**
     * @var array|string[]
     */
    protected array $relations = [
        'beneficiary',
        'projectBankAccount.bank',
        'createdBy'
    ];

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Cheque::query()->withTrashed()->with($this->relations);

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where('number', 'like', $searchTerm)
                  ->orWhere('cheque_id', 'like', $searchTerm);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['beneficiary_id']) && $filters['beneficiary_id'] !== 'null') {
            $query->where('beneficiary_id', $filters['beneficiary_id']);
        }

        if (!empty($filters['project_bank_account_id']) && $filters['project_bank_account_id'] !== 'null') {
            $query->where('project_bank_account_id', $filters['project_bank_account_id']);
        }

        if (isset($filters['status_filter'])) {
            if ($filters['status_filter'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status_filter'] === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        } else {
            $query->whereNull('deleted_at');
        }

        $sortBy = $filters['sort_by'] ?? 'emission_date';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * @param int $id
     * @return Cheque|null
     */
    public function findById(int $id): ?Cheque
    {
        return Cheque::withTrashed()->with($this->relations)->find($id);
    }

    /**
     * @param array $data
     * @return Cheque
     */
    public function create(array $data): Cheque
    {
        return Cheque::create($data);
    }

    /**
     * @param Cheque $cheque
     * @param array $data
     * @return bool
     */
    public function update(Cheque $cheque, array $data): bool
    {
        return $cheque->update($data);
    }

    /**
     * @param Cheque $cheque
     * @return bool|null
     */
    public function delete(Cheque $cheque): ?bool
    {
        return $cheque->delete();
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $cheque = Cheque::withTrashed()->find($id);

            if (!$cheque) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Chèque non trouvé.'];
                continue;
            }

            try {
                if ($cheque->trashed()) {
                    $cheque->restore();
                    $message = 'Chèque restauré avec succès.';
                } else {
                    $cheque->delete();
                    $message = 'Chèque désactivé avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (\Exception $e) {
                Log::error("Error toggling activation for cheque ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur lors du changement de statut.'];
            }
        }
        return $results;
    }
}

