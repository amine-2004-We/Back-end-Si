<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\TraineeCollaborator;
use App\Models\Collaborator;
use App\Models\TrainingGroup;
use App\Repositories\TraineeCollaboratorRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class TraineeCollaboratorService
{
    /** @var TraineeCollaboratorRepository */
    protected TraineeCollaboratorRepository $tcRepository;

    public function __construct(TraineeCollaboratorRepository $tcRepository)
    {
        $this->tcRepository = $tcRepository;
    }

    public function getTraineeCollaborators(Request $request): LengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'participant_id',
            'collaborator_id',
            'remarks',
            'final_evaluation',
            'insured',
            'training_id',
            'training_group_id',
            'registered_from',
            'registered_to',
            'collaborator_name',
            'per_page',
        ]);

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $this->tcRepository
            ->allWithFilters($filters)
            ->with([
                'participant',
                'collaborator',
                'trainings',
                'trainingGroups',
            ])
            ->paginate($perPage);
    }
    public function createMultipleTrainings(array $payload, ?int $createdByUserId = null): TraineeCollaborator
    {
        return DB::transaction(function () use ($payload, $createdByUserId) {
            $participant = Participant::create([
                'attendance_recorded' => false,
                'insured'             => false,
                'comments'            => null,
                'created_by_id'       => $createdByUserId,
                'type'                => 'trainee',
            ]);

            $collaborator = Collaborator::findOrFail($payload['collaborator_id']);

            $tc = new TraineeCollaborator([
                'remarks' => $payload['remarks'] ?? null,
            ]);
            $tc->participant()->associate($participant);
            $tc->collaborator()->associate($collaborator);
            $tc->save();

            $attach = [];
            $allGroupIds = [];
            foreach ($payload['trainings'] as $t) {
                $tid = (int)$t['training_id'];
                $attach[$tid] = [
                    'training_evaluation_status'     => $t['training_evaluation_status']     ?? null,
                    'satisfaction_evaluation' => $t['satisfaction_evaluation'] ?? null,
                    'registered_at'           => $t['registered_at']           ?? null,
                ];
            }
            if (!empty($attach)) {
                $tc->trainings()->attach($attach);
            }

             if (!empty($groupIds)) {
            /** @var Collection<int,TrainingGroup> $groups */
            $groups = TrainingGroup::whereIn('id', $groupIds)->lockForUpdate()->get();

            $byId = $groups->keyBy('id');

            foreach ($groupIds as $gid) {
                /** @var TrainingGroup|null $g */
                $g = $byId->get($gid);
                if (!$g) {
                    throw new \RuntimeException("Groupe $gid introuvable.");
                }

                $target  = (int) ($g->target_size ?? 0);
                $current = (int) ($g->current_size ?? 0);
                if ($target > 0 && $current >= $target) {
                    throw new \RuntimeException("Le groupe « {$g->title} » est plein.");
                }
            }

            $tc->trainingGroups()->syncWithoutDetaching($groupIds);

            foreach ($groups as $g) {
                $target = (int) ($g->target_size ?? 0);
                if ($target > 0 && $g->current_size >= $target) {
                    throw new \RuntimeException("Le groupe « {$g->title} » est plein.");
                }
                $g->increment('current_size', 1);
            }
        }

            return $tc->load([
                'participant',
                'collaborator',
                'trainings' => fn($q) => $q->withPivot(['training_evaluation_status','satisfaction_evaluation','registered_at']),
                'trainingGroups',
            ]);
        });
    }

   /**
     * @param TraineeCollaborator $tc
     * @param array $payload
     * @return TraineeCollaborator
     */
    public function updateMultipleTrainings(TraineeCollaborator $tc, array $payload): TraineeCollaborator
    {
        return DB::transaction(function () use ($tc, $payload) {
            if (array_key_exists('remarks', $payload)) {
                $tc->remarks = $payload['remarks'];
            }
            if (isset($payload['collaborator_id'])) {
                $collaborator = Collaborator::findOrFail($payload['collaborator_id']);
                $tc->collaborator()->associate($collaborator);
            }
            $tc->save();

            if ($tc->participant && array_key_exists('participant', $payload)) {
                $tc->participant->update($payload['participant']);
            }

            if (array_key_exists('trainings', $payload)) {
                $trainingsToSync = [];
                foreach ($payload['trainings'] as $t) {
                    $tid = (int)$t['training_id'];
                    $trainingsToSync[$tid] = [
                        'training_evaluation_status'  => $t['training_evaluation_status']  ?? null,
                        'satisfaction_evaluation' => $t['satisfaction_evaluation'] ?? null,
                        'registered_at'           => $t['registered_at']           ?? null,
                    ];
                }
                $tc->trainings()->sync($trainingsToSync);
            }

            if (array_key_exists('training_groups', $payload)) {
                $allGroupIds = $payload['training_groups'] ?? [];
                $tc->trainingGroups()->sync($allGroupIds);
            }

            return $tc->load([
                'participant',
                'collaborator',
                'trainings' => fn($q) => $q->withPivot(['training_evaluation_status','satisfaction_evaluation','registered_at']),
                'trainingGroups',
            ]);
        });
    }

    public function createBasic(array $payload, ?int $createdByUserId = null): TraineeCollaborator
    {
        return DB::transaction(function() use ($payload, $createdByUserId) {
            // 1. Create participant as before
            $participant = Participant::create([
                'attendance_recorded' => false,
                'insured'             => false,
                'comments'            => null,
                'created_by_id'       => $createdByUserId,
                'type'                => 'trainee',
            ]);
            // 2. Find collabo
            $collaborator = Collaborator::findOrFail($payload['collaborator_id']);
            // 3. TraineeCollaborator model
            $tc = new TraineeCollaborator([
                'remarks' => $payload['remarks'] ?? null,
            ]);
            $tc->participant()->associate($participant);
            $tc->collaborator()->associate($collaborator);
            $tc->save();
            // 4. Optionally add training groups
            if (!empty($payload['training_groups'])) {
                $tc->trainingGroups()->sync($payload['training_groups']);
            }
            // 5. Return
            return $tc->load([
                'participant',
                'collaborator',
                'trainingGroups',
            ]);
        });
    }

    public function updateBasic(TraineeCollaborator $tc, array $payload): TraineeCollaborator
    {
        return DB::transaction(function() use ($tc, $payload) {
            if (array_key_exists('remarks', $payload)) {
                $tc->remarks = $payload['remarks'];
            }
            if (isset($payload['collaborator_id'])) {
                $collaborator = Collaborator::findOrFail($payload['collaborator_id']);
                $tc->collaborator()->associate($collaborator);
            }
            $tc->save();
            if ($tc->participant && array_key_exists('participant', $payload)) {
                $tc->participant->update($payload['participant']);
            }
            if (array_key_exists('training_groups', $payload)) {
                $groups = $payload['training_groups'] ?? [];
                $tc->trainingGroups()->sync($groups);
            }
            return $tc->load([
                'participant',
                'collaborator',
                'trainingGroups',
            ]);
        });
    }

    /**
     * Lightweight list (e.g. for selects).
     */
    public function getAllWithoutPagination(): Collection
    {
        return $this->tcRepository->allWithoutPagination();
    }

    /**
     * Find one (includes soft-deleted).
     */
    public function getTraineeCollaborator(int $id): TraineeCollaborator
    {
        return $this->tcRepository->find($id);
    }

    /**
     * @param int $trainingId
     * @param array $payload
     * @param int|null $createdByUserId
     */
    public function createForTraining(
        int $trainingId,
        array $payload,
        ?int $createdByUserId = null
    ): TraineeCollaborator {
        return DB::transaction(function () use ($trainingId, $payload, $createdByUserId) {
            $participant = Participant::create([
                'attendance_recorded' => false,
                'insured'             => false,              // <-- ALWAYS FALSE here (ignore payload.insured)
                'comments'            => null,
                'created_by_id'       => $createdByUserId,
            ]);

            $collaborator = Collaborator::findOrFail($payload['collaborator_id']);

            $tc = new TraineeCollaborator([
                'remarks' => $payload['remarks'] ?? null,
            ]);
            $tc->participant()->associate($participant);
            $tc->collaborator()->associate($collaborator);
            $tc->save();

            $groupIds = array_values(array_unique($payload['training_groups'] ?? []));
            if (!empty($groupIds)) {
                $this->assertTrainingGroupsBelongToTraining($groupIds, $trainingId);
            }

            $meta = $payload['trainings'] ?? [];
            $tc->trainings()->syncWithoutDetaching([
                $trainingId => [
                    'training_evaluation_status'     => $meta['training_evaluation_status']     ?? null,
                    'satisfaction_evaluation' => $meta['satisfaction_evaluation'] ?? null,
                    'registered_at'           => $meta['registered_at']           ?? null,
                ],
            ]);

            if (!empty($groupIds)) {
                $tc->trainingGroups()->syncWithoutDetaching($groupIds);
            }

            return $tc->load([
                'participant',
                'collaborator',
                'trainings' => fn($q) => $q->withPivot(['training_evaluation_status', 'satisfaction_evaluation', 'registered_at']),
                'trainingGroups',
            ]);
        });
    }

    /**
     * @param int $tcId
     * @param int $trainingId
     * @param array $payload
     * @param mixed $newCollaboratorId
     * @return \App\Models\TraineeCollaborator
     */
    public function updateForTraining(
        int $tcId,
        int $trainingId,
        array $payload,
        ?int $newCollaboratorId = null
    ): TraineeCollaborator {
        return DB::transaction(function () use ($tcId, $trainingId, $payload, $newCollaboratorId) {
            $tc = TraineeCollaborator::findOrFail($tcId);

            if (array_key_exists('remarks', $payload)) {
                $tc->remarks = $payload['remarks'];
            }
            if ($newCollaboratorId) {
                $collab = Collaborator::findOrFail($newCollaboratorId);
                $tc->collaborator()->associate($collab);
            }
            $tc->save();

            if (array_key_exists('trainings', $payload)) {
                $meta = $payload['trainings'] ?? [];
                $tc->trainings()->syncWithoutDetaching([$trainingId => []]); // ensure it exists
                $tc->trainings()->updateExistingPivot($trainingId, [
                    'training_evaluation_status'     => $meta['training_evaluation_status']     ?? null,
                    'satisfaction_evaluation' => $meta['satisfaction_evaluation'] ?? null,
                    'registered_at'           => $meta['registered_at']           ?? null,
                ]);
            }

            if (array_key_exists('training_groups', $payload)) {
                $groupIds = array_values(array_unique($payload['training_groups'] ?? []));
                $this->assertTrainingGroupsBelongToTraining($groupIds, $trainingId);
                $tc->trainingGroups()->sync($groupIds);
            }

            return $tc->load([
                'participant',
                'collaborator',
                'trainings' => fn($q) => $q->withPivot(['training_evaluation_status', 'satisfaction_evaluation', 'registered_at']),
                'trainingGroups',
            ]);
        });
    }

    /**
     * @param array $ids
     * @return int
     */
    public function deleteMany(array $ids): int
    {
        return $this->tcRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return TraineeCollaborator
     */
    public function restore(int $id): TraineeCollaborator
    {
        return $this->tcRepository->restore($id);
    }

    /**
     * @param int $id
     * @return TraineeCollaborator
     */
    public function getWithRelations(int $id): TraineeCollaborator
    {
        return $this->tcRepository->findWithRelations($id);
    }

    /**
     * Enroll in the SAME training (helper if needed later).
     */
    public function enrollInTraining(int $tcId, int $trainingId, array $pivot = []): void
    {
        $tc = TraineeCollaborator::findOrFail($tcId);
        $tc->trainings()->syncWithoutDetaching([
            $trainingId => [
                'training_evaluation_status'     => $pivot['training_evaluation_status']     ?? null,
                'satisfaction_evaluation' => $pivot['satisfaction_evaluation'] ?? null,
                'registered_at'           => $pivot['registered_at']           ?? null,
            ],
        ]);
    }

    /**
     * @param int $tcId
     * @param int $trainingId
     */
    public function softUnenrollFromTraining(int $tcId, int $trainingId): void
    {
        $this->tcRepository->softDetachTraining($tcId, $trainingId);
    }

    /**
     * @param array $groupIds
     * @param int $trainingId
     * @return void
     */
    private function assertTrainingGroupsBelongToTraining(array $groupIds, int $trainingId): void
    {
        if (empty($groupIds)) {
            return;
        }
        $count = TrainingGroup::whereIn('id', $groupIds)
            ->where('training_id', $trainingId)
            ->count();

        if ($count !== count($groupIds)) {
            abort(422, 'One or more training groups do not belong to this training.');
        }
    }
}
