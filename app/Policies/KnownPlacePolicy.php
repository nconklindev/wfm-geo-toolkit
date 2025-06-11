<?php

namespace App\Policies;

use App\Models\KnownPlace;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KnownPlacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * Typically, allows any authenticated user to view the index page.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Allow any authenticated user to view the list (index page).
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  KnownPlace  $knownPlace
     * @return bool
     */
    public function view(User $user, KnownPlace $knownPlace): bool
    {
        // Check if the user owns the known place
        return $user->id === $knownPlace->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * Typically, allows any authenticated user to access the create form and store data.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Allow any authenticated user to attempt creation.
        // The store method should ensure the KnownPlace is associated with the correct user.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  KnownPlace  $knownPlace
     * @return bool
     */
    public function update(User $user, KnownPlace $knownPlace): bool
    {
        // Check if the user owns the known place
        return $user->id === $knownPlace->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  KnownPlace  $knownPlace
     * @return bool
     */
    public function delete(User $user, KnownPlace $knownPlace): bool
    {
        // Check if the user owns the known place
        return $user->id === $knownPlace->user_id;
    }
}
