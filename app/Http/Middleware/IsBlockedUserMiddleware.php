<?php

namespace App\Http\Middleware;

use App\Enums\ActivateStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsBlockedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->user()->status==ActivateStatusEnum::BLOCK){
            abort(403,'ليس لديك تصريح بالدخول');
        }
        return $next($request);
    }
}
