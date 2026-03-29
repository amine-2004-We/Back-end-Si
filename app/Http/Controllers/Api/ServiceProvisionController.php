<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvision;
use App\Models\BudgetLine;
use App\Services\ServiceProvisionService;
use App\Http\Requests\StoreServiceProvisionRequest;
use App\Http\Requests\UpdateServiceProvisionRequest;
use App\Http\Resources\ServiceProvisionResource;
use App\Enums\ServiceProvisionTypeEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * class ServiceProvisionController
 */
class ServiceProvisionController extends Controller
{
    /**
     * @var ServiceProvisionService
     */
    protected ServiceProvisionService $provisionService;

    /**
     * @param ServiceProvisionService $provisionService
     */
    public function __construct(ServiceProvisionService $provisionService)
    {
        $this->provisionService = $provisionService;
        $this->authorizeResource(ServiceProvision::class, 'service_provision');
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $provisions = $this->provisionService->getPaginatedProvisions(
            $request->all(),
            $request->input('per_page', 15)
        );
        return ServiceProvisionResource::collection($provisions)->response();
    }

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function create(): JsonResponse
    {
        $this->authorize('create', ServiceProvision::class);
        return response()->json([
            'budget_lines' => BudgetLine::where('status', 'active')->get(['id', 'code', 'label']),
            'types' => array_column(ServiceProvisionTypeEnum::cases(), 'value'),
        ]);
    }

    /**
     * @param StoreServiceProvisionRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceProvisionRequest $request): JsonResponse
    {
        $provision = $this->provisionService->createProvision(
            $request->safe()->except('justification'),
            $request->file('justification')
        );
        return response()->json(new ServiceProvisionResource($provision), HttpResponse::HTTP_CREATED);
    }

    /**
     * @param ServiceProvision $serviceProvision
     * @return JsonResponse
     */
    public function show(ServiceProvision $serviceProvision): JsonResponse
    {
        return response()->json(new ServiceProvisionResource($this->provisionService->getProvision($serviceProvision->id)));
    }

    /**
     * @param UpdateServiceProvisionRequest $request
     * @param ServiceProvision $serviceProvision
     * @return JsonResponse
     */
    public function update(UpdateServiceProvisionRequest $request, ServiceProvision $serviceProvision): JsonResponse
    {
        $updatedProvision = $this->provisionService->updateProvision(
            $serviceProvision->id,
            $request->safe()->except('justification'),
            $request->file('justification')
        );
        return response()->json(new ServiceProvisionResource($updatedProvision));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        $this->authorize('massUpdate', ServiceProvision::class);
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $results = $this->provisionService->toggleProvisionActivation($request->input('ids'));
        return response()->json($results);
    }
}
