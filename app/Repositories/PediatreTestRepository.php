<?php

namespace App\Repositories;

use App\Models\PediatreTest;

class PediatreTestRepository
{
    public function findByBeneficiaryId($beneficiaryId)
    {
        return PediatreTest::where('beneficiary_id', $beneficiaryId)->first();
    }

    public function upsert($beneficiaryId, array $data)
    {
        return PediatreTest::updateOrCreate(
            ['beneficiary_id' => $beneficiaryId],
            $data
        );
    }

}
