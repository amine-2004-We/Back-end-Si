<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Collaborator;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Level;
use App\Models\ProjectClass;
use App\Models\User;
use App\Services\GroupService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class GroupController
 */
class GroupController extends Controller
{
    /**
     * @var GroupService
     */
    public GroupService $groupService;

    /**
     * @param GroupService $groupService
     */
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $groups = $this->groupService->getAll($request);
            return response()->json($groups, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur dans le serveur!' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreGroupRequest $request
     * @return JsonResponse
     */
    public function store(StoreGroupRequest $request)
    {
        try{
            $data = $request->validated();
            $group = $this->groupService->create($data);
            return response()->json([
                'message' => 'Groupe créé avec succès',
                'group' => $group,
            ], Response::HTTP_CREATED);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur dans le serveur!' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        try{
            $group = $this->groupService->show($id);
            return response()->json([
                'message' => 'Groupe récupéré avec succès',
                'group' => $group,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération du groupe' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateGroupRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateGroupRequest $request, string $id)
    {
        try{
            $data = $request->validated();
            $group = $this->groupService->update($id, $data);
            return response()->json([
                'message' => 'Groupe mis à jour avec succès',
                'group' => $group,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la mise à jour du groupe' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        try{
            $this->groupService->delete($id);
            return response()->json([
                'message' => 'Groupe supprimé avec succès',
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la suppression du groupe' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try{
            $ids = $request->input('ids', []);
            $this->groupService->bulkDestroy($ids);
            return response()->json([
                'message' => count($ids).' groupe(s) supprimé(s) avec succès !'
            ]);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la suppression des groupes' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        try{
            $group = $this->groupService->restore($id);
            return response()->json([
                'message' => 'Groupe restauré avec succès',
                'group' => $group,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la restauration du groupe: ' . $e->getMessage()],
                Response::HTTP_CONFLICT);
        }
    }

    public function options()
    {
        try{
            return response()->json(
                [
                    'group_types' => GroupType::all(),
                    'classes' => ProjectClass::all(),
                    'educators' => $this->getEducators()->original['data'],
                    'statuses' => [
                        'active' => 'Actif',
                        'closed' => 'Fermé',
                        'paused' => 'En pause',
                    ],
                ]
            );
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des options' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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



    public function getGroupsByClass($classId){
        try{
            $groups=Group::query()
                ->where('class_id', $classId)
                ->where('deleted_at', null)
                ->get();
            return response()->json([
                'message' => 'Groupes récupérés avec succès',
                'groups' => $groups,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des groupes' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


   public function getLevelsByClassCycle($classId)
{
    try {
       
     $class = ProjectClass::query()
    ->where('id', $classId)
    ->whereNull('deleted_at')
    ->with([
        'classResources' => function ($query) {
            $query->where('isStill', true)
                  ->with([
                      'collaborator.user:id,name'
                  ]);
        }
    ])
    ->firstOrFail();


       
        $levels = Level::query()
            ->where('cycle_id', $class->cycle_id)
            ->whereNull('deleted_at')
            ->orderBy('order', 'asc')
            ->get();

       
    $educators = $class->classResources
    ->pluck('collaborator.user')
    ->filter()        
    ->unique('id')  
    ->values();       

        return response()->json([
            'message'   => 'Données récupérées avec succès',
            'levels'    => $levels,
            'educators' => $educators,
        ], Response::HTTP_OK);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'error' => 'Classe introuvable'
        ], Response::HTTP_NOT_FOUND);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la récupération des données'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}
