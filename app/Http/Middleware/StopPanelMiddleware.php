<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class StopPanelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('branch/*') && !Cache::get('branch_panel') || $request->is('employ/*') && !Cache::get('employee_panel') || $request->is('user/*') && !Cache::get('user_panel')) {
            abort(404, '');
        }
        return $next($request);
    }
}
