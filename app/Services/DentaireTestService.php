<?php

namespace App\Services;

use App\Repositories\DentaireTestRepository;
use Illuminate\Support\Facades\Auth;

class DentaireTestService
{
    protected $dentaireTestRepository;

    public function __construct(DentaireTestRepository $dentaireTestRepository)
    {
        $this->dentaireTestRepository = $dentaireTestRepository;
    }

    public function getDentaireTest($beneficiaryId)
    {
        return $this->dentaireTestRepository->findByBeneficiaryId($beneficiaryId);
    }

    public function saveDentaireTest($beneficiaryId, array $validatedData)
    {
        $data = $validatedData + [
            'beneficiaire_id'   => $beneficiaryId,
            'consultation_date' => now(),
            'created_by'        => Auth::id(),
        ];

        return $this->dentaireTestRepository->upsert($beneficiaryId, $data);
    }
}
