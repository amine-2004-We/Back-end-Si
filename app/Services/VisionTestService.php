<?php

namespace App\Services;

use App\Repositories\VisionTestRepository;
use Illuminate\Support\Facades\Auth;

class VisionTestService
{
    protected $visionTestRepository;

    public function __construct(VisionTestRepository $visionTestRepository)
    {
        $this->visionTestRepository = $visionTestRepository;
    }

    public function getVisionTest($beneficiaryId)
    {
        return $this->visionTestRepository->findByBeneficiaryId($beneficiaryId);
    }

    public function saveVisionTest($beneficiaryId, array $validatedData)
    {
        $data = $validatedData + [
            'beneficiary_id'    => $beneficiaryId,
            'consultation_date' => now(),
            'created_by'        => Auth::id(),
        ];

        return $this->visionTestRepository->upsert($beneficiaryId, $data);
    }
}
