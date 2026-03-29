<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectBankAccountRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Models\PlanType;
use App\Models\Position;
use App\Models\ProjectStatus;

class ProjectService
{
    protected ProjectRepository  $projectRepository;
    protected UserRepository $userRepository;
    protected ProjectBankAccountRepository $bankAccountRepository;
    protected TaskService $taskService;

    public function __construct(
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        ProjectBankAccountRepository $bankAccountRepository,
        TaskService $taskService
    )
    {
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->taskService = $taskService;
    }

    public function getProjects(Request $request): LengthAwarePaginator
    {
        $filters = $request->only([
            'project_name',
            'project_code',
            'is_active',
            'responsible_name',
            'partner_name',
            'per_page',
            'project_type_id',
            'project_status_id',
            'project_nature',
            'intervention_axis_id',
            'region_id',
            'province_id',
            'program_id',
            'program_type_id',
        ]);

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $this->projectRepository
            ->allWithFilters($filters)
            ->with([
                'responsible',
                'createdBy',
                'partners',
                'projectBankAccount',
                'projectType',
                'projectStatus',
                'financialInstallments',
                'region',
                'province',
                'program',
                'programType'
            ])
            ->paginate($perPage);
    }

    public function getAllWithoutPagination() : Collection
    {
        return $this->projectRepository->allWithoutPagination();
    }

    public function getProject(int $id): Project{
        return $this->projectRepository->find($id);
    }

    /**
     * Check if project has all required fields for task creation
     */
    private function canCreateTasks(Project $project): bool
    {
        return !empty($project->start_date)
            && !empty($project->program_id)
            && !empty($project->project_status_id);
    }

    /**
     * Check if status is Brouillon (draft)
     */
    private function isBrouillonStatus(?int $statusId): bool
    {
        if (empty($statusId)) {
            return true;
        }

        $brouillonStatus = ProjectStatus::where('name', 'Brouillon')->first();
        return $brouillonStatus && $statusId === $brouillonStatus->id;
    }

    /**
     * Check if project should have tasks generated
     * Only generate if:
     * 1. Status is NOT Brouillon
     * 2. Project has all required fields
     * 3. Project doesn't already have tasks
     */
    private function shouldGenerateTasks(Project $project): bool
    {
        if ($this->isBrouillonStatus($project->project_status_id)) {
            \Log::info('Project is in Brouillon status, skipping task generation', [
                'project_id' => $project->id,
                'status_id' => $project->project_status_id
            ]);
            return false;
        }

        $existingTasksCount = $project->tasks()->count();
        if ($existingTasksCount > 0) {
            \Log::info('Project already has tasks, skipping generation', [
                'project_id' => $project->id,
                'existing_tasks' => $existingTasksCount
            ]);
            return false;
        }

        if (!$this->canCreateTasks($project)) {
            \Log::warning('Project missing required fields for task generation', [
                'project_id' => $project->id,
                'has_start_date' => !empty($project->start_date),
                'has_program_id' => !empty($project->program_id),
                'has_status_id' => !empty($project->project_status_id)
            ]);
            return false;
        }

        return true;
    }

