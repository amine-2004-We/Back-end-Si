<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     * "Consulter": "Tous rôles terrain et direction des opérations"
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::SUPERVISEUR,
            Role::RESPONSABLE_LOCAL,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_REGIONAL,
            Role::RESPONSABLE_NATIONAL,
            Role::EDUCATRICE,
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_REGIONAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::DIRECTEUR_DES_OPERATIONS,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Créer": "Direction des Opérations (Locale/Centrale)"
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::SUPERVISEUR,
            Role::RESPONSABLE_LOCAL,
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * "Modifier": "N+1" - Interpreted as supervisory and management roles.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::SUPERVISEUR,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Valider": "N+1" - Interpreted as supervisory and management roles.
     */
    public function validateTask(User $user, Task $task): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_NATIONAL,
            Role::RESPONSABLE_REGIONAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::RESPONSABLE_PEDAGOGIQUE_REGIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with the 'update' permission as a safe default.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether user can perform bulk actions.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::SUPERVISEUR,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
