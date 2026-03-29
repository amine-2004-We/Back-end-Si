<?php

namespace App\Services;

use App\Models\NaturePartner;
use App\Repositories\NaturePartnerRepository;
use Illuminate\Database\Eloquent\Collection;

class NaturePartnerService
{
    protected NaturePartnerRepository $repository;

    public function __construct(NaturePartnerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all NaturePartner.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Store a new NaturePartner.
     *
     * @param array $data
     * @return NaturePartner
     */
    public function store(array $data): NaturePartner
    {
        return $this->repository->store($data);
    }

    /**
     * Update an existing NaturePartner.
     *
     * @param NaturePartner $nature
     * @param array $data
     * @return NaturePartner
     */
    public function update(NaturePartner $nature, array $data): NaturePartner
    {
        return $this->repository->update($nature, $data);
    }

    /**
     * Delete a NaturePartner if not used.
     *
     * @param NaturePartner $nature
     * @return bool|null
     */
    public function delete(NaturePartner $nature): ?bool
    {
        return $this->repository->delete($nature);
    }
}