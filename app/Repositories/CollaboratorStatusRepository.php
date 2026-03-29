<?php

namespace App\Repositories;

use App\Models\CollaboratorStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class CollaboratorStatusRepository
{

    /**
     * Récupérer tous les status collaborateurs avec pagination.
     */

    public function all(int $paginate = 10): LengthAwarePaginator
    {
        return CollaboratorStatus::paginate($paginate);
    }

    /**
     * Créer un nouveau collaborateur.
     */

    public function create(array $data): CollaboratorStatus
    {
        return CollaboratorStatus::create($data);
    }

    /**
     * Mettre à jour les données d'un collaborateur.
     */

    public function update(string $collaborateurStatId, array $data): CollaboratorStatus
    {
        $collaborateur = CollaboratorStatus::findOrFail($collaborateurStatId);
        $collaborateur->update($data);
        return $collaborateur;
    }

    /**
     * Supprimer plusieurs collaborateurs par leurs IDs.
     */

    public function bulkDelete(array $ids): int
    {
        return CollaboratorStatus::whereIn('id', $ids)->delete();
    }

    /**
     * Trouver un collaborateur par ID (ou retourner null si non trouvé).
     */

    public function find(string $collaborateurStatId): ?CollaboratorStatus
    {
        return CollaboratorStatus::find($collaborateurStatId);
    }

    /**
     * Supprimer un collaborateur par son ID.
     */

    public function delete(string $collaborateurStatId): bool
    {
        $collaborateurStat = $this->find($collaborateurStatId);
        if (!$collaborateurStat) {
            throw new ModelNotFoundException("Collaborateur not found.");
        }

        return $collaborateurStat->delete();
    }

    /**
     * Récupérer tous les collaborateurs, y compris les supprimés (soft deleted).
     */

    public function getAllWithTrashed($paginate = 10): LengthAwarePaginator
    {
        return CollaboratorStatus::onlyTrashed()->paginate($paginate);
    }

    public function restore(string $collaborateurStatId): CollaboratorStatus
    {
        $collaborateurStat = CollaboratorStatus::onlyTrashed()->findOrFail($collaborateurStatId);
        $collaborateurStat->restore();
        return $collaborateurStat;
    }

}
