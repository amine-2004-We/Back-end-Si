<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Exception;

class TaskRepository
{
    /**
     * @var array
     */
    public array $defaultWith = [
        'project.financialInstallments', 'project', 'program.programType', 'phase', 'responsibleCollaborator', 'locationSite',
        'budgetLine', 'creator', 'groups', 'attachments',
        'pedagogicalSession', 'visit.observedCollaborator', 'meeting', 'atelier', 'evaluation.evaluatedBeneficiary',
        'meeting.parents',
    ];

    /**
     *
     * @param array
     * @param int
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15)
    {
        $query = Task::query()->with($this->defaultWith)->withTrashed();

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (isset($filters['program_type_id'])) {
            $query->where('program_type_id', $filters['program_type_id']);
        }

        if (!empty($filters['activation_status'])) {
            if ($filters['activation_status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['activation_status'] === 'deactivated') {
                $query->whereNotNull('deleted_at');
            }
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     *
     * @param int
     * @return Task|null
     */
    public function find(int $id)
    {
        return Task::with($this->defaultWith)->withTrashed()->find($id);
    }

    /**
     *
     * @param array
     * @return Task|null
     */
    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            Log::info('TaskRepository::create - Starting task creation', ['task_type' => $data['type'] ?? null]);

            $data['task_id'] = $this->generateTaskId($data);
            Log::info('TaskRepository::create - Task ID generated', ['task_id' => $data['task_id']]);

            if (!empty($data['project_id'])) {
                $project = Project::find($data['project_id']);
                if ($project) {
                    $data['program_id'] = $project->program_id;
                    Log::info('TaskRepository::create - Program ID set from project', ['project_id' => $data['project_id'], 'program_id' => $data['program_id']]);
                }
            }

            $detailsData = Arr::pull($data, 'details', []);
            $groupIds = Arr::pull($data, 'group_ids', []);
            $attachments = Arr::pull($data, 'attachments', []);
            $parentIds = Arr::pull($detailsData, 'parent_ids');

            Log::info('TaskRepository::create - Data prepared', [
                'has_details' => !empty($detailsData),
                'group_count' => count($groupIds),
                'attachment_count' => count($attachments),
            ]);

            $task = Task::create($data);
            Log::info('TaskRepository::create - Task created in database', ['task_id' => $task->id]);

            if (!empty($detailsData)) {
                $relationName = $this->getRelationName($task->type);
                Log::info('TaskRepository::create - Creating task details', ['relation_name' => $relationName, 'task_type' => $task->type]);

                if ($relationName) {
                    $detailsModel = $task->{$relationName}()->create($detailsData);
                    Log::info('TaskRepository::create - Task details created', ['relation_name' => $relationName, 'details_id' => $detailsModel->id]);

                    if ($task->type === 'Réunion' && !empty($parentIds)) {
                        $detailsModel->parents()->sync($parentIds);
                        Log::info('TaskRepository::create - Meeting parents synced', ['parent_count' => count($parentIds)]);
                    }
                }
            }

            if (!empty($groupIds)) {
                $task->groups()->sync($groupIds);
                Log::info('TaskRepository::create - Groups synced', ['group_count' => count($groupIds)]);
            }

            if (!empty($attachments)) {
                $this->handleAttachments($task, $attachments);
                Log::info('TaskRepository::create - Attachments handled', ['attachment_count' => count($attachments)]);
            }

