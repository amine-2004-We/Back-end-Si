<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssuranceRequest;
use App\Http\Requests\UpdateAssuranceRequest;
use App\Http\Resources\AssuranceResource;
use App\Models\Assurance;
use App\Services\AssuranceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Exports\UninsuredExport;
use App\Imports\AssurancesImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 *class AssuranceController
 */
class AssuranceController extends Controller
{
    /**
     * @param AssuranceService $service
     */
    public function __construct(protected AssuranceService $service)
    {
        //$this->authorizeResource(Assurance::class, 'assurance');
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $assurances = $this->service->getFiltered($request->all());
        return AssuranceResource::collection($assurances)->response();
    }

    /**
     * @param StoreAssuranceRequest $request
     * @return JsonResponse
     */
    public function store(StoreAssuranceRequest $request): JsonResponse
    {
        $assurance = $this->service->create($request->validated());
        return (new AssuranceResource($assurance->load($this->service->repository->defaultWith)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Assurance $assurance
     * @return AssuranceResource
     */
    public function show(Assurance $assurance): AssuranceResource
    {
        return new AssuranceResource($assurance->load($this->service->repository->defaultWith));
    }

    /**
     * @param UpdateAssuranceRequest $request
     * @param Assurance $assurance
     * @return AssuranceResource
     */
    public function update(UpdateAssuranceRequest $request, Assurance $assurance): AssuranceResource
    {
        $updatedAssurance = $this->service->update($assurance, $request->validated());
        return new AssuranceResource($updatedAssurance);
    }

    /**
     * @param Assurance $assurance
     * @return JsonResponse
     */
    public function destroy(Assurance $assurance): JsonResponse
    {
        $this->service->toggleActivation([$assurance->id]);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getFormOptions(): JsonResponse
    {
        //$this->authorize('create', Assurance::class);
        return response()->json($this->service->getFormOptions());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', Assurance::class);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:insurances_training,id',
        ]);

        $results = $this->service->toggleActivation($validated['ids']);
        return response()->json($results);
    }


    /**
     * @return BinaryFileResponse
     */
    public function exportUninsured()
    {
        try {
            //$this->authorize('viewAny', Assurance::class);

            return Excel::download(new UninsuredExport, 'personnes-non-assurees.xlsx');

        } catch (\Exception $e) {
            Log::error("Export Uninsured Failed: " . $e->getMessage());

            return response()->json([
                'message' => "Une erreur est survenue lors de la génération du fichier d'exportation."
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function import(Request $request)
    {
        //$this->authorize('create', Assurance::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new AssurancesImport, $request->file('file'));
            return response()->json(['message' => 'Importation terminée avec succès.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => "Erreur durant l'importation: " . $e->getMessage()], 500);
        }
    }
}
