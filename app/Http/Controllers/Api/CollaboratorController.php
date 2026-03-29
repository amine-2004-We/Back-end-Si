<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectsCollaborator;
use App\Models\Collaborator;
use App\Services\CollaboratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use Exception;
use App\Http\Resources\CollaboratorResource;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CollaboratorController extends Controller
{
    protected CollaboratorService $collaboratorService;

    public function __construct(CollaboratorService $collaboratorService)
    {
        $this->collaboratorService = $collaboratorService;
        //$this->authorizeResource(Collaborator::class, 'collaborator');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $collaborators = $withTrashed
                ? $this->collaboratorService->getTrashed($request->all())
                : $this->collaboratorService->getAllCollaborators($request->all());
            return response()->json([
                'data' => CollaboratorResource::collection($collaborators),
                'pagination' => [
                    'total' => $collaborators->total(),
                    'count' => $collaborators->count(),
                    'per_page' => $collaborators->perPage(),
                    'current_page' => $collaborators->currentPage(),
                    'total_pages' => $collaborators->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function fetchCollaborators(): JsonResponse
    {
        try {
            $collaborators = $this->collaboratorService->getAll();
            return response()->json([
                'data' => CollaboratorResource::collection($collaborators)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Collaborator $collaborator): JsonResponse
    {
        try {
            return response()->json($this->collaboratorService->findCollaboratorById($collaborator->id));
        } catch (Exception $e) {
            return response()->json(['error' => 'Collaborator not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    public function showDeletedCollaborators(Collaborator $collaborator): JsonResponse
    {
        try {
            return response()->json($this->collaboratorService->findDeletedCollaboratorById($collaborator->id));
        } catch (Exception $e) {
            return response()->json(['error' => 'Collaborator not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreCollaboratorRequest $request): JsonResponse
    {
        try {
            return response()->json(
                new CollaboratorResource($this->collaboratorService->create($request)),
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return response()->json(
                ['error' => 'Server error: ' . $e->getMessage()

                ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateCollaboratorRequest $request, Collaborator $collaborator): JsonResponse
    {
        try {
            $collaborator = $this->collaboratorService->update($collaborator->id, $request);
            return response()->json(
                new CollaboratorResource($collaborator),
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Collaborator not found'], Response::HTTP_NOT_FOUND);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return response()->json(['error' => 'HTTP error: ' . $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Collaborator $collaborator): JsonResponse
    {
        try {
            $this->collaboratorService->delete($collaborator->id);
            return response()->json(['message' => 'Collaborator deleted.'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Collaborator not found.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:collaborators,id',
        ]);
        $deletedCount = $this->collaboratorService->bulkDelete($validated['ids']);
        return response()->json([
            'message' => "$deletedCount collaborator(s) deleted."
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $collaborator = $this->collaboratorService->restoreCollaborator($id);
            return response()->json([
                'message' => 'Collaborator successfully restored.',
                'data' => $collaborator
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Collaborator not found or already active.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error while restoring.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assignProjects(StoreProjectsCollaborator $request, int $id): JsonResponse
    {
        $collaborator = Collaborator::findOrFail($id);

        $this->collaboratorService->assignProjects($collaborator, $request->input('projects'));

        return response()->json([
            'message' => 'Projects successfully assigned.',
            'projects' => $collaborator->projects()->get(),
        ]);
    }

    public function getProjects(int $id): JsonResponse
    {
        $collaborator = $this->collaboratorService->getCollaboratorWithProjects($id);

        if (!$collaborator) {
            return response()->json(['message' => 'Collaborator not found'], 404);
        }

        return response()->json([
            'collaborator_id' => $collaborator->id,
            'projects' => $collaborator->projects,
        ]);
    }
    public function hire(StoreCollaboratorRequest $request): JsonResponse
    {
        try{
            if ($request->hasFile('photo')) {
            $request['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $request['password'] = Hash::make($request['password']);

        $collaborator = $this->collaboratorService->create($request);
        if (isset($request['projects']) && is_array($request['projects'])) {
            $collaborator->projects()->sync($request['projects']);
        }

            return response()->json([
                'message' => 'Collaborator hired successfully.',
                'data' => $collaborator
            ], Response::HTTP_CREATED);
        }
        catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Collaborator not found'], Response::HTTP_NOT_FOUND);
        }
        catch (NotFoundHttpException $e) {
            return response()->json(['error' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        }
        catch (HttpException $e) {
            return response()->json(['error' => 'HTTP error: ' . $e->getMessage()], $e->getStatusCode());
        }
        catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getCollaboratorByUserID(int $id): JsonResponse
    {
        try {
            $collaborator = $this->collaboratorService->getCollaboratorWithUserId($id);

            return response()->json([
                'success' => true,
                'data' => $collaborator,
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Collaborator not found.',
            ], Response::HTTP_NOT_FOUND);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred.',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /***
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function storeBulk(Request $request): JsonResponse
    {
        $collaboratorsData = $request->input('collaborators', []);
        if (!is_array($collaboratorsData) || empty($collaboratorsData)) {
            return response()->json(
                ['error' => 'The request body must contain a non-empty array under the key "collaborators".'],
                Response::HTTP_BAD_REQUEST
            );
        }
        $rules = (new StoreCollaboratorRequest())->rules();
        $validatedData = [];
        $errors = [];
        foreach ($collaboratorsData as $index => $data) {
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $errors["item_$index"] = $validator->errors();
            } else {
                $validatedData[] = $validator->validated();
            }
        }

        if (!empty($errors)) {
            return response()->json(
                ['errors' => $errors, 'message' => 'Validation failed for one or more collaborators.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $newCollaborators = $this->collaboratorService->createBulk($validatedData);

            return response()->json(
                CollaboratorResource::collection($newCollaborators),
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return response()->json(
                ['error' => 'Server error during bulk operation: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
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

        $educators = Collaborator::query()
            ->where('hierarchical_superior', $authCollaborator->id)
            ->whereHas('position', function ($query) {
                $query->where('title', 'Éducatrice')
                        ->orWhere('title', 'Éducateur');
            })
            ->get();

        return response()->json([
            'data' => $educators
        ], Response::HTTP_OK);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Server error: ' . $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    public function isCollabSupervisor(int $createdBy):JsonResponse
{
   
        $collaborator = User::findOrFail($createdBy)->collaborator;
        \Log::info('created by collaborator:',['collaborator'=>$collaborator]);
        if(!$collaborator){
            return response()->json([
                'error'=>'User is not associated with any collaborator'
            ],Response::HTTP_BAD_REQUEST);
        }
            
    try{
        $collaboratorSupervisor=$collaborator->hierarchical_superior;
        
        $supervisorUserId=Collaborator::findOrFail($collaboratorSupervisor)->user->id;
        \Log::info('supervisor user id:',['supervisor_user_id'=>$supervisorUserId]);
        \Log::info('auth user id',['auth_user_id'=>auth()->id()]);
        if ($supervisorUserId==auth()->id()){
            return response()->json([
                'is_supervisor'=>true
            ],Response::HTTP_OK);
        }
        else{
            return response()->json([
                'is_supervisor'=>false
            ],Response::HTTP_OK);
        }
    }
    catch(Exception $e){
        return response()->json([
            'error'=>'Server error: '.$e->getMessage()
        ],Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    
}
}
