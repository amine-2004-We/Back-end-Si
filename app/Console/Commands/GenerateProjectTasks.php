<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\PlanType;
use App\Models\Position;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateProjectTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:generate-missing-tasks {--project-id= : Generate tasks for a specific project ID}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Generate missing tasks for existing projects in the database';

    protected TaskService $taskService;

    /**
     * Create a new command instance.
     */
    public function __construct(TaskService $taskService)
    {
        parent::__construct();
        $this->taskService = $taskService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectId = $this->option('project-id');

        if ($projectId) {
            // Generate tasks for a specific project
            return $this->generateTasksForProject((int) $projectId);
        }

        // Generate tasks for all projects
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->info('No projects found in the database.');
            return self::SUCCESS;
        }

        $this->info("Found {$projects->count()} projects. Starting task generation...\n");

        $successCount = 0;
        $failureCount = 0;

        foreach ($projects as $project) {
            try {
                $this->generateTasksForProject($project->id);
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                $this->error("Failed to generate tasks for project ID {$project->id}: {$e->getMessage()}");
            }
        }

        $this->line("\n" . str_repeat('=', 50));
        $this->info("Task generation completed!");
        $this->info("✓ Successfully processed: {$successCount} projects");
        $this->error("✗ Failed: {$failureCount} projects");

        return self::SUCCESS;
    }

    /**
     * Generate tasks for a specific project
     */
    private function generateTasksForProject(int $projectId): int
    {
        $project = Project::find($projectId);

        if (!$project) {
            $this->error("Project with ID {$projectId} not found.");
            return self::FAILURE;
        }

        // Check if project already has tasks
        $existingTaskCount = $project->tasks()->count();

        if ($existingTaskCount > 0) {
            $this->warn("Project {$project->project_code} (ID: {$project->id}) already has {$existingTaskCount} tasks. Skipping...");
            return self::SUCCESS;
        }

        $this->line("Generating tasks for project: {$project->project_code}");

        try {
            DB::transaction(function () use ($project) {
                $taskCount = 0;
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

                        $this->taskService->createTask($taskData);
                        $taskCount++;
                    }

                    if (!in_array($order, [99, 100, 101, 102])) {
                        $previousEndDate = $this->addWeekdays($previousEndDate, $maxDuration);
                    }
                }

                $this->info("✓ Generated {$taskCount} tasks for project {$project->project_code}");
            });

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating tasks for project {$project->project_code}: {$e->getMessage()}");
            return self::FAILURE;
        }
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
}
