<?php

namespace App\Policies;

use App\Models\TraineeCollaborator;
use App\Models\User;

class TraineeCollaboratorPolicy
{
    private array $viewRoles           = ['RH (Ressources Humaines)', 'Direction Générale (DG)', 'Super Admin'];
    private array $createUpdateDelRoles= ['RH (Ressources Humaines)', 'Super Admin'];
    private array $restoreForceRoles   = ['Super Admin'];

    /**
     * Admin SI : full access
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
        return null;
    }

    /**
     * Qui peut consulter ?  -> RH, DG
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole($this->viewRoles);
    }

    public function view(User $user, TraineeCollaborator $traineeCollaborator): bool
    {
        return $user->hasRole($this->viewRoles);
    }

    /**
     * Qui peut créer ? -> RH
     */
    public function create(User $user): bool
    {
        return $user->hasRole($this->createUpdateDelRoles);
    }

    /**
     * Qui peut modifier ? -> RH
     */
    public function update(User $user, TraineeCollaborator $traineeCollaborator): bool
    {
        return $user->hasRole($this->createUpdateDelRoles);
    }

    /**
     * Suppression logique alignée sur “modifier” -> RH
     */
    public function delete(User $user, TraineeCollaborator $traineeCollaborator): bool
    {
        return $user->hasRole($this->createUpdateDelRoles);
    }

    /**
     * Restore / Force delete -> Super Admin (Admin SI via before)
     */
    public function restore(User $user, TraineeCollaborator $traineeCollaborator): bool
    {
        return $user->hasRole($this->restoreForceRoles);
    }

    public function forceDelete(User $user, TraineeCollaborator $traineeCollaborator): bool
    {
        return $user->hasRole($this->restoreForceRoles);
    }

}
