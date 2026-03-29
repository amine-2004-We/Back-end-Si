<?php

namespace App\Policies;

use App\Models\CompagnieAssurance;
use App\Models\User;

class CompagnieAssurancePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Admin SI')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        // Allow a wider range of users to view the list
        return $user->hasAnyRole(['Admin SI', 'Superviseur', 'Formateur']);
    }

    public function view(User $user, CompagnieAssurance $compagnieAssurance): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superviseur');
    }

    public function update(User $user, CompagnieAssurance $compagnieAssurance): bool
    {
        return $user->hasRole('Superviseur');
    }

    public function delete(User $user, CompagnieAssurance $compagnieAssurance): bool
    {
        return $this->update($user, $compagnieAssurance);
    }
    
    public function massUpdate(User $user): bool
    {
        return $user->hasRole('Superviseur');
    }
}
