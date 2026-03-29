<?php

namespace App\Services;

use App\Repositories\OrlTestRepository;
use Illuminate\Support\Facades\Auth;

class OrlTestService
{
    protected $orlTestRepository;

    public function __construct(OrlTestRepository $orlTestRepository)
    {
        $this->orlTestRepository = $orlTestRepository;
    }

    public function getOrlTest($beneficiaryId)
    {
        return $this->orlTestRepository->findByBeneficiaryId($beneficiaryId);
    }

    public function saveOrlTest($beneficiaryId, array $validatedData)
    {
        $data = $validatedData + [
            'beneficiaire_id'   => $beneficiaryId,
            'consultation_date' => now(),
            'created_by'        => Auth::id(),
        ];

        return $this->orlTestRepository->upsert($beneficiaryId, $data);
    }
}
