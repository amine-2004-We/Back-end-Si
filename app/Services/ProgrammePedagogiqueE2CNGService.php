<?php

namespace App\Services;

use App\Http\Requests\StoreProgrammePedagogiqueE2CNGRequest;
use App\Http\Requests\UpdateProgrammePedagogiqueE2CNGRequest;
use App\Models\ProgrammePedagogiqueE2CNG;
use App\Models\Phase;
use App\Repositories\ProgrammePedagogiqueE2CNGRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProgrammePedagogiqueE2CNGService
{
    protected ProgrammePedagogiqueE2CNGRepository $repository;
    protected TaskService $taskService;

    public function __construct(ProgrammePedagogiqueE2CNGRepository $repository, TaskService $taskService)
    {
        $this->repository = $repository;
        $this->taskService = $taskService;
    }

    public function show(int $id): ProgrammePedagogiqueE2CNG
    {
        return $this->repository->findOrFail($id);
    }

    public function create(StoreProgrammePedagogiqueE2CNGRequest $request): ProgrammePedagogiqueE2CNG
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $programme = $this->repository->create($data);

            try {
                \Log::error("Creating task for E2CNG programme {$programme->id}");
                
                $group = $programme->groupe;
                if (!$group) {
                    \Log::warning("No group found for E2CNG programme {$programme->id}");
                    return $programme;
                }

                $phase = Phase::whereNull('deleted_at')->first();
                $phaseId = $phase ? $phase->id : 1; 
                
                $taskData = [
                    'title' => 'E2CNG Programme : ' . ($programme->project_pedagogique?->value ?? $programme->metier?->value ?? $programme->ateliers?->value ?? 'N/A'),
                    'project_id' => $group->project_id ?? 9,
                    'class_id' => $group->class_id,
                    'phase_id' => $phaseId,
                    'type' => 'Autre',
                    'expected_start_date' => $programme->date_prevu ?? $programme->date_prevue,
                    'expected_end_date' => $programme->date_realisation ?? $programme->real_end_date,
                    'status' => 'Prévue',
                    'responsible_collaborator_id' => 1, 
                    'created_by' => Auth::id(),
                ];

                $task = $this->taskService->createTask($taskData);
                \Log::error("Task created successfully for E2CNG programme {$programme->id}");
                
                if ($task && $group) {
                    $this->attachGroupToTask($task, $group->id);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to create task for E2CNG programme {$programme->id}. Error: " . $e->getMessage());
                throw $e;
            }

            return $programme;
        });
    }

    public function update(int $id, UpdateProgrammePedagogiqueE2CNGRequest $request): ProgrammePedagogiqueE2CNG
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

    public function restore(int $id): ProgrammePedagogiqueE2CNG
    {
        $item = $this->repository->findTrashedById($id);
        if (!$item) {
            throw new ModelNotFoundException('Deleted programme not found.');
        }
        $this->repository->restore($item);
        return $item;
    }

    /**
     * Attach a group to a task
     */
    private function attachGroupToTask($task, $groupId): void
    {
        // Check if the entry already exists
        $exists = DB::table('task_group')
            ->where('task_id', $task->id)
            ->where('group_id', $groupId)
            ->exists();

        if (!$exists) {
            DB::table('task_group')->insert([
                'task_id' => $task->id,
                'group_id' => $groupId,
            ]);
        }
    }
}
