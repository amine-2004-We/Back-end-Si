<?php

namespace App\Services;

use App\Models\StructurePartner;
use App\Repositories\StructurePartnerRepository;
use Illuminate\Database\Eloquent\Collection;

class StructurePartnerService
{
    protected StructurePartnerRepository $repository;

    public function __construct(StructurePartnerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all StructurePartner.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Store a new StructurePartner.
     *
     * @param array $data
     * @return StructurePartner
     */
    public function store(array $data): StructurePartner
    {
        return $this->repository->store($data);
    }

    /**
     * Update an existing StructurePartner.
     *
     * @param StructurePartner $structure
     * @param array $data
     * @return StructurePartner
     */
    public function update(StructurePartner $structure, array $data): StructurePartner
    {
        return $this->repository->update($structure, $data);
    }

    /**
     * Delete a StructurePartner if not used.
     *
     * @param StructurePartner $structure
     * @return bool|null
     */
    public function delete(StructurePartner $structure): ?bool
    {
        return $this->repository->delete($structure);
    }
}