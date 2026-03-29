<?php

namespace App\Services;

use App\Http\Resources\TaskResource;
use App\Models\Beneficiary;
use App\Models\BudgetLine;
use App\Models\Collaborator;
use App\Models\EducationalProgram;
use App\Models\Group;
use App\Models\ParentModel;
use App\Models\Phase;
use App\Models\PreschoolPedagogiqueProgram;
use App\Models\Program;
use App\Models\ProgramType;
use App\Models\Project;
use App\Models\ProjectClass;
use App\Models\Site;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *class TaskService
 */
class TaskService
{
    public function __construct(public TaskRepository $taskRepository) {}

    public function getPaginatedTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginated($filters, $perPage);
    }

    public function findTask(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }

    public function createTask(array $data): ?Task
    {
        if (! isset($data['created_by']) && auth()->check()) {
            $data['created_by'] = auth()->id();
        }

        return $this->taskRepository->create($data);
    }

    public function updateTask(Task $task, array $data): ?Task
    {
        try {
            $updatedTask = $this->taskRepository->update($task, $data);
            return $updatedTask;
        } catch (Exception $e) {
            // Re-throw the exception so the controller can catch it
            throw $e;
        }
    }

    /**
     * Get tasks grouped by phase.
     */
    public function getTasksGroupedByPhase(int $projectId): array
    {
        $tasks = Task::with('phase')
            ->where('project_id', $projectId)
            ->where('isProject', true)
            ->get();

        $grouped = $tasks->groupBy(function ($task) {
            return $task->phase->name ?? 'Sans phase';
        })->map(function ($tasks) {
            return TaskResource::collection($tasks);
        });

        $order = ['Conception', 'Préparation', 'Concrétisation','Opérationnalisation des process', 'Déploiement opérationnel','Sans phase'];

        $sorted = collect($order)
            ->filter(fn ($phase) => $grouped->has($phase))
            ->mapWithKeys(fn ($phase) => [$phase => $grouped[$phase]]);

        $remaining = $grouped->except($order);

        return $sorted->merge($remaining)->toArray();
    }

    public function deleteTask(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }

    public function toggleTaskActivation(array $ids): array
    {
        return $this->taskRepository->toggleActivation($ids);
    }

    public function getFormOptions(): array
    {
        return [
            'projects' => Project::select('id', 'project_name as name')->get(),
            'programs' => Program::select('id', 'title as name')->get(),
            'program_types' => ProgramType::select('id', 'name as name')->get(),
            'phases' => Phase::select('id', 'name','name_arabe')->where('tag',"project")->get(),
            'phases_not_project' => Phase::select('id', 'name','name_arabe')->where('tag', "pre-scolaire")->get(),
            'phases_not_project_ss' => Phase::select('id', 'name','name_arabe')->where('tag', "petite-section")->get(),
            'phases_not_project_support_plan' => Phase::select('id', 'name','name_arabe')->where('tag', "plan d'appui")->get(),
            'phases_project'=>EducationalProgram::select('id', 'pedagogical_project','pedagogical_project_arabe','subcomponent')->get(),
            'phases_project_pre'=>PreschoolPedagogiqueProgram::select('id', 'pedagogical_project','pedagogical_project_arabe','subcomponent')->get(),
            'projects_preschool' => Project::select('id', 'project_name as name','program_type_id')
                ->whereIn('program_type_id', function ($query) {
                    $query->select('id')
                        ->from('program_types')
                        ->where('name', 'Préscolaire');
                })
                ->get(),
            'projects_small_section' => Project::select('id', 'project_name as name')
                ->whereIn('program_type_id', function ($query) {
                    $query->select('id')
                        ->from('program_types')
                        ->where('name', 'Petite section');
                })
                ->get(),
            'projects_kader' => Project::select('id', 'project_name as name','program_type_id')
                ->whereIn('program_type_id', function ($query) {
                    $query->select('id')
                        ->from('program_types')
                        ->where('name', 'KADER');
                })
                ->get(),
            'projects_support_plan' => Project::select('id', 'project_name as name','program_type_id')
                ->whereIn('program_type_id', function ($query) {
                    $query->select('id')
                        ->from('program_types')
                        ->where('name', 'Remédiation scolaire primaire');
                })
                ->get(),
            'collaborators' => Collaborator::select('id', 'first_name', 'last_name')->get()->map(fn ($c) => ['id' => $c->id, 'name' => "{$c->first_name} {$c->last_name}"]),
            'classes' => ProjectClass::select('id', 'class_name')->get(),
            'groups' => Group::select(
                'id',
                'name',
                'current_headcount',
                'class_id'
            )->get(),
            'sites' => Site::select('id', 'name')->get(),
            'beneficiaries' => Beneficiary::select('id', 'first_name', 'last_name')->get()->map(fn ($b) => ['id' => $b->id, 'name' => "{$b->first_name} {$b->last_name}"]),
            'parents' => ParentModel::select('id', 'first_name', 'last_name')->get()->map(fn ($b) => ['id' => $b->id, 'name' => "{$b->first_name} {$b->last_name}"]),
            'budget_lines' => BudgetLine::select()->get(),
            'task_types' => ['Séance pédagogique', 'Réunion', 'Atelier', 'Visite', 'Évaluation', 'Autre', 'Program'],
            'statuses' => ['Prévue', 'En cours', 'Réalisée', 'Annulée', 'Reportée'],
        ];
    }
    /**
     * Get tasks grouped by phase and Collab.
     */
    public function getTasksGroupedByPedagogicalProject(int $projectId, int $collab): array
    {
        $tasks = Task::select(
            'tasks.*',
            'educational_programs.pedagogical_project',
            'phases.name as phase_name',
            'educational_programs.level as program_level'
        )
            ->leftJoin('phases', 'tasks.phase_id', '=', 'phases.id')
            ->leftJoin('educational_programs', 'tasks.phase_id', '=', 'educational_programs.subcomponent')
            ->where('tasks.project_id', $projectId)
            ->where('tasks.responsible_collaborator_id', $collab)
            ->orderBy('tasks.created_at')
            ->get();
        $groupedByProject = $tasks->groupBy(fn($task) =>
            $task->pedagogical_project ?? 'Sans projet'
        );

        $finalGroup = $groupedByProject->map(function ($taskGroup, $projectName) {
            $byPhase = $taskGroup->groupBy(fn($task) =>
                ($task->phase_name ?? 'Sans phase') . " (Niveau {$task->program_level})"
            )->map(fn($phaseGroup) =>
            TaskResource::collection(
                $phaseGroup->sortBy('created_at')
            )
            );

            return [
                'pedagogical_project' => $projectName,
                'phases' => $byPhase,
            ];
        });

        return $finalGroup->values()->toArray();
    }
    /**
     * Get all tasks assigned to a specific collaborator.
     *
     * @param int $collaboratorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaginatedTasksByCollaborator(int $collaboratorId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedByCollaborator($collaboratorId, $filters, $perPage);
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPreSchool(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedPreSchool($filters, $perPage);
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedSmallSection(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedSmallSection($filters, $perPage);
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedKader(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedKader($filters, $perPage);
    }

    /***
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedSupportPlan(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedSupportPlan($filters, $perPage);
    }
}
