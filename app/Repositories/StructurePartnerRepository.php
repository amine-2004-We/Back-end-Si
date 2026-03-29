<?php

namespace App\Repositories;

use App\Models\StructurePartner;
use Illuminate\Database\Eloquent\Collection;

class StructurePartnerRepository
{
    /**
     * Get all StructurePartner.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return StructurePartner::all();
    }

    /**
     * Store a new StructurePartner.
     *
     * @param array $data
     * @return StructurePartner
     */
    public function store(array $data): StructurePartner
    {
        return StructurePartner::create($data);
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
        $structure->update($data);
        return $structure;
    }

    /**
     * Delete a StructurePartner if not used.
     *
     * @param StructurePartner $structure
     * @return bool|null
     */
    public function delete(StructurePartner $structure): ?bool
    {
        if ($structure->partners()->exists()) {
            return false;
        }
        return $structure->delete();
    }
}