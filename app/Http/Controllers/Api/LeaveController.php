<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use App\Services\LeaveService;
use App\Services\Notification\MailService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class LeaveController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
        //$this->authorizeResource(Leave::class, 'leave');
    }
    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $collaborators = $withTrashed
                ? $this->leaveService->getAllTrashedLeaves($request->all())
                : $this->leaveService->getAllLeaves($request->all());
            return response()->json($collaborators);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            return response()->json($this->leaveService->show($id));

        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function store(StoreLeaveRequest $request): JsonResponse{
        try {
            return response()->json($this->leaveService->store($request));
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(StoreLeaveRequest $request,Leave $leave): JsonResponse
    {
        try {
            $this->authorize('update', $leave);
            return response()->json($this->leaveService->update($leave->id, $request));
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(Leave $leave): JsonResponse
    {
        try {
            $this->leaveService->delete($leave->id);
            return response()->json(['message' => 'Demande de congé supprimée.'], Response::HTTP_NO_CONTENT);
        }catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(int $id): JsonResponse{
        try {
            $this->leaveService->restore($id);
            return response()->json(['message' => 'Demande de congé restaurée.'], Response::HTTP_NO_CONTENT);
        }catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:leave,id',
        ]);
        $deletedCount = $this->leaveService->bulkDelete($validated['ids']);
        return response()->json([
        'message' => "$deletedCount collaborator(s) deleted."
        ]);
    }

    public function validateLeave(Request $request, Leave $leave): JsonResponse
    {
        try {
            //$this->authorize('approve', $leave);
            $user = $request->user();
            $previousStatus = $leave->status;
            $collaborator = $leave->collaborator;

            switch ($leave->status) {

                case 'pending':
                    if ($user->id !== $collaborator->superior->user_id) {
                        return response()->json([
                            'error' => 'Seul le supérieur hiérarchique peut valider ce congé à cette étape.'
                        ], 403);
                    }

                    $leave->status = 'approved_by_manager';
                    $leave->save();

                    $emails = [
                        'k.lamoubariki@fondationzakoura.org',
                        'm.lamoumni@fondationzakoura.org'
                    ];

                    MailService::sendMail(
                        $emails,
                        "Mise à jour du statut de congé",
                        'emails.training_alert',
                        [
                            'item' => $leave,
                            'validator' => $collaborator->superior
                        ]
                    );
                    break;

                case 'approved_by_manager':
                    if (!$user->hasRole('RH (Ressources Humaines)') || !$user->hasRole('Admin SI')) {
                        return response()->json([
                            'error' => 'Seul le service RH peut valider ce congé à cette étape.'
                        ], 403);
                    }

                    $leave->status = 'approved_by_hr';
                    $leave->save();

                    $emails = [
                        $collaborator->email,
                        $collaborator->superior->email
                    ];

                    MailService::sendMail(
                        $emails,
                        "Mise à jour du statut de congé",
                        'emails.training_alert',
                        [
                            'item' => $leave,
                            'validator' => $user
                        ]
                    );
                    break;

                default:
                    return response()->json([
                        'error' => 'Ce congé ne peut pas être validé dans son statut actuel.'
                    ], 403);
            }

            return response()->json([
                'message' => "Statut du congé modifié de $previousStatus à {$leave->status}",
                'leave' => $leave
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}
