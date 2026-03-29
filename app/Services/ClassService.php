<?php

namespace App\Services;

use App\Http\Requests\StoreClassRequest;
use App\Models\EducationalProgram;
use App\Models\PreschoolPedagogiqueProgram;
use App\Models\ProgrammePedagogiqueKader;
use App\Models\ProjectClass;
use App\Models\PsProgramme;
use App\Models\Task;
use App\Repositories\ClassRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassService
{
    protected ClassRepository $classRepository;
    protected TaskService $taskService;
    public function __construct(ClassRepository $classRepository,TaskService $taskService)
    {
        $this->classRepository = $classRepository;
        $this->taskService = $taskService;
    }
    public function show(int $id): ProjectClass
    {
        return $this->classRepository->findOrFail($id);
    }

    /***
     * @param StoreClassRequest $request
     * @return ProjectClass
     */
    public function create(StoreClassRequest $request): ProjectClass
    {
        return DB::transaction(function () use ($request) {

            $data = $request->validated();
            $classResources = $data['class_resources'] ?? null;

            $class = $this->classRepository->createWithAssociations($data, $classResources);

            // \Log::info("Starting task generation for Class ID: {$class->id}");

            // $createdBy = Auth::id();
            // $class->load('level');

            // $resources = $class->classResources()
            //     ->where('isStill', true)
            //     ->with('project.programType')
            //     ->get();

            // foreach ($resources as $resource) {
            //     $project = $resource->project;
            //     $responsibleCollaboratorId = $resource->collaborator_id;

            //     if (!$project) {
            //         \Log::warning("ClassResource {$resource->id} has no project. Skipping.");
            //         continue;
            //     }

            //     $programTypeName = $project->programType?->name;
            //     if (empty($programTypeName)) {
            //         \Log::warning("Project {$project->id} has no program type. Skipping.");
            //         continue;
            //     }

            //     $programs = collect();

            //     // --- 1. SELECTION DES PROGRAMMES ---
            //     if ($programTypeName === 'Préscolaire') {
            //         $levelTitle = $class->level?->title;
            //         $preschoolLevelMap = ["Moyenne section" => 1, "Grande section" => 2];
            //         $mappedLevel = $preschoolLevelMap[$levelTitle] ?? null;

            //         if (!$mappedLevel) {
            //             \Log::warning("Niveau préscolaire '{$levelTitle}' non reconnu.");
            //             continue;
            //         }

            //         $programs = EducationalProgram::where('level', $mappedLevel)
            //             ->orderBy('subcomponent', 'asc')
            //             ->orderBy('id', 'asc')
            //             ->get();

            //     } elseif ($programTypeName === 'Remédiation scolaire primaire') {
            //         $levelTitle = $class->level?->title;
            //         $levelMap = [
            //             "3ʳᵉ année primaire" => "3",
            //             "4ʳᵉ année primaire" => "4",
            //             "5ʳᵉ année primaire" => "5",
            //             "6ʳᵉ année primaire" => "6",
            //         ];
            //         $mappedLevel = $levelMap[$levelTitle] ?? null;

            //         if (!$mappedLevel) {
            //             \Log::warning("Level title '{$levelTitle}' not mapped for Remédiation.");
            //             continue;
            //         }

            //         $programs = PsProgramme::where('level', $mappedLevel)
            //             ->orderBy('subcomponent', 'asc')
            //             ->orderBy('id', 'asc')
            //             ->get();

            //     } elseif ($programTypeName === 'Petite section') {
            //         $programs = PreschoolPedagogiqueProgram::with('phase')->orderBy('id')->get();
            //     } elseif ($programTypeName === 'KADER') {
            //         $programs = ProgrammePedagogiqueKader::with('phase')->orderBy('id')->get();
            //     } else {
            //         \Log::warning("Unknown program type '{$programTypeName}'. Skipping.");
            //         continue;
            //     }

            //     if ($programs->isEmpty()) {
            //         \Log::warning("No programs found for '{$programTypeName}'.");
            //         continue;
            //     }

            //     $taskNumber = 1;
            //     $currentDate = $resource->start_date ? Carbon::parse($resource->start_date) : Carbon::now();

            //     foreach ($programs as $program) {

            //         if ($programTypeName === 'KADER') {
            //             $activities = is_string($program->activities) ? json_decode($program->activities, true) : $program->activities;
            //             $activities = is_array($activities) ? $activities : [];

            //             foreach ($activities as $activity) {
            //                 $this->generateSingleTask($project, $program, $class, $activity, $currentDate, $responsibleCollaboratorId, $createdBy);
            //                 $currentDate = $this->nextWeekday($currentDate);
            //             }
            //         }

            //         elseif ($programTypeName === 'Préscolaire') {
            //             $titleParts = array_filter([$program->pedagogical_project_arabe, $program->pedagogical_project]);
            //             $baseTitle = implode(' / ', $titleParts);

            //             for ($i = 1; $i <= $program->duration; $i++) {
            //                 $taskData = [
            //                     'title' => "{$baseTitle} - J{$i}",
            //                     'project_id' => $project->id,
            //                     'program_id' => $project->program_id,
            //                     'program_type_id' => $project->program_type_id,
            //                     'phase_id' => $program->subcomponent,
            //                     'class_id' => $class->id,
            //                     'type' => 'Autre',
            //                     'level_id' => $class->levels_id,
            //                     'activities' => $program->activities ?? null,
            //                     'expected_start_date' => $currentDate->copy(),
            //                     'expected_end_date' => $currentDate->copy(),
            //                     'status' => 'Prévue',
            //                     'responsible_collaborator_id' => $responsibleCollaboratorId,
            //                     'created_by' => $createdBy,
            //                 ];
            //                 $this->taskService->createTask($taskData);
            //                 $currentDate = $this->nextWeekday($currentDate);
            //             }
            //         }

            //         elseif ($programTypeName === 'Remédiation scolaire primaire') {
            //             $titleParts = array_filter([$program->name_ar, $program->name]);
            //             $taskData = [
            //                 'title' => implode(' / ', $titleParts),
            //                 'project_id' => $project->id,
            //                 'program_id' => $project->program_id,
            //                 'program_type_id' => $project->program_type_id,
            //                 'phase_id' => $program->subcomponent,
            //                 'class_id' => $class->id,
            //                 'type' => 'Autre',
            //                 'level_id' => $class->levels_id,
            //                 'activities' => $program->activities,
            //                 'expected_start_date' => $currentDate->copy(),
            //                 'expected_end_date' => $currentDate->copy(),
            //                 'status' => 'Prévue',
            //                 'responsible_collaborator_id' => $responsibleCollaboratorId,
            //                 'created_by' => $createdBy,
            //             ];
            //             $this->taskService->createTask($taskData);
            //             $currentDate = $this->nextWeekday($currentDate);
            //         }

            //         else {
            //             for ($i = 0; $i < $program->duration; $i++) {
            //                 $title = ($program->phase?->name ?? 'Phase') . ' J-' . $taskNumber;
            //                 $this->generateSingleTask($project, $program, $class, $title, $currentDate, $responsibleCollaboratorId, $createdBy);
            //                 $taskNumber++;
            //                 $currentDate = $this->nextWeekday($currentDate);
            //             }
            //         }
            //     }
            //     $this->updateProjectStartupTask($project->id);
            // }

            return $class;
        });
    }

    /**
     * Helper to update project startup task dates based on generated tasks
     */
    private function updateProjectStartupTask($projectId)
    {
        $minStart = \DB::table('tasks')
            ->where('isProject', false)
            ->where('project_id', $projectId)
            ->min('expected_start_date');

        $maxEnd = \DB::table('tasks')
            ->where('isProject', false)
            ->where('project_id', $projectId)
            ->max('expected_end_date');

        $externalTask = Task::where('title', 'Démarrage des activités du programme')
            ->where('project_id', $projectId)
            ->first();

        if ($externalTask) {
            $externalTask->update([
                'expected_start_date' => $minStart,
                'expected_end_date'   => $maxEnd,
            ]);
        }
    }

    /**
     * Simple helper to avoid code duplication for standard task creation
     */
    private function generateSingleTask($project, $program, $class, $title, $date, $respId, $userId)
    {
        $this->taskService->createTask([
            'title' => $title,
            'project_id' => $project->id,
            'program_id' => $project->program_id,
            'program_type_id' => $project->program_type_id,
            'phase_id' => $program->subcomponent,
            'class_id' => $class->id,
            'type' => 'Autre',
            'level_id' => $class->levels_id,
            'expected_start_date' => $date->copy(),
            'expected_end_date' => $date->copy(),
            'status' => 'Prévue',
            'responsible_collaborator_id' => $respId,
            'created_by' => $userId,
        ]);
    }
    /***
     * @param int $id
     * @param StoreClassRequest $request
     * @return ProjectClass
     */
    public function update(int $id, StoreClassRequest $request): ProjectClass
    {
        return DB::transaction(function () use ($id, $request) {
            $data = $request->validated();
            if (isset($data['end_date']) && $data['end_date'] < $data['start_date']) {
                abort(422, 'La date de fin ne peut pas être antérieure à la date de début.');
            }
            $class = $this->classRepository->update($id, $data);

            if (isset($data['projects']) && is_array($data['projects'])) {
                $class->projects()->sync($data['projects']);
            }

            return $class;
        });
    }

    public function delete(int $id): bool
    {
        return $this->classRepository->delete($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->classRepository->bulkDelete($ids);
    }

    public function getAll(array $filters = [])
    {
        return $this->classRepository->getAllClasses($filters);
    }

    public function getAllWithTrashed(array $filters = [])
    {
        return $this->classRepository->getAllTrashed($filters);
    }

    public function restore(int $id): ProjectClass
    {
        $class = $this->classRepository->findTrashedById($id);

        if (!$class) {
            throw new ModelNotFoundException("Deleted class not found.");
        }

        $this->classRepository->restore($class);

        return $class;
    }
    private function nextWeekday(Carbon $date): Carbon
    {
        do {
            $date->addDay();
        } while ($date->isWeekend());

        return $date;
    }

    /***
     * @param int $id
     * @return mixed
     */
    public function getResourcesWithClassId(int $id)
    {
        return $this->classRepository->findByClassId($id);
    }

    /***
     * @param int $id
     * @return mixed
     */
    public function getResourcesWithClassIdArchive(int $id)
    {
        return $this->classRepository->findByClassIdArchive($id);
    }
}
