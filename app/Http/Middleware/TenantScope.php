<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Multi-tenant scoping middleware.
 * Ensures users only access data within their tenant boundary.
 *
 * - Trainers: see only their own clients and programs
 * - Facility Owners: see all trainers/clients in their facility
 * - Clients: see only their subscribed trainers' content
 * - Admins: see everything (no scope applied)
 */
class TenantScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Store tenant context for use in controllers and model scopes
        app()->instance('tenant.user', $user);
        app()->instance('tenant.role', $user->user_role);
        app()->instance('tenant.facility_id', $user->facility_id);

        // Set view share so Blade templates know the context
        view()->share('tenantUser', $user);
        view()->share('tenantRole', $user->user_role);

        return $next($request);
    }
}
