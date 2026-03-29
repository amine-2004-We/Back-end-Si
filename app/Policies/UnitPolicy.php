<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UnitPolicy
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
            Role::IT,
            Role::RESPONSABLE_REGIONAL,
            Role::RESPONSABLE_NATIONAL,
            Role::RESPONSABLE_OPERATIONNEL,
            Role::RESPONSABLE_OPERATIONNEL_NATIONAL,
            Role::RESPONSABLE_OPERATIONNEL_REGIONAL,
            Role::RESPONSABLE_OPERATIONNEL_LOCAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Unit $unit): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * "Créer": "Responsable régional, Responsable opérationnel / Assistant administratif, chef de projet"
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::IT,
            Role::RESPONSABLE_REGIONAL,
            Role::RESPONSABLE_NATIONAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * "Modifier": "Responsable régional / Responsable opérationnel national"
     */
    public function update(User $user, Unit $unit): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_NATIONAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * "Valider": "Responsable opérationnel national"
     */
    public function validateUnit(User $user, Unit $unit): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_OPERATIONNEL_NATIONAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with the 'update' permission as a safe default.
     */
    public function delete(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }

    /**
     * Determine whether user can perform bulk actions.
     * Aligns with the 'update' permission.
     */
    public function massUpdate(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_NATIONAL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }
}
