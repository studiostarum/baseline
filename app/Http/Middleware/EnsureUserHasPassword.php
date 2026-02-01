<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPassword
{
    /**
     * Redirect to profile with error if the user has no password set.
     * Used before password.confirm on unlink so OAuth-only users cannot reach the confirm page.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && empty(($user->fresh() ?? $user)->password)) {
            return redirect()->route('profile.edit')
                ->with('error', 'You must set a password before you can unlink a social account. Go to Password settings to set one.');
        }

        return $next($request);
    }
}
