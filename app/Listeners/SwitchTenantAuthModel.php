<?php

namespace App\Listeners;

use App\Models\User;
use Stancl\Tenancy\Events\TenancyInitialized;

class SwitchTenantAuthModel
{
    /**
     * Switch the auth provider model to the tenant User so that
     * Auth::attempt() queries the tenant database instead of the
     * central Admin/mysql connection.
     */
    public function handle(TenancyInitialized $event): void
    {
        config(['auth.providers.users.model' => User::class]);
    }
}
