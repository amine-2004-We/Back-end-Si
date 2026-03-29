<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GeneralAccountResource;
use App\Http\Resources\ThirdPartyAccountResource;
use App\Models\ThirdPartyAccount;
use App\Models\GeneralAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Exception;

class ThirdPartyAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ThirdPartyAccount::query()->with('generalAccount')->orderByDesc('created_at');

            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('subdivision', 'like', "%{$searchTerm}%")
                      ->orWhereHas('generalAccount', function($subQuery) use ($searchTerm) {
                          $subQuery->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }

            if($request->has('general_account_id')) {
                $query->where('general_account_id', $request->input('general_account_id'));
            }

            $accounts = $query->paginate($request->input('per_page', 10));
            // Use the resource collection for pagination
            return ThirdPartyAccountResource::collection($accounts)->response();

        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'general_account_id' => ['required', 'integer', 'exists:general_accounts,id'],
            'subdivision' => ['required', 'string', 'size:4', Rule::unique('third_party_accounts')->where(function ($query) use ($request) {
                return $query->where('general_account_id', $request->general_account_id);
            })],
        ]);

        try {
            $account = ThirdPartyAccount::create($validated);
            return (new ThirdPartyAccountResource($account->load('generalAccount')))
                    ->additional(['message' => 'Compte tiers créé avec succès'])
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ThirdPartyAccount $thirdPartyAccount): JsonResponse
    {
        return (new ThirdPartyAccountResource($thirdPartyAccount->load('generalAccount')))
                ->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThirdPartyAccount $thirdPartyAccount): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'general_account_id' => ['sometimes', 'integer', 'exists:general_accounts,id'],
            'subdivision' => ['sometimes', 'string', 'size:4', Rule::unique('third_party_accounts')->where(function ($query) use ($request, $thirdPartyAccount) {
                return $query->where('general_account_id', $request->general_account_id ?? $thirdPartyAccount->general_account_id);
            })->ignore($thirdPartyAccount->id)],
        ]);

        try {
            $thirdPartyAccount->update($validated);
            return (new ThirdPartyAccountResource($thirdPartyAccount->load('generalAccount')))
                    ->additional(['message' => 'Compte tiers mis à jour avec succès'])
                    ->response();
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThirdPartyAccount $thirdPartyAccount): JsonResponse
    {
        try {
            $thirdPartyAccount->delete();
            return response()->json(['message' => 'Compte tiers supprimé avec succès'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get options for dropdowns.
     */
    public function options(): JsonResponse
    {
        try {
            $generalAccounts = GeneralAccount::all();
            return GeneralAccountResource::collection($generalAccounts)->response();
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur de serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

