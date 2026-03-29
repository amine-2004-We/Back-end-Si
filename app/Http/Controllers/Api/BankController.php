<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyBankRequest;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\UpdateBankRequest;
use App\Services\BankService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class BankController
 */
class BankController extends Controller
{
    /**
     * @var BankService
     */
    public BankService $bankService;

    /**
     * @param BankService $bankService
     */
    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $banks = $this->bankService->getAll($request);
            return response()->json($banks, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur!' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreBankRequest $request
     * @return JsonResponse
     */
    public function store(StoreBankRequest $request)
    {
        try{
            $data = $request->validated();
            return response()->json($this->bankService->create($data), Response::HTTP_CREATED);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur!' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateBankRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBankRequest $request, int $id)
    {
        try{
            $data = $request->validated();
            return response()->json($this->bankService->update($id, $data));
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la mise à jour de la banque' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        try{
            return response()->json($this->bankService->delete($id));
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la desactivation de la banque' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DestroyBankRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(DestroyBankRequest $request)
    {
        try{
            $ids = $request->validated();
            $this->bankService->bulkDestroy($ids['ids']);
            return response()->json([
                'message' => count($ids['ids']).' banque(s) supprimée(s) avec succès !'
            ]);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la suppression des banques' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try{
            return response()->json($this->bankService->restore($id));
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la restauration de la banque: ' . $e->getMessage()],
                Response::HTTP_CONFLICT);
        }
    }
}
