<?php

namespace App\Services;

use App\Repositories\PediatreTestRepository;
use Illuminate\Support\Facades\Auth;

class PediatreTestService
{
    protected $pediatreTestRepository;

    public function __construct(PediatreTestRepository $pediatreTestRepository)
    {
        $this->pediatreTestRepository = $pediatreTestRepository;
    }

    public function getPediatreTest($beneficiaryId)
    {
        return $this->pediatreTestRepository->findByBeneficiaryId($beneficiaryId);
    }

    public function savePediatreTest($beneficiaryId, array $validatedData)
    {
        $data = $validatedData + [
                'beneficiary_id'   => $beneficiaryId, // <— corrige ici
                'consultation_date'=> now(),
                'created_by'       => Auth::id(),
            ];

        return $this->pediatreTestRepository->upsert($beneficiaryId, $data);
    }
}
