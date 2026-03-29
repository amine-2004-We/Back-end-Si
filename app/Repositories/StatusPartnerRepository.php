<?php

namespace App\Repositories;

use App\Models\StatusPartner;
use Illuminate\Database\Eloquent\Collection;

class StatusPartnerRepository
{
    /**
     * Get all StatusPartner.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return StatusPartner::all();
    }

    /**
     * Store a new StatusPartner.
     *
     * @param array $data
     * @return StatusPartner
     */
    public function store(array $data): StatusPartner
    {
        return StatusPartner::create($data);
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
        $status->update($data);
        return $status;
    }

    /**
     * Delete a StatusPartner if not used.
     *
     * @param StatusPartner $status
     * @return bool|null
     */
    public function delete(StatusPartner $status): ?bool
    {
        if ($status->partners()->exists()) {
            return false;
        }
        return $status->delete();
    }
}