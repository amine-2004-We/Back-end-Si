<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Services\BeneficiaryService;
use App\Http\Requests\StoreBeneficiaryRequest;
use App\Http\Requests\UpdateBeneficiaryRequest;
use App\Http\Resources\BeneficiaryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BeneficiaryController extends Controller
{
    protected BeneficiaryService $beneficiaryService;

    public function __construct(BeneficiaryService $beneficiaryService)
    {
        $this->beneficiaryService = $beneficiaryService;
    }

    /**
     * Display a listing of beneficiaries
     *
     * @param Request $request
     * @return JsonResponse
     */
public function index(Request $request): JsonResponse
{
    // $this->authorize('viewAny', Beneficiary::class);
    try {
        $filters = $request->all();
        $perPage = $request->input('per_page', 15);

        $beneficiaries = $this->beneficiaryService->getPaginatedBeneficiaries($filters, $perPage);

        // Get the resource collection
        $resourceCollection = BeneficiaryResource::collection($beneficiaries);
        
        // Return with explicit pagination metadata
        return response()->json([
            'data' => $resourceCollection->collection,
            'current_page' => $beneficiaries->currentPage(),
            'last_page' => $beneficiaries->lastPage(),
            'per_page' => $beneficiaries->perPage(),
            'total' => $beneficiaries->total(),
            'from' => $beneficiaries->firstItem(),
            'to' => $beneficiaries->lastItem(),
        ], HttpResponse::HTTP_OK);
        
    } catch (Exception $e) {
        Log::error('Error fetching beneficiaries: ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
        return response()->json([
            'message' => 'Erreur lors de la récupération des bénéficiaires.',
            'error' => $e->getMessage()
        ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    /**
     * Get form options for creating a beneficiary
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        // $this->authorize('create', Beneficiary::class);
        try {
            $options = $this->beneficiaryService->getFormOptions();
            return response()->json($options, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching form options for beneficiary: ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Erreur lors de la récupération des options du formulaire bénéficiaire.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created beneficiary
     *
     * @param StoreBeneficiaryRequest $request
     * @return JsonResponse
     */
    public function store(StoreBeneficiaryRequest $request): JsonResponse
    {
        // $this->authorize('create', Beneficiary::class);
        try {
            $beneficiaryStatus = $request->input('new_status_data');
            $beneficiary = $this->beneficiaryService->createBeneficiary($request->validated(), $beneficiaryStatus);

            $beneficiary->load($this->beneficiaryService->beneficiaryRepository->defaultWith);

            return response()->json([
                'message' => 'Bénéficiaire créé avec succès.',
                'beneficiary' => BeneficiaryResource::make($beneficiary)
            ], HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Error creating beneficiary: ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Échec de la création du bénéficiaire.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified beneficiary
     *
     * @param Beneficiary $beneficiary
     * @return JsonResponse
     */
    public function show(Beneficiary $beneficiary): JsonResponse
    {
        // $this->authorize('view', $beneficiary);
        try {
            $beneficiary->load($this->beneficiaryService->beneficiaryRepository->defaultWith);
            return response()->json([
                'beneficiary' => BeneficiaryResource::make($beneficiary)
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching beneficiary ' . $beneficiary->id . ': ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Bénéficiaire introuvable.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get beneficiary data for editing
     *
     * @param Beneficiary $beneficiary
     * @return JsonResponse
     */
    public function edit(Beneficiary $beneficiary): JsonResponse
    {
        // $this->authorize('update', $beneficiary);
        try {
            $beneficiary->load($this->beneficiaryService->beneficiaryRepository->defaultWith);
            $options = $this->beneficiaryService->getFormOptions();

            return response()->json(array_merge(['beneficiary' => BeneficiaryResource::make($beneficiary)], $options), HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching data for beneficiary edit form for beneficiary ' . $beneficiary->id . ': ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Erreur lors de la préparation du formulaire de modification du bénéficiaire.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified beneficiary
     *
     * @param UpdateBeneficiaryRequest $request
     * @param Beneficiary $beneficiary
     * @return JsonResponse
     */
    public function update(UpdateBeneficiaryRequest $request, Beneficiary $beneficiary): JsonResponse
    {
        // $this->authorize('update', $beneficiary);
        try {
            $beneficiaryStatus = $request->input('new_status_data');
            $updatedBeneficiary = $this->beneficiaryService->updateBeneficiary($beneficiary, $request->validated(), $beneficiaryStatus);
            
            if (!$updatedBeneficiary) {
                return response()->json([
                    'message' => 'Bénéficiaire non trouvé pour la mise à jour.',
                ], HttpResponse::HTTP_NOT_FOUND);
            }
            
            $updatedBeneficiary->load($this->beneficiaryService->beneficiaryRepository->defaultWith);
            return response()->json([
                'message' => 'Bénéficiaire mis à jour avec succès.',
                'beneficiary' => BeneficiaryResource::make($updatedBeneficiary)
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error updating beneficiary ' . $beneficiary->id . ': ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Échec de la mise à jour du bénéficiaire.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified beneficiary
     *
     * @param Beneficiary $beneficiary
     * @return JsonResponse
     */
    public function destroy(Beneficiary $beneficiary): JsonResponse
    {
        // $this->authorize('delete', $beneficiary);
        try {
            $this->beneficiaryService->deleteBeneficiary($beneficiary);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error('Error deleting beneficiary: ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du bénéficiaire.',
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle activation status for multiple beneficiaries
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        // $this->authorize('massUpdate', Beneficiary::class);
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:beneficiaires,id',
        ]);
        
        $ids = $request->input('ids');
        if (empty($ids)) {
            return response()->json([], HttpResponse::HTTP_OK);
        }

        try {
            $results = $this->beneficiaryService->toggleBeneficiaryActivation($ids);
            return response()->json($results, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error toggling beneficiary activation: ' . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'message' => 'Une erreur est survenue lors du changement de statut des bénéficiaires.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Return the count of beneficiaries belonging to a class
     *
     * @param string $id Class ID
     * @return JsonResponse
     */
    public function countByClass(string $id): JsonResponse
    {
        // $this->authorize('viewAny', Beneficiary::class);
        try {
            $count = $this->beneficiaryService->countByClassId((int) $id);

            return response()->json([
                'success' => true,
                'data' => $count,
                'message' => 'Beneficiary count retrieved successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve beneficiary count.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate a beneficiary - Hierarchical validation with 2 levels
     *
     * @param Request $request
     * @param Beneficiary $beneficiary
     * @return JsonResponse
     */
    public function validateBeneficiary(Request $request, Beneficiary $beneficiary): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'action' => ['required', 'in:validate,reject'],
        ]);

        $beneficiaryFound = Beneficiary::withTrashed()->find($beneficiary->id);
        if (!$beneficiaryFound) {
            return response()->json([
                'message' => 'Bénéficiaire non trouvé.',
            ], HttpResponse::HTTP_NOT_FOUND);
        }

        // Get creator's collaborator and hierarchical chain
        $creator = $beneficiaryFound->creator;
        if (!$creator || !$creator->collaborator) {
            return response()->json([
                'error' => 'Impossible de déterminer la hiérarchie.',
            ], 400);
        }

        $creatorCollaborator = $creator->collaborator;
        $level1Supervisor = $creatorCollaborator->superior; // N+1
        $level2Supervisor = $level1Supervisor?->superior; // N+2

        $isAdminSI = $user->hasRole(Role::ADMIN_SI);

        // Handle rejection
        if ($validated['action'] === 'reject') {
            $beneficiaryFound->validation_status = 'rejected';
            $beneficiaryFound->save();
            return response()->json(['message' => 'Bénéficiaire rejeté.']);
        }

        // Validation 1 (N+1 - direct supervisor of creator)
        if ($beneficiaryFound->validation_status === 'pending') {
            if (!$level1Supervisor || ($level1Supervisor->user_id !== $user->id && !$isAdminSI)) {
                return response()->json([
                    'error' => 'Vous n\'êtes pas autorisé à valider cette demande (Validation 1).',
                ], 403);
            }

            $beneficiaryFound->validation_status = 'validation_1';
            $beneficiaryFound->validated_by_1 = $user->id;
            $beneficiaryFound->validated_at_1 = now();
            $beneficiaryFound->save();

            return response()->json(['message' => 'Validation 1 effectuée. En attente de Validation 2.']);
        }

        // Validation 2 (N+2 - supervisor of the level 1 validator)
        if ($beneficiaryFound->validation_status === 'validation_1') {
            if (!$level2Supervisor || ($level2Supervisor->user_id !== $user->id && !$isAdminSI)) {
                return response()->json([
                    'error' => 'Vous n\'êtes pas autorisé à valider cette demande (Validation 2).',
                ], 403);
            }

            $beneficiaryFound->validation_status = 'validation_2';
            $beneficiaryFound->validated_by_2 = $user->id;
            $beneficiaryFound->validated_at_2 = now();
            $beneficiaryFound->save();

            return response()->json(['message' => 'Validation 2 effectuée. Bénéficiaire totalement validé.']);
        }

        return response()->json([
            'error' => 'Cette demande n\'est plus en attente de validation.',
        ], 409);
    }

    /**
     * Export beneficiaries for insurance
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function exportAssurance(): BinaryFileResponse|JsonResponse
    {
        try {
            $actorCollaboratorId = Auth::user()->collaborator->id ?? null;
            
            if (!$actorCollaboratorId) {
                \Log::warning('BeneficiaryController: user not a collaborator', [
                    'user_id' => Auth::id(),
                ]);
                return response()->json([
                    'message' => 'User not a collaborator or missing'
                ], HttpResponse::HTTP_FORBIDDEN);
            }

            $filePath = $this->beneficiaryService->exportAssurance($actorCollaboratorId);

            return response()->download($filePath, 'beneficiaires_assurance_export_' . now()->format('Ymd_His') . '.xlsx')->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error('Assurance export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'exportation pour l\'assurance.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Import insurance data from file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function importAssurance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assurance_file' => ['required', 'file', 'mimes:xlsx,csv', 'max:10240'], 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier invalide ou manquant.',
                'errors' => $validator->errors()
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $file = $request->file('assurance_file');
            $actorCollaboratorId = Auth::user()->collaborator->id ?? null;

            if (!$actorCollaboratorId) {
                \Log::warning('BeneficiaryController: user not a collaborator', [
                    'user_id' => Auth::id(),
                ]);
                return response()->json([
                    'message' => 'User not a collaborator or missing'
                ], HttpResponse::HTTP_FORBIDDEN);
            }

            $summary = $this->beneficiaryService->importAssurance($file, $actorCollaboratorId);

            return response()->json([
                'success' => true,
                'message' => 'Importation Assurance réussie. Statuts d\'assurance mis à jour.',
                'data' => $summary,
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Assurance import failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'importation Assurance. Veuillez vérifier le fichier.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}