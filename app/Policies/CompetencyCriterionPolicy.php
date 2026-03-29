<?php

namespace App\Policies;

use App\Models\CompetencyCriterion;
use App\Models\User;

class CompetencyCriterionPolicy
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
        return $user->hasAnyRole(['ZA', 'Assistante', 'Resp. formation', 'Admin SI']);
    }


    public function view(User $user, CompetencyCriterion $competencyCriterion): bool
    {
        return $this->viewAny($user);
    }


    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Assistante', 'Resp. formation', 'Admin SI']);
    }


    public function update(User $user, CompetencyCriterion $competencyCriterion): bool
    {
        return $user->hasAnyRole(['Assistante', 'Resp. formation', 'Admin SI']);
    }


    public function delete(User $user, CompetencyCriterion $competencyCriterion): bool
    {
        return $this->update($user, $competencyCriterion);
    }
    
    public function massUpdate(User $user): bool
    {
        return $this->update($user, new CompetencyCriterion());
    }

    public function restore(User $user, CompetencyCriterion $competencyCriterion): bool
    {
        return $this->update($user, $competencyCriterion);
    }

    public function forceDelete(User $user, CompetencyCriterion $competencyCriterion): bool
    {
        return $this->update($user, $competencyCriterion);
    }
}
