<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
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
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole([
            "Chargé d'Achat",
            'Responsable Achat',
            'Admin SI',
        ]);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasRole([
            "Chargé d'Achat",
            'Responsable Achat',
            'Admin SI',
        ]);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasRole([
            "Chargé d'Achat",
            'Responsable Achat',
            'Admin SI',
        ]);
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasRole(["Chargé d'Achat", 'Responsable Achat', 'Admin SI',]);
    }

    public function bulkDelete(User $user): bool
    {
        return $user->hasRole([
            "Chargé d'Achat",
            'Responsable Achat',
            'Admin SI',
        ]);
    }
}

