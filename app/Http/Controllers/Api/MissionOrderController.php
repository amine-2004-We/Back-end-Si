<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMissionOrderRequest;
use App\Http\Requests\UpdateMissionOrderRequest;
use App\Models\Project;
use App\Models\Collaborator;
use App\Models\Province;
use App\Models\Region;
use App\Services\MissionOrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class MissionOrderController
 */
class MissionOrderController extends Controller
{
    /**
     * @var MissionOrderService
     */
    public MissionOrderService $missionOrderService;

    /**
     * @param MissionOrderService $missionOrderService
     */
    public function __construct(MissionOrderService $missionOrderService)
    {
        $this->missionOrderService = $missionOrderService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $missionOrders = $this->missionOrderService->getAll($request);
            return response()->json($missionOrders, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur dans le serveur!' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreMissionOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreMissionOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $missionOrder = $this->missionOrderService->create($data);
            return response()->json([
                'message' => 'Ordre de mission créé avec succès',
                'mission_order' => $missionOrder,
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
            $missionOrder = $this->missionOrderService->showWithRelations($id);
            return response()->json([
                'message' => 'Ordre de mission récupéré avec succès',
                'mission_order' => $missionOrder,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération de l\'ordre de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateMissionOrderRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateMissionOrderRequest $request, string $id)
    {
        try{
            $data = $request->validated();
            $missionOrder = $this->missionOrderService->update($id, $data);
            return response()->json([
                'message' => 'Ordre de mission mis à jour avec succès',
                'mission_order' => $missionOrder,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'ordre de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        try{
            $this->missionOrderService->delete($id);
            return response()->json([
                'message' => 'Ordre de mission supprimé avec succès',
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la suppression de l\'ordre de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $this->missionOrderService->bulkDestroy($ids);
            return response()->json([
                'message' => count($ids).' ordre(s) de mission supprimé(s) avec succès !'
            ]);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la suppression des ordres de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        try{
            $missionOrder = $this->missionOrderService->restore($id);
            return response()->json([
                'message' => 'Ordre de mission restauré avec succès',
                'mission_order' => $missionOrder,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la restauration de l\'ordre de mission: ' . $e->getMessage()],
                Response::HTTP_CONFLICT);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function approve(string $id): JsonResponse
    {
        try{
            $missionOrder = $this->missionOrderService->approve($id);
            return response()->json([
                'message' => 'Ordre de mission approuvé avec succès',
                'mission_order' => $missionOrder,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de l\'approbation de l\'ordre de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function refuse(string $id): JsonResponse
    {
        try{
            $missionOrder = $this->missionOrderService->refuse($id);
            return response()->json([
                'message' => 'Ordre de mission refusé avec succès',
                'mission_order' => $missionOrder,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors du refus de l\'ordre de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $collaboratorId
     * @return JsonResponse
     */
    public function getByCollaborator(string $collaboratorId): JsonResponse
    {
        try{
            $missionOrders = $this->missionOrderService->getByCollaborator($collaboratorId);
            return response()->json([
                'message' => 'Ordres de mission récupérés avec succès',
                'mission_orders' => $missionOrders,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des ordres de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $projectId
     * @return JsonResponse
     */
    public function getByProject(string $projectId): JsonResponse
    {
        try{
            $missionOrders = $this->missionOrderService->getByProject($projectId);
            return response()->json([
                'message' => 'Ordres de mission récupérés avec succès',
                'mission_orders' => $missionOrders,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des ordres de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus(string $status): JsonResponse
    {
        try{
            $missionOrders = $this->missionOrderService->getByStatus($status);
            return response()->json([
                'message' => 'Ordres de mission récupérés avec succès',
                'mission_orders' => $missionOrders,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des ordres de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $missionType
     * @return JsonResponse
     */
    public function getByMissionType(string $missionType): JsonResponse
    {
        try{
            $missionOrders = $this->missionOrderService->getByMissionType($missionType);
            return response()->json([
                'message' => 'Ordres de mission récupérés avec succès',
                'mission_orders' => $missionOrders,
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des ordres de mission' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options()
    {
        try{
            return response()->json(
                [
                    'projects' => Project::all(),
                    'collaborators' => Collaborator::all(),
                    'provinces' => Province::all(),
                    'regions' => Region::all(),
                    'mission_types' => [
                        'internal' => 'Interne',
                        'external' => 'Externe',
                    ],
                    'statuses' => [
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'refused' => 'Refusé',
                    ],
                ]
            );
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des options' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
