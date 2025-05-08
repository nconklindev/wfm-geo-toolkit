<?php

namespace App\Policies;

use App\Models\BusinessStructureNode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessStructureNodePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Allow any authenticated user to view their list (index page).
        return true;
    }

    public function view(User $user, BusinessStructureNode $businessStructureNode): bool
    {
        return $user->id === $businessStructureNode->user_id;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, BusinessStructureNode $businessStructureNode): bool
    {
    }

    public function delete(User $user, BusinessStructureNode $businessStructureNode): bool
    {
    }

    public function restore(User $user, BusinessStructureNode $businessStructureNode): bool
    {
    }

    public function forceDelete(User $user, BusinessStructureNode $businessStructureNode): bool
    {
    }
}
