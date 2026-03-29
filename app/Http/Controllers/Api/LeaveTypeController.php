<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveTypeStore;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LeaveTypeController extends Controller
{
    protected LeaveTypeService $leaveTypeService;

    public function __construct(LeaveTypeService $leaveTypeService)
    {
        $this->leaveTypeService = $leaveTypeService;
    }
    public function index(Request $request):JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $leaveTypes = $withTrashed
                ?$this->leaveTypeService->allWithTrashed()
                :$this->leaveTypeService->all();
            return response()->json($leaveTypes);

        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(string $leaveTypeID):JsonResponse
    {
        try {
            return response()->json(
                $this->leaveTypeService->show($leaveTypeID)
            );
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
    public function store(StoreLeaveTypeStore $request):JsonResponse
    {
        try {
            return response()->json(
                $this->leaveTypeService->create($request)
                , Response::HTTP_CREATED
            );
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(string $leavetypeId,StoreLeaveTypeStore $request):JsonResponse
    {
        try {
            return response()->json(
                $this->leaveTypeService->update($leavetypeId, $request)
            );
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(string $leavetypeId):JsonResponse
    {
        try {
            $this->leaveTypeService->delete($leavetypeId);
            return response()->json(
                ['message' => 'type de congé supprimé avec succès.'],
                Response::HTTP_NO_CONTENT
            );
        }catch (Exception $e) {
            return response()->json(['error' => 'Erreur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(string $leavetypeId):JsonResponse
    {
        try {
            $this->leaveTypeService->restore($leavetypeId);
            return response()->json(
                ['message'=>'type de congé restauré avec succès.']
            );
        }catch (Exception $e) {
            return response()->json(['error' => 'Erreur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function bulkDelete(Request $request):JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json(['message' => 'Aucun type de congé sélectionné pour suppression.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->leaveTypeService->bulkDelete($ids);

            return response()->json(['message' => "type de congé(s) supprimé(s) avec succès."], Response::HTTP_OK);
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
