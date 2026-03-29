<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\DeleteParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Models\Beneficiary;
use App\Services\ParentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ParentController extends Controller
{
    public ParentService $parentService;

    public function __construct(ParentService $parentService)
    {
        $this->parentService = $parentService;
    }

    /**
     * List parents (with filters & pagination)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $parents = $this->parentService->getAll($request);

            // Optional eager-load of beneficiaries via query ?with_beneficiaries=1
            if (method_exists($parents, 'getCollection') && $request->boolean('with_beneficiaries')) {
                $parents->getCollection()->load(['beneficiaries' => function ($q) {
                    $q->withPivot('legal_role');
                }, 'creator']);
            }

            return response()->json($parents, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a parent (handles beneficiaries pivot)
     */
    public function store(StoreParentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $parent = $this->parentService->create($data)->load([
                'beneficiaries' => fn ($q) => $q->withPivot('legal_role'),
                'creator'
            ]);

            return response()->json([
                'message' => 'Parent créé avec succès',
                'parent'  => $parent,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show a parent by ID
     */
    public function show(string $id): JsonResponse
    {
        try {
            $parent = $this->parentService->show((int) $id);

            if (!$parent) {
                return response()->json(['message' => 'Parent non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $parent->load([
                'beneficiaries' => fn ($q) => $q->withPivot('legal_role'),
                'creator'
            ]);

            return response()->json([
                'message' => 'Parent récupéré avec succès',
                'parent'  => $parent,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération du parent: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a parent (handles beneficiaries pivot sync)
     */
    public function update(UpdateParentRequest $request, string $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $parent = $this->parentService->update((int) $id, $data)->load([
                'beneficiaries' => fn ($q) => $q->withPivot('legal_role'),
                'creator'
            ]);

            return response()->json([
                'message' => 'Parent mis à jour avec succès',
                'parent'  => $parent,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du parent: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Soft delete
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->parentService->delete((int) $id);

            return response()->json([
                'message' => 'Parent supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression du parent: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk soft delete
     */
    public function bulkDelete(DeleteParentRequest $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $this->parentService->bulkDestroy($ids);

            return response()->json([
                'message' => count($ids) . ' parent(s) supprimé(s) avec succès !'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression des parents: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore soft-deleted parent
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $parent = $this->parentService->restore((int) $id);

            return response()->json([
                'message' => 'Parent restauré avec succès',
                'parent'  => $parent,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration du parent: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Options for selects (sex & legal roles)
     * Kept "genders" key for backward compatibility; feel free to switch your frontend to "sexes".
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'genders' => [ // legacy key
                    'male' => 'Masculin',
                    'female' => 'Féminin',
                ],
                'sexes' => [ // preferred
                    'Masculin',
                    'Féminin',
                ],
                'legal_roles' => [
                    'father' => 'Père',
                    'mother' => 'Mère',
                    'legal_guardian' => 'Tuteur légal',
                ],
                'benefeciaries' => Beneficiary::all('id', 'first_name', 'last_name'),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function fathers(): JsonResponse
    {
        try {
            $fathers = $this->parentService->getFathers();
            return response()->json([
                'message' => 'Pères récupérés avec succès',
                'fathers' => $fathers,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des pères: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function mothers(): JsonResponse
    {
        try {
            $mothers = $this->parentService->getMothers();
            return response()->json([
                'message' => 'Mères récupérées avec succès',
                'mothers' => $mothers,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des mères: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function legalGuardians(): JsonResponse
    {
        try {
            $guardians = $this->parentService->getLegalGuardians();
            return response()->json([
                'message' => 'Tuteurs légaux récupérés avec succès',
                'legal_guardians' => $guardians,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des tuteurs légaux: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findByCin(string $cin): JsonResponse
    {
        try {
            $parent = $this->parentService->findByCin($cin);

            if (!$parent) {
                return response()->json(['message' => 'Parent non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $parent->load(['beneficiaries' => fn ($q) => $q->withPivot('legal_role'), 'creator']);

            return response()->json([
                'message' => 'Parent trouvé avec succès',
                'parent'  => $parent,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la recherche du parent: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findByPhone(string $phone): JsonResponse
    {
        try {
            $parent = $this->parentService->findByPhone($phone);

            if (!$parent) {
                return response()->json(['message' => 'Parent non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $parent->load(['beneficiaries' => fn ($q) => $q->withPivot('legal_role'), 'creator']);

            return response()->json([
                'message' => 'Parent trouvé avec succès',
                'parent'  => $parent,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la recherche du parent: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
