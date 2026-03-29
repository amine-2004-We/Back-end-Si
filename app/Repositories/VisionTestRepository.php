<?php

namespace App\Repositories;

use App\Models\VisionTest;

class VisionTestRepository
{
    public function findByBeneficiaryId($beneficiaryId)
    {
        return VisionTest::where('beneficiary_id', $beneficiaryId)->first();
    }

    public function upsert($beneficiaryId, array $data)
    {
        return VisionTest::updateOrCreate(
            ['beneficiary_id' => $beneficiaryId],
            $data
        );
    }
}
