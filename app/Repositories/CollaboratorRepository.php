<?php

namespace App\Repositories;

use App\Models\Collaborator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CollaboratorRepository
{
    /**
     * Retrieve all collaborators with pagination.
     */
    public function getAll(): Collection
    {
        return Collaborator::all();
    }
    /**
     * Create a new collaborator.
     */
    public function create(array $data): Collaborator
    {
        return Collaborator::create($data);
    }

    /**
     * Update a collaborator.
     */
    public function update(string $collaboratorId, array $data): Collaborator
    {
        $collaborator = Collaborator::findOrFail($collaboratorId);
        $collaborator->update($data);
        return $collaborator;
    }

    /**
     * Delete multiple collaborators by IDs.
     */
    public function bulkDelete(array $ids): int
    {
        return Collaborator::whereIn('id', $ids)->delete();
    }

    /**
     * Find a collaborator by ID (or return null).
     */
    public function find(string $collaboratorId): ?Collaborator
    {
        return Collaborator::find($collaboratorId);
    }

    /**
     * Delete a collaborator by ID.
     */
    public function delete(string $collaboratorId): bool
    {
        $collaborator = $this->find($collaboratorId);
        if (!$collaborator) {
            throw new ModelNotFoundException("Collaborator not found.");
        }

        return $collaborator->delete();
    }

    /**
     * Retrieve all collaborators, including soft deleted.
     */
    public function getAllWithTrashed(int $paginate = 10): LengthAwarePaginator
    {
        return Collaborator::withTrashed()->paginate($paginate);
    }

    /**
     * Retrieve all collaborators with optional filters.
     */
    public function getAllCollaborators(array $filters = []): LengthAwarePaginator
    {
        $query = Collaborator::query()
            ->with(['collaboratorStatus', 'contractType', 'contractStatus']);

        if (isset($filters['activation_status']) && $filters['activation_status'] !== 'active') {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }
        if (!empty($filters['collaborator_code'])) {
            $query->where('collaborator_code', 'ilike', '%' . $filters['collaborator_code'] . '%');
        }

        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'ilike', '%' . $filters['last_name'] . '%');
        }


        if (!empty($filters['cin'])) {
            $query->where('cin', 'ilike', '%' . $filters['cin'] . '%');
        }

        if (!empty($filters['cnss'])) {
            $query->where('cnss', 'ilike', '%' . $filters['cnss'] . '%');
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', 'like', '%' . $filters['position_id'] . '%');
        }

        if (!empty($filters['contract_type_id'])) {
            $query->where('contract_type_id', $filters['contract_type_id']);
        }

        if (!empty($filters['collaborator_status_id'])) {
            $query->where('collaborator_status_id', $filters['collaborator_status_id']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (isset($filters['activation_status']) && $filters['activation_status'] === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Retrieve only soft deleted collaborators with optional filters.
     */
    public function getAllTrashedCollaborators(array $filters = []): LengthAwarePaginator
    {
        $query = Collaborator::query()
            ->with(['collaboratorStatus', 'contractType', 'contractStatus'])
            ->onlyTrashed();

        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'like', '%' . $filters['last_name'] . '%');
        }

        if (!empty($filters['cin'])) {
            $query->where('cin', 'like', '%' . $filters['cin'] . '%');
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', 'like', '%' . $filters['position_id'] . '%');
        }

        if (!empty($filters['contract_type_id'])) {
            $query->where('contract_type_id', $filters['contract_type_id']);
        }

        if (!empty($filters['collaborator_status_id'])) {
            $query->where('collaborator_status_id', $filters['collaborator_status_id']);
        }

        $sortBy = $filters['order_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a soft-deleted collaborator by ID.
     */
    public function findTrashedById(int $id): ?Collaborator
    {
        return Collaborator::onlyTrashed()->find($id);
    }

    /**
     * Restore a soft-deleted collaborator.
     */
    public function restore(Collaborator $collaborator): bool
    {
        return $collaborator->restore();
    }

    /**
     * Find a collaborator with their assigned projects.
     */
    public function findWithProjects(int $id): ?Collaborator
    {
        return Collaborator::with('projects')->find($id);
    }

    /**
     * @param int $id
     * @return Collaborator|null
     */

    public function findCollaboratorWithUserID(int $userId): ?Collaborator
    {
        return Collaborator::with('user')
            ->where('user_id', $userId)
            ->first(['first_name', 'last_name']);
    }
}
