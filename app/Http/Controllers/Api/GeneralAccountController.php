<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GeneralAccountCollection;
use App\Http\Resources\GeneralAccountResource;
use App\Models\GeneralAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Exception;

class GeneralAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = GeneralAccount::query()->orderByDesc('created_at');
            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('class', 'like', "%{$searchTerm}%")->orWhere('account', 'like', "%{$searchTerm}%")->orWhere('sub_account', 'like', "%{$searchTerm}%"));
            }
            $accounts = $query->paginate($request->input('per_page', 10));
            return GeneralAccountResource::collection($accounts)->response();
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class' => ['required', 'string', 'digits:1'],
            'account' => ['required', 'string', 'digits:1'],
            'sub_account' => ['required', 'string', 'digits:2', Rule::unique('general_accounts')->where(fn($q) => $q->where('class', $request->class)->where('account', $request->account))],
        ]);
        try {
            $account = GeneralAccount::create($validated);
            return (new GeneralAccountResource($account))
                ->additional(['message' => 'Compte général créé avec succès'])
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(GeneralAccount $generalAccount): JsonResponse
    {
        return (new GeneralAccountResource($generalAccount->load('thirdPartyAccounts')))->response();
    }

    public function update(Request $request, GeneralAccount $generalAccount): JsonResponse
    {
         $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'class' => ['sometimes', 'string', 'digits:1'],
            'account' => ['sometimes', 'string', 'digits:1'],
            'sub_account' => ['sometimes', 'string', 'digits:2', Rule::unique('general_accounts')->where(fn($q) => $q->where('class', $request->class ?? $generalAccount->class)->where('account', $request->account ?? $generalAccount->account))->ignore($generalAccount->id)],
        ]);
        try {
            $generalAccount->update($validated);
            return (new GeneralAccountResource($generalAccount))
                ->additional(['message' => 'Compte général mis à jour avec succès'])
                ->response();
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(GeneralAccount $generalAccount): JsonResponse
    {
        try {
            $generalAccount->delete();
            return response()->json(['message' => 'Compte général supprimé avec succès'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $generalAccount = GeneralAccount::withTrashed()->findOrFail($id);
            $generalAccount->restore();
            return (new GeneralAccountResource($generalAccount))
                ->additional(['message' => 'Compte général restauré avec succès'])
                ->response();
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function options(): JsonResponse
    {
        try {
            $generalAccounts = GeneralAccount::orderBy('name')->get();

            // Use the new collection class to format the response without the "data" wrapper.
            return response()->json([
                'general_accounts' => new GeneralAccountCollection($generalAccounts)
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