    /**
     * Méthode interne simple (utilisée par la méthode complexe ci-dessous)
     */
    public function createProject(array $data, ?array $partners = [], ?array $projectNotes = []): Project
    {
        try {
            \Log::info('notes received in service', ['notes' => $projectNotes ?? 'VIDE']);

            if (!isset($data['project_code']) || empty($data['project_code'])) {
                $abbreviation = $data['project_abbreviation'] ?? 'PROJ';
                $timestamp = now()->format('YmdHis');
                $data['project_code'] = strtoupper($abbreviation) . '-' . $timestamp;
            }

            if (!isset($data['project_status_id']) || empty($data['project_status_id'])) {
                $brouillonStatus =  ProjectStatus::where('name', 'Brouillon')->first();
                if ($brouillonStatus) {
                    $data['project_status_id'] = $brouillonStatus->id;
                }
            }

            $createdByUser = $this->userRepository->findOrFail($data['created_by_id']);
            $responsibleUser = null;
            if (!empty($data['responsible_id'])) {
                $responsibleUser = $this->userRepository->find($data['responsible_id']);
                if (!$responsibleUser) {
                    throw new Exception("Utilisateur responsable introuvable (id={$data['responsible_id']})");
                }
            }

            $bankAccount = null;
            if (!empty($data['project_bank_account_id'])) {
                $bankAccount = $this->bankAccountRepository->find($data['project_bank_account_id']);
            }

            return $this->projectRepository->createWithAssociations(
                $data,
                $createdByUser,
                $responsibleUser,
                $bankAccount,
                $partners,
                $projectNotes
            );
        } catch (Exception $e) {
            throw new Exception("Erreur base de données : " . $e->getMessage());
        }
    }

    /**
     * Crée le Projet + Note + Partenaires + TÂCHES AUTOMATIQUES (selon le statut)
     */
    public function createProjectWithAutomaticTasks(array $data, array $partners = [], $projectNotes = null): array
    {
        return DB::transaction(function () use ($data, $partners, $projectNotes) {

            if (!isset($data['total_budget']) || $data['total_budget'] === null) {
                $data['total_budget'] = 0;
            }
            if (!isset($data['zakoura_contribution'])) {
                $data['zakoura_contribution'] = 0;
            }
            if (isset($data['project_nature']) && $data['project_nature'] === 'Public') {
                $data['project_nature'] = 'Publique';
            }
            if (!isset($data['created_by_id'])) {
                $data['created_by_id'] = auth()->id();
            }

           if (!isset($data['project_code']) || empty($data['project_code'])) {
                $abbreviation = $data['project_abbreviation'] ?? 'PROJ';
                $timestamp = now()->format('YmdHis');
                $data['project_code'] = strtoupper($abbreviation) . '-' . $timestamp;
            }

           if (!isset($data['project_status_id']) || empty($data['project_status_id'])) {
                $brouillonStatus = ProjectStatus::where('name', 'Brouillon')->first();
                if ($brouillonStatus) {
                    $data['project_status_id'] = $brouillonStatus->id;
                }
            }

            $project = $this->createProject(
                $data,
                $partners,
                $projectNotes
            );

            $taskResults = [];

            if ($this->shouldGenerateTasks($project)) {
                \Log::info('Generating tasks during project creation', [
                    'project_id' => $project->id,
                    'status_id' => $project->project_status_id
                ]);
                $taskResults = $this->generateAutomaticTasks($project);
            } else {
                \Log::info('Project created without tasks', [
                    'project_id' => $project->id,
                    'status_id' => $project->project_status_id,
                    'is_brouillon' => $this->isBrouillonStatus($project->project_status_id)
                ]);
            }

            return [
                'project' => $project,
                'tasks' => $taskResults
            ];
        });
    }

