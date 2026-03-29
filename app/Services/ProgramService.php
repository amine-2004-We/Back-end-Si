<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Project;
use App\Models\Collaborator;
use App\Models\ProgramType;
use App\Repositories\ProgramRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class ProgramService
{
    /**
     * @var ProgramRepository
     */
    public ProgramRepository $programRepository;

    /**
     * @param ProgramRepository $programRepository
     */
    public function __construct(ProgramRepository $programRepository)
    {
        $this->programRepository = $programRepository;
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPrograms(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->programRepository->getPaginated($filters, $perPage);
    }

    /**
     * @param array $data
     * @param array $programTypes
     * @return Program|null
     */
     public function createProgram(array $data, array $programTypes = []): ?Program
{
    return DB::transaction(function () use ($data, $programTypes) {
        if (!isset($data['created_by']) && auth()->check()) {
            $data['created_by'] = auth()->id();
        }

      
        unset($data['program_types']);

   
        $program = $this->programRepository->create($data);

      
        if ($program && !empty($programTypes)) {
            foreach ($programTypes as $type) {
                $name = is_string($type) ? trim($type) : null;
                if ($name === '' || $name === null) {
                    continue;
                }
                ProgramType::firstOrCreate([
                    'program_id' => $program->id,
                    'name' => $name,
                ]);
            }
        }

        return $program ? $program->fresh() : null;
    });
}

   
public function updateProgram(Program $program, array $data, array $programTypes = []): ?Program
{
    return DB::transaction(function () use ($program, $data, $programTypes) {
      
        $desiredNames = [];
        if (!empty($programTypes)) {
            if (is_string($programTypes)) {
                $decoded = json_decode($programTypes, true);
                $programTypes = is_array($decoded) ? $decoded : [$programTypes];
            }
            foreach ((array)$programTypes as $t) {
                if (is_string($t)) {
                    $name = trim($t);
                } elseif (is_array($t) && isset($t['name'])) {
                    $name = trim($t['name']);
                } else {
                    $name = null;
                }
                if ($name !== null && $name !== '') {
                    $desiredNames[] = $name;
                }
            }
   
            $desiredNames = array_values(array_unique($desiredNames));
        }

      
        $updatePayload = $data;
     
        unset($updatePayload['program_types']);
        $updated = $this->programRepository->update($program, $updatePayload);

        if (!$updated) {
            throw new \RuntimeException("Failed to update program attributes (id: {$program->id})");
        }

   
        if ($programTypes !== [] ) { 
            
            foreach ($desiredNames as $name) {
                ProgramType::firstOrCreate([
                    'program_id' => $program->id,
                    'name' => $name,
                ]);
            }

            if (!empty($desiredNames)) {
                ProgramType::where('program_id', $program->id)
                    ->whereNotIn('name', $desiredNames)
                    ->delete();
            } else {
                
                ProgramType::where('program_id', $program->id)->delete();
            }
        }


        return $program->fresh()->load('programTypes');
    });
}

    /**
     * @param array $ids
     * @return array
     */
    public function toggleProgramActivation(array $ids): array
    {
        return $this->programRepository->toggleActivation($ids);
    }

    /**
     * @return array
     */
    public function getFormOptions(): array
    {
        $programTypes = ['Education', 'Environnement', 'Parentalité', 'Inclusion']; 
        $statuses = ['Actif', 'Clôturé', 'En pause', 'Archivé']; 

        return [
            'projects' => Project::select('id', 'project_name')->get(),
            'users' => Collaborator::select('id', 'last_name', "first_name")->get(), 
            'program_types' => $programTypes,
            'statuses' => $statuses,
        ];
    }

    public function getProgramsByInterventionAxis(int $axisId): Collection
    {
        return $this->programRepository->getByInterventionAxis($axisId);
    }

    public function getProgramTypesForProgram(int $programId): Collection
    {
        return $this->programRepository->getProgramTypes($programId);
    }

    public function findProgram(int $programId): Program
    {
        return $this->programRepository->find($programId);
    }
}
