<?php

namespace App\Policies;

use App\Models\ServiceProvision;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceProvisionPolicy
{

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
    public function view(User $user, ServiceProvision $serviceProvision): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceProvision $serviceProvision): bool
    {
        return true;
    }
    
    /**
     * Determine whether the user can perform mass updates.
     */
    public function massUpdate(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceProvision $serviceProvision): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceProvision $serviceProvision): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceProvision $serviceProvision): bool
    {
        // For safety, you might want to keep this false even in development
        return true;
    }
}