<?php

namespace App\Http\Controllers\Api;

use App\Auth\SignInThrottle;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Signs the Android gate app in and out with a personal access token instead of a browser session.
 */
class GateAppSessionController extends Controller
{
    private const TOKEN_LIFETIME_DAYS = 30;

    public function store(Request $request, SignInThrottle $throttle): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        if ($seconds = $throttle->secondsUntilAllowed($request)) {
            return response()->json(['message' => "Too many sign-in attempts. Please try again in {$seconds} seconds."], 429);
        }

        if (! Auth::guard('web')->validate($request->only('username', 'password'))) {
            $throttle->recordFailure($request);

            return response()->json(['message' => 'These credentials do not match our records.'], 422);
        }

        $throttle->recordSuccess($request);

        /** @var User $user */
        $user = Auth::guard('web')->getLastAttempted();

        if (! in_array($user->role, [Role::Security, Role::Admin], true)) {
            return response()->json(['message' => 'This app is only for gate Security and the Admin.'], 403);
        }

        if ($user->must_change_password) {
            return response()->json(['message' => 'Sign in on the website first and replace your temporary password.'], 403);
        }

        $device = $credentials['device_name'] ?? 'Android';
        $token = $user->createToken("Gate app: {$device}", ['gate'], now()->addDays(self::TOKEN_LIFETIME_DAYS));

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => ['name' => $user->name, 'role' => $user->role->value],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.']);
    }
}
