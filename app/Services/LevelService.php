<?php

namespace App\Services;

use App\Models\Level;
use App\Repositories\LevelRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 *class LevelService
 */
class LevelService
{
    protected LevelRepository $levelRepository;

    public function __construct(LevelRepository $levelRepository)
    {
        $this->levelRepository = $levelRepository;
    }

    /**
     * Get all levels with pagination and filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Level>
     */
    public function getPaginatedLevels(array $filters = [], int $perPage = 10 , int $page = 1): LengthAwarePaginator
    {
        return $this->levelRepository->all($filters, $perPage,$page );
    }

    /**
     * Get a single level by ID.
     *
     * @param int $id
     * @return Level|null
     */
    public function getLevel(int $id): ?Level
    {
        return $this->levelRepository->find($id);
    }

    /**
     * Create a new level.
     *
     * @param array<string, mixed> $data
     * @return Level|null
     */
    public function createLevel(array $data): ?Level
    {
        return $this->levelRepository->create($data);
    }

    /**
     * Update an existing level.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Level|null The updated level, or null if not found/update failed.
     */
    public function updateLevel(int $id, array $data): ?Level
    {
        return $this->levelRepository->update($id, $data);
    }

    /**
     * Delete a level by ID (soft delete).
     *
     * @param int $id
     * @return bool
     */
    public function deleteLevel(int $id): bool
    {
        return $this->levelRepository->delete($id);
    }

    /**
     * Toggle status (soft delete/restore) for multiple levels by IDs.
     *
     * @param array<int> $ids
     * @return bool
     */
    public function bulkToggleLevelsStatus(array $ids): bool
    {
        return $this->levelRepository->bulkToggleStatus($ids);
    }

    /**
     * Get options for level forms (e.g., cycles).
     *
     * @return array
     */
    public function getFormOptions(): array
    {
        return $this->levelRepository->getFormOptions();
    }
}
