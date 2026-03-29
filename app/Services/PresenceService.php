<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Models\PresenceSheet;
use App\Models\Task;
use App\Repositories\PresenceSheetRepository; 
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PresenceService
{
    protected PresenceSheetRepository $presenceSheetRepository;

    public function __construct(PresenceSheetRepository $presenceSheetRepository)
    {
        $this->presenceSheetRepository = $presenceSheetRepository;
    }

    /**
     * Get a paginated list of presence sheets.
     */
    public function getAll(array $filters): LengthAwarePaginator
    {
        return $this->presenceSheetRepository->getPaginated($filters);
    }

    /**
     * Toggle the activation status of presence sheets.
     *
     * @param array<int> $ids
     * @return array<array{id: int, success: bool, message: string}>
     */
    public function togglePresenceSheetActivation(array $ids): array
    {
        return $this->presenceSheetRepository->toggleActivation($ids);
    }

    /**
     * Create or update a single presence sheet for an event.
     */
    public function createOrUpdateSheet(array $data): PresenceSheet
    {
        $participantsData = collect($data['participants'])->map(function ($participant) {
            try {
                if (!isset($participant['personable_type']) || !isset($participant['personable_id'])) {
                    return null;
                }
                $person = app($participant['personable_type'])->find($participant['personable_id']);
                if (!$person) {
                    return null;
                }
                return [
                    'personable_id' => $participant['personable_id'],
                    'personable_type' => $participant['personable_type'],
                    'name' => $person->first_name . ' ' . $person->last_name,
                    'type' => class_basename($person),
                    'status' => $participant['status'],
                    'arrival_time' => $participant['arrival_time'] ?? null,
                    'justification' => $participant['justification'] ?? null,
                    'observations' => $participant['observations'] ?? null,
                ];
            } catch (\Throwable $e) {
                Log::error('Error processing participant data.', ['error' => $e->getMessage(), 'participant' => $participant]);
                return null;
            }
        })->filter()->values()->all();

        if (empty($participantsData)) {
            throw new \Exception('No valid participant data provided.');
        }

        return PresenceSheet::updateOrCreate(
            ['task_id' => $data['task_id'], 'event_date' => $data['event_date']],
            ['participants' => $participantsData, 'declared_by' => $data['declared_by'], 'created_by' => auth()->id()]
        );
    }

    /**
     * Summary of getParticipantsForTask
     * @param \App\Models\Task $task
     * @return Collection<int, array{id: mixed, name: string, personable_id: mixed, personable_type: string, type: string|null>}
     */
    public function getParticipantsForTask(Task $task): Collection
    {
        $participants = collect();

        if ($task->type === 'Réunion') {
            $task->load('meeting.parents');

            if ($task->meeting && $task->meeting->parents) {
                foreach ($task->meeting->parents as $parent) {
                    $participants->push($parent);
                }
            }
        } elseif ($task->type === 'Autre') {
            $task->load('groups');
            
            Log::info('PresenceService::getParticipantsForTask - Autre type', [
                'task_id' => $task->id,
                'groups_count' => $task->groups->count(),
                'groups' => $task->groups->pluck('id')->toArray(),
            ]);
            
            if ($task->responsibleCollaborator) {
                Log::info('Adding responsible collaborator', ['collaborator_id' => $task->responsibleCollaborator->id]);
                $participants->push($task->responsibleCollaborator);
            }
            
            if ($task->groups && $task->groups->count() > 0) {
                foreach ($task->groups as $group) {
                    $beneficiaries = Beneficiary::where('group_id', $group->id)->get();
                    
                    Log::info('Processing group', [
                        'group_id' => $group->id,
                        'beneficiaries_count' => $beneficiaries->count(),
                    ]);
                    
                    if ($beneficiaries && $beneficiaries->count() > 0) {
                        foreach ($beneficiaries as $beneficiary) {
                            Log::info('Adding beneficiary', ['beneficiary_id' => $beneficiary->id, 'name' => $beneficiary->first_name . ' ' . $beneficiary->last_name]);
                            $participants->push($beneficiary);
                            
                            $beneficiary->load('parents');
                            if ($beneficiary->parents && $beneficiary->parents->count() > 0) {
                                foreach ($beneficiary->parents as $parent) {
                                    Log::info('Adding parent', ['parent_id' => $parent->id, 'name' => $parent->first_name . ' ' . $parent->last_name]);
                                    $participants->push($parent);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ($task->responsibleCollaborator) {
                $participants->push($task->responsibleCollaborator);
            }

            if ($task->type === 'Évaluation' && $task->evaluation) {
                $task->load('evaluation.evaluatedBeneficiary.parents');
                if ($beneficiary = $task->evaluation->evaluatedBeneficiary) {
                    $participants->push($beneficiary);
                    foreach ($beneficiary->parents as $parent) {
                        $participants->push($parent);
                    }
                }
            } else {
                $task->load('groups.beneficiaries.parents');
                foreach ($task->groups as $group) {
                    foreach ($group->beneficiaries as $beneficiary) {
                        $participants->push($beneficiary);
                        foreach ($beneficiary->parents as $parent) {
                            $participants->push($parent);
                        }
                    }
                }
            }
        }
        
        return $participants->unique('id')->map(function ($person) {
            if (!$person) return null;
            
            $className = class_basename($person);
            $type = match($className) {
                'ParentModel' => 'parent',
                'Collaborator' => 'collaborateur',
                'Beneficiary' => 'beneficiaire',
                default => strtolower($className),
            };
            
            return [
                'id' => $person->id,
                'name' => ($person->first_name ?? '') . ' ' . ($person->last_name ?? ''),
                'type' => $type,
                'personable_id' => $person->id,
                'personable_type' => get_class($person),
            ];
        })->filter()->values();
    }
    
    /**
     * Get the form options needed for the frontend modal.
     */
    public function getFormOptions(): array
    {
        return [
            'statuses' => ['Présent', 'Absent', 'En retard', 'Excusé'],
            'tasks' => Task::all(['id', 'title']),
        ];
    }
}
