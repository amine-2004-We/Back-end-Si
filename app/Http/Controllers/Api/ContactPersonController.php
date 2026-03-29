<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContactStatusEnum;
use App\Enums\OriginalChannelEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactPersonRequest;
use App\Http\Resources\ContactResource;
use App\Models\ContactPerson;
use App\Services\ContactPersonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ContactPersonController extends Controller
{
    protected ContactPersonService $contactService;

    public function __construct(ContactPersonService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $contacts = $this->contactService->getAll($request);
            return response()->json($contacts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a basic listing (id, name, email, etc).
     */
    public function getContacts(): JsonResponse
    {
        try {
            $contacts = $this->contactService->allBasic();
            return response()->json($contacts, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified contact.
     */
    public function show(ContactPerson $contact): JsonResponse
    {
        try {
            $contactItem = $this->contactService->find($contact->id);
            return response()->json(new ContactResource($contactItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du contact '
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created contact.
     */
    public function store(StoreContactPersonRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $contactItem = $this->contactService->create($validated);
            return response()->json(new ContactResource($contactItem), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du contact '
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified contact.
     */
    public function update(StoreContactPersonRequest $request, ContactPerson $contact): JsonResponse
    {
        try {
            $validated = $request->validated();
            $contactItem = $this->contactService->update($contact->id, $validated);
            return response()->json(new ContactResource($contactItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du contact'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(ContactPerson $contact): JsonResponse
    {
        try {
            $this->contactService->delete($contact->id);
            return response()->json([
                'message' => 'Le contact a été supprimé avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du contact '
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete contacts.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:contacts,id',
            ]);

            $this->contactService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des contacts '
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted contact.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $contactItem = $this->contactService->restore($id);
            return response()->json(new ContactResource($contactItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du contact '
            ], Response::HTTP_CONFLICT);
        }
    }
    public function enums(): JsonResponse
    {
        return response()->json([
            'contact_status' => ContactStatusEnum::options(),
            'orginal_channel' => OriginalChannelEnum::options(),
        ]);
    }
}
