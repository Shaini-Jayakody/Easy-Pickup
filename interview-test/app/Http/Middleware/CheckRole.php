<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get user role
        $userRole = Auth::user()->role;

        // Check if user role is in allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If not authorized, redirect to home with error message
        return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
    }
}