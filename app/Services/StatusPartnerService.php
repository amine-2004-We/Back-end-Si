<?php

namespace App\Services;

use App\Models\StatusPartner;
use App\Repositories\StatusPartnerRepository;
use Illuminate\Database\Eloquent\Collection;

class StatusPartnerService
{
    protected StatusPartnerRepository $repository;

    public function __construct(StatusPartnerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all StatusPartner.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Store a new StatusPartner.
     *
     * @param array $data
     * @return StatusPartner
     */
    public function store(array $data): StatusPartner
    {
        return $this->repository->store($data);
    }

    /**
     * Update an existing StatusPartner.
     *
     * @param StatusPartner $status
     * @param array $data
     * @return StatusPartner
     */
    public function update(StatusPartner $status, array $data): StatusPartner
    {
        return $this->repository->update($status, $data);
    }

    /**
     * Delete a StatusPartner if not used.
     *
     * @param StatusPartner $status
     * @return bool|null
     */
    public function delete(StatusPartner $status): ?bool
    {
        return $this->repository->delete($status);
    }
}