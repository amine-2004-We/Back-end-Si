<?php

namespace App\Policies;

use App\Models\ProgrammePedagogique;
use App\Models\User;

class ProgrammePedagogiquePolicy
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
        return $user->hasRole([
            'Responsable Opérationnel',
            'Responsable pédagogique',
            'Superviseur',
            'Admin SI',
            'Formateur et accompagnateur',
            'Educateur/éducatrice',
        ]);
    }

    public function view(User $user, ProgrammePedagogique $programme): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole([
            'Responsable Opérationnel',
            'Responsable pédagogique',
            'Admin SI',
        ]);
    }

    public function update(User $user, ProgrammePedagogique $programme): bool
    {
        return $user->hasRole([
            'Responsable Pédagogique National',
            'Responsable pédagogique',
            'Admin SI',
        ]);
    }

    public function delete(User $user, ProgrammePedagogique $programme): bool
    {
        return $user->hasRole([
            'Responsable Pédagogique National',
            'Responsable pédagogique',
            'Admin SI',
        ]);
    }

    public function restore(User $user, ProgrammePedagogique $programme): bool
    {
        return $user->hasRole(['Responsable RH', 'Admin SI']);
    }
}
