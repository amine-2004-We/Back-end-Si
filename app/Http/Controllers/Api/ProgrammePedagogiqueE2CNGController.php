<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProfessionalOptionEnum;
use App\Enums\ProgrammeTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgrammePedagogiqueE2CNGRequest;
use App\Http\Requests\UpdateProgrammePedagogiqueE2CNGRequest;
use App\Http\Resources\ProgrammePedagogiqueE2CNGResource;
use App\Models\Group;
use App\Models\ProgrammePedagogiqueE2CNG;
use App\Models\ProjectClass;
use App\Services\ProgrammePedagogiqueE2CNGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\AtelierE2CNGEnum;
use App\Enums\MatiereE2CNGEnum;
use Throwable;

class ProgrammePedagogiqueE2CNGController extends Controller
{
    protected ProgrammePedagogiqueE2CNGService $service;

    public function __construct(ProgrammePedagogiqueE2CNGService $service)
    {
        $this->service = $service;
        // $this->authorizeResource(ProgrammePedagogiqueE2CNG::class, 'programme_pedagogique_e2cng');
    }

    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'groupes' => Group::all(['id', 'name']),
                'types' => ProgrammeTypeEnum::options(),
                'professional_options' => ProfessionalOptionEnum::options(),
                'ateliers' => AtelierE2CNGEnum::options(),
                'matieres' => MatiereE2CNGEnum::options(),
                'metiers' => AtelierE2CNGEnum::options(), 
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');

            $filters = $request->input('filter', []);

            if ($request->has('per_page')) {
                $filters['per_page'] = $request->query('per_page');
            }
            if ($request->has('page')) {
                $filters['page'] = $request->query('page');
            }
            if ($request->has('sort_by')) {
                $filters['sort_by'] = $request->query('sort_by');
            }
            if ($request->has('sort_direction')) {
                $filters['sort_direction'] = $request->query('sort_direction');
            }

            if ($withTrashed) {
                $items = $this->service->getAllWithTrashed($filters);
            } else {
                $items = $this->service->getAll($filters);
            }

            return response()->json([
                'data' => ProgrammePedagogiqueE2CNGResource::collection($items->items()),
                'pagination' => [
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->service->show($id);
            return response()->json(new ProgrammePedagogiqueE2CNGResource($item));
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Programme not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreProgrammePedagogiqueE2CNGRequest $request): JsonResponse
    {
        try {
            $item = $this->service->create($request);
            return response()->json(new ProgrammePedagogiqueE2CNGResource($item), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateProgrammePedagogiqueE2CNGRequest $request, ProgrammePedagogiqueE2CNG $programme_pedagogique_e2cng): JsonResponse
    {
        try {
            $this->authorize('update', $programme_pedagogique_e2cng);
            $item = $this->service->update($programme_pedagogique_e2cng->id, $request);
            return response()->json(new ProgrammePedagogiqueE2CNGResource($item));
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Programme not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ProgrammePedagogiqueE2CNG $programme_pedagogique_e2cng): JsonResponse
    {
        try {
            $this->service->delete($programme_pedagogique_e2cng->id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Programme not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $item = $this->service->restore($id);
            return response()->json(['message' => 'Programme successfully restored.', 'data' => new ProgrammePedagogiqueE2CNGResource($item)]);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Programme not found or not deleted.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:programme_pedagogique_e2cng,id',
        ]);

        try {
            $deleted = $this->service->bulkDelete($validated['ids']);
            return response()->json(['message' => "$deleted programme(s) deleted."]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
}
