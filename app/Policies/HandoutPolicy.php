<?php

namespace App\Policies;

use App\Models\Handout;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HandoutPolicy
{
    /**
     * Determine whether the user can update the handout.
     */
    public function update(User $user, Handout $handout): Response
    {
        return $this->allowOwner($user, $handout);
    }

    /**
     * Determine whether the user can delete the handout.
     */
    public function delete(User $user, Handout $handout): Response
    {
        return $this->allowOwner($user, $handout);
    }

    /**
     * Allow only the owner and hide the handout's existence from everyone else.
     */
    private function allowOwner(User $user, Handout $handout): Response
    {
        return $handout->user()->is($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
