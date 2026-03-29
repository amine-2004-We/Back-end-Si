<?php

namespace App\Services;

use App\Models\Group;
use App\Repositories\GroupRepository;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;

class GroupService
{
    protected GroupRepository $groupRepository;
    protected TaskService $taskService;

    public function __construct(GroupRepository $groupRepository, TaskService $taskService)
    {
        $this->groupRepository = $groupRepository;
        $this->taskService = $taskService;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request): mixed
    {
        $filters = $request->only(['group_id', 'is_active', 'name', 'code', 'class_id', 'educator_id', 'status', 'start_date', 'end_date', 'per_page']);
        return $this->groupRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->groupRepository->allWithoutPagination();
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function show(string $id): mixed
    {
        return $this->groupRepository->find($id);
    }

   
  

    /**
     * @param string $id
     * @param array $data
     * @return Group
     */
    public function update(string $id, array $data): mixed
    {
        return $this->groupRepository->update($data, $id);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delete(string $id): mixed
    {
        return $this->groupRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->groupRepository->bulkDelete($ids);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function restore(string $id): mixed
    {
        try {
            return $this->groupRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

   

   
   public function create(array $data): Group
    {
        return DB::transaction(function () use ($data) {

            $group = $this->groupRepository->create($data);

            Log::info("Starting task generation for Group ID: {$group->id}");
            Log::info("=== START Task Generation for Group ID: {$group->id} ===");

            $createdBy = Auth::id();
            $group->load('level', 'class');

            // Fetch ClassResources from the Class
            $resources = $group->class->classResources()
                ->where('isStill', true)
                ->with('project.programType')
                ->get();

            foreach ($resources as $resource) {
                Log::info("Processing ClassResource ID: {$resource->id}, Class ID: {$group->class->id}");

                $project = $resource->project;
                $responsibleCollaboratorId = $resource->collaborator_id;

                if (!$project) {
                    Log::warning("ClassResource {$resource->id} has no project. Skipping.");
                    continue;
                }

                $programTypeName = $project->programType?->name;
                if (empty($programTypeName)) {
                    Log::warning("Project {$project->id} has no program type. Skipping.");
                    continue;
                }

                $programs = collect();

                // --- SELECT PROGRAMS BASED ON TYPE ---
                if ($programTypeName === 'Préscolaire') {
                    $levelTitle = $group->level?->title;
                    $preschoolLevelMap = ["Moyenne section" => 1, "Grande section" => 2];
                    $mappedLevel = $preschoolLevelMap[$levelTitle] ?? null;

                    if (!$mappedLevel) {
                        Log::warning("Niveau préscolaire '{$levelTitle}' non reconnu.");
                        continue;
                    }

                    $programs = \App\Models\EducationalProgram::where('level', $mappedLevel)
                        ->orderBy('subcomponent', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                } elseif ($programTypeName === 'Remédiation scolaire primaire') {
                    $levelTitle = $group->level?->title;
                    $levelMap = [
                        "3ʳᵉ année primaire" => "3",
                        "4ʳᵉ année primaire" => "4",
                        "5ʳᵉ année primaire" => "5",
                        "6ʳᵉ année primaire" => "6",
                    ];
                    $mappedLevel = $levelMap[$levelTitle] ?? null;

                    if (!$mappedLevel) {
                        Log::warning("Level title '{$levelTitle}' not mapped for Remédiation.");
                        continue;
                    }

                    $programs = \App\Models\PsProgramme::where('level', $mappedLevel)
                        ->orderBy('subcomponent', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                } elseif ($programTypeName === 'Petite section') {
                    $programs = \App\Models\PreschoolPedagogiqueProgram::with('phase')->orderBy('id')->get();
                } elseif ($programTypeName === 'KADER') {
                    $programs = \App\Models\ProgrammePedagogiqueKader::with('phase')->orderBy('id')->get();
                } else {
                    Log::warning("Unknown program type '{$programTypeName}'. Skipping.");
                    continue;
                }

                if ($programs->isEmpty()) {
                    Log::warning("No programs found for '{$programTypeName}'.");
                    continue;
                }

                $taskNumber = 1;
                $currentDate = $resource->start_date ? Carbon::parse($resource->start_date) : Carbon::now();

                foreach ($programs as $program) {

                    if ($programTypeName === 'KADER') {
                        $activities = is_string($program->activities) ? json_decode($program->activities, true) : $program->activities;
                        $activities = is_array($activities) ? $activities : [];

                        foreach ($activities as $activity) {
                            $this->generateSingleTask($project, $program, $group, $activity, $currentDate, $responsibleCollaboratorId, $createdBy);
                            $currentDate = $this->nextWeekday($currentDate);
                        }
                    } elseif ($programTypeName === 'Préscolaire') {
                        $titleParts = array_filter([$program->pedagogical_project_arabe, $program->pedagogical_project]);
                        $baseTitle = implode(' / ', $titleParts);

                        for ($i = 1; $i <= $program->duration; $i++) {
                            $taskData = [
                                'title' => "{$baseTitle} - J{$i}",
                                'project_id' => $project->id,
                                'program_id' => $project->program_id,
                                'program_type_id' => $project->program_type_id,
                                'phase_id' => $program->subcomponent,
                                'group_id' => $group->id,
                                'class_id' => $group->class->id,
                                'type' => 'Autre',
                                'level_id' => $group->level->id,
                                'activities' => $program->activities ?? null,
                                'expected_start_date' => $currentDate->copy(),
                                'expected_end_date' => $currentDate->copy(),
                                'status' => 'Prévue',
                                'responsible_collaborator_id' => $responsibleCollaboratorId,
                                'created_by' => $createdBy,
                            ];
                            $this->taskService->createTask($taskData);
                            $currentDate = $this->nextWeekday($currentDate);
                        }
                    } elseif ($programTypeName === 'Remédiation scolaire primaire') {
                        $titleParts = array_filter([$program->name_ar, $program->name]);
                        $taskData = [
                            'title' => implode(' / ', $titleParts),
                            'project_id' => $project->id,
                            'program_id' => $project->program_id,
                            'program_type_id' => $project->program_type_id,
                            'phase_id' => $program->subcomponent,
                            'group_id' => $group->id,
                            'class_id' => $group->class->id,
                            'type' => 'Autre',
                            'level_id' => $group->level->id,
                            'activities' => $program->activities,
                            'expected_start_date' => $currentDate->copy(),
                            'expected_end_date' => $currentDate->copy(),
                            'status' => 'Prévue',
                            'responsible_collaborator_id' => $responsibleCollaboratorId,
                            'created_by' => $createdBy,
                        ];
                        $this->taskService->createTask($taskData);
                        $currentDate = $this->nextWeekday($currentDate);
                    } else {
                        for ($i = 0; $i < $program->duration; $i++) {
                            $title = ($program->phase?->name ?? 'Phase') . ' J-' . $taskNumber;
                            $this->generateSingleTask($project, $program, $group, $title, $currentDate, $responsibleCollaboratorId, $createdBy);
                            $taskNumber++;
                            $currentDate = $this->nextWeekday($currentDate);
                        }
                    }
                }
            }

            Log::info("=== END Task Generation for Group ID: {$group->id} ===");
            return $group;
        });
    }

    private function generateSingleTask($project, $program, $group, $title, $date, $respId, $userId)
    {
        $this->taskService->createTask([
            'title' => $title,
            'project_id' => $project->id,
            'program_id' => $project->program_id,
            'program_type_id' => $project->program_type_id,
            'phase_id' => $program->subcomponent,
            'group_id' => $group->id,
            'class_id' => $group->class->id,
            'type' => 'Autre',
            'level_id' => $group->level->id,
            'expected_start_date' => $date->copy(),
            'expected_end_date' => $date->copy(),
            'status' => 'Prévue',
            'responsible_collaborator_id' => $respId,
            'created_by' => $userId,
        ]);
    }

    private function nextWeekday(Carbon $date): Carbon
    {
        do {
            $date->addDay();
        } while ($date->isWeekend());

        return $date;
    }
}