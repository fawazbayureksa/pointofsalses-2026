<?php

namespace App\Listeners;

use App\Models\Admin;
use Stancl\Tenancy\Events\TenancyEnded;

class RevertCentralAuthModel
{
    /**
     * Revert the auth provider model back to the central Admin model
     * when tenancy context ends.
     */
    public function handle(TenancyEnded $event): void
    {
        config(['auth.providers.users.model' => Admin::class]);
    }
}
