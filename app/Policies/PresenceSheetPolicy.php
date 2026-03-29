<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\PresenceSheet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PresenceSheetPolicy
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
     * [cite_start]"Consulter": "Tous rôles terrain et direction des opérations" [cite: 1]
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::SUPERVISEUR,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL,
            Role::AXE_OPERATIONNEL,
            Role::RESPONSABLE_PEDAGOGIQUE_NATIONAL,
            Role::ADMIN_SI
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PresenceSheet $presenceSheet): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * [cite_start]"Créer": "Éducatrice / Animatrice / Formateur / facilitatrice" [cite: 1]
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::EDUCATRICE,
            Role::ANIMATRICE,
            Role::FACILITATRICE,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * [cite_start]"Modifier": "Superviseur/Chef de projet" [cite: 1]
     */
    public function update(User $user, PresenceSheet $presenceSheet): bool
    {
        return $user->hasAnyRole([
            Role::SUPERVISEUR,
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can validate the model.
     * [cite_start]"Valider": "Responsable opérationnel régional / Responsable Opérationnel national" [cite: 1]
     */
    public function validatePresence(User $user, PresenceSheet $presenceSheet): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PEDAGOGIQUE_LOCAL,
            Role::RESPONSABLE_OPERATIONNEL,
            Role::ADMIN_SI,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     * Aligns with modification permissions as a safe default.
     */
    public function delete(User $user, PresenceSheet $presenceSheet): bool
    {
        return $this->update($user, $presenceSheet);
    }

    /**
     * Determine whether the user can restore the model.
     * Aligns with modification permissions.
     */
    public function restore(User $user, PresenceSheet $presenceSheet): bool
    {
        return $this->update($user, $presenceSheet);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Aligns with modification permissions.
     */
    public function forceDelete(User $user, PresenceSheet $presenceSheet): bool
    {
        return $this->update($user, $presenceSheet);
    }
}
