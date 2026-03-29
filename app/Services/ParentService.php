<?php

namespace App\Services;

use App\Models\ParentModel;
use App\Repositories\ParentRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * class ParentService
 */
class ParentService
{
    /**
     * @var ParentRepository
     */
    public ParentRepository $parentRepository;

    /**
     * @param \App\Repositories\ParentRepository $parentRepository
     */
    public function __construct(ParentRepository $parentRepository)
    {
        $this->parentRepository = $parentRepository;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'id',
            'code',
            'is_active',
            'last_name',
            'first_name',
            'sex',
            'legal_role',
            'primary_phone',
            'cin',
            'per_page',
            'name',
        ]);

        return $this->parentRepository->withFilters($filters);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->parentRepository->allWithoutPagination();
    }

    /**
     * @param int $id
     * @return ParentModel|null
     */
    public function show(int $id): mixed
    {
        return $this->parentRepository->find($id);
    }

    /**
     * @param array $data
     * @return ParentModel
     */
    public function create(array $data): ParentModel
    {
        return DB::transaction(function () use ($data) {
            $beneficiaries = $data['beneficiaries'] ?? null;
            unset($data['beneficiaries']);

            $parent = $this->parentRepository->create($data);

            if ($beneficiaries && is_array($beneficiaries)) {
                $pivotData = [];
                foreach ($beneficiaries as $item) {
                    if (!empty($item['id'])) {
                        $pivotData[$item['id']] = [
                            'legal_role' => $item['legal_role'] ?? null
                        ];
                    }
                }
                if ($pivotData) {
                    $parent->beneficiaries()->attach($pivotData);
                }
            }

            return $parent;
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return ParentModel
     */
    public function update(int $id, array $data): ParentModel
    {
        return DB::transaction(function () use ($id, $data) {
            $beneficiaries = $data['beneficiaries'] ?? null;
            unset($data['beneficiaries']);

            $parent = $this->parentRepository->update($data, $id);

            if (is_array($beneficiaries)) {
                if (empty($beneficiaries)) {
                    $parent->beneficiaries()->detach();
                } else {
                    $pivotData = [];
                    foreach ($beneficiaries as $item) {
                        if (!empty($item['id'])) {
                            $pivotData[$item['id']] = [
                                'legal_role' => $item['legal_role'] ?? null
                            ];
                        }
                    }
                    $parent->beneficiaries()->sync($pivotData);
                }
            }
        return $parent;
        });
    }

    /**
     * Soft delete
     */
    public function delete(int $id): mixed
    {
        return $this->parentRepository->delete($id);
    }

    /**
     * Bulk delete
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->parentRepository->bulkDelete($ids);
    }

    /**
     * Restore
     */
    public function restore(int $id): mixed
    {
        try {
            return $this->parentRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    public function getFathers(): mixed
    {
        return $this->parentRepository->getFathers();
    }

    public function getMothers(): mixed
    {
        return $this->parentRepository->getMothers();
    }

    public function getLegalGuardians(): mixed
    {
        return $this->parentRepository->getLegalGuardians();
    }

    public function getByLegalRole(string $role): mixed
    {
        return $this->parentRepository->getByLegalRole($role);
    }

    public function findByCin(string $cin): mixed
    {
        return $this->parentRepository->findByCin($cin);
    }

    public function findByPhone(string $phone): mixed
    {
        return $this->parentRepository->findByPhone($phone);
    }
}
