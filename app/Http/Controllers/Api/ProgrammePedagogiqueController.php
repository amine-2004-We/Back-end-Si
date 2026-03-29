<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgrammePedagogiqueRequest;
use App\Http\Requests\UpdateProgrammePedagogiqueRequest;
use App\Http\Resources\ProgrammePedagogiqueResource;
use App\Models\ProgrammePedagogique;
use App\Services\ProgrammePedagogiqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Support\Facades\Log;
use  App\Models\ProjectClass;
use  App\Models\Collaborator;
use  App\Models\User;

class ProgrammePedagogiqueController extends Controller
{
    protected ProgrammePedagogiqueService $service;

    public function __construct(ProgrammePedagogiqueService $service)
    {
        $this->service = $service;
        //$this->authorizeResource(ProgrammePedagogique::class, 'programme_pedagogique');
    }

    public function options(): JsonResponse
    {
        try {
            $projects = [];
            $user = auth()->user();

            if ($user && $user->collaborator) {
                Log::info('user has collaborator', ['collaborator_id' => $user->collaborator->id ?? null]);
                $projects = $user->collaborator->projects()
                    ->select('projects.id as id', 'projects.project_name as name')
                    ->distinct()
                    ->get()
                    ->map(function ($p) {
                        return ['id' => $p->id, 'name' => $p->name ?? $p->project_name ?? null];
                    })->toArray();
            }

            return response()->json([
                'subjects' => ProgrammePedagogique::subjectsList(),
                'classes' => ProjectClass::all(['id', 'class_name as name']),
                'collaborators' => User::all('id', 'name'),
                'projects' => $projects,
            ]);
        } catch (Throwable $e) {
            Log::error('ProgrammePedagogiqueController::options error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
                'data' => ProgrammePedagogiqueResource::collection($items),
                'pagination' => [
                    'total' => $items->total(),
                    'count' => $items->count(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'total_pages' => $items->lastPage(),
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
            return response()->json(new ProgrammePedagogiqueResource($item));
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Programme not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreProgrammePedagogiqueRequest $request): JsonResponse
    {
        try {
            $item = $this->service->create($request);
            return response()->json(new ProgrammePedagogiqueResource($item), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateProgrammePedagogiqueRequest $request, ProgrammePedagogique $programme_pedagogique): JsonResponse
    {
        try {
            //$this->authorize('update', $programme_pedagogique);
            $item = $this->service->update($programme_pedagogique->id, $request);
            return response()->json(new ProgrammePedagogiqueResource($item));
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Programme not found.'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ProgrammePedagogique $programme_pedagogique): JsonResponse
    {
        try {
            $this->service->delete($programme_pedagogique->id);
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
            return response()->json(['message' => 'Programme successfully restored.', 'data' => new ProgrammePedagogiqueResource($item)]);
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
            'ids.*' => 'integer|exists:programme_pedagogique,id',
        ]);

        try {
            $deleted = $this->service->bulkDelete($validated['ids']);
            return response()->json(['message' => "$deleted programme(s) deleted."]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
