<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\UnitService;
use App\Services\SiteService;
use App\Services\UserService;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Collaborator;
use App\UnitStatusEnum;
use App\UnitTypeEnum;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Handles API requests for Unit management.
 */
class UnitController extends Controller
{
    /**
     * @var UnitService The service for handling unit business logic.
     */
    protected UnitService $unitService;
    /**
     * @var SiteService The service for handling site-related logic.
     */
    protected SiteService $siteService;
    /**
     * @var UserService The service for handling user-related logic.
     */
    protected UserService $userService;

    /**
     * UnitController constructor.
     *
     * @param UnitService $unitService The unit service instance.
     * @param SiteService $siteService The site service instance.
     * @param UserService $userService The user service instance.
     */
    public function __construct(
        UnitService $unitService,
        SiteService $siteService,
        UserService $userService
    ) {
        $this->unitService = $unitService;
        $this->siteService = $siteService;
        $this->userService = $userService;
        //$this->authorizeResource(Unit::class, 'unit');
    }

    /**
     * Display a paginated listing of the units.
     *
     * @param Request $request The request object containing filters and pagination.
     * @return JsonResponse A JSON response containing the paginated list of units.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $perPage = $request->input('per_page', 15);
            $units = $this->unitService->getPaginatedUnits($filters, $perPage);
            return response()->json($units, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching units: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la récupération des unités.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve the options required for the unit creation form.
     *
     * @return JsonResponse A JSON response containing form options.
     */
    public function create(): JsonResponse
    {
        try {
            $sites = $this->siteService->getAllSites();
            $educators = $this->userService->getAll();
            $unitTypes = array_column(UnitTypeEnum::cases(), 'value');
            $unitStatuses = array_column(UnitStatusEnum::cases(), 'value');
            return response()->json([
                'sites' => $sites,
                'educators' => $this->getEducators()->original['data'],
                'unit_types' => $unitTypes,
                'unit_statuses' => $unitStatuses,
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching data for unit creation form: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la préparation du formulaire de création d\'unité.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getEducators()
{
    try {
        $authCollaborator = auth()->user()->collaborator;

        if (!$authCollaborator) {
            return response()->json([
                'error' => 'User is not associated with any collaborator'
            ], Response::HTTP_BAD_REQUEST);
        }

        $users = Collaborator::query()
            ->where('hierarchical_superior', $authCollaborator->id)
            ->whereHas('position', function ($query) {
                $query->whereIn('title', ['Éducatrice', 'Éducateur']);
            })
            ->with('user') 
            ->get()
            ->pluck('user') 
            ->filter()      
            ->values();    

        return response()->json([
            'data' => $users
        ], Response::HTTP_OK);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Server error: ' . $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    

    /**
     * Store a newly created unit in storage.
     *
     * @param StoreUnitRequest $request The request object with validated data.
     * @return JsonResponse A JSON response containing the created unit resource.
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        try {
            $unit = $this->unitService->createUnit($request->validated());
            return response()->json([
                'message' => 'Unité créée avec succès.',
                'unit' => UnitResource::make($unit->load('site', 'educator', 'creator'))
            ], HttpResponse::HTTP_CREATED);
        } catch (QueryException $e) {
            Log::error('Database error creating unit: ' . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la création de l\'unité en raison d\'une erreur de base de données.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Error creating unit: ' . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la création de l\'unité.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified unit.
     *
     * @param Unit $unit The unit model instance.
     * @return JsonResponse A JSON response containing the specified unit.
     */
    public function show(Unit $unit): JsonResponse
    {
        try {
            return response()->json([
                'unit' => UnitResource::make($unit->load('site.commune.cercle.province.region', 'educator', 'creator','classes.cycles', 'classes.classStatus',))
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching unit ' . $unit->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Unité introuvable.'], HttpResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Retrieve the options required for the unit edit form.
     *
     * @param Unit $unit The unit model instance.
     * @return JsonResponse A JSON response containing form options and the unit data.
     */
    public function edit(Unit $unit): JsonResponse
    {
        try {
            $sites = $this->siteService->getAllSites();
            $educators = $this->userService->getAll();
            $unitTypes = array_column(UnitTypeEnum::cases(), 'value');
            $unitStatuses = array_column(UnitStatusEnum::cases(), 'value');
            return response()->json([
                'unit' => UnitResource::make($unit->load('site', 'educator', 'creator', 'classes')),
                'sites' => $sites,
                'educators' => $educators,
                'unit_types' => $unitTypes,
                'unit_statuses' => $unitStatuses,
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching data for unit edit form for unit ' . $unit->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la préparation du formulaire de modification.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified unit in storage.
     *
     * @param UpdateUnitRequest $request The request object with validated data.
     * @param Unit $unit The unit model instance to update.
     * @return JsonResponse A JSON response containing the updated unit.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        try {
            $updatedUnit = $this->unitService->updateUnit($unit->id, $request->validated());
            if (!$updatedUnit) {
                return response()->json(['message' => 'Unité non trouvée pour la mise à jour.'], HttpResponse::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Unité mise à jour avec succès.',
                'unit' => UnitResource::make($updatedUnit->load('site', 'educator', 'creator'))
            ], HttpResponse::HTTP_OK);
        } catch (QueryException $e) {
            Log::error('Database error updating unit: ' . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la mise à jour de l\'unité en raison d\'une erreur de base de données.',
                'error' => $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Error updating unit ' . $unit->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Échec de la mise à jour de l\'unité.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle the activation status for a batch of units.
     *
     * @param Request $request The request containing an array of unit IDs.
     * @return JsonResponse A JSON response with the results of the operation.
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', Unit::class);

        $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'integer|exists:units,id',
        ]);
        $ids = $request->input('unit_ids');
        if (empty($ids)) {
            return response()->json([], HttpResponse::HTTP_OK);
        }

        try {
            $results = $this->unitService->toggleUnitActivation($ids);
            return response()->json($results, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error toggling unit activation: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a list of all users, typically for form dropdowns.
     *
     * @return JsonResponse
     */
    public function getUnitUsers(): JsonResponse
    {
        //$this->authorize('viewAny', Unit::class);

        try {
            $users = $this->userService->getAll();
            return response()->json($users, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error fetching unit users: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des utilisateurs.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getFilteredUnits(int $regionId): JsonResponse
    {
        try {
            return response()->json($this->unitService->getFilteredUnits($regionId));
        }catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des unités.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