    /**
     * Generate automatic tasks for a project
     */
    private function generateAutomaticTasks(Project $project): array
    {
        $taskResults = [];
        $planTypesByOrder = PlanType::orderBy('order')->get()->groupBy('order');
        $positions = Position::with(['collaborators' => fn($q) => $q->orderBy('id')])->get()->keyBy('title');

        $previousEndDate = $project->start_date->copy();

        foreach ($planTypesByOrder as $order => $planTypesGroup) {

            $maxDuration = 0;

            foreach ($planTypesGroup as $planType) {
                $startDate = match ($planType->order) {
                    99 => $this->addWeekdays($project->start_date->copy(), 14),
                    100 => $this->addWeekdays($project->start_date->copy(), 35),
                    101 => $this->addWeekdays($project->start_date->copy(), 42),
                    102 => $this->addWeekdays($project->start_date->copy(), 98),
                    default => $previousEndDate->copy(),
                };
                $endDate = $this->addWeekdays($startDate->copy(), $planType->duration);

                $maxDuration = max($maxDuration, $planType->duration);

                $collaboratorId = null;
                $responsibleTitles = $planType->responsible_title ?: [null];

                foreach ($responsibleTitles as $positionTitle) {
                    if ($positionTitle && isset($positions[$positionTitle])) {
                        $collaborator = $positions[$positionTitle]->collaborators->first();
                        if ($collaborator) {
                            $collaboratorId = $collaborator->id;
                            break;
                        }
                    }
                }

                $taskData = [
                    'title' => $planType->task_name,
                    'project_id' => $project->id,
                    'program_id' => $project->program_id,
                    'phase_id' => $planType->phase_id,
                    'previous_phases_id' => $planType->previous_phases_id,
                    'isProject' => true,
                    'type' => 'Autre',
                    'expected_start_date' => $startDate,
                    'expected_end_date' => $endDate,
                    'status' => 'Prévue',
                    'responsible_collaborator_id' => $collaboratorId,
                ];

                $task = $this->taskService->createTask($taskData);
                $taskResults[] = [
                    'title' => $taskData['title'],
                    'status' => $task ? 'success' : 'failed',
                ];
            }

            if (!in_array($order, [99, 100, 101, 102])) {
                $previousEndDate = $this->addWeekdays($previousEndDate, $maxDuration);
            }
        }

        \Log::info('Tasks generated successfully', [
            'project_id' => $project->id,
            'total_tasks' => count($taskResults)
        ]);

        return $taskResults;
    }

    /**
     * Add N weekdays (skip Sat/Sun).
     */
    private function addWeekdays(Carbon $date, ?int $days = 0): Carbon
    {
        $days = $days ?? 0;

        for ($i = 0; $i < $days; $i++) {
            $date->addDay();

            while ($date->isWeekend()) {
                $date->addDay();
            }
        }

        return $date;
    }

    /**
     * Update project and generate tasks if needed
     */
    public function updateProject(int $id, array $data, ?array $partners = [], ?array $projectNotes = null): array
    {
        return DB::transaction(function () use ($id, $data, $partners, $projectNotes) {
            try {
                $responsibleUser = null;
                if (!empty($data['responsible_id'])) {
                    $responsibleUser = $this->userRepository->find($data['responsible_id']);
                }

                $bankAccount = null;
                if (array_key_exists('project_bank_account_id', $data)) {
                    if (!empty($data['project_bank_account_id'])) {
                        $bankAccount = $this->bankAccountRepository->find($data['project_bank_account_id']);
                    }
                }

                $project = $this->projectRepository->update(
                    $id,
                    $data,
                    $responsibleUser,
                    $bankAccount,
                    $partners,
                    $projectNotes
                );

                $project->refresh();

                $taskResults = [];

                if ($this->shouldGenerateTasks($project)) {
                    \Log::info('Generating tasks after project update', [
                        'project_id' => $project->id,
                        'status_id' => $project->project_status_id
                    ]);

                    $taskResults = $this->generateAutomaticTasks($project);
                }

                return [
                    'project' => $project,
                    'tasks' => $taskResults
                ];

            } catch (Exception $e) {
                throw new Exception("Échec de la mise à jour du projet : " . $e->getMessage());
            }
        });
    }

    public function deleteProjects(array $projectIds): int
    {
        return $this->projectRepository->bulkDelete($projectIds);
    }

    public function restoreProject(int $id): Project
    {
        try{
            return $this->projectRepository->restoreProject($id);
        }catch(Exception $e){
            abort(409, $e->getMessage());
        }
    }

    public function getWithCollaborator(int $id)
    {
        return $this->projectRepository->findWithCollaborators($id);
    }

    public function assignCollaborators(Project $project, array $collaboratorIds): void
    {
        $this->projectRepository->assignCollaborators($project, $collaboratorIds);
    }

    public function removeCollaborator(int $projectId, int $collaboratorId): void
    {
        $this->projectRepository->detachCollaborator($projectId, $collaboratorId);
    }

