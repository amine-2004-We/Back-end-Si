<?php

namespace App\Services;

use App\Models\Module;
use App\Repositories\ModuleRepository;
use App\Traits\UploadFileTrait; 
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleService
{
    use UploadFileTrait; 

    protected ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'module_id', 'title', 'training_id', 'trainer_id',
            'competency_grid_id', 'status', 'is_active', 'per_page',
        ]);

        return $this->moduleRepository->withFilters($filters);
    }

    public function getAllWithoutPagination(): mixed
    {
        return $this->moduleRepository->all();
    }

    public function show(int $id): mixed
    {
        return $this->moduleRepository->find($id);
    }

    public function create(array $data, Request $request): Module
    {
        return DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('pedagogical_supports')) {
                $paths = [];
                foreach ($request->file('pedagogical_supports') as $file) {
                    $paths[] = $this->uploadPublicFile($file, 'modules');
                }
                $data['pedagogical_supports'] = $paths;
            }

            $module = $this->moduleRepository->create($data);

            return $module->load(['training', 'trainer', 'competencyGrid', 'creator']);
        });
    }

    public function update(array $data, Module $module, Request $request): Module
    {
        return DB::transaction(function () use ($data, $module, $request) {
            $existingSupports = $module->pedagogical_supports ?? [];
            
            $removedSupports = $request->input('removed_supports', []);
            $remainingSupports = array_diff($existingSupports, $removedSupports);
            foreach ($removedSupports as $path) {
                $this->deletePublicFile($path);
            }

            $newPaths = [];
            if ($request->hasFile('pedagogical_supports')) {
                foreach ($request->file('pedagogical_supports') as $file) {
                    $newPaths[] = $this->uploadPublicFile($file, 'modules');
                }
            }
            
            $data['pedagogical_supports'] = array_merge(array_values($remainingSupports), $newPaths);
            
            $module->update($data);

            return $module->load(['training', 'trainer', 'competencyGrid', 'creator']);
        });
    }
    
    public function delete(int $id)
    {
        return $this->moduleRepository->delete($id);
    }

    public function bulkDestroy(array $ids): int
    {
        return $this->moduleRepository->bulkDelete($ids);
    }

    public function restore(int $id): Module
    {
        try {
            return $this->moduleRepository->restore($id)->load(['training', 'trainer', 'competencyGrid', 'creator']);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}

