<?php

namespace App\Policies;

use App\Constants\Role;
use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CabinetPolicy
{
    public function before(User $user,string $ability): ?bool
    {
        if($user->hasRole(Role::ADMIN_SI)){
            return true;
        }
        return null;
    }
    private $roles = [
        Role::ADMIN_SI,
        Role::ASSISTANTE
    ];
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::ZA
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cabinet $cabinet): bool
    {
        return $user->hasAnyRole([
            Role::ZA
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::ASSISTANTE
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cabinet $cabinet): bool
    {
        return $user->hasAnyRole([
            Role::ASSISTANTE
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cabinet $cabinet): bool
    {
        return $user->hasAnyRole([
            Role::ASSISTANTE
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cabinet $cabinet): bool
    {
        return $user->hasAnyRole([
            Role::ASSISTANTE
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cabinet $cabinet): bool
    {
        return $user->hasAnyRole([
            Role::ASSISTANTE
        ]);
    }
}
