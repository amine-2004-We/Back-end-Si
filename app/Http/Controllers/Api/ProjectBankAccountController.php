<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBankAccountSupportingDocumentRequest;
use App\Services\BankService;
use App\Services\ProjectBankAccountService;
use App\Http\Requests\StoreProjectBankAccountRequest;
use App\Http\Requests\UpdateProjectBankAccountRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProjectBankAccountController
 */
class ProjectBankAccountController extends Controller
{
    /**
     * @var ProjectBankAccountService
     */
    protected ProjectBankAccountService $bankAccountService;

    /**
     * @var BankService
     */
    protected BankService $bankService;

    /**
     * @param ProjectBankAccountService $bankAccountService
     * @param BankService $bankService
     */
    public function __construct(ProjectBankAccountService $bankAccountService,BankService $bankService)
    {
        $this->bankAccountService = $bankAccountService;
        $this->bankService = $bankService;
    }

    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $bankAccounts = $this->bankAccountService->getAll($request);
            return response()->json($bankAccounts);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des comptes bancaires.'.$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $account = $this->bankAccountService->find($id);
            return response()->json($account);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur  lors de la récupération du compte bancaire : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try{
            $banks = $this->bankService->getAllWithoutPagination();

            return response()->json([
                'banks' => $banks
            ]);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Erreur lors de la récupération des options'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreProjectBankAccountRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectBankAccountRequest $request): JsonResponse
    {
        try {
            $account = $this->bankAccountService->create($request->validated(),$request);
            return response()->json($account, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du compte bancaire : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @param UpdateBankAccountSupportingDocumentRequest $request
     * @return JsonResponse
     */
    public function updateSupportingDocument(int $id, UpdateBankAccountSupportingDocumentRequest $request): JsonResponse
    {
        try{
            $request->validated();
            $account = $this->bankAccountService->updateSupportingDocument($id, $request);
            return response()->json($account);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param UpdateProjectBankAccountRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProjectBankAccountRequest $request, int $id): JsonResponse
    {
        try {
            $account = $this->bankAccountService->update($id, $request->validated());
            return response()->json($account);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du compte bancaire : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->bankAccountService->delete($id);
            return response()->json(['message' => 'Suppression effectuée avec succès.']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du compte bancaire : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try{
            $bankAccount = $this->bankAccountService->restore($id);
            return response()->json([
                'message' => 'Restauration effectuée avec succès.',
                'data' => $bankAccount
            ]);
        }catch (Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

}
