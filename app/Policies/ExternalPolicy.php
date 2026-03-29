<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\External;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExternalPolicy
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
     * "Qui peut consulter ?" -> Assistante, ZA
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([Role::ASSISTANTE, Role::ZA, Role::ADMIN_SI]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, External $external): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Qui peut créer ?" -> Assistante
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([Role::ASSISTANTE, Role::ADMIN_SI]);
    }

    /**
     * Determine whether the user can update the model.
     * "Qui peut modifier ?" -> Assistante
     */
    public function update(User $user, External $external): bool
    {
        return $user->hasAnyRole([Role::ASSISTANTE, Role::ADMIN_SI]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Qui peut valider ?" -> (Personne)
     */
    public function validateExternal(User $user, External $external): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with update permissions as a safe default.
     */
    public function delete(User $user, External $external): bool
    {
        return $this->update($user, $external);
    }

    /**
     * Determine whether user can perform bulk updates (like toggleActivation).
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([Role::ASSISTANTE, Role::ADMIN_SI]);
    }

    public function restore(User $user, External $external): bool
    {
        return $this->update($user, $external);
    }

    public function forceDelete(User $user, External $external): bool
    {
        return $this->update($user, $external);
    }
}
