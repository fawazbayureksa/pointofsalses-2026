<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Handles authentication concerns for the Auth module.
 * Controllers delegate to this service – they do NOT contain auth logic.
 */
class AuthService
{
    /**
     * Attempt login, return the plain-text Sanctum token.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user        = Auth::user();
        $permissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $token       = $user->createToken('pos-token', $permissions)->plainTextToken;

        return [
            'token' => $token,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames(),
                'permissions' => $permissions,
            ],
        ];
    }

    /**
     * Revoke current token.
     */
    public function logout(User $user): void
    {
        // $user->currentAccessToken()->delete();
    }
}
