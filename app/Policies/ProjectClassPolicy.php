<?php

namespace App\Policies;

use App\Models\ProjectClass;
use App\Models\User;

class ProjectClassPolicy
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

    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'Responsable Opérationnel',
            'Responsable pédagogique',
            'Responsable Opérationnel National',
            'Responsable pédagogique national',
            'Superviseur',
            'Directeur des Opérations',
            'Directeur de la Zakoura Academy',
            'Responsable régional',
            'Formateur et accompagnateur',
            'Facilitateur/facilitatrice',
            'Animateur/animatrice',
            'Educateur/éducatrice',
            'Assistant/assistante',
            'Chargée de Projet Digital Learning',
            'Chef de projet',
            'Responsable Opérations - Pédagogique',
            'Responsable Opérations - Pédagogique RS',
            'Superviseur RS',
            'Admin SI',
        ]);
    }

    public function view(User $user, ProjectClass $class): bool
    {
        return $user->hasRole([
            'Responsable Opérationnel',
            'Responsable pédagogique',
            'Responsable Opérationnel National',
            'Responsable pédagogique national',
            'Superviseur',
            'Directeur des Opérations',
            'Directeur de la Zakoura Academy',
            'Responsable régional',
            'Formateur et accompagnateur',
            'Facilitateur/facilitatrice',
            'Animateur/animatrice',
            'Educateur/éducatrice',
            'Assistant/assistante',
            'Chargée de Projet Digital Learning',
            'Chef de projet',
            'Responsable Opérations - Pédagogique',
            'Responsable Opérations - Pédagogique RS',
            'Superviseur RS',
            'Admin SI',
        ]);
    }
    public function create(User $user): bool
    {
        return $user->hasRole([
            'Responsable Opérationnel',
            'Responsable pédagogique',
            'Admin SI',
        ]);
    }
    public function update(User $user, ProjectClass $class): bool
    {
        return $user->hasRole([
            'Responsable Pédagogique National',
            'Responsable pédagogique',
            'Admin SI',
        ]);
    }


    public function delete(User $user,ProjectClass $class): bool
    {
        return $user->hasRole([
            'Responsable Pédagogique National',
            'Responsable pédagogique',
            'Admin SI',
        ]);
    }

    public function restore(User $user, ProjectClass $class): bool
    {
        return $user->hasRole(['Responsable RH', 'Admin SI',]);
    }

    public function approve(User $user, ProjectClass $class): bool
    {
        return $user->hasRole([
            'Responsable Pédagogique National',
            'Admin SI',
        ]);
    }
}

