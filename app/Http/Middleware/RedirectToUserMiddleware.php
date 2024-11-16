<?php

namespace App\Http\Middleware;

use App\Enums\LevelUserEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            if (auth()->user()->level == LevelUserEnum::BRANCH) {
                return redirect('/branch');
            } elseif (auth()->user()->level == LevelUserEnum::STAFF) {
                return redirect('/employ');
            } elseif (auth()->user()->level == LevelUserEnum::ADMIN) {
                return redirect('/admin');
            } elseif (auth()->user()->level == LevelUserEnum::USER) {
                return $next($request);
            } else {
                abort(403, 'ليس لديك صلاحية للدخول');
            }
        }


    }
}
