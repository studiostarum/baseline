<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordModal
{
    private const PASSWORD_TIMEOUT = 10800;

    /**
     * Handle an incoming request.
     * When password is not recently confirmed, redirects to the same URL with
     * confirm_password=1 so the page loads with a modal instead of a full-page redirect.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->passwordRecentlyConfirmed($request)) {
            return $next($request);
        }

        if ($request->has('confirm_password')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password confirmation required.'], 423);
        }

        $request->session()->put('url.intended', $request->url());

        $query = $request->query();
        $query['confirm_password'] = '1';

        return redirect()->to($request->url().'?'.http_build_query($query));
    }

    private function passwordRecentlyConfirmed(Request $request): bool
    {
        $confirmedAt = $request->session()->get('auth.password_confirmed_at', 0);

        return (Date::now()->unix() - $confirmedAt) < self::PASSWORD_TIMEOUT;
    }
}
