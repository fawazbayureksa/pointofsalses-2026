<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CashierService
{
    /**
     * Verify a cashier's PIN and return a new Sanctum token.
     */
    public function authenticateWithPin(string $email, string $pin): array
    {
        $user = User::where('email', $email)->where('is_active', true)->first();

        if (! $user || ! $user->verifyPin($pin)) {
            throw ValidationException::withMessages([
                'pin' => ['The provided PIN is incorrect.'],
            ]);
        }

        $token = $user->createToken(
            'pos-pin-token',
            $user->getPermissionsViaRoles()->pluck('name')->toArray()
        )->plainTextToken;

        return [
            'token' => $token,
            'user'  => $this->formatUser($user),
        ];
    }

    /**
     * Switch the active cashier on a shared device using PIN.
     */
    public function switchCashier(int $userId, string $pin): array
    {
        $user = User::where('id', $userId)->where('is_active', true)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'user_id' => ['Cashier not found or inactive.'],
            ]);
        }

        if (! $user->verifyPin($pin)) {
            throw ValidationException::withMessages([
                'pin' => ['The provided PIN is incorrect.'],
            ]);
        }

        $token = $user->createToken(
            'pos-switch-token',
            $user->getPermissionsViaRoles()->pluck('name')->toArray()
        )->plainTextToken;

        return [
            'token' => $token,
            'user'  => $this->formatUser($user),
        ];
    }

    /**
     * Verify a supervisor's PIN for restricted actions (refund, void, etc.).
     */
    public function verifySupervisorPin(string $pin, int $tenantId): User
    {
        $supervisors = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('pin')
            ->role(['tenant_admin', 'manager', 'super_admin'])
            ->get();

        foreach ($supervisors as $supervisor) {
            if (Hash::check($pin, $supervisor->pin)) {
                return $supervisor;
            }
        }

        throw ValidationException::withMessages([
            'pin' => ['Invalid supervisor PIN.'],
        ]);
    }

    /**
     * Start a new shift for a cashier.
     */
    public function startShift(User $user, int $outletId, float $startingCash = 0, ?string $notes = null): CashierShift
    {
        $existingShift = CashierShift::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if ($existingShift) {
            throw ValidationException::withMessages([
                'shift' => ['You already have an active shift. Please end it first.'],
            ]);
        }

        return CashierShift::create([
            'tenant_id'     => $user->tenant_id,
            'user_id'       => $user->id,
            'outlet_id'     => $outletId,
            'started_at'    => now(),
            'starting_cash' => $startingCash,
            'notes'         => $notes,
        ]);
    }

    /**
     * End the active shift for a cashier.
     */
    public function endShift(User $user, float $endingCash = 0, ?string $notes = null): CashierShift
    {
        $shift = CashierShift::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if (! $shift) {
            throw ValidationException::withMessages([
                'shift' => ['No active shift found.'],
            ]);
        }

        $endNotes = $shift->notes;
        if ($notes) {
            $endNotes = $endNotes ? "{$endNotes} | End: {$notes}" : $notes;
        }

        $shift->update([
            'ended_at'    => now(),
            'ending_cash' => $endingCash,
            'notes'       => $endNotes,
        ]);

        return $shift->refresh();
    }

    /**
     * Set or update a user's PIN.
     */
    public function setPin(User $user, string $pin): void
    {
        $user->setPin($pin);
    }

    /**
     * Format user data for API responses.
     */
    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'avatar'      => $user->avatar,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'has_pin'     => $user->hasPin(),
        ];
    }
}
