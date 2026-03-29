<?php

namespace App\Services;

use App\Models\CollaboratorStatus;
use App\Repositories\CollaboratorStatusRepository;

class CollaboratorStatusService
{
    /**
     * Repository for managing collaborator status operations
     */
    protected CollaboratorStatusRepository $collaboratorStatusRepository;

    public function __construct(CollaboratorStatusRepository $collaboratorStatusRepository)
    {
        $this->collaboratorStatusRepository = $collaboratorStatusRepository;
    }

    /**
     * Retrieve all active (non-deleted) collaborator statuses
     */
    public function getAll()
    {
        return $this->collaboratorStatusRepository->all();
    }

    /**
     * Find a collaborator status by its ID
     */
    public function findCollaboratorStatusById(string $id)
    {
        return $this->collaboratorStatusRepository->find($id);
    }

    /**
     * Create a new collaborator status with the provided data
     */
    public function create(array $data)
    {
        return $this->collaboratorStatusRepository->create($data);
    }

    /**
     * Update an existing collaborator status by ID with new data
     */
    public function update(string $id, array $data)
    {
        return $this->collaboratorStatusRepository->update($id, $data);
    }

    /**
     * Delete a collaborator status by ID
     */
    public function delete(string $id): bool
    {
        return $this->collaboratorStatusRepository->delete($id);
    }

    /**
     * Bulk delete collaborator statuses by their IDs
     */
    public function bulkDelete(array $ids): int
    {
        return $this->collaboratorStatusRepository->bulkDelete($ids);
    }

    /**
     * Retrieve all collaborator statuses, including soft deleted ones
     */
    public function getAllWithTrashed()
    {
        return $this->collaboratorStatusRepository->getAllWithTrashed();
    }

    public function restore($collaborateurStatId):CollaboratorStatus
    {
        return $this->collaboratorStatusRepository->restore($collaborateurStatId);
    }
}
