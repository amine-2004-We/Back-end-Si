<?php

namespace App\Repositories;

use App\Models\NaturePartner;
use Illuminate\Database\Eloquent\Collection;

class NaturePartnerRepository
{
    /**
     * Get all NaturePartner.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return NaturePartner::all();
    }

    /**
     * Store a new NaturePartner.
     *
     * @param array $data
     * @return NaturePartner
     */
    public function store(array $data): NaturePartner
    {
        return NaturePartner::create($data);
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
        $nature->update($data);
        return $nature;
    }

    /**
     * Delete a NaturePartner if not used.
     *
     * @param NaturePartner $nature
     * @return bool|null
     */
    public function delete(NaturePartner $nature): ?bool
    {
        if ($nature->partners()->exists()) {
            return false;
        }
        return $nature->delete();
    }
}