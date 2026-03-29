<?php

namespace App\Services;

use App\Enums\InsuranceEnum;
use App\Models\Assurance;
use App\Models\Candidate;
use App\Models\External;
use App\Repositories\AssuranceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *
 */
class AssuranceService
{
    /**
     * @param AssuranceRepository $repository
     */
    public function __construct(public AssuranceRepository $repository)
    {
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {
        return $this->repository->getFiltered($params);
    }

    /**
     * @param array $data
     * @return Assurance
     */
    public function create(array $data): Assurance
    {
        return $this->repository->create($data);
    }

    /**
     * @param Assurance $assurance
     * @param array $data
     * @return Assurance
     */
    public function update(Assurance $assurance, array $data): Assurance
    {
        $this->repository->update($assurance, $data);
        return $assurance->fresh($this->repository->defaultWith);
    }

    /**
     * @param int $id
     * @return Assurance|null
     */
    public function find(int $id): ?Assurance
    {
        return $this->repository->find($id);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        return $this->repository->toggleActivation($ids);
    }

    /**
     * @return array
     */
    public function getFormOptions(): array
    {
        $collaborators = Candidate::select('id', 'first_name', 'last_name')->get()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => "{$item->first_name} {$item->last_name} (Candidat)",
                'type' => Candidate::class,
            ];
        });

        $externals = External::select('id', 'full_name as name')->get()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => "{$item->name} (Externe)",
                'type' => External::class,
            ];
        });

        $personnesAssurees = $collaborators->concat($externals)->sortBy('label')->values();

        return [
            'personnesAssurees' => $personnesAssurees,
            'insuranceTypes' => ['CNSS', 'AMO', 'Retraite complémentaire'],
            'insurance_types' => InsuranceEnum::values()
        ];
    }
}
