<?php

namespace App\Services;

use App\Models\TrainingGroup;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingGroupService
{
    protected TrainingGroupRepository $trainingGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
    }

    public function getAll($request)
    {
        return $this->trainingGroupRepository->withFilters($request->all());
    }

    public function allWithoutPagination()
    {
        return $this->trainingGroupRepository->allWithoutPagination();
    }

    public function show(int $id)
    {
        return $this->trainingGroupRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->trainingGroupRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->trainingGroupRepository->update($data, $id);
    }

    public function delete(int $id)
    {
        return $this->trainingGroupRepository->delete($id);
    }

    public function bulkDestroy(array $ids)
    {
        return $this->trainingGroupRepository->bulkDelete($ids);
    }

    public function restore(int $id)
    {
        return $this->trainingGroupRepository->restore($id);
    }

    /**
     * @param array $data
     */
    public function createWithParticipants(array $data)
    {
        return DB::transaction(function () use ($data) {
            $group = $this->trainingGroupRepository->create($data);
            if(!empty($data['responsible_id'])) {
                $group->responsible_id = $data['responsible_id'];
            }
            $group->created_by_id = Auth::id();
            if(!empty($data['participants'])) {
                foreach($data['participants'] as $participant) {
                    if($participant['type'] == 'collaborator') {
                        // Create Participant and TraineeCollaborator records
                        $this->createTraineeCollaboratorForGroup($group, $participant['id']);
                    }elseif($participant['type'] == 'candidate') {
                        $group->candidates()->attach($participant['id']);
                    }elseif($participant['type'] == 'external') {
                        $group->externals()->attach($participant['id']);
                    }
                }
            }
            $group->current_size = count($data['participants']);
            $group->save();
            return $group->load(['training', 'createdBy', 'collaborators', 'candidates', 'externals']);
        });
    }

    /**
     * Create a TraineeCollaborator record when adding a collaborator to a training group
     * @param TrainingGroup $group
     * @param int $collaboratorId
     */
    private function createTraineeCollaboratorForGroup(TrainingGroup $group, int $collaboratorId)
    {
        $participant = \App\Models\Participant::create([
            'attendance_recorded' => false,
            'insured' => false,
            'comments' => null,
            'created_by_id' => Auth::id(),
            'type' => 'trainee',
        ]);

        $traineeCollaborator = new \App\Models\TraineeCollaborator([
            'remarks' => null,
        ]);
        $traineeCollaborator->participant()->associate($participant);
        $traineeCollaborator->collaborator()->associate(\App\Models\Collaborator::findOrFail($collaboratorId));
        $traineeCollaborator->save();

        // Attach the trainee collaborator to the training group
        $traineeCollaborator->trainingGroups()->attach($group->id);
        
        // Also attach to the training associated with this group
        if ($group->training_id) {
            $traineeCollaborator->trainings()->attach($group->training_id);
        }
    }

    /**
     * @param array $data
     */
    public function updateWithParticipants(int $id, array $data): TrainingGroup
    {
        return DB::transaction(function () use ($id, $data) {
            $group = $this->trainingGroupRepository->update($data, $id);

            if (array_key_exists('participants', $data)) {
                $participantIdsByType = $this->getParticipantIdsByType($data['participants']);

                // Handle collaborators - remove old ones and create new TraineeCollaborators
                $existingCollaboratorIds = $group->collaborators()->pluck('collaborator_id')->toArray();
                $newCollaboratorIds = $participantIdsByType['collaborator'];
                
                // Remove collaborators that are no longer in the list
                $toRemoveIds = array_diff($existingCollaboratorIds, $newCollaboratorIds);
                if (!empty($toRemoveIds)) {
                    \App\Models\TraineeCollaborator::whereIn('collaborator_id', $toRemoveIds)
                        ->whereHas('trainingGroups', function ($q) use ($group) {
                            $q->where('training_group_id', $group->id);
                        })
                        ->delete();
                }
                
                // Add new collaborators
                $toAddIds = array_diff($newCollaboratorIds, $existingCollaboratorIds);
                foreach ($toAddIds as $collaboratorId) {
                    $this->createTraineeCollaboratorForGroup($group, $collaboratorId);
                }

                // Handle candidates and externals
                $group->candidates()->sync($participantIdsByType['candidate']);
                $group->externals()->sync($participantIdsByType['external']);

                $group->current_size = count($data['participants']);
                $group->save();
            }

            return $group->load(['training', 'createdBy', 'collaborators', 'candidates', 'externals']);
        });
    }
    private function getParticipantIdsByType(array $participants): array
    {
        $idsByType = [
            'collaborator' => [],
            'candidate'    => [],
            'external'     => [],
        ];

        foreach ($participants as $participant) {
            if (isset($idsByType[$participant['type']])) {
                $idsByType[$participant['type']][] = $participant['id'];
            }
        }
        return $idsByType;
    }
}
