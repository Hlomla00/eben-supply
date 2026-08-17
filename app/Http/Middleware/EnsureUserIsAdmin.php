<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Registered under the 'admin' alias in bootstrap/app.php and used
     * alongside 'auth' on the /admin/* route group in routes/web.php.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'You do not have access to the admin area.');
        }

        return $next($request);
    }
}
