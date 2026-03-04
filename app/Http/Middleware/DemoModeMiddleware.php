<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ApiResponseService;
use App\Services\ResponseService;

class DemoModeMiddleware
{
    /**
     * Handle an incoming request.
     * Block all operations when DEMO_MODE is enabled (1)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if DEMO_MODE is enabled (1 = on, 0 = off)
        $demoMode = env('DEMO_MODE', 0);
        
        // Allow login routes even in demo mode (for both admin and users)
        $allowedRoutes = [
            'login',                    // Web login
            'api/mobile-login',         // API mobile login
            'api/user-signup',          // API user signup
            'api/mobile-registration',  // API mobile registration
            'api/user-exists',          // API user exists check
            'api/mobile-reset-password', // API password reset
            'api/download-invoice',     // API download invoice
            'api/place_order'           // API place order
        ];
        
        $currentRoute = $request->path();
        $isAllowedRoute = false;
        
        foreach ($allowedRoutes as $route) {
            if ($currentRoute === $route || $request->is($route)) {
                $isAllowedRoute = true;
                break;
            }
        }
        
        // Allow cart operations (add, remove, clear, apply-promo, remove-promo)
        if (!$isAllowedRoute && $request->is('api/cart/*')) {
            $isAllowedRoute = true;
        }
        
        // Allow wishlist operations (add-update-wishlist)
        if (!$isAllowedRoute && $request->is('api/wishlist/*')) {
            $isAllowedRoute = true;
        }
        
        // Allow refund operations (request, my-refunds, check-eligibility)
        if (!$isAllowedRoute && $request->is('api/refund/*')) {
            $isAllowedRoute = true;
        }
        
        // If demo mode is on, block only write operations (POST, PUT, PATCH, DELETE)
        // Allow GET, HEAD, OPTIONS for viewing data
        // Allow login routes for both admin and users
        if ($demoMode == 1 && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && !$isAllowedRoute) {
            // Check if request is API or AJAX (expects JSON response)
            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                ApiResponseService::errorResponse('not allow any operation in demo mode');
                // This will exit, but we need to satisfy return type
                return response()->json(['error' => true, 'message' => 'not allow any operation in demo mode'], 403);
            } else {
                // For web routes, redirect back with error message
                return redirect()->back()->withErrors(['message' => 'not allow any operation in demo mode'])->send();
            }
        }
        
        return $next($request);
    }
}

