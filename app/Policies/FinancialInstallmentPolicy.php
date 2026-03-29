<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\User;
use App\Models\FinancialInstallment;

class FinancialInstallmentPolicy
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
     * Consulter : Finance, Partenariat
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::FINANCE,
            Role::RESPONSABLE_PARTENARIAT,
            Role::CHARGE_DE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
            Role::CHEF_DE_PROJET,
            Role::DIRECTION_GENERALE,
            Role::DAF,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FinancialInstallment $installment): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     * Créer : Responsable Projet
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            Role::CHEF_DE_PROJET,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     * Modifier : Responsable Partenariat
     */
    public function update(User $user, FinancialInstallment $installment): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinancialInstallment $installment): bool
    {
        return $user->hasAnyRole([
            Role::RESPONSABLE_PARTENARIAT,
            Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
            Role::CHEF_DE_PROJET,
        ]);
    }

    /**
     * Determine whether the user can validate the installment.
     */
    public function validate(User $user, FinancialInstallment $installment): bool
    {
        if ($user->hasRole(Role::DIRECTION_GENERALE)) {
            return true;
        }

        if ($installment->status === \App\Enums\InstallmentStatus::Planned) {
            return $user->hasRole(Role::DIRECTION_GENERALE);
        }

        if ($installment->status === \App\Enums\InstallmentStatus::Pending) {
            return $user->hasAnyRole([
                Role::CHEF_DE_PROJET,
                Role::FINANCE,
                Role::DIRECTION_FINANCIERE,
            ]);
        }

        return false;
    }
}

