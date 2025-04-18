<?php

namespace App\Policies;

use App\Models\BusinessStructureType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessStructureTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // All authenticated users can view their own types list
        return true;
    }

    public function view(User $user, BusinessStructureType $businessStructureType): bool
    {
        // Users can only view business structure types they're associated with
        return $user->types->contains($businessStructureType);
    }

    public function create(User $user): bool
    {
        // All authenticated users can create their own types
        return true;
    }

    /**
     * Determine whether the user can update the business structure type.
     */
    public function update(User $user, BusinessStructureType $businessStructureType): bool
    {
        // Users can only update business structure types they're associated with
//        Log::debug($user->types->contains($businessStructureType));
        return $user->types->contains($businessStructureType);

    }

    /**
     * Determine whether the user can delete the business structure type.
     */
    public function delete(User $user, BusinessStructureType $businessStructureType): bool
    {
        // Users can only delete business structure types they're associated with
        // You might want to add additional checks here, like preventing deletion if there are nodes attached
        return $user->types->contains($businessStructureType);
    }

}
