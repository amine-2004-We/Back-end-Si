<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Http\Requests\ValidatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Models\PartnerNote;
use App\Services\PartnerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Exception;

/**
 * Class PartnerController
 * @package App\Http\Controllers\Api
 */
class PartnerController extends Controller
{
    protected PartnerService $partnerService;

    public function __construct(PartnerService $partnerService)
    {
        $this->partnerService = $partnerService;
        // $this->authorizeResource(Partner::class, 'partner');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // $this->authorize('viewAny', Partner::class);

        $partners = $this->partnerService->getAllPartners($request->all());
        return PartnerResource::collection($partners);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePartnerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('partner_logo')) {
            $data['partner_logo'] = $request->file('partner_logo');
        }

        $partner = $this->partnerService->createPartner($data);
        return new PartnerResource($partner->load(['naturePartner', 'structurePartner', 'status', 'creator']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Partner $partner): PartnerResource
    {
        return new PartnerResource($partner->load(['contactPeople', 'naturePartner', 'structurePartner', 'status', 'notes', 'creator']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePartnerRequest $request, Partner $partner)
    {
        $data = $request->validated();

        if ($request->hasFile('partner_logo')) {
            $data['partner_logo'] = $request->file('partner_logo');
        }

        $partner = $this->partnerService->updatePartner($partner, $data);
        return new PartnerResource($partner->load(['naturePartner', 'structurePartner', 'status','creator']));
    }

    /**
     * MODIFIÉ : Désactive (Soft Delete) le partenaire.
     */
    public function destroy(Request $request, Partner $partner): JsonResponse
    {
        try {
             $results = $this->partnerService->desactiverPartners([$partner->id]);

            $result = $results[0] ?? null;

            if ($result && $result['success']) {
                return response()->json(['message' => $result['message']], HttpResponse::HTTP_OK);
            } elseif ($result) {
                return response()->json(['message' => $result['message']], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                return response()->json(['message' => 'Opération de désactivation échouée.'], HttpResponse::HTTP_BAD_REQUEST);
            }
        } catch (Exception $e) {
            Log::error('Error in PartnerController destroy: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur inattendue est survenue lors de l\'opération.',
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * MODIFIÉ : Désactivation (Soft Delete) de masse.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('massUpdate', Partner::class);

        try {
            $ids = $request->input('ids');

            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'Aucun ID de partenaire fourni.'], HttpResponse::HTTP_BAD_REQUEST);
            }

             $results = $this->partnerService->desactiverPartners($ids);

            $successCount = 0;
            $failedResults = [];

            foreach ($results as $result) {
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedResults[] = $result;
                }
            }

            if (!empty($failedResults)) {
                return response()->json([
                    'message' => 'Certaines opérations de désactivation ont échoué.',
                    'failed_operations' => $failedResults,
                    'successful_count' => $successCount,
                ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            return response()->json([
                'message' => 'Opération(s) de désactivation effectuée(s) avec succès.',
                'successful_count' => $successCount,
            ], HttpResponse::HTTP_OK);

        } catch (Exception $e) {
            Log::error('Error in PartnerController bulkDelete: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur inattendue est survenue lors de l\'opération.',
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * MODIFIÉ : Valide un Prospect en utilisant la validation stricte
     */
    public function validatePartner(ValidatePartnerRequest $request, Partner $partner): PartnerResource
    {
        $this->authorize('validate', $partner);

         $data = $request->validated();

         $partner = $this->partnerService->validerProspect($partner, $data);

         return new PartnerResource($partner->load(['naturePartner', 'structurePartner', 'status', 'contactPeople', 'notes', 'creator']));
    }

    /**
     * Réactive un ou plusieurs partenaires (Soft Delete).
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $this->authorize('massUpdate', Partner::class);

        try {
            $ids = $request->input('ids');
            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'Aucun ID de partenaire fourni.'], HttpResponse::HTTP_BAD_REQUEST);
            }

            $results = $this->partnerService->reactiverPartners($ids);

            $successCount = 0;
            $failedResults = [];
            foreach ($results as $result) {
                if ($result['success']) $successCount++;
                else $failedResults[] = $result;
            }

            if (!empty($failedResults)) {
                return response()->json([
                    'message' => 'Certaines réactivations ont échoué.',
                    'failed_operations' => $failedResults,
                    'successful_count' => $successCount,
                ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            return response()->json([
                'message' => 'Opération(s) de réactivation effectuée(s) avec succès.',
                'successful_count' => $successCount,
            ], HttpResponse::HTTP_OK);

        } catch (Exception $e) {
            Log::error('Error in PartnerController bulkRestore: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur inattendue est survenue lors de la réactivation.',
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ajoute une note à un partenaire.
     */
    public function addNote(Request $request, Partner $partner): JsonResponse
    {
        $this->authorize('addNote', $partner);

        $validated = $request->validate([
            'note' => 'required|string|min:1',
        ]);

        $note = $this->partnerService->addNoteToPartner($partner, $validated['note']);

        return response()->json($note, HttpResponse::HTTP_CREATED);
    }

    /**
     * Supprime une note spécifique d'un partenaire.
     */
    public function destroyNote(Partner $partner, PartnerNote $note): JsonResponse
    {
         if ($note->partner_id !== $partner->id) {
            return response()->json(['message' => 'Non autorisé.'], HttpResponse::HTTP_FORBIDDEN);
        }

         $this->authorize('deleteNote', $note);

         $this->partnerService->deleteNote($note);

         return response()->json(['message' => 'Note supprimée avec succès.'], HttpResponse::HTTP_OK);
    }

    /**
     * Activer un partenaire (En cours → Partenaire actif)
     * POST /partners/{id}/activate
     */
    public function activatePartner(Request $request, Partner $partner): PartnerResource
    {
        $this->authorize('update', $partner);

        $validated = $request->validate([
            'phase_id' => 'nullable|exists:phases,id',
        ]);

        try {
            $partner = $this->partnerService->activatePartner($partner, $validated['phase_id'] ?? null);
            return new PartnerResource($partner->load(['status', 'phase', 'contactPeople', 'notes', 'creator']));
        } catch (Exception $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Clôturer un partenaire (Partenaire actif/En cours → Clôturé)
     * POST /partners/{id}/close
     */
    public function closePartner(Request $request, Partner $partner): PartnerResource
    {
        $this->authorize('update', $partner);

        $validated = $request->validate([
            'closure_reason' => 'required|string|min:5',
        ]);

        try {
            $partner = $this->partnerService->closePartner($partner, $validated['closure_reason']);
            return new PartnerResource($partner->load(['status', 'phase', 'contactPeople', 'notes', 'creator']));
        } catch (Exception $e) {
            abort(400, $e->getMessage());
        }
    }}