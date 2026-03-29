<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\User;
use App\Models\Convention;

class ConventionPolicy
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
     * Consulter : Partenariat, Finance, DG
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::CHARGE_DE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
            Role::FINANCE,
            Role::DIRECTION_FINANCIERE,
            Role::DIRECTION_GENERALE,
            Role::CHEF_DE_PROJET,
            Role::DAF,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Convention $convention): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * Créer : Responsable Partenariat
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * Modifier : Responsable Partenariat
     */
    public function update(User $user, Convention $convention): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Convention $convention): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
        ]);
    }

    /**
     * Determine whether the user can validate the convention.
     */
    public function validate(User $user, Convention $convention): bool
    {
        if ($user->hasRole(Role::DIRECTION_GENERALE)) {
            if ($convention->status === \App\Enums\ConventionStatus::Validated) {
                return true;
            }
        }

        if ($convention->status === \App\Enums\ConventionStatus::Draft) {
            return $user->hasAnyRole([
                Role::RESPONSABLE_PARTENARIAT,
                Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
            ]);
        }

        if ($convention->status === \App\Enums\ConventionStatus::Validated) {
            return $user->hasRole(Role::DIRECTION_GENERALE);
        }

        if ($convention->status === \App\Enums\ConventionStatus::Signed) {
            return $user->hasAnyRole([
                Role::CHEF_DE_PROJET,
                Role::FINANCE,
                Role::DIRECTION_FINANCIERE,
            ]);
        }

        if ($convention->status === \App\Enums\ConventionStatus::InProgress) {
            return $user->hasAnyRole([
                Role::RESPONSABLE_PARTENARIAT,
                Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
            ]);
        }

        return false;
    }
}

