<?php

namespace App\Policies;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartementPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Departement $departement): bool
    {
        return  true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Departement $departement): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Departement $departement): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Departement $departement): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI'
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Departement $departement): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI'
        ]);
    }

    public function validate(User $user, Departement $departement): bool
    {
        return $user->hasRole([
            'Direction Générale (DG)',
            'Admin SI'
        ]);
    }
}
