<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:owner') or ->middleware('role:admin,owner').
 */
class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->user()?->role;

        $allowed = array_map(fn (string $role): UserRole => UserRole::from($role), $roles);

        abort_unless($userRole !== null && in_array($userRole, $allowed, true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
