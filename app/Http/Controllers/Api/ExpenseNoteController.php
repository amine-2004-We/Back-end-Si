<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseNoteRequest;
use App\Http\Requests\UpdateExpenseNoteRequest;
use App\Http\Requests\BulkDeleteExpenseNoteRequest;
use App\Http\Requests\UpdateExpenseNoteStatusRequest;
use App\Models\ExpenseNote;
use App\Models\Training;
use App\Models\BudgetLine;
use App\Models\Collaborator;
use App\Models\Trainer;
use App\Models\Participant;
use App\Services\ExpenseNoteService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\Options\TrainerOptionsResource;
use App\Http\Resources\Options\ParticipantOptionResource;

/**
 * class ExpenseNoteController
 */
class ExpenseNoteController extends Controller
{
    /**
     * @var ExpenseNoteService
     */
    public ExpenseNoteService $service;

    /**
     * @param ExpenseNoteService $service
     */
    public function __construct(ExpenseNoteService $service)
    {
        $this->service = $service;
        $this->authorizeResource(ExpenseNote::class, 'expenseNote');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $notes = $this->service->getAll($request);
            return response()->json($notes, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreExpenseNoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreExpenseNoteRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $note = $this->service->create(
                data: $data,
                createdById: (int) auth()->id(),
                attachmentFile: $request->file('attachment') // <- upload
            );

            return response()->json([
                'message' => 'Note de dépenses créée avec succès',
                'expense_note' => $note,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param ExpenseNote $expenseNote
     * @return JsonResponse
     */
    public function show(ExpenseNote $expenseNote): JsonResponse
    {
        try {
            $note = $this->service->get($expenseNote->id);
            return response()->json([
                'message' => 'Note de dépenses récupérée avec succès',
                'expense_note' => $note,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateExpenseNoteRequest $request
     * @param ExpenseNote $expenseNote
     * @return JsonResponse
     */
    public function update(UpdateExpenseNoteRequest $request, ExpenseNote $expenseNote): JsonResponse
    {
        try {
            $data = $request->validated();
            $note = $this->service->update(
                id: $expenseNote->id,
                data: $data,
                newAttachment: $request->file('attachment'),
                removeAttachment: (bool) $request->boolean('attachment_remove')
            );

            return response()->json([
                'message' => 'Note de dépenses mise à jour avec succès',
                'expense_note' => $note,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param ExpenseNote $expenseNote
     * @return JsonResponse
     */
    public function destroy(ExpenseNote $expenseNote): JsonResponse
    {
        try {
            $this->service->deleteMany([$expenseNote->id]);

            return response()->json([
                'message' => 'Note de dépenses supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param BulkDeleteExpenseNoteRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(BulkDeleteExpenseNoteRequest $request): JsonResponse
    {
        try {
            $ids = $request->validated('ids');
            $count = $this->service->deleteMany($ids);

            return response()->json([
                'message' => $count.' note(s) de dépenses supprimée(s) avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression multiple: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(ExpenseNote $expenseNote): JsonResponse
    {
        try {
            $withTrashed = ExpenseNote::withTrashed()->findOrFail($expenseNote->id);
            $note = $this->service->restore($withTrashed->id);

            return response()->json([
                'message' => 'Note de dépenses restaurée avec succès',
                'expense_note' => $note,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration: '.$e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    public function updateStatus(UpdateExpenseNoteStatusRequest $request, ExpenseNote $expenseNote): JsonResponse
    {
        try {
            $this->authorize('updateStatus', $expenseNote);

            $status   = $request->validated('status');
            $comments = $request->validated('comments') ?? null;

            $note = $this->service->updateStatus($expenseNote->id, $status, $comments);

            return response()->json([
                'message' => 'Statut de la note mis à jour avec succès',
                'expense_note' => $note,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du statut: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        $this->authorize('create', ExpenseNote::class);

        $static = $this->service->getOptions();

        $trainers = Trainer::query()
            ->with([
                'internalTrainer.collaborator:id,first_name,last_name,collaborator_code',
                'externalTrainer:id,trainer_id,full_name,trainer_identifier',
            ])
            ->where(function ($q) {
                $q->whereHas('internalTrainer.collaborator')
                ->orWhereHas('externalTrainer');
            })
            ->get();

        $trainerOptions = TrainerOptionsResource::collection($trainers)->resolve();
        $trainerOptions = array_values(array_filter(
            $trainerOptions,
            fn ($t) => isset($t['label']) && trim($t['label']) !== ''
        ));

        $participants = Participant::query()
            ->with([
                'traineeCollaborator.collaborator:id,first_name,last_name,collaborator_code,cin',
                'externalTrainee:id,full_name,external_identifier',
            ])
            ->where(function ($q) {
                $q->whereHas('traineeCollaborator.collaborator')
                ->orWhereHas('externalTrainee');
            })
            ->get();

        $participantOptions = ParticipantOptionResource::collection($participants)->resolve();
        $participantOptions = array_values(array_filter(
            $participantOptions,
            fn ($p) => isset($p['label']) && trim($p['label']) !== ''
        ));

        return response()->json([
            'beneficiary_types'   => $static['beneficiary_types'],
            'expense_natures'     => $static['expense_natures'],
            'validation_statuses' => $static['validation_statuses'],

            'trainings'      => Training::whereIn('training_type', ['continuous','monthly'])
                                ->get(['id', 'title', 'training_type']),
            'collaborators'  => Collaborator::select('id','first_name','last_name','cin','collaborator_code')->get(),
            'trainers'       => $trainerOptions,
            'participants'   => $participantOptions,

            'budget_lines'   => BudgetLine::select('id','code','label')->get(),
        ]);
    }

}
