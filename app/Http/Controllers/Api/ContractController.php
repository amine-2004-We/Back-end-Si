<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Services\ContractService;

class ContractController extends Controller
{
    protected ContractService $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function generate($id)
    {
        $collaborator = Collaborator::findOrFail($id);

        $path = $this->contractService->generateContract($collaborator);

        $fileName = basename($path);

        return response()->download($path, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])->deleteFileAfterSend(true);
    }
}
