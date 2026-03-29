<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Http\Resources\SiteResource;
use App\Models\Site;
use App\Services\SiteService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * class SiteController
 */
class SiteController extends Controller
{
    /**
     * The service for handling site business logic.
     * @var SiteService
     */
    protected SiteService $siteService;

    /**
     * The service for handling user-related logic.
     * @var UserService
     */
    protected UserService $userService;

    /**
     * SiteController constructor.
     *
     * @param SiteService $siteService The site service instance.
     * @param UserService $userService The user service instance.
     */
    public function __construct(SiteService $siteService, UserService $userService)
    {
        $this->siteService = $siteService;
        $this->userService = $userService;
        // Automatically apply SitePolicy rules to all standard controller actions.
        //$this->authorizeResource(Site::class, 'site');
    }

    /**
     * Display a paginated listing of the sites.
     *
     * @param Request $request The request object containing filters and pagination.
     * @return AnonymousResourceCollection A collection of site resources.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->input('per_page', 15);
        $sites = $this->siteService->getPaginatedSites($filters, $perPage);
        return SiteResource::collection($sites);
    }

    /**
     * Store a newly created site in storage.
     *
     * @param StoreSiteRequest $request The request object with validated data.
     * @return JsonResponse A JSON response containing the created site resource.
     */
    public function store(StoreSiteRequest $request): JsonResponse
    {
        $site = $this->siteService->createSite($request->validated());
        $site->load(['commune.cercle.province.region.country', 'douar', 'creator']);
        return (new SiteResource($site))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified site.
     *
     * @param Site $site The site model instance.
     * @return SiteResource A single site resource.
     */
    public function show(Site $site): SiteResource
    {
        $site->load(['commune.cercle.province.region.country', 'douar', 'creator']);
        return new SiteResource($site);
    }

    /**
     * Update the specified site in storage.
     *
     * @param UpdateSiteRequest $request The request object with validated data.
     * @param Site $site The site model instance to update.
     * @return SiteResource A single site resource representing the updated site.
     */
    public function update(UpdateSiteRequest $request, Site $site): SiteResource
    {
        $this->siteService->updateSite($site, $request->validated());
        $site->load(['commune.cercle.province.region.country', 'douar', 'creator']);
        return new SiteResource($site);
    }

    /**
     * Remove the specified site from storage.
     *
     * @param Site $site The site model instance to delete.
     * @return JsonResponse A JSON response with no content.
     */
    public function destroy(Site $site): JsonResponse
    {
        $this->siteService->deleteSite($site);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Perform a bulk soft delete or restore on multiple sites.
     *
     * @param Request $request The request containing an array of site IDs.
     * @return JsonResponse A JSON response summarizing the operation.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', Site::class);

        try {
            $validated = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:sites,id']);
            $results = $this->siteService->bulkDeleteSites($validated['ids']);

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failedResults = array_filter($results, fn($r) => !$r['success']);

            if (!empty($failedResults)) {
                return response()->json([
                    'message' => 'Certaines opérations ont échoué.',
                    'failed_operations' => array_values($failedResults),
                    'successful_count' => $successCount,
                ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            return response()->json([
                'message' => 'Opération(s) effectuée(s) avec succès.',
                'successful_count' => $successCount,
            ], HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in SiteController bulkDelete: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur inattendue est survenue.',
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a list of all users, typically for form dropdowns.
     *
     * @return JsonResponse
     */
    public function getSiteUsers(): JsonResponse
    {
        //$this->authorize('viewAny', Site::class);

        $users = $this->userService->getAll();
        return response()->json($users, Response::HTTP_OK);
    }
}
