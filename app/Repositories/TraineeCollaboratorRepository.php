<?php

namespace App\Repositories;

use App\Models\TraineeCollaborator;
use App\Models\Participant;
use App\Models\Collaborator;
use App\Models\Training;
use App\Models\TrainingGroup;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * class TraineeCollaboratorRepository
 */
class TraineeCollaboratorRepository
{
    /**
     * @return Builder
     */
    public function all(): Builder
    {
        return TraineeCollaborator::query();
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function allWithFilters(array $filters): Builder
    {
        $query = TraineeCollaborator::query()
            ->with(['participant', 'collaborator.department', 'collaborator.position', 'trainings', 'trainingGroups'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }

        if (!empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (!empty($filters['remarks'])) {
            $query->where('remarks', 'like', '%' . $filters['remarks'] . '%');
        }

        if (!empty($filters['final_evaluation'])) {
            $query->whereHas('participant', function (Builder $q) use ($filters) {
                $q->where('final_evaluation', $filters['final_evaluation']);
            });
        }

        if (isset($filters['insured'])) {
            if ($filters['insured'] === 'true') {
                $query->whereHas('participant', fn (Builder $q) => $q->where('insured', true));
            } elseif ($filters['insured'] === 'false') {
                $query->whereHas('participant', fn (Builder $q) => $q->where('insured', false));
            }
        }

        if (!empty($filters['training_id'])) {
            $trainingId = (int) $filters['training_id'];
            $query->whereHas('trainings', function (Builder $q) use ($trainingId) {
                $q->where('trainings.id', $trainingId);
            });
        }

        if (!empty($filters['training_group_id'])) {
            $groupId = (int) $filters['training_group_id'];
            $query->whereHas('trainingGroups', function (Builder $q) use ($groupId) {
                $q->where('training_group.id', $groupId);
            });
        }

        if(!empty($filters['collaborator_name'])){
            $query->whereHas('collaborator', function (Builder $q) use ($filters) {
                $q->where('last_name', 'like', '%' . $filters['collaborator_name'] . '%');
            });
        }

        $from = $filters['registered_from'] ?? null;
        $to   = $filters['registered_to']   ?? null;

        if ($from || $to) {
            $query->whereHas('trainings', function (Builder $q) use ($from, $to) {
                if ($from) {
                    $q->wherePivot('registered_at', '>=', $from);
                }
                if ($to) {
                    $q->wherePivot('registered_at', '<=', $to);
                }
            });
        }

        return $query;
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return TraineeCollaborator::select('id', 'participant_id', 'collaborator_id')
            ->get();
    }

    /**
     * @param int $id
     * @return TraineeCollaborator
     * @throws ModelNotFoundException
     */
    public function find(int $id): TraineeCollaborator
    {
        return TraineeCollaborator::withTrashed()
            ->with([
                'participant',
                'collaborator',
                'trainings' => function ($q) {
                    $q->withPivot(['training_evaluation', 'satisfaction_evaluation', 'registered_at']);
                },
                'trainingGroups',
            ])
            ->findOrFail($id);
    }

    /**
     * @param array $data
     * @return TraineeCollaborator
     */
    public function create(array $data): TraineeCollaborator
    {
        return TraineeCollaborator::create($data);
    }

    /**
     * @param array $data
     * @param Participant $participant
     * @param Collaborator $collaborator
     * @param array<int,array<string,mixed>>|null $trainingsData
     * @param array<int,int>|null $trainingGroupIds
     * @return TraineeCollaborator
     * @throws Exception
     */
    public function createWithAssociations(
        array $data,
        Participant $participant,
        Collaborator $collaborator,
        ?array $trainingsData,
        ?array $trainingGroupIds
    ): TraineeCollaborator {
        return DB::transaction(function () use ($data, $participant, $collaborator, $trainingsData, $trainingGroupIds) {
            $tc = new TraineeCollaborator([
                'remarks'        => $data['remarks'] ?? null,
            ]);
            $tc->participant()->associate($participant);
            $tc->collaborator()->associate($collaborator);
            $tc->save();

            if (!empty($trainingsData)) {
                $attach = [];
                foreach ($trainingsData as $row) {
                    if (!empty($row['training_id'])) {
                        $attach[(int) $row['training_id']] = [
                            'training_evaluation'     => $row['training_evaluation']     ?? null,
                            'satisfaction_evaluation' => $row['satisfaction_evaluation'] ?? null,
                            'registered_at'           => $row['registered_at']           ?? null,
                        ];
                    }
                }
                if (!empty($attach)) {
                    $tc->trainings()->attach($attach);
                }
            }

            if (!empty($trainingGroupIds)) {
                $tc->trainingGroups()->attach($trainingGroupIds);
            }

            return $tc->load(['participant', 'collaborator', 'trainings', 'trainingGroups']);
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @param Collaborator|null $collaborator
     * @param array<int,array<string,mixed>>|null $trainingsData
     * @param array<int,int>|null $trainingGroupIds
     * @return TraineeCollaborator
     * @throws Exception
     */
    public function update(
        int $id,
        array $data,
        ?Collaborator $collaborator,
        ?array $trainingsData,
        ?array $trainingGroupIds
    ): TraineeCollaborator {
        try {
            $tc = TraineeCollaborator::findOrFail($id);

            $tc->fill([
                'remarks' => $data['remarks'] ?? $tc->remarks,
            ]);

            if ($collaborator) {
                $tc->collaborator()->associate($collaborator);
            }
            $tc->save();

            if (is_array($trainingsData)) {
                $sync = [];
                foreach ($trainingsData as $row) {
                    if (!empty($row['training_id'])) {
                        $sync[(int) $row['training_id']] = [
                            'training_evaluation'     => $row['training_evaluation']     ?? null,
                            'satisfaction_evaluation' => $row['satisfaction_evaluation'] ?? null,
                            'registered_at'           => $row['registered_at']           ?? null,
                        ];
                    }
                }
                $tc->trainings()->sync($sync);
            }

            if (is_array($trainingGroupIds)) {
                $tc->trainingGroups()->sync($trainingGroupIds);
            }

            return $tc->load(['participant', 'collaborator', 'trainings', 'trainingGroups']);
        } catch (Exception $e) {
            throw new Exception('Error updating trainee collaborator: ' . $e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $tc = $this->find($id);
        return $tc->delete();
    }

    /**
     * @param array<int,int> $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return TraineeCollaborator::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return TraineeCollaborator
     * @throws ModelNotFoundException
     */
    public function restore(int $id): TraineeCollaborator
    {
        $tc = TraineeCollaborator::withTrashed()->findOrFail($id);
        $tc->restore();
        return $tc;
    }

    /**
     * @param int $id
     * @return TraineeCollaborator
     * @throws ModelNotFoundException
     */
    public function findWithRelations(int $id): TraineeCollaborator
    {
        $tc = TraineeCollaborator::with([
            'participant',
            'collaborator',
            'trainings' => fn ($q) => $q->withPivot(['training_evaluation_status', 'satisfaction_evaluation', 'registered_at']),
            'trainingGroups',
        ])->findOrFail($id);
        return $tc;
    }

    /**
     * @param TraineeCollaborator $tc
     * @param array<int,int> $groupIds
     */
    public function assignTrainingGroups(TraineeCollaborator $tc, array $groupIds): void
    {
        $tc->trainingGroups()->syncWithoutDetaching($groupIds);
    }

    /**
     * @param int $tcId
     * @param int $groupId
     * @return void
     */
    public function detachTrainingGroup(int $tcId, int $groupId): void
    {
        $tc = TraineeCollaborator::findOrFail($tcId);
        $tc->trainingGroups()->detach($groupId);
    }

    /**
     * @param TraineeCollaborator $tc
     * @param int $trainingId
     * @param array<string,mixed> $pivot
     * @return void
     */
    public function enrollInTraining(TraineeCollaborator $tc, int $trainingId, array $pivot = []): void
    {
        $tc->trainings()->syncWithoutDetaching([
            $trainingId => [
                'training_evaluation'     => $pivot['training_evaluation']     ?? null,
                'satisfaction_evaluation' => $pivot['satisfaction_evaluation'] ?? null,
                'registered_at'           => $pivot['registered_at']           ?? null,
            ],
        ]);
    }

    /**
     * @param int $tcId
     * @param int $trainingId
     * @param array<string,mixed> $pivot
     * @return void
     */
    public function updateTrainingPivot(int $tcId, int $trainingId, array $pivot): void
    {
        $tc = TraineeCollaborator::findOrFail($tcId);
        $tc->trainings()->updateExistingPivot($trainingId, [
            'training_evaluation'     => $pivot['training_evaluation']     ?? null,
            'satisfaction_evaluation' => $pivot['satisfaction_evaluation'] ?? null,
            'registered_at'           => $pivot['registered_at']           ?? null,
        ]);
    }

    /**
     * @param int $tcId
     * @param int $trainingId
     * @return void
     */
    public function softDetachTraining(int $tcId, int $trainingId): void
    {
        DB::table('trainee_collaborator_training')
            ->where('trainee_collaborator_id', $tcId)
            ->where('training_id', $trainingId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }
}
