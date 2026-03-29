<?php

namespace App\Repositories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class BankRepository
 */
class BankRepository
{
    /**
     * @return mixed
     */
    public function all(): mixed
    {
        return Bank::paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator|mixed
     */
    public function withFilters(array $filters): mixed
    {
        $query = Bank::query()
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

        if (!empty($filters['bank_id'])) {
            $query->where('bank_id', '=', $filters['bank_id'] );
        }

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        if(!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if(!empty($filters['currency'])) {
            $query->where('currency', '=', $filters['currency'] );
        }

        if(!empty($filters['country'])){
            $query->where('country','=', $filters['country'] );
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return Bank::all();
    }

    /**
     * @param int $id
     * @return Bank
     */
    public function find(int $id): Bank
    {
        return Bank::find($id);
    }

    /**
     * @param int $id
     * @return Bank
     */
    public function findWithTrashed(int $id): Bank
    {
        return Bank::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return Bank
     */
    public function create(array $data): Bank
    {
        $bank = new Bank();
        $bank->fill($data);
        $bank->save();
        return $bank;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $bank = $this->find($id);
        $bank->update($data);
        return $bank;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $bank = $this->find($id);
        $bank->delete();
        return $bank;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public  function bulkDelete(array $ids): mixed
    {
        return Bank::destroy($ids);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(int $id): mixed
    {
        $bank = $this->findWithTrashed($id);

        if (!$bank) {
            abort(404, 'Bank not found.');
        }

        $exists = Bank::where('name', $bank->name)->whereNull('deleted_at')->exists();

        if ($exists) {
            abort(409, 'le nom de la banque est déjà utilisé.');
        }

        $bank->restore();
        return $bank;
    }

}
