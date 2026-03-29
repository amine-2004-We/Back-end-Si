<?php

namespace App\Http\Controllers\Api;

use App\Constants\Role;
use App\Enums\DeliveryRequestStatus;
use App\Enums\PriorityFcEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Resources\DeliveryRequestResource;
use App\Models\DeliveryRequest;
use App\Services\DeliveryRequestService;
use App\Services\pdfs\DeliveryRequestPdfService;
use App\Services\Notification\MailService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeliveryRequestController extends Controller
    /**
     * Return all delivery requests with purchaseOrder and quote (no pagination).
     */
    {
    public function allFull(): \Illuminate\Http\JsonResponse
    {
        $requests = DeliveryRequest::with(['purchaseOrder.quote'])->get();
        return response()->json([
            'data' => $requests
        ], \Illuminate\Http\Response::HTTP_OK);
    }
    protected DeliveryRequestService $deliveryRequestService;

    protected DeliveryRequestPdfService $deliveryRequestPdfService;

    public function __construct(DeliveryRequestService $deliveryRequestService, DeliveryRequestPdfService $deliveryRequestPdfService)
    {
        $this->deliveryRequestService = $deliveryRequestService;
        $this->deliveryRequestPdfService = $deliveryRequestPdfService;
    }

    /**
     * Génère le PDF de la demande de livraison (prévisualisation)
     */
    public function pdfRevise(int $id)
    {
        $path = $this->deliveryRequestPdfService->generatePdf($id);
        return response()->file($path);
    }

    /**
     * Télécharge le PDF de la demande de livraison
     */
    public function pdfDownload(int $id)
    {
        $path = $this->deliveryRequestPdfService->generatePdf($id);
        return response()->download($path);
    }

    /**
     * Display a listing of the delivery requests.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $requests = $this->deliveryRequestService->getAll($request);
            return response()->json($requests, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a basic listing (id, code, etc.).
     */
    public function getDeliveryRequests(): JsonResponse
    {
        try {
            $requests = $this->deliveryRequestService->allBasic();
            return response()->json($requests, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified delivery request.
     */
    public function show(DeliveryRequest $deliveryRequest): JsonResponse
    {
        try {
            $item = $this->deliveryRequestService->find($deliveryRequest->id);
            return response()->json(new DeliveryRequestResource($item), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Demande de livraison non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la demande de livraison : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created delivery request in storage.
     */
    public function store(StoreDeliveryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $deliveryRequest = $this->deliveryRequestService->create($data);

            return response()->json([
                'message' => 'Delivery request created successfully.',
                'data' => $deliveryRequest,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create delivery request: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified delivery request in storage.
     */
    public function update(StoreDeliveryRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $deliveryRequest = $this->deliveryRequestService->update($id, $data);

            return response()->json([
                'message' => 'Delivery request updated successfully.',
                'data' => $deliveryRequest,
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Delivery request not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update delivery request: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified delivery request.
     */
    public function destroy(DeliveryRequest $deliveryRequest): JsonResponse
    {
        try {
            $this->deliveryRequestService->delete($deliveryRequest->id);
            return response()->json([
                'message' => 'La demande de livraison a été supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Demande de livraison non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la demande de livraison : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete delivery requests.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:delivery_request,id',
            ]);

            $this->deliveryRequestService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des demandes de livraison : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted delivery request.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $item = $this->deliveryRequestService->restore($id);
            return response()->json(new DeliveryRequestResource($item), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Demande de livraison non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration de la demande de livraison : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get enums for dropdowns (priority, status, etc.).
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'priority' => PriorityFcEnum::options(),
            'status' => DeliveryRequestStatus::options(),
        ]);
    }


    public function validateDeliveryRequest(Request $request, DeliveryRequest $deliveryRequest)
{
    $user = $request->user();

    $validated = $request->validate([
        'action' => ['required', 'in:validate,reject'],
    ]);
    \Log::info('status ',['status'=>$deliveryRequest->status]);
    $currentStatus = $deliveryRequest->status;
    if ($currentStatus !== DeliveryRequestStatus::ENATTENT->value) {
            \Log::info('Delivery request not pending', ['delivery_request_id' => $deliveryRequest->id, 'enum-status'=>DeliveryRequestStatus::ENATTENT->value]);
        return response()->json([
            'error' => 'Cette demande n’est plus en attente.'
        ], 409);
    }

    $collaborator = $deliveryRequest->creator?->collaborator;

    if (
        !$collaborator ||
        !$collaborator->superior ||
        (
            $collaborator->superior->user_id !== $user->id &&
            !$user->hasRole(Role::ADMIN_SI)
        )
    ) {
        return response()->json([
            'error' => 'Vous n’êtes pas autorisé à traiter cette demande.'
        ], 403);
    }

    // 4. Decision
    $deliveryRequest->status =
        $validated['action'] === 'reject'
            ? DeliveryRequestStatus::REFUSEE->value
            : DeliveryRequestStatus::VALIDEE->value;

    $deliveryRequest->save();

   \Log::info('Delivery request data', ['delivery_request' => $deliveryRequest->status]);

    $this->generateEmail( $deliveryRequest->status,$deliveryRequest);

    return response()->json([
        'message' => 'Décision enregistrée avec succès.',
        'delivery_request' => $deliveryRequest,
    ]);
}

    public function generateEmail(string $status, DeliveryRequest $deliveryRequest)
    {
        //log received data
        \Log::info('Generating email for delivery request', ['status' => $status]);
        try{
           $creatorEmail = $deliveryRequest->creator?->email;


    MailService::sendMail(
            [$creatorEmail,'i.ennajy@fondationzakoura.org'],
            "Décision sur une demande de livraison",
            'emails.deliveryRequests.validationDecision',
            [
                'title' => 'Décision sur la demande de livraison',
                'subject' => 'Décision sur la demande de livraison',
                'status'=>$status,
               'deliveryRequest' => $deliveryRequest,
            ]
        );
        \Log::info("Delivery request decision email sent to: " . $creatorEmail);
    }
    catch(\Exception $e){
        \Log::error("Error sending delivery request decision email: " . $e->getMessage());
}

}
    }