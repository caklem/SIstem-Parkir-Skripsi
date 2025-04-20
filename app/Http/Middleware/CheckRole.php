<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            // Redirect atau return response
            return redirect('/')->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
}
