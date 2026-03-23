<?php

namespace App\Listeners;

use App\Models\User;

class RevertCentralAuthModel
{
    /**
     * Revert the auth provider model back to the User model.
     */
    public function handle(): void
    {
        config(['auth.providers.users.model' => User::class]);
    }
}
