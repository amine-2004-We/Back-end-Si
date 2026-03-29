<?php

namespace App\Policies;

use App\Models\RequestModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RequestModelPolicy
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

    public function view(User $user, RequestModel $requestModel): bool
    {
        return true;
    }


    public function create(User $user): bool
    {
        return true;
    }
    public function update(User $user, RequestModel $requestModel): bool
    {
        return true;
    }


    public function delete(User $user, RequestModel $requestModel): bool
    {
        return $user->hasRole([
                'RH (Ressources Humaines)',
                'Admin SI',
            ]);
    }

    public function restore(User $user, RequestModel $requestModel): bool
    {
        return $user->hasRole(['RH (Ressources Humaines)', 'Admin SI',]);
    }

    public function approve(User $user, RequestModel $requestModel): bool
    {
        return $user->hasRole([
            'RH (Ressources Humaines)',
            'Admin SI',
        ]);
    }
}