    public function getProjectWithBudgetCategories(int $projectId): Project
    {
        return $this->projectRepository->findWithBudgetCategories($projectId);
    }

    public function getBudgetLinesByCategory(int $projectId, int $categoryId): array
    {
        return $this->projectRepository->getBudgetLinesByCategory($projectId, $categoryId);
    }

    public function getProjectBudgetLines(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->projectRepository->getProjectBudgetLines($projectId);
    }

    /**
     * Manually regenerate tasks for a project
     * Useful for fixing projects that should have tasks but don't
     */
    public function regenerateTasks(int $projectId, bool $deleteExisting = false): array
    {
        return DB::transaction(function () use ($projectId, $deleteExisting) {
            $project = $this->projectRepository->find($projectId);

            if ($this->isBrouillonStatus($project->project_status_id)) {
                throw new Exception("Cannot generate tasks for projects with Brouillon status. Please update the project status first.");
            }

            if (!$this->canCreateTasks($project)) {
                throw new Exception("Cannot generate tasks: project missing required fields (start_date, program_id, or project_status_id)");
            }

            if ($deleteExisting) {
                $deletedCount = $project->tasks()->delete();
                \Log::info('Deleted existing tasks before regeneration', [
                    'project_id' => $projectId,
                    'deleted_count' => $deletedCount
                ]);
            }

            $taskResults = $this->generateAutomaticTasks($project);

            return [
                'project' => $project,
                'tasks' => $taskResults
            ];
        });
    }

    public function getProjectBudgetCategoriesWithAggregatesFormatted(int $projectId): array
    {
        $project = $this->projectRepository->findWithBudgetCategoriesAndAggregates($projectId);

        $formattedCategories = $project->budgetCategories->map(function ($category) use ($projectId) {
            $lines = $category->budgetLines;

            $catTotal = 0;
            $catConsumed = 0;
            $catRemaining = 0;
            $catEngaged = 0;

            $formattedLines = $lines->map(function ($line) use ($projectId, &$catTotal, &$catConsumed, &$catRemaining, &$catEngaged) {

                $projectForLine = $line->projects
                    ->firstWhere('id', $projectId);

                $pivot = $projectForLine?->pivot;

                $lineTotal = (float) ($pivot->total_amount ?? 0);
                $lineConsumed = (float) ($pivot->consumed_amount ?? 0);
                $lineRemaining = (float) ($pivot->remaining_amount ?? 0);
                $lineEngaged = (float) ($pivot->engaged_amount ?? 0);

                $catTotal += $lineTotal;
                $catConsumed += $lineConsumed;
                $catRemaining += $lineRemaining;
                $catEngaged += $lineEngaged;

                return [
                    'id' => $pivot->id,
                    'code' => $line->code,
                    'label' => $line->label,
                    'project_id' => $projectId,
                    'unit_amount' => (float) ($pivot->unit_amount ?? 0),
                    'quantity' => (int) ($pivot->quantity ?? 0),
                    'total_amount' => $lineTotal,
                    'consumed_amount' => $lineConsumed,
                    'engaged_amount' => $lineEngaged,
                    'reliquate_amount' => (float) ($pivot->reliquate_amount ?? 0),
                    'remaining_amount' => $lineRemaining,
                    'created_at' => $line->created_at,
                    'updated_at' => $line->updated_at,
                ];
            });

            return [
                'id' => $category->id,
                'code' => $category->code,
                'label' => $category->label,
                'type' => $category->type,
                'budgetary_area' => $category->budgetary_area,
                'is_active' => (bool) $category->is_active,
                'total_amount' => $catTotal,
                'consumed_amount' => $catConsumed,
                'remaining_amount' => $catRemaining,
                'engaged_amount' => $catEngaged,
                'budget_lines_count' => $lines->count(),
                'budget_lines' => $formattedLines,
                'deleted_at' => $category->deleted_at,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });

        return [
            'project' => $project,
            'budget_categories' => $formattedCategories
        ];
    }
}
