<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Assurance;
use App\Models\User;

class AssurancePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMIN_SI)) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([Role::SUPERVISEUR, Role::RESPONSABLE_REGIONAL]);
    }

    public function view(User $user, Assurance $assurance): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        // Example role
        return $user->hasRole(Role::SUPERVISEUR);
    }

    public function update(User $user, Assurance $assurance): bool
    {
        return $user->hasRole(Role::SUPERVISEUR);
    }

    public function delete(User $user, Assurance $assurance): bool
    {
        return $this->update($user, $assurance);
    }

    public function massUpdate(User $user): bool
    {
        return $user->hasRole(Role::SUPERVISEUR);
    }

    public function restore(User $user, Assurance $assurance): bool
    {
        return $this->update($user, $assurance);
    }

    public function forceDelete(User $user, Assurance $assurance): bool
    {
        return $this->update($user, $assurance);
    }
}
