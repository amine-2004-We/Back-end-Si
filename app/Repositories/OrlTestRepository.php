<?php

namespace App\Repositories;

use App\Models\OrlTest;

class OrlTestRepository
{
    public function findByBeneficiaryId($beneficiaryId)
    {
        return OrlTest::where('beneficiaire_id', $beneficiaryId)->first();
    }

    public function upsert($beneficiaryId, array $data)
    {
        return OrlTest::updateOrCreate(
            ['beneficiaire_id' => $beneficiaryId],
            $data
        );
    }
}
