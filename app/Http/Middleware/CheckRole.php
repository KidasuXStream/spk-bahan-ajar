<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Check if user has any of the required roles
        $hasRole = false;

        foreach ($roles as $role) {
            if ($this->userHasRole($user, $role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            abort(403, 'Access denied. You do not have the required role.');
        }

        return $next($request);
    }

    /**
     * Check if user has a specific role
     */
    protected function userHasRole($user, $roleName): bool
    {
        try {
            // Check if user has roles relationship
            if (method_exists($user, 'roles')) {
                return $user->roles()->where('name', $roleName)->exists();
            }

            // Fallback: check if user has role attribute
            if (property_exists($user, 'role') || method_exists($user, 'getRole')) {
                $userRole = $user->role ?? $user->getRole();
                return $userRole === $roleName;
            }

            // Fallback: check if user has role_name attribute
            if (property_exists($user, 'role_name')) {
                return $user->role_name === $roleName;
            }

            // For now, allow access if user is authenticated
            // TODO: Implement proper role checking
            return true;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::warning('Role checking failed for user: ' . $user->id, [
                'error' => $e->getMessage(),
                'role_requested' => $roleName
            ]);

            // For now, allow access if user is authenticated
            // TODO: Implement proper role checking
            return true;
        }
    }
}
