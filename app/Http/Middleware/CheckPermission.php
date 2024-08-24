<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BasController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class CheckPermission extends BasController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::user()->can(Route::current()->getName())) {
            return $this->sendError('Authorization Error.', "You are not authorize", 403);
        }
        return $next($request);
    }
}

