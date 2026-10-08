<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistryStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(['admin', 'staff', 'veterinarian'])) {
            abort(403, 'You do not have access to the registry dashboard.');
        }

        return $next($request);
    }
}