            DB::commit();
            Log::info('TaskRepository::create - Task creation successful', ['task_id' => $task->id]);
            return $task->load($this->defaultWith);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('TaskRepository::create - Error creating task', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     *
     * @param Task
     * @param array
     * @return Task|null
     */
    public function update(Task $task, array $data)
    {
        DB::beginTransaction();
        try {
            Log::info('TaskRepository::update - Starting task update', ['task_id' => $task->id, 'task_type' => $task->type]);

            // This will throw an exception if previous phase validation fails
            $this->validatePreviousPhase($task, $data);
            Log::info('TaskRepository::update - Previous phase validation passed', ['task_id' => $task->id]);

            $detailsData = Arr::pull($data, 'details', []);
            $groupIds = Arr::pull($data, 'group_ids');
            $attachments = Arr::pull($data, 'attachments', []);
            $attachmentsToDelete = Arr::pull($data, 'attachments_to_delete', []);
            $parentIds = Arr::pull($detailsData, 'parent_ids', []);

            Log::info('TaskRepository::update - Data prepared for update', [
                'task_id' => $task->id,
                'has_details' => !empty($detailsData),
                'group_ids_provided' => !is_null($groupIds),
                'group_count' => is_array($groupIds) ? count($groupIds) : null,
                'attachment_count' => count($attachments),
                'attachments_to_delete_count' => count($attachmentsToDelete),
            ]);

            $task->update($data);
            Log::info('TaskRepository::update - Task updated in database', ['task_id' => $task->id]);

            if (!empty($detailsData)) {
                $relationName = $this->getRelationName($task->type);
                Log::info('TaskRepository::update - Updating task details', [
                    'task_id' => $task->id,
                    'relation_name' => $relationName,
                    'task_type' => $task->type,
                ]);

                if ($relationName) {
                    $detailsModel = $task->{$relationName}()->updateOrCreate(['task_id' => $task->id], $detailsData);
                    Log::info('TaskRepository::update - Task details updated', [
                        'task_id' => $task->id,
                        'relation_name' => $relationName,
                        'details_id' => $detailsModel->id,
                    ]);

                    if ($task->type === 'Réunion' && !is_null($parentIds)) {
                        $detailsModel->parents()->sync($parentIds);
                        Log::info('TaskRepository::update - Meeting parents synced', [
                            'task_id' => $task->id,
                            'parent_count' => count($parentIds),
                        ]);
                    }
                }
            }

            if (!is_null($groupIds)) {
                $task->groups()->sync($groupIds);
                Log::info('TaskRepository::update - Groups synced', [
                    'task_id' => $task->id,
                    'group_count' => count($groupIds),
                ]);
            }

            if (!empty($attachmentsToDelete)) {
                $this->deleteAttachments($attachmentsToDelete);
                Log::info('TaskRepository::update - Attachments deleted', [
                    'task_id' => $task->id,
                    'deleted_count' => count($attachmentsToDelete),
                ]);
            }

            if (!empty($attachments)) {
                $this->handleAttachments($task, $attachments);
                Log::info('TaskRepository::update - New attachments added', [
                    'task_id' => $task->id,
                    'attachment_count' => count($attachments),
                ]);
            }

            DB::commit();
            Log::info('TaskRepository::update - Task update successful', ['task_id' => $task->id]);
            return $task->fresh($this->defaultWith);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('TaskRepository::update - Error updating task', [
                'task_id' => $task->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            // RE-THROW the exception so the controller can handle it
            throw $e;
        }
    }

    /**
     *
     * @param Task
     * @return bool
     */
    public function delete(Task $task)
    {
        try {
            return $task->delete();
        } catch (Exception $e) {
            Log::error("Error deleting task ID {$task->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids)
    {
        $results = [];
        foreach ($ids as $id) {
            $task = Task::withTrashed()->find($id);
            if (!$task) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Tache non trouvée.'];
                continue;
            }
            try {
                if ($task->trashed()) {
                    $task->restore();
                    $message = 'Tache réactivée avec succès.';
                } else {
                    $task->delete();
                    $message = 'Tache désactivée avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for Tache ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur lors du changement de statut.'];
            }
        }
        return $results;
    }

    /**
     *
     * @param int $id
     * @return Task|null
     */
    public function restore(int $id)
    {
        $task = Task::withTrashed()->find($id);
        if ($task && $task->trashed()) {
            $task->restore();
            return $task;
        }
        return null;
    }

    /**
     * @param string $type
     * @return string|null
     */
    private function getRelationName(string $type)
    {
        return match ($type) {
            'Séance pédagogique' => 'pedagogicalSession',
            'Visite' => 'visit',
            'Réunion' => 'meeting',
            'Atelier' => 'atelier',
            'Évaluation' => 'evaluation',
            default => null,
        };
    }

    /**
     * @param Task $task
     * @param array $files
     * @return void
     */
    private function handleAttachments(Task $task, array $files)
    {
        foreach ($files as $file) {
            $path = $file->store('task_attachments', 'public');
            $task->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * @param array $attachmentIds
     * @return void
     */
    private function deleteAttachments(array $attachmentIds)
    {
        $attachments = TaskAttachment::whereIn('id', $attachmentIds)->get();
        foreach ($attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    private function generateTaskId(array $data)
    {
        $type = $data['type'] ?? null;
        if (!$type) {
            throw new Exception("Task type is required to generate an ID.");
        }

        $fullPrefix = '';
        $sequencePadding = 3;

        switch ($type) {
            case 'Séance pédagogique':
                $projectId = $data['project_id'] ?? null;
                $project = $projectId ? Project::find($projectId) : null;
                $projectCode = $project->project_abbreviation ?? 'PRJ' . ($project->id ?? 'UNK');
                $fullPrefix = 'ACT-' . strtoupper($projectCode) . '-';
                break;

            case 'Visite':
                $groupId = $data['group_ids'][0] ?? '0';
                $fullPrefix = 'VIS-GRP' . str_pad($groupId, 2, '0', STR_PAD_LEFT) . '-';
                break;

            case 'Réunion':
                $groupId = $data['group_ids'][0] ?? '0';
                $fullPrefix = 'RE-PAR-CLS' . str_pad($groupId, 2, '0', STR_PAD_LEFT) . '-';
                break;

            case 'Évaluation':
                $beneficiaryId = $data['details']['evaluated_beneficiary_id'] ?? '0';
                $siteId = $data['location_site_id'] ?? '0';
                $projectId = $data['project_id'] ?? '0';
                $fullPrefix = 'EVAL-BEN' . str_pad($beneficiaryId, 3, '0', STR_PAD_LEFT) . '_GS_S' . $siteId . '_P' . $projectId . '-';
                $sequencePadding = 1; // A simple sequence number is enough for uniqueness
                break;

            case 'Autre':
                $projectId = $data['project_id'] ?? null;
                $project = $projectId ? Project::find($projectId) : null;
                $projectCode = $project ? ('PRJ' . $project->id) : 'PRJUNK';
                $fullPrefix = 'AUT-' . strtoupper($projectCode) . '-';
                break;

            default:
                $projectId = $data['project_id'] ?? null;
                $project = $projectId ? Project::find($projectId) : null;
                $projectCode = $project ? ('PRJ' . $project->id) : 'PRJUNK';
                $fullPrefix = 'TSK-' . strtoupper($projectCode) . '-';
                break;
        }

        $latestTask = Task::withTrashed()
            ->where('task_id', 'like', $fullPrefix . '%')
            ->latest('id')
            ->first();

        $sequence = 1;
        if ($latestTask) {
            $lastId = $latestTask->task_id;
            $lastSequencePart = substr($lastId, strrpos($lastId, '-') + 1);
            if (is_numeric($lastSequencePart)) {
                $sequence = (int)$lastSequencePart + 1;
            }
        }

        return $fullPrefix . str_pad($sequence, $sequencePadding, '0', STR_PAD_LEFT);
    }
    /**
     * Get all tasks assigned to a specific collaborator.
     *
     * @param int $collaboratorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaginatedByCollaborator(int $collaboratorId, array $filters = [], int $perPage = 15)
    {
        $query = Task::query()
            ->with($this->defaultWith)
            ->withTrashed()
            ->where('responsible_collaborator_id', $collaboratorId)
            ->where('isProject', true);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['activation_status'])) {
            if ($filters['activation_status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['activation_status'] === 'deactivated') {
                $query->whereNotNull('deleted_at');
            }
        }
        return $query->latest('id')->paginate($perPage);
    }
    /**
     * Prevent updating task if previous phase tasks are not completed.
     *
     * @throws Exception
     */
    private function validatePreviousPhase(Task $task, array $data)
    {
        $previousPhaseId = $data['previous_phases_id'] ?? $task->previous_phases_id;

        Log::info('TaskRepository::validatePreviousPhase - Validation started', [
            'task_id' => $task->id,
            'previous_phase_id' => $previousPhaseId,
            'project_id' => $task->project_id,
        ]);

        if (is_null($previousPhaseId)) {
            Log::info('TaskRepository::validatePreviousPhase - No previous phase ID, skipping validation', [
                'task_id' => $task->id,
            ]);
            return;
        }

        $previousTasks = Task::where('project_id', $task->project_id)
            ->where('phase_id', $previousPhaseId)
            ->get();

        Log::info('TaskRepository::validatePreviousPhase - Previous tasks found', [
            'task_id' => $task->id,
            'previous_phase_id' => $previousPhaseId,
            'previous_tasks_count' => $previousTasks->count(),
            'previous_tasks' => $previousTasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
            ])->toArray(),
        ]);

        if ($previousTasks->isEmpty()) {
            Log::info('TaskRepository::validatePreviousPhase - No previous tasks found, skipping validation', [
                'task_id' => $task->id,
            ]);
            return;
        }

        $incompleteTasks = $previousTasks->filter(function ($t) {
            return $t->status !== 'Réalisée';
        });

        Log::info('TaskRepository::validatePreviousPhase - Incomplete tasks filtered', [
            'task_id' => $task->id,
            'incomplete_tasks_count' => $incompleteTasks->count(),
            'incomplete_tasks' => $incompleteTasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
            ])->toArray(),
        ]);

        if ($incompleteTasks->isNotEmpty()) {
            Log::error('TaskRepository::validatePreviousPhase - Validation failed due to incomplete previous phase tasks', [
                'task_id' => $task->id,
                'incomplete_tasks_count' => $incompleteTasks->count(),
                'incomplete_tasks' => $incompleteTasks->map(fn($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'status' => $t->status,
                ])->toArray(),
            ]);
            throw new Exception(
                "Impossible de mettre à jour la tâche ID {$task->id} car certaines tâches de la phase précédente ne sont pas terminées."
            );
        }
    }
    public function getPaginatedPreSchool(array $filters = [], int $perPage = 15)
    {
        $query = Task::query()
            ->with($this->defaultWith)
            ->withTrashed()
            ->whereHas('programType', function ($q) {
                $q->where('name', 'Préscolaire');
            });

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (isset($filters['program_type_id'])) {
            $query->where('program_type_id', $filters['program_type_id']);
        }
        if (isset($filters['level_id'])) {
            $query->where('level_id', $filters['level_id']);
        }

        if (!empty($filters['activation_status'])) {
            if ($filters['activation_status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['activation_status'] === 'deactivated') {
                $query->whereNotNull('deleted_at');
            }
        }

        return $query->latest('id')->paginate($perPage);
    }
    public function getPaginatedSmallSection(array $filters = [], int $perPage = 15)
    {
        $query = Task::query()
            ->with($this->defaultWith)
            ->withTrashed()
            ->whereHas('programType', function ($q) {
                $q->where('name', 'Petite section');
            });

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (isset($filters['program_type_id'])) {
            $query->where('program_type_id', $filters['program_type_id']);
        }

        if (!empty($filters['activation_status'])) {
            if ($filters['activation_status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['activation_status'] === 'deactivated') {
                $query->whereNotNull('deleted_at');
            }
        }

        return $query->latest('id')->paginate($perPage);
    }

    public function getPaginatedKader(array $filters = [], int $perPage = 15)
    {
        $query = Task::query()
            ->with($this->defaultWith)
            ->withTrashed()
            ->whereHas('programType', function ($q) {
                $q->where('name', 'KADER');
            });

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (isset($filters['program_type_id'])) {
            $query->where('program_type_id', $filters['program_type_id']);
        }

        if (!empty($filters['activation_status'])) {
            if ($filters['activation_status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['activation_status'] === 'deactivated') {
                $query->whereNotNull('deleted_at');
            }
        }

        return $query->latest('id')->paginate($perPage);
    }

    /***
     * @param array $filters
     * @param int $perPage
     * @return mixed
     */
    public function getPaginatedSupportPlan(array $filters = [], int $perPage = 15)
    {
        $query = Task::query()
            ->with($this->defaultWith)
            ->withTrashed()
            ->whereHas('programType', function ($q) {
                $q->where('name', 'Remédiation scolaire primaire');
            });

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (isset($filters['program_type_id'])) {
            $query->where('program_type_id', $filters['program_type_id']);
        }
        if (isset($filters['level_id'])) {
            $query->where('level_id', $filters['level_id']);
        }

        if (!empty($filters['activation_status'])) {
            if ($filters['activation_status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['activation_status'] === 'deactivated') {
                $query->whereNotNull('deleted_at');
            }
        }

        return $query->latest('id')->paginate($perPage);
    }
}
