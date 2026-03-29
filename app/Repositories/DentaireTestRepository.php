<?php

namespace App\Repositories;

use App\Models\DentaireTest;

class DentaireTestRepository
{
    public function findByBeneficiaryId($beneficiaryId)
    {
        return DentaireTest::where('beneficiaire_id', $beneficiaryId)->first();
    }

    public function upsert($beneficiaryId, array $data)
    {
        return DentaireTest::updateOrCreate(
            ['beneficiaire_id' => $beneficiaryId],
            $data
        );
    }
}
