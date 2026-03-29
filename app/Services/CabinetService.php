<?php

namespace App\Services;

use App\Models\Cabinet;
use App\Repositories\CabinetRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCabinetRequest;
use App\Http\Requests\UpdateCabinetRequest;
use App\Traits\UploadFileTrait; // Import the trait for file handling
use Illuminate\Support\Facades\Storage;

/**
 * class CabinetService
 */
class CabinetService
{
    use UploadFileTrait; // Use the trait for file upload functionality

    /** @var CabinetRepository */
    protected CabinetRepository $cabinetRepository;

    public function __construct(CabinetRepository $cabinetRepository)
    {
        $this->cabinetRepository = $cabinetRepository;
    }

    /**
     * Get all cabinets with filters & pagination.
     */
    public function getAll(Request $request)
    {
        $filters = $request->only([
            'name',
            'address',
            'contact_phone',
            'is_active',
            'per_page',
            'average_rating'
        ]);

        return $this->cabinetRepository->withFilters($filters);
    }

    /**
     * Find a cabinet by its id.
     * @throws ModelNotFoundException
     */
    public function find(int $id): Cabinet
    {
        return $this->cabinetRepository->find($id)->load(['createdBy', 'place']);
    }

    /**
     * Create a new cabinet and handle the contract file upload.
     *
     * @param array $data
     * @param StoreCabinetRequest $request
     * @return Cabinet
     */
    public function create(array $data, StoreCabinetRequest $request): Cabinet
    {
        $filePaths = [];
        if ($request->hasFile('contracts')) {
            foreach ($request->file('contracts') as $file) {
                $filePaths[] = $this->uploadPublicFile($file, 'cabinets/contracts');
            }
        }

        if (!empty($filePaths)) {
            $data['contracts'] = $filePaths;
        }

        return $this->cabinetRepository->create($data);
    }

    /**
     * Update an existing cabinet and handle contract file replacement.
     *
     * @param int $id
     * @param array $data
     * @param UpdateCabinetRequest $request
     * @return Cabinet
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data, UpdateCabinetRequest $request): Cabinet
    {
        $cabinet = $this->cabinetRepository->find($id);

        $currentContracts = $cabinet->contracts ?? [];

        $pathsToDelete = $data['deleted_contracts'] ?? [];

        if (!empty($pathsToDelete)) {
            foreach ($pathsToDelete as $path) {
                $this->deletePublicFile($path);
            }

            $currentContracts = array_filter($currentContracts, function ($path) use ($pathsToDelete) {
                return !in_array($path, $pathsToDelete);
            });
        }

        $newFilePaths = [];
        if ($request->hasFile('contracts')) {
            foreach ($request->file('contracts') as $file) {
                $newFilePaths[] = $this->uploadPublicFile($file, 'cabinets/contracts');
            }
        }

        $data['contracts'] = array_merge(array_values($currentContracts), $newFilePaths);

        unset($data['deleted_contracts']);

        return $this->cabinetRepository->update($data, $id);
    }

    /**
     * Soft delete the cabinet.
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->cabinetRepository->delete($id);
    }

    /**
     * Bulk delete cabinets.
     *
     * @param array<int> $ids
     * @return int
     */
    public function bulkDestroy(array $ids): int
    {
        return $this->cabinetRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted cabinet.
     */
    public function restore(int $id): Cabinet
    {
        try {
            $cabinet = $this->cabinetRepository->restore($id);

            return $cabinet->load(['createdBy', 'place']);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    /**
     * * Helper method to delete a file from the public disk.
     */
    public function deletePublicFile(?string $path = null)
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
