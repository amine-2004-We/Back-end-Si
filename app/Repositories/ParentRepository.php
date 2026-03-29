<?php

namespace App\Repositories;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ParentRepository
{
    /**
     * @return LengthAwarePaginator|mixed
     */
    public function all(): mixed
    {
        return ParentModel::paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator|mixed
     */
   public function withFilters(array $filters): mixed
{
    \Log::info('Applying filters in ParentRepository:', $filters);
    $query = ParentModel::query()
        ->with('creator', 'beneficiaries')
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

        if (!empty($filters['id'])) {
            $query->where('id', '=', $filters['id']);
        }

        if (!empty($filters['code'])) {
            $query->where('code', 'like', '%' . $filters['code'] . '%');
        }
        if (!empty($filters['name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'ilike', '%' . $filters['name'] . '%')
                  ->orWhere('last_name', 'ilike', '%' . $filters['name'] . '%')
                  ->orWhere('cin', 'ilike', '%' . $filters['name'] . '%');
            });
            
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
    
 


   
    if (!empty($filters['id'])) {
        $query->where('id', '=', $filters['id']);
    }

    if (!empty($filters['code'])) {
        $query->where('code', 'ILIKE', '%' . $filters['code'] . '%');
    }
    
    if (!empty($filters['sex'])) {
        $query->where('sex', '=', $filters['sex']);
    }

    if (!empty($filters['legal_role'])) {
        $role = $filters['legal_role'];
        $query->whereExists(function ($q) use ($role) {
            $q->from('beneficiary_parent as bp')
              ->whereColumn('bp.parent_id', 'parents.id')
              ->where('bp.legal_role', $role);
        });
    }

    if (!empty($filters['primary_phone'])) {
        $query->where('primary_phone', 'ilike', '%' . $filters['primary_phone'] . '%');
    }

    
    if (!empty($filters['cin']) && !isset($filters['name'])) { 
        $query->where('cin', 'like', '%' . $filters['cin'] . '%');
    }

    $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;
    
    return $query->paginate($perPage);
}
    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return ParentModel::all(['id', 'code', 'last_name', 'first_name', 'sex']);
    }

    /**
     * @param int $id
     * @return ParentModel|null
     */
    public function find(int $id): ?ParentModel
    {
        return ParentModel::find($id);
    }

    /**
     * @param int $id
     * @return ParentModel|null
     */
    public function findWithTrashed(int $id): ?ParentModel
    {
        return ParentModel::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return ParentModel
     */
    public function create(array $data): ParentModel
    {
        $parent = new ParentModel();
        $parent->fill($data);
        $parent->save();

        return $parent;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $parent = $this->find($id);

        if (!$parent) {
            abort(404, 'Parent not found.');
        }

        $parent->update($data);
        return $parent;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $parent = $this->find($id);

        if (!$parent) {
            abort(404, 'Parent not found.');
        }

        $parent->delete();
        return $parent;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete(array $ids): mixed
    {
        return ParentModel::destroy($ids);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(int $id): mixed
    {
        $parent = $this->findWithTrashed($id);

        if (!$parent) {
            abort(404, 'Parent not found.');
        }

       
        $exists = ParentModel::where('cin', $parent->cin)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(409, 'Le CIN du parent est déjà utilisé.');
        }

        $parent->restore();
        return $parent;
    }

    /**
     * Get all parents that have at least one pivot with role = 'Père'
     */
    public function getFathers(): Collection
    {
        return ParentModel::whereExists(function ($q) {
                $q->from('beneficiary_parent as bp')
                  ->whereColumn('bp.parent_id', 'parents.id')
                  ->where('bp.legal_role', 'Père');
            })
            ->get();
    }

    /**
     * Get all parents that have at least one pivot with role = 'Mère'
     */
    public function getMothers(): Collection
    {
        return ParentModel::whereExists(function ($q) {
                $q->from('beneficiary_parent as bp')
                  ->whereColumn('bp.parent_id', 'parents.id')
                  ->where('bp.legal_role', 'Mère');
            })
            ->get();
    }

    /**
     * Get all parents that have at least one pivot with role = 'Tuteur légal'
     */
    public function getLegalGuardians(): Collection
    {
        return ParentModel::whereExists(function ($q) {
                $q->from('beneficiary_parent as bp')
                  ->whereColumn('bp.parent_id', 'parents.id')
                  ->where('bp.legal_role', 'Tuteur légal');
            })
            ->get();
    }

    /**
     * Generic: parents having at least one pivot with the given legal role
     */
    public function getByLegalRole(string $role): Collection
    {
        return ParentModel::whereExists(function ($q) use ($role) {
                $q->from('beneficiary_parent as bp')
                  ->whereColumn('bp.parent_id', 'parents.id')
                  ->where('bp.legal_role', $role);
            })
            ->get();
    }

    /**
     * @param string $cin
     * @return ParentModel|null
     */
    public function findByCin(string $cin): ?ParentModel
    {
        return ParentModel::where('cin', $cin)->first();
    }

    /**
     * @param string $phone
     * @return ParentModel|null
     */
    public function findByPhone(string $phone): ?ParentModel
    {
        return ParentModel::where('primary_phone', $phone)
            ->orWhere('secondary_phone', $phone)
            ->first();
    }
}
