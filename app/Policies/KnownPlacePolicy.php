<?php

namespace App\Policies;

use App\Models\KnownPlace;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KnownPlacePolicy
{
    use HandlesAuthorization;

    public function view(User $user, KnownPlace $knownPlace): bool
    {
        return $user->id === $knownPlace->user->id;
    }

    public function update(User $user, KnownPlace $knownPlace): bool
    {
        return $user->id === $knownPlace->user->id;
    }

    public function delete(User $user, KnownPlace $knownPlace): bool
    {
        return $user->id === $knownPlace->user->id;
    }

}
