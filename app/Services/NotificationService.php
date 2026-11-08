<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public function sendToUser(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /**
     * Send a notification to all active users belonging to a tenant (optionally filtered by roles).
     *
     * @param  array<string>|null  $roles
     */
    public function sendToTenant(int $tenantId, Notification $notification, ?array $roles = null): void
    {
        $query = User::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true);

        if (! empty($roles)) {
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }

        $users = $query->get();

        if ($users->isNotEmpty()) {
            NotificationFacade::send($users, $notification);
        }
    }

    /**
     * Send a notification to all super administrators.
     */
    public function sendToSuperAdmins(Notification $notification): void
    {
        $superAdmins = User::role('super_admin')
            ->where('is_active', true)
            ->get();

        if ($superAdmins->isNotEmpty()) {
            NotificationFacade::send($superAdmins, $notification);
        }
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentFor(User $user, int $limit = 5): Collection
    {
        return $user->notifications()->latest()->take($limit)->get();
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCountFor(User $user): int
    {
        return $user->unreadNotifications()->count();
    }
}
