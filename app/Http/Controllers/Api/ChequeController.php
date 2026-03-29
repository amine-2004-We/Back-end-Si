<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\Beneficiary;
use App\Models\Bank;
use App\Models\ProjectBankAccount;
use App\Services\ChequeService;
use App\Http\Requests\StoreChequeRequest;
use App\Http\Requests\UpdateChequeRequest;
use App\Http\Resources\ChequeResource;
use App\Enums\ChequeStatusEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 *class ChequeController
 */
class ChequeController extends Controller
{
    /**
     * @var ChequeService
     */
    protected ChequeService $chequeService;

    /**
     * @param ChequeService $chequeService
     */
    public function __construct(ChequeService $chequeService)
    {
        $this->chequeService = $chequeService;
        $this->authorizeResource(Cheque::class, 'cheque', [
            'except' => ['create'],
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cheques = $this->chequeService->getPaginatedCheques(
            $request->all(),
            $request->input('per_page', 15)
        );
        return response()->json($cheques);
    }

    /**
     * Fetch the necessary options for the create/edit form.
     */
    public function create(): JsonResponse
    {
        $beneficiaries = Beneficiary::select('id', 'first_name', 'last_name')->get()->map(function ($item) {
            return ['id' => $item->id, 'name' => $item->first_name . ' ' . $item->last_name];
        });


        $accounts = ProjectBankAccount::select('id', 'rib_iban', 'account_holder_name')->get()->map(function ($item) {
            return ['id' => $item->id, 'label' => $item->account_holder_name ];
        });

        return response()->json([
            'beneficiaries' => $beneficiaries,
            'project_bank_accounts' => $accounts,
            'statuses' => array_column(ChequeStatusEnum::cases(), 'value'),
        ]);
    }

    /**
     * @param StoreChequeRequest $request
     * @return JsonResponse
     */
    public function store(StoreChequeRequest $request): JsonResponse
    {
        $cheque = $this->chequeService->createCheque($request->validated());
        return response()->json(new ChequeResource($cheque), HttpResponse::HTTP_CREATED);
    }

    /**
     * @param Cheque $cheque
     * @return JsonResponse
     */
    public function show(Cheque $cheque): JsonResponse
    {
        return response()->json(new ChequeResource($this->chequeService->getCheque($cheque->id)));
    }

    /**
     * @param UpdateChequeRequest $request
     * @param Cheque $cheque
     * @return JsonResponse
     */
    public function update(UpdateChequeRequest $request, Cheque $cheque): JsonResponse
    {
        $updatedCheque = $this->chequeService->updateCheque($cheque->id, $request->validated());
        return response()->json(new ChequeResource($updatedCheque));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        $this->authorize('massUpdate', Cheque::class);
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $results = $this->chequeService->toggleChequeActivation($request->input('ids'));
        return response()->json($results);
    }
}
