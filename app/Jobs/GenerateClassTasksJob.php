<?php

namespace App\Jobs;

use App\Services\ProgramPedagogicService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ProjectClass;

class GenerateClassTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ProjectClass $class;
    public int $createdBy;

    /**
     * Create a new job instance.
     */
    public function __construct(ProjectClass $class, int $createdBy)
    {
        $this->class = $class;
        $this->createdBy = $createdBy;
    }

    /**
     * Execute the job.
     */
    public function handle(ProgramPedagogicService $programPedagogicService): void
    {
        $projects = $this->class->projects()->get();

        if ($projects->isEmpty()) {
            return;
        }

        foreach ($projects as $project) {
            $programTypeName = $project->program?->programType?->name;
            if ($programTypeName === 'Préscolaire') {
                $programPedagogicService->generateTasksForProject($project, $this->class, $this->createdBy);
            } elseif ($programTypeName === 'Petite section') {
                $programPedagogicService->generateTasksForProgramPs($project, $this->class, $this->createdBy);
            } elseif ($programTypeName === 'KADER') {
                $programPedagogicService->generateTasksForProgramKader($project, $this->class, $this->createdBy);
            }
        }
    }

    /***
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('GenerateClassTasksJob failed: ' . $exception->getMessage());
    }
}
