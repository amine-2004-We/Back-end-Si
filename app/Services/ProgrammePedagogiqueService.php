<?php

namespace App\Services;

use App\Http\Requests\StoreProgrammePedagogiqueRequest;
use App\Http\Requests\UpdateProgrammePedagogiqueRequest;
use App\Models\ProgrammePedagogique;
use App\Models\Phase;
use App\Repositories\ProgrammePedagogiqueRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProgrammePedagogiqueService
{
    protected ProgrammePedagogiqueRepository $repository;
    protected TaskService $taskService;

    public function __construct(ProgrammePedagogiqueRepository $repository, TaskService $taskService)
    {
        $this->repository = $repository;
        $this->taskService = $taskService;
    }

    public function show(int $id): ProgrammePedagogique
    {
        return $this->repository->findOrFail($id);
    }

    public function create(StoreProgrammePedagogiqueRequest $request): ProgrammePedagogique
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $programme = $this->repository->create($data);

            // Create task after programme creation
            try {
                \Log::error("Creating task for programme {$programme->id}");
                
                // Get a phase for the task (use first available phase)
                $phase = Phase::whereNull('deleted_at')->first();
                $phaseId = $phase ? $phase->id : 1; // Default to phase 1 if none exist
                
                $taskData = [
                    'title' => $programme->title ?? $programme->class->class_name,
                    'project_id' => $programme->project_id,
                    'class_id' => $programme->class_id,
                    'phase_id' => $phaseId,
                    'type' => 'Autre',
                    'expected_start_date' => $programme->date_prevu ?? $programme->start_date,
                    'expected_end_date' => $programme->date_realisation ?? $programme->end_date,
                    'status' => 'Prévue',
                    'responsible_collaborator_id' => $programme->user_id,
                    'created_by' => Auth::id(),
                ];

                $task = $this->taskService->createTask($taskData);
                \Log::error("Task created successfully for programme {$programme->id}");
                
                // Attach groups related to the class
                if ($task) {
                    $this->attachGroupsToTask($task, $programme->class_id);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to create task for programme {$programme->id}. Error: " . $e->getMessage());
                throw $e;
            }

            return $programme;
        });
    }

    public function update(int $id, UpdateProgrammePedagogiqueRequest $request): ProgrammePedagogique
    {
        $data = $request->validated();
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    public function getAll(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function getAllWithTrashed(array $filters = [])
    {
        return $this->repository->getAllTrashed($filters);
    }

    public function restore(int $id): ProgrammePedagogique
    {
        $item = $this->repository->findTrashedById($id);
        if (!$item) {
            throw new ModelNotFoundException('Deleted programme not found.');
        }
        $this->repository->restore($item);
        return $item;
    }

    /**
     * Attach groups related to a class to a task
     */
    private function attachGroupsToTask($task, $classId): void
    {
        $groupIds = DB::table('groups')
            ->where('class_id', $classId)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        if (empty($groupIds)) {
            return;
        }

        $rows = array_map(function ($groupId) use ($task) {
            return [
                'task_id' => $task->id,
                'group_id' => $groupId,
            ];
        }, $groupIds);

        DB::table('task_group')->insert($rows);
    }
}
